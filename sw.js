const CACHE = "inkriver-v25-form-polish";
const CORE = ["/", "/index.html", "/src/app.js?v=20260717-form-polish", "/src/styles.css?v=20260717-form-polish", "/manifest.webmanifest", "/src/icon.svg"];

self.addEventListener("install", (event) => {
  event.waitUntil(caches.open(CACHE).then((cache) => cache.addAll(CORE)));
  self.skipWaiting();
});

self.addEventListener("activate", (event) => {
  event.waitUntil(caches.keys().then((keys) => Promise.all(keys.filter((key) => key !== CACHE).map((key) => caches.delete(key)))));
  self.clients.claim();
});

self.addEventListener("fetch", (event) => {
  if (event.request.method !== "GET") return;
  const url = new URL(event.request.url);
  if (url.pathname.startsWith("/api/")) {
    event.respondWith(fetch(event.request));
    return;
  }
  if (event.request.mode === "navigate") {
    event.respondWith(
      fetch(event.request).then((response) => {
        const copy = response.clone();
        caches.open(CACHE).then((cache) => cache.put("/index.html", copy));
        return response;
      }).catch(() => caches.match(event.request).then((cached) => cached || caches.match("/index.html")))
    );
    return;
  }
  event.respondWith(
    caches.match(event.request).then((cached) => cached || fetch(event.request).then((response) => {
      const copy = response.clone();
      caches.open(CACHE).then((cache) => cache.put(event.request, copy));
      return response;
    }).catch(() => caches.match("/index.html")))
  );
});

self.addEventListener("sync", (event) => {
  if (event.tag === "inkriver-reader-sync") {
    event.waitUntil(flushQueue());
  }
});

function openQueueDatabase() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open("inkriver-offline", 1);
    request.onupgradeneeded = () => request.result.createObjectStore("requests", { keyPath: "id", autoIncrement: true });
    request.onsuccess = () => resolve(request.result);
    request.onerror = () => reject(request.error);
  });
}

async function queueRequest(payload) {
  const database = await openQueueDatabase();
  await new Promise((resolve, reject) => {
    const transaction = database.transaction("requests", "readwrite");
    transaction.objectStore("requests").add(payload);
    transaction.oncomplete = resolve;
    transaction.onerror = () => reject(transaction.error);
  });
}

async function flushQueue() {
  const database = await openQueueDatabase();
  const queued = await new Promise((resolve, reject) => {
    const transaction = database.transaction("requests", "readonly");
    const request = transaction.objectStore("requests").getAll();
    request.onsuccess = () => resolve(request.result);
    request.onerror = () => reject(request.error);
  });
  for (const item of queued) {
    try {
      const response = await fetch(item.url, item.options);
      if (!response.ok) continue;
      await new Promise((resolve, reject) => {
        const transaction = database.transaction("requests", "readwrite");
        transaction.objectStore("requests").delete(item.id);
        transaction.oncomplete = resolve;
        transaction.onerror = () => reject(transaction.error);
      });
    } catch {
      break;
    }
  }
}

self.addEventListener("message", (event) => {
  const message = event.data || {};
  if (message.type === "QUEUE_REQUEST") {
    event.waitUntil(queueRequest(message.payload).then(() => self.registration.sync?.register("inkriver-reader-sync")));
  }
  if (message.type === "CACHE_ARTICLE" && message.url) {
    event.waitUntil(caches.open(CACHE).then((cache) => cache.add(message.url)));
  }
});

self.addEventListener("push", (event) => {
  const data = event.data?.json?.() || { title: "InkRiver", body: "A publication you follow has a new story." };
  event.waitUntil(self.registration.showNotification(data.title, { body: data.body, icon: "/src/icon.svg", badge: "/src/icon.svg", data: { url: data.url || "/" } }));
});

self.addEventListener("notificationclick", (event) => {
  event.notification.close();
  const url = event.notification.data?.url || "/";
  event.waitUntil(clients.matchAll({ type: "window", includeUncontrolled: true }).then((clientList) => {
    for (const client of clientList) {
      if ("focus" in client) return client.navigate(url).then(() => client.focus());
    }
    return clients.openWindow(url);
  }));
});
