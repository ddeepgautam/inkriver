const defaultCategories = [
  { id: "category-ai", name: "AI", slug: "ai", description: "Artificial intelligence, tools, workflows, and practical adoption.", color: "blue", seoTitle: "AI Articles and Insights", metaDescription: "Explore practical AI workflows, tools, research, and publishing insights." },
  { id: "category-startups", name: "Startups", slug: "startups", description: "Company building, independent publishing, and sustainable growth.", color: "rose", seoTitle: "Startup Ideas and Growth", metaDescription: "Read startup strategy, growth, company-building, and founder insights." },
  { id: "category-marketing", name: "Marketing", slug: "marketing", description: "Editorial strategy, audience growth, positioning, and distribution.", color: "mint", seoTitle: "Marketing Strategy and Editorial Growth", metaDescription: "Discover marketing strategy, audience growth, positioning, and editorial distribution." },
  { id: "category-design", name: "Design", slug: "design", description: "Product design, user experience, systems, and creative operations.", color: "amber", seoTitle: "Design and User Experience", metaDescription: "Explore product design, user experience, systems thinking, and creative operations." },
  { id: "category-india", name: "India", slug: "india", description: "Ideas, businesses, culture, and digital publishing across India.", color: "mint", seoTitle: "India Stories and Ideas", metaDescription: "Read stories about Indian business, culture, technology, and publishing." },
  { id: "category-money", name: "Money", slug: "money", description: "Personal finance, creator economics, business models, and revenue.", color: "blue", seoTitle: "Money and Creator Economics", metaDescription: "Learn about personal finance, creator economics, business models, and revenue." },
  { id: "category-culture", name: "Culture", slug: "culture", description: "Media, communities, habits, and the ideas shaping modern life.", color: "rose", seoTitle: "Culture, Media, and Communities", metaDescription: "Explore media, communities, habits, and ideas shaping modern culture." },
];

const defaultStories = [
  {
    slug: "inside-the-new-reader-economy",
    title: "Inside the new reader economy",
    dek: "How paid communities, editorial trust, and slower feeds are changing digital publishing.",
    author: "Meera Iyer",
    role: "Editor, Market Notes",
    topic: "Marketing",
    readTime: "8 min read",
    claps: 1840,
    comments: 64,
    premium: true,
    color: "mint",
    revenue: 18450,
    reads: 21800,
    tags: ["publishing", "memberships", "audience", "strategy"],
  },
  {
    slug: "building-a-practical-ai-content-desk",
    title: "Building a practical AI content desk",
    dek: "A workflow for research, drafts, edits, audits, and brand voice without losing editorial judgment.",
    author: "Arjun Rao",
    role: "Product Writer",
    topic: "AI",
    readTime: "6 min read",
    claps: 920,
    comments: 31,
    premium: false,
    color: "blue",
    revenue: 7200,
    reads: 14600,
    tags: ["artificial intelligence", "workflow", "content", "productivity"],
  },
  {
    slug: "why-small-publications-win-loyalty",
    title: "Why small publications win loyalty",
    dek: "Niche editorial rooms are beating generic feeds with voice, cadence, and useful curation.",
    author: "Naina Kapoor",
    role: "Publication Owner",
    topic: "Startups",
    readTime: "5 min read",
    claps: 1320,
    comments: 42,
    premium: true,
    color: "rose",
    revenue: 12980,
    reads: 17400,
    tags: ["community", "publishing", "loyalty", "growth"],
  },
  {
    slug: "the-clean-dashboard-test",
    title: "The clean dashboard test",
    dek: "The fastest admin panels are not the ones with fewer features. They are the ones with fewer surprises.",
    author: "Dev Shah",
    role: "UX Engineer",
    topic: "Design",
    readTime: "7 min read",
    claps: 760,
    comments: 18,
    premium: false,
    color: "amber",
    revenue: 4200,
    reads: 9800,
    tags: ["user experience", "dashboards", "product design", "systems"],
  },
  {
    slug: "the-indian-creator-business-stack",
    title: "The Indian creator business stack",
    dek: "A practical map of memberships, UPI payments, newsletters, communities, and sustainable audience revenue.",
    author: "Kavya Menon",
    role: "Creator Economy Analyst",
    topic: "India",
    readTime: "7 min read",
    claps: 1160,
    comments: 38,
    premium: false,
    color: "mint",
    revenue: 9400,
    reads: 15800,
    tags: ["india", "creators", "payments", "business"],
  },
  {
    slug: "a-calm-system-for-personal-finance",
    title: "A calm system for personal finance",
    dek: "How to replace scattered money habits with a simple monthly operating system that is easy to maintain.",
    author: "Rohan Sen",
    role: "Independent Advisor",
    topic: "Money",
    readTime: "9 min read",
    claps: 1480,
    comments: 55,
    premium: true,
    color: "blue",
    revenue: 16700,
    reads: 19600,
    tags: ["personal finance", "habits", "planning", "money"],
  },
  {
    slug: "what-communities-remember",
    title: "What communities remember",
    dek: "The rituals, language, and shared references that turn an audience into a culture people want to revisit.",
    author: "Zoya Khan",
    role: "Culture Researcher",
    topic: "Culture",
    readTime: "6 min read",
    claps: 880,
    comments: 29,
    premium: false,
    color: "rose",
    revenue: 6100,
    reads: 12100,
    tags: ["community", "media", "culture", "identity"],
  },
  {
    slug: "designing-ai-products-people-trust",
    title: "Designing AI products people trust",
    dek: "Useful patterns for confidence, control, explanations, and graceful failure in AI-assisted experiences.",
    author: "Leena Bose",
    role: "Product Designer",
    topic: "Design",
    readTime: "8 min read",
    claps: 1730,
    comments: 47,
    premium: true,
    color: "amber",
    revenue: 15100,
    reads: 20400,
    tags: ["artificial intelligence", "trust", "user experience", "product design"],
  },
  {
    slug: "the-seo-brief-that-writers-use",
    title: "The SEO brief writers actually use",
    dek: "A compact research format that balances search intent, editorial voice, evidence, and a genuinely useful angle.",
    author: "Ishita Verma",
    role: "Editorial Strategist",
    topic: "Marketing",
    readTime: "5 min read",
    claps: 1040,
    comments: 24,
    premium: false,
    color: "mint",
    revenue: 7900,
    reads: 13900,
    tags: ["seo", "content strategy", "writing", "research"],
  },
  {
    slug: "from-side-project-to-small-company",
    title: "From side project to small company",
    dek: "The operating decisions that matter when a useful weekend project begins attracting customers and expectations.",
    author: "Kabir Malhotra",
    role: "Founder",
    topic: "Startups",
    readTime: "7 min read",
    claps: 1260,
    comments: 41,
    premium: true,
    color: "rose",
    revenue: 13700,
    reads: 16900,
    tags: ["founders", "operations", "growth", "business"],
  },
  {
    slug: "small-language-models-at-work",
    title: "Where small language models fit at work",
    dek: "Why focused models can be faster, cheaper, and easier to govern for narrow business workflows.",
    author: "Arjun Rao",
    role: "Product Writer",
    topic: "AI",
    readTime: "6 min read",
    claps: 970,
    comments: 33,
    premium: false,
    color: "blue",
    revenue: 8300,
    reads: 15100,
    tags: ["artificial intelligence", "business", "automation", "governance"],
  },
  {
    slug: "the-new-shape-of-indian-internet-culture",
    title: "The new shape of Indian internet culture",
    dek: "Regional language creators, private communities, and new formats are changing who gets to shape online taste.",
    author: "Aditi Nair",
    role: "Media Researcher",
    topic: "India",
    readTime: "8 min read",
    claps: 1390,
    comments: 52,
    premium: false,
    color: "amber",
    revenue: 11200,
    reads: 18300,
    tags: ["india", "culture", "creators", "media"],
  },
];

const defaultStoryBody = [
  "The next generation of publishing platforms will be measured by the quality of attention they create. Readers want strong editorial signals, fast pages, clear membership value, and fewer noisy interruptions.",
  "For writers, the platform has to make the exchange visible: member read time, saves, comments, conversion bonuses, publication edits, and payout timing. For teams, moderation and advertising need to live inside the same operating room instead of scattered tools.",
  "InkRiver models those loops directly. A story can be free, ad-supported, member-only, or shared through a friend link. Staff roles can review submissions, publish newsletters, approve ads, and manage comments without touching payment settings.",
  "The result is a calm reading surface with a serious business engine behind it: subscriptions in INR, multiple payment processors, revenue analytics, and writer payouts shaped by genuine member engagement.",
];

const defaultProfiles = {
  "meera-iyer": { name: "Meera Iyer", bio: "Editor and researcher covering audience strategy, publishing economics, and sustainable media.", expertise: ["Publishing", "Memberships", "Audience strategy"], followers: 18400, badge: "Editor", website: "https://example.com/meera", social: "@meeraedits", publication: "Market Notes" },
  "arjun-rao": { name: "Arjun Rao", bio: "Product writer translating artificial intelligence and software systems into useful working practices.", expertise: ["Artificial intelligence", "Productivity", "Software"], followers: 12600, badge: "Top writer", website: "https://example.com/arjun", social: "@arjunwrites", publication: "Practical AI" },
  "naina-kapoor": { name: "Naina Kapoor", bio: "Independent publication owner writing about niche media, startups, and durable reader communities.", expertise: ["Startups", "Community", "Publishing"], followers: 9700, badge: "Publication owner", website: "https://example.com/naina", social: "@nainak", publication: "Small Rooms" },
  "dev-shah": { name: "Dev Shah", bio: "UX engineer focused on calm operational tools, accessible interfaces, and design systems.", expertise: ["Product design", "UX engineering", "Design systems"], followers: 8300, badge: "Design specialist", website: "https://example.com/dev", social: "@devdesigns", publication: "Useful Interfaces" },
  "kavya-menon": { name: "Kavya Menon", bio: "Analyst studying creator businesses, payments, and digital publishing across India.", expertise: ["Creator economy", "India", "Payments"], followers: 11200, badge: "Industry analyst", website: "https://example.com/kavya", social: "@kavyamenon", publication: "India Creator Brief" },
  "rohan-sen": { name: "Rohan Sen", bio: "Independent financial educator building simple systems for thoughtful personal finance.", expertise: ["Personal finance", "Planning", "Habits"], followers: 15100, badge: "Finance educator", website: "https://example.com/rohan", social: "@rohansen", publication: "Calm Money" },
  "zoya-khan": { name: "Zoya Khan", bio: "Culture researcher exploring media, identity, community rituals, and online behavior.", expertise: ["Culture", "Communities", "Media"], followers: 7800, badge: "Researcher", website: "https://example.com/zoya", social: "@zoyak", publication: "Shared References" },
  "leena-bose": { name: "Leena Bose", bio: "Product designer working on trustworthy AI experiences and human-centered systems.", expertise: ["AI design", "Trust", "User experience"], followers: 13300, badge: "Top designer", website: "https://example.com/leena", social: "@leenabose", publication: "Human Systems" },
  "ishita-verma": { name: "Ishita Verma", bio: "Editorial strategist helping expert-led teams create useful search-driven content.", expertise: ["SEO", "Editorial strategy", "Research"], followers: 6900, badge: "SEO specialist", website: "https://example.com/ishita", social: "@ishitaverma", publication: "Search With Taste" },
  "kabir-malhotra": { name: "Kabir Malhotra", bio: "Founder writing about the operational transition from side project to small company.", expertise: ["Founders", "Operations", "Growth"], followers: 10400, badge: "Founder", website: "https://example.com/kabir", social: "@kabirm", publication: "Small Company Notes" },
  "aditi-nair": { name: "Aditi Nair", bio: "Media researcher following regional creators and the changing shape of Indian internet culture.", expertise: ["India", "Media", "Internet culture"], followers: 8900, badge: "Media researcher", website: "https://example.com/aditi", social: "@aditinair", publication: "New Internet India" },
};

const defaultCuratedLists = [
  { id: "editors-picks", name: "Editor's Picks", description: "Strong reporting and practical ideas selected by the InkRiver editorial desk.", owner: "InkRiver Editors", followers: 32400, slugs: ["inside-the-new-reader-economy", "designing-ai-products-people-trust", "the-new-shape-of-indian-internet-culture"] },
  { id: "build-better-products", name: "Build Better Products", description: "Design, AI, operations, and product thinking for teams making useful software.", owner: "Leena Bose", followers: 11700, slugs: ["building-a-practical-ai-content-desk", "the-clean-dashboard-test", "designing-ai-products-people-trust", "small-language-models-at-work"] },
  { id: "independent-growth", name: "Independent Growth", description: "A focused collection for founders, creators, and small publications.", owner: "Naina Kapoor", followers: 9400, slugs: ["why-small-publications-win-loyalty", "the-indian-creator-business-stack", "the-seo-brief-that-writers-use", "from-side-project-to-small-company"] },
];

const defaultComments = {
  "building-a-practical-ai-content-desk": [
    { id: "comment-ai-1", parentId: "", author: "Sara Ali", role: "reader", text: "The distinction between assistance and editorial judgment is exactly right. The audit step is the part most teams skip.", likes: 18, likedBy: [], pinned: true, writerReply: false, reported: false, createdAt: "2026-06-09T09:30:00.000Z", editedAt: "" },
    { id: "comment-ai-2", parentId: "comment-ai-1", author: "Arjun Rao", role: "writer", text: "@Sara Ali absolutely. A fast draft is useful only when the review process is equally deliberate.", likes: 11, likedBy: [], pinned: false, writerReply: true, reported: false, createdAt: "2026-06-09T10:15:00.000Z", editedAt: "" },
    { id: "comment-ai-3", parentId: "", author: "Dev Shah", role: "subscriber", text: "I would love a follow-up showing how this workflow changes for a two-person content team.", likes: 7, likedBy: [], pinned: false, writerReply: false, reported: false, createdAt: "2026-06-10T07:45:00.000Z", editedAt: "" },
  ],
};

function slugifyName(value) {
  return String(value || "reader").toLowerCase().trim().replace(/[^a-z0-9]+/g, "-").replace(/^-|-$/g, "");
}

const defaultPlans = [
  {
    id: "starter",
    name: "Reader",
    price: 299,
    period: "month",
    note: "Unlimited member stories",
    features: ["Full paywall access", "Ad-light reading", "Save lists", "Audio queue"],
  },
  {
    id: "annual",
    name: "Annual Plus",
    price: 2499,
    period: "year",
    note: "Best for regular readers",
    features: ["Everything in Reader", "No reader ads", "Gift links", "Priority recommendations"],
  },
  {
    id: "patron",
    name: "Patron",
    price: 4999,
    period: "year",
    note: "Support writers directly",
    features: ["Everything in Plus", "Writer bonus pool", "Publication invites", "Early editorial drops"],
  },
];

const gateways = [
  { name: "Razorpay", type: "Cards, UPI, NetBanking" },
  { name: "Stripe", type: "Cards and wallets" },
  { name: "PayPal", type: "International wallets" },
  { name: "UPI Direct", type: "QR and intent" },
];

const paymentGateways = [
  {
    id: "razorpay",
    name: "Razorpay",
    type: "Cards, UPI, wallets",
    fields: [["razorpayKeyId", "Key ID"], ["razorpayKeySecret", "Key Secret"]],
    serverNote: "Create and verify orders on your backend before opening Checkout.",
  },
  {
    id: "paypal",
    name: "PayPal",
    type: "PayPal wallet, cards",
    fields: [["paypalClientId", "Client ID"], ["paypalSecret", "Secret"]],
    serverNote: "Use the client ID in the JS SDK and create/capture orders on your backend.",
  },
  {
    id: "payu",
    name: "PayU",
    type: "Cards, UPI, netbanking",
    fields: [["payuMerchantKey", "Merchant Key"], ["payuSalt", "Salt"]],
    serverNote: "Generate hashes with Salt on your backend. Do not expose Salt in production frontend code.",
  },
  {
    id: "cashfree",
    name: "Cashfree Payments",
    type: "Hosted checkout, UPI",
    fields: [["cashfreeClientId", "Client ID"], ["cashfreeClientSecret", "Client Secret"]],
    serverNote: "Create an order/session server-side, then load the Cashfree web checkout.",
  },
];

const supportedCurrencies = [
  { code: "INR", symbol: "INR", label: "Indian Rupee" },
  { code: "USD", symbol: "$", label: "US Dollar" },
  { code: "EUR", symbol: "€", label: "Euro" },
  { code: "GBP", symbol: "£", label: "British Pound" },
  { code: "AED", symbol: "AED", label: "UAE Dirham" },
  { code: "SGD", symbol: "S$", label: "Singapore Dollar" },
  { code: "AUD", symbol: "A$", label: "Australian Dollar" },
  { code: "CAD", symbol: "C$", label: "Canadian Dollar" },
];

const roleMap = {
  reader: ["Read free stories", "Follow topics", "Save lists"],
  subscriber: ["Unlock paywalled stories", "Ad-free mode", "Friend links"],
  writer: ["Publish stories", "Join write to earn", "View earnings"],
  moderator: ["Review reports", "Approve comments", "Manage queues"],
  admin: ["Manage users", "Subscriptions", "Ads", "Platform settings"],
};

function loadSavedSlugs() {
  try {
    const stored = JSON.parse(localStorage.getItem("inkriver-read-later") || "null");
    return Array.isArray(stored) ? stored : ["inside-the-new-reader-economy"];
  } catch {
    return ["inside-the-new-reader-economy"];
  }
}

function persistSavedSlugs() {
  localStorage.setItem("inkriver-read-later", JSON.stringify([...state.saved]));
  persistUserDocument("saved", [...state.saved]);
}

function loadCurrentUser() {
  return null;
}

function persistCurrentUser() {
  localStorage.removeItem("inkriver-current-user");
}

function recommendationProfileKey(user) {
  const identity = user?.email?.trim().toLowerCase() || "guest";
  return `inkriver-recommendation-profile:${identity}`;
}

function emptyRecommendationProfile() {
  return {
    version: 1,
    selectedInterests: [],
    topicScores: {},
    tagScores: {},
    authorScores: {},
    storyScores: {},
    hiddenStories: [],
    activity: [],
    completedOnboarding: false,
    updatedAt: "",
  };
}

function loadRecommendationProfile(user) {
  try {
    return {
      ...emptyRecommendationProfile(),
      ...JSON.parse(localStorage.getItem(recommendationProfileKey(user)) || "{}"),
    };
  } catch {
    return emptyRecommendationProfile();
  }
}

function persistRecommendationProfile() {
  state.recommendation.updatedAt = new Date().toISOString();
  localStorage.setItem(recommendationProfileKey(state.user), JSON.stringify(state.recommendation));
  persistUserDocument("recommendation-profile", state.recommendation);
}

function readerDataKey(type, user = state?.user) {
  const identity = user?.email?.trim().toLowerCase() || "guest";
  return `inkriver-${type}:${identity}`;
}

function loadReaderData(type, fallback, user = initialUser) {
  try {
    const value = JSON.parse(localStorage.getItem(readerDataKey(type, user)) || "null");
    return value && typeof value === "object" ? value : fallback;
  } catch {
    return fallback;
  }
}

function emptyFollowing() {
  return { writers: [], publications: [], topics: [], tags: [], lists: [] };
}

function persistFollowing() {
  localStorage.setItem(readerDataKey("following"), JSON.stringify(state.following));
  persistUserDocument("following", state.following);
}

function persistReadingHistory() {
  localStorage.setItem(readerDataKey("reading-history"), JSON.stringify(state.readingHistory));
  persistUserDocument("reading-history", state.readingHistory);
}

function persistPollResponses() {
  localStorage.setItem(readerDataKey("interactive-responses"), JSON.stringify(state.pollResponses));
  persistUserDocument("interactive-responses", state.pollResponses);
}

function loadComments() {
  return {};
}

function persistComments() {
  localStorage.setItem("inkriver-comments", JSON.stringify(state.commentsByStory));
}

function loadGatewaySettings() {
  return { fxApiKey: "", cashfreeEnvironment: "sandbox", googleLoginEnabled: true, facebookLoginEnabled: true, googleClientId: "", facebookAppId: "" };
}

function persistGatewaySettings() {
  persistAdminDocument("platform-settings", {
    googleLoginEnabled: state.gatewaySettings.googleLoginEnabled !== false,
    facebookLoginEnabled: state.gatewaySettings.facebookLoginEnabled !== false,
    cashfreeEnvironment: state.gatewaySettings.cashfreeEnvironment || "sandbox",
    translationLanguages: state.translationLanguages,
  });
}

function loadSubscriptionPlans() {
  return defaultPlans;
}

function persistSubscriptionPlans() {
  persistAdminDocument("plans", state.plans);
}

function loadCategories() {
  return defaultCategories;
}

function persistCategories() {
  persistAdminDocument("categories", state.categories);
}

function categoryNames() {
  return state.categories.map((category) => category.name);
}

function emptyCategoryForm() {
  return {
    id: "",
    name: "",
    slug: "",
    description: "",
    color: "mint",
    seoTitle: "",
    metaDescription: "",
  };
}

function slugifyCategory(value) {
  const base = String(value || "category").toLowerCase().trim().replace(/[^a-z0-9]+/g, "-").replace(/^-|-$/g, "");
  let slug = base || `category-${Date.now()}`;
  let counter = 2;
  while (state?.categories?.some((category) => category.slug === slug && category.id !== state.editingCategoryId)) {
    slug = `${base}-${counter}`;
    counter += 1;
  }
  return slug;
}

function populateCategoryForm(category) {
  state.editingCategoryId = category.id;
  state.categoryForm = { ...category };
  state.categoryMessage = `Editing ${category.name}`;
  render();
}

function saveCategoryFromForm() {
  const name = state.categoryForm.name.trim();
  if (!name) {
    state.categoryMessage = "Category name is required.";
    render();
    return;
  }
  const previous = state.categories.find((category) => category.id === state.editingCategoryId);
  const nextCategory = {
    id: state.editingCategoryId || `category-${Date.now()}`,
    name,
    slug: slugifyCategory(state.categoryForm.slug || name),
    description: state.categoryForm.description.trim(),
    color: state.categoryForm.color || "mint",
    seoTitle: state.categoryForm.seoTitle.trim() || `${name} Articles and Insights`,
    metaDescription: state.categoryForm.metaDescription.trim() || state.categoryForm.description.trim(),
  };
  if (previous) {
    state.categories = state.categories.map((category) => (category.id === previous.id ? nextCategory : category));
    if (previous.name !== nextCategory.name) {
      state.stories = state.stories.map((story) => (story.topic === previous.name ? { ...story, topic: nextCategory.name } : story));
      if (state.activeTopic === previous.name) state.activeTopic = nextCategory.name;
      persistBlogs();
    }
    state.categoryMessage = `${nextCategory.name} updated.`;
  } else {
    state.categories = [...state.categories, nextCategory];
    state.categoryMessage = `${nextCategory.name} created.`;
  }
  state.editingCategoryId = "";
  state.categoryForm = emptyCategoryForm();
  persistCategories();
  render();
}

function deleteCategory(categoryId) {
  if (state.categories.length <= 1) {
    state.categoryMessage = "At least one blog category is required.";
    render();
    return;
  }
  const removed = state.categories.find((category) => category.id === categoryId);
  const fallback = state.categories.find((category) => category.id !== categoryId);
  state.categories = state.categories.filter((category) => category.id !== categoryId);
  if (removed && fallback) {
    state.stories = state.stories.map((story) => (story.topic === removed.name ? { ...story, topic: fallback.name } : story));
    if (state.activeTopic === removed.name) state.activeTopic = "For you";
    if (state.blogForm.topic === removed.name) state.blogForm.topic = fallback.name;
    persistBlogs();
  }
  if (state.editingCategoryId === categoryId) {
    state.editingCategoryId = "";
    state.categoryForm = emptyCategoryForm();
  }
  state.categoryMessage = `${removed?.name || "Category"} deleted. Assigned posts moved to ${fallback?.name || "another category"}.`;
  persistCategories();
  render();
}

function defaultPostSeo(story = {}) {
  return {
    focusKeyphrase: "",
    additionalKeyphrases: "",
    seoTitle: story.title || "",
    metaDescription: story.dek || "",
    canonicalUrl: "",
    robotsIndex: true,
    robotsFollow: true,
    maxSnippet: -1,
    maxImagePreview: "large",
    maxVideoPreview: -1,
    cornerstone: false,
    schemaPageType: "WebPage",
    schemaArticleType: "Article",
    breadcrumbTitle: story.title || "",
    socialTitle: story.title || "",
    socialDescription: story.dek || "",
    socialImage: story.imageUrl || "",
  };
}

const defaultSiteSeo = {
  siteTitle: "InkRiver",
  tagline: "Ideas worth returning to.",
  titleSeparator: "-",
  homepageSeoTitle: "InkRiver - Publishing, Memberships, and Writer Earnings",
  homepageMetaDescription: "Read thoughtful stories, support writers, and join a modern publishing community.",
  representationType: "organization",
  organizationName: "InkRiver",
  alternateName: "",
  organizationLogo: "",
  personName: "",
  defaultSocialImage: "",
  facebookPage: "",
  xProfile: "",
  linkedinPage: "",
  postTitleTemplate: "%%title%% %%sep%% %%sitename%%",
  postDescriptionTemplate: "%%excerpt%%",
  categoryTitleTemplate: "%%term_title%% %%sep%% %%sitename%%",
  categoryDescriptionTemplate: "Stories filed under %%term_title%%.",
  indexPosts: true,
  indexCategories: true,
  indexTags: false,
  indexAuthorArchives: true,
  indexDateArchives: false,
  enableXmlSitemap: true,
  sitemapPosts: true,
  sitemapPages: true,
  sitemapCategories: true,
  sitemapTags: false,
  enableBreadcrumbs: true,
  breadcrumbSeparator: "/",
  breadcrumbHomeLabel: "Home",
  googleVerification: "",
  bingVerification: "",
  pinterestVerification: "",
  yandexVerification: "",
  robotsTxt: "User-agent: *\nAllow: /\n\nSitemap: https://example.com/sitemap.xml",
  rssBefore: "",
  rssAfter: "This article first appeared on %%BLOGLINK%%. Read the original: %%POSTLINK%%.",
  defaultPageSchema: "WebPage",
  defaultArticleSchema: "Article",
  enableSeoAnalysis: true,
  enableReadabilityAnalysis: true,
  enableCornerstone: true,
  enableOpenGraph: true,
  enableSchema: true,
  enableLlmsTxt: false,
};

function loadSiteSeo() {
  return { ...defaultSiteSeo };
}

function persistSiteSeo() {
  persistAdminDocument("site-seo", state.siteSeo);
}

function normalizeStory(story, index = 0) {
  const body = Array.isArray(story.body) && story.body.length ? story.body : defaultStoryBody;
  const generatedTags = `${story.title || ""} ${story.dek || ""}`
    .toLowerCase()
    .split(/[^a-z0-9]+/)
    .filter((word) => word.length > 4)
    .slice(0, 5);
  return {
    ...story,
    claps: 0,
    comments: 0,
    reads: 0,
    revenue: 0,
    id: story.id || `blog-${index + 1}`,
    status: story.status || "published",
    imageUrl: story.imageUrl || "",
    tags: Array.isArray(story.tags) && story.tags.length ? story.tags : generatedTags,
    publication: story.publication || defaultProfiles[slugifyName(story.author)]?.publication || story.role || "InkRiver",
    publishedAt: story.publishedAt || new Date(Date.UTC(2026, 4, Math.max(1, 28 - index))).toISOString(),
    interactiveBlocks: Array.isArray(story.interactiveBlocks) && story.interactiveBlocks.length
      ? story.interactiveBlocks
      : defaultInteractiveBlocks(story.slug),
    body,
    contentHtml: story.contentHtml || body.map((paragraph) => `<p>${escapeHtml(paragraph)}</p>`).join(""),
    seo: { ...defaultPostSeo(story), ...(story.seo || {}) },
  };
}

function defaultInteractiveBlocks(slug) {
  if (slug === "building-a-practical-ai-content-desk") {
    return [{ id: "poll-ai-workflow", type: "poll", question: "Where would AI help your editorial workflow most?", options: ["Research", "First drafts", "Editing", "Distribution"], multiple: false }];
  }
  if (slug === "the-clean-dashboard-test") {
    return [{ id: "quiz-dashboard", type: "quiz", question: "Which change usually improves an operational dashboard first?", options: ["More charts", "Clearer hierarchy", "More colors", "More navigation"], correctIndex: 1, explanation: "Clear hierarchy reduces scanning effort before additional data or controls are introduced." }];
  }
  if (slug === "the-seo-brief-that-writers-use") {
    return [{ id: "survey-reader-value", type: "survey", question: "What makes a paid publication worth renewing?", options: ["Original reporting", "Practical guidance", "Community access", "Supporting writers"], multiple: true }];
  }
  return [];
}

function loadBlogs() {
  return defaultStories.map(normalizeStory);
}

function persistBlogs() {
  persistAdminDocument("stories", state.stories);
}

async function uploadBlogImage(file) {
  if (!file) return;
  state.mediaMessage = "Uploading image...";
  render();
  try {
    const formData = new FormData();
    formData.append("file", file);
    formData.append("altText", state.blogForm.title || file.name);
    const response = await fetch("/api/admin/media", {
      method: "POST",
      body: formData,
      credentials: "same-origin",
    });
    const payload = await response.json().catch(() => ({}));
    if (!response.ok) throw new Error(payload.message || "Image upload failed.");
    state.mediaAssets = [payload.asset, ...state.mediaAssets.filter((asset) => asset.id !== payload.asset.id)];
    state.blogForm.imageUrl = payload.asset.url;
    state.blogForm.seo.socialImage = state.blogForm.seo.socialImage || payload.asset.url;
    state.mediaMessage = "Image uploaded and attached to this post.";
  } catch (error) {
    state.mediaMessage = error.message;
  }
  render();
}

function publishedStories() {
  return state.stories.filter((story) => story.status === "published");
}

function emptyBlogForm() {
  return {
    id: "",
    title: "",
    slug: "",
    dek: "",
    author: "",
    role: "",
    topic: "Marketing",
    readTime: "5 min read",
    premium: false,
    color: "mint",
    imageUrl: "",
    tagsText: "",
    publication: "InkRiver",
    interactiveBlocks: [],
    body: defaultStoryBody.join("\n\n"),
    contentHtml: defaultStoryBody.map((paragraph) => `<p>${escapeHtml(paragraph)}</p>`).join(""),
    seo: defaultPostSeo(),
  };
}

function slugifyBlogSlug(title) {
  const base = (title || "story").toLowerCase().trim().replace(/[^a-z0-9]+/g, "-").replace(/^-|-$/g, "");
  let slug = base || `story-${Date.now()}`;
  let counter = 2;
  while (state?.stories?.some((story) => story.slug === slug && story.id !== state.editingBlogId)) {
    slug = `${base}-${counter}`;
    counter += 1;
  }
  return slug;
}

function populateBlogForm(story) {
  state.editingBlogId = story.id;
  state.blogForm = {
    ...story,
    body: story.body.join("\n\n"),
    contentHtml: story.contentHtml || story.body.map((paragraph) => `<p>${escapeHtml(paragraph)}</p>`).join(""),
    tagsText: (story.tags || []).join(", "),
    publication: story.publication || story.role || "InkRiver",
    interactiveBlocks: structuredClone(story.interactiveBlocks || []),
    seo: { ...defaultPostSeo(story), ...(story.seo || {}) },
  };
  state.blogMessage = `Editing ${story.title}`;
  setRoute(`/admin/blogs/edit/${story.id}`);
}

function saveBlogFromForm(status = "published") {
  const title = state.blogForm.title.trim();
  if (!title) {
    state.blogMessage = "Blog title is required.";
    render();
    return;
  }
  const nextSlug = slugifyBlogSlug(state.blogForm.slug || title);
  const contentHtml = sanitizeRichHtml(state.blogForm.contentHtml || "");
  const body = richTextParagraphs(contentHtml);
  const previous = state.stories.find((story) => story.id === state.editingBlogId);
  const nextBlog = {
    id: state.editingBlogId || `blog-${Date.now()}`,
    slug: nextSlug,
    title,
    dek: state.blogForm.dek.trim() || "A concise editorial summary for this story.",
    author: state.blogForm.author.trim() || "InkRiver Editor",
    role: state.blogForm.role.trim() || "Editorial desk",
    topic: state.blogForm.topic || "Marketing",
    tags: state.blogForm.tagsText ? state.blogForm.tagsText.split(",").map((tag) => tag.trim()).filter(Boolean) : (previous?.tags || []),
    publication: state.blogForm.publication.trim() || "InkRiver",
    publishedAt: previous?.publishedAt || new Date().toISOString(),
    interactiveBlocks: state.blogForm.interactiveBlocks || [],
    readTime: state.blogForm.readTime.trim() || "5 min read",
    claps: previous?.claps || 0,
    comments: previous?.comments || 0,
    premium: Boolean(state.blogForm.premium),
    color: state.blogForm.color || "mint",
    revenue: previous?.revenue || 0,
    reads: previous?.reads || 0,
    status,
    imageUrl: state.blogForm.imageUrl.trim(),
    body: body.length ? body : defaultStoryBody,
    contentHtml: contentHtml || defaultStoryBody.map((paragraph) => `<p>${escapeHtml(paragraph)}</p>`).join(""),
    seo: {
      ...defaultPostSeo({ title, dek: state.blogForm.dek, imageUrl: state.blogForm.imageUrl }),
      ...state.blogForm.seo,
      seoTitle: state.blogForm.seo.seoTitle.trim() || title,
      metaDescription: state.blogForm.seo.metaDescription.trim() || state.blogForm.dek.trim(),
      breadcrumbTitle: state.blogForm.seo.breadcrumbTitle.trim() || title,
      socialTitle: state.blogForm.seo.socialTitle.trim() || title,
      socialDescription: state.blogForm.seo.socialDescription.trim() || state.blogForm.dek.trim(),
    },
  };
  if (previous && previous.slug !== nextBlog.slug) {
    state.claps[nextBlog.slug] = state.claps[previous.slug] || previous.claps || 0;
    delete state.claps[previous.slug];
    if (state.saved.has(previous.slug)) {
      state.saved.delete(previous.slug);
      state.saved.add(nextBlog.slug);
      persistSavedSlugs();
    }
  } else {
    state.claps[nextBlog.slug] = state.claps[nextBlog.slug] || nextBlog.claps || 0;
  }
  if (state.editingBlogId) {
    state.stories = state.stories.map((story) => (story.id === state.editingBlogId ? nextBlog : story));
    state.blogMessage = `${nextBlog.title} saved as ${status}.`;
  } else {
    state.stories = [nextBlog, ...state.stories];
    state.blogMessage = `${nextBlog.title} created as ${status}.`;
  }
  state.editingBlogId = "";
  state.blogForm = emptyBlogForm();
  persistBlogs();
  setRoute("/admin/blogs");
}

function deleteBlog(blogId) {
  const removed = state.stories.find((story) => story.id === blogId);
  state.stories = state.stories.filter((story) => story.id !== blogId);
  if (removed) {
    delete state.claps[removed.slug];
    state.saved.delete(removed.slug);
    persistSavedSlugs();
  }
  if (state.editingBlogId === blogId) {
    state.editingBlogId = "";
    state.blogForm = emptyBlogForm();
  }
  state.blogMessage = `${removed?.title || "Blog"} deleted.`;
  persistBlogs();
  setRoute("/admin/blogs");
}

async function saveWriterStory(status) {
  if (!state.user || !["writer", "admin"].includes(state.user.role)) {
    state.draftMessage = "A writer or administrator account is required to publish.";
    render();
    return;
  }
  try {
    const paragraphs = state.draft.body.split(/\n{2,}/).map((paragraph) => paragraph.trim()).filter(Boolean);
    const payload = await apiRequest("/api/stories", {
      method: "POST",
      body: JSON.stringify({
        title: state.draft.title,
        dek: state.draft.subtitle,
        topic: state.draft.topic,
        premium: state.draft.paywalled,
        ads: state.draft.ads,
        earning: state.draft.earning,
        status,
        body: paragraphs,
        contentHtml: paragraphs.map((paragraph) => `<p>${escapeHtml(paragraph)}</p>`).join(""),
      }),
    });
    state.stories = [normalizeStory(payload.story), ...state.stories.filter((story) => story.id !== payload.story.id)];
    state.claps[payload.story.slug] = 0;
    state.draftMessage = `${payload.story.title} saved as ${status}.`;
    if (status === "published") setRoute(`/stories/${payload.story.slug}`);
    else render();
  } catch (error) {
    state.draftMessage = error.message;
    render();
  }
}

function setBlogStatus(blogId, status) {
  const blog = state.stories.find((story) => story.id === blogId);
  state.stories = state.stories.map((story) => (story.id === blogId ? { ...story, status } : story));
  state.blogMessage = `${blog?.title || "Blog"} moved to ${status}.`;
  persistBlogs();
  render();
}

const initialStories = loadBlogs();
const initialCategories = loadCategories();
const initialUser = null;
const initialRecommendation = loadRecommendationProfile(initialUser);
const initialFollowing = loadReaderData("following", emptyFollowing(), initialUser);
const initialReadingHistory = loadReaderData("reading-history", [], initialUser);
const initialPollResponses = loadReaderData("interactive-responses", {}, initialUser);

function emptyPlanForm() {
  return {
    id: "",
    name: "",
    price: 299,
    period: "month",
    note: "",
    features: "",
  };
}

function slugifyPlanId(name) {
  const base = (name || "plan").toLowerCase().trim().replace(/[^a-z0-9]+/g, "-").replace(/^-|-$/g, "");
  let slug = base || `plan-${Date.now()}`;
  let counter = 2;
  while (state?.plans?.some((plan) => plan.id === slug && plan.id !== state.editingPlanId)) {
    slug = `${base}-${counter}`;
    counter += 1;
  }
  return slug;
}

function populatePlanForm(plan) {
  state.editingPlanId = plan.id;
  state.planForm = {
    ...plan,
    features: plan.features.join("\n"),
  };
  state.planMessage = `Editing ${plan.name}`;
  render();
}

function savePlanFromForm() {
  const name = state.planForm.name.trim();
  const price = Number(state.planForm.price);
  if (!name || !Number.isFinite(price) || price <= 0) {
    state.planMessage = "Plan name and valid INR price are required.";
    render();
    return;
  }
  const nextPlan = {
    id: state.editingPlanId || slugifyPlanId(name),
    name,
    price,
    period: state.planForm.period || "month",
    note: state.planForm.note.trim() || "Custom membership package",
    features: state.planForm.features
      .split("\n")
      .map((feature) => feature.trim())
      .filter(Boolean),
  };
  if (!nextPlan.features.length) nextPlan.features = ["Member-only access"];
  if (state.editingPlanId) {
    state.plans = state.plans.map((plan) => (plan.id === state.editingPlanId ? nextPlan : plan));
    state.planMessage = `${nextPlan.name} updated.`;
  } else {
    state.plans = [...state.plans, nextPlan];
    state.planMessage = `${nextPlan.name} created.`;
  }
  state.editingPlanId = "";
  state.planForm = emptyPlanForm();
  persistSubscriptionPlans();
  render();
}

function deletePlan(planId) {
  if (state.plans.length <= 1) {
    state.planMessage = "At least one subscription package is required.";
    render();
    return;
  }
  const removed = state.plans.find((plan) => plan.id === planId);
  state.plans = state.plans.filter((plan) => plan.id !== planId);
  if (state.editingPlanId === planId) {
    state.editingPlanId = "";
    state.planForm = emptyPlanForm();
  }
  if (state.checkoutPlan?.id === planId) state.checkoutPlan = null;
  state.planMessage = `${removed?.name || "Package"} deleted.`;
  persistSubscriptionPlans();
  render();
}

function filteredUsers() {
  const query = state.userSearch.trim().toLowerCase();
  if (!query) return state.users;
  return state.users.filter((user) => [user.id, user.name, user.email].some((value) => value.toLowerCase().includes(query)));
}

function subscriptionOptions() {
  return ["Free", "Staff", ...state.plans.map((plan) => plan.name)];
}

async function apiRequest(path, options = {}) {
  const response = await fetch(path, {
    credentials: "same-origin",
    cache: "no-store",
    headers: { "Content-Type": "application/json", ...(options.headers || {}) },
    ...options,
  });
  const payload = await response.json().catch(() => ({}));
  if (!response.ok) throw new Error(payload.message || "The request could not be completed.");
  return payload;
}

const pendingDocumentSyncs = new Map();

function persistUserDocument(key, value) {
  if (!state?.user) return;
  window.clearTimeout(pendingDocumentSyncs.get(`user:${key}`));
  pendingDocumentSyncs.set(`user:${key}`, window.setTimeout(() => {
    apiRequest(`/api/me/documents/${encodeURIComponent(key)}`, {
      method: "PUT",
      body: JSON.stringify({ value }),
    }).catch((error) => {
      state.userMessage = `Could not sync ${key}: ${error.message}`;
    });
  }, 250));
}

function persistAdminDocument(key, value) {
  if (state?.user?.role !== "admin") return;
  window.clearTimeout(pendingDocumentSyncs.get(`admin:${key}`));
  pendingDocumentSyncs.set(`admin:${key}`, window.setTimeout(() => {
    apiRequest(`/api/admin/documents/${encodeURIComponent(key)}`, {
      method: "PUT",
      body: JSON.stringify({ value }),
    }).catch((error) => {
      state.userMessage = `Could not save ${key}: ${error.message}`;
    });
  }, 250));
}

function recordServerEvent(type, storySlug = "", value = null, metadata = {}) {
  const body = JSON.stringify({ type, storySlug, value, metadata });
  apiRequest("/api/events", {
    method: "POST",
    body,
  }).catch(() => {
    navigator.serviceWorker?.controller?.postMessage({
      type: "QUEUE_REQUEST",
      payload: {
        url: "/api/events",
        options: { method: "POST", headers: { "Content-Type": "application/json" }, body, credentials: "same-origin" },
      },
    });
  });
}

async function hydratePlatformState() {
  const payload = await apiRequest(`/api/platform/bootstrap?fresh=${Date.now()}`);
  const documents = payload.documents || {};
  const userDocuments = payload.userDocuments || {};
  if (Array.isArray(documents.stories) && documents.stories.length) {
    state.stories = documents.stories.map((story, index) => ({
      ...normalizeStory(story, index),
      ...(payload.storyStats?.[story.slug] || {}),
    }));
    state.claps = Object.fromEntries(state.stories.map((story) => [story.slug, story.claps || 0]));
  }
  if (Array.isArray(documents.categories) && documents.categories.length) state.categories = documents.categories;
  if (Array.isArray(documents.plans) && documents.plans.length) state.plans = documents.plans;
  state.translations = payload.translations || {};
  if (Array.isArray(payload.translationLanguages)) state.translationLanguages = payload.translationLanguages;
  if (documents["site-seo-public"]) state.siteSeo = { ...state.siteSeo, ...documents["site-seo-public"] };
  if (documents["site-seo"]) state.siteSeo = { ...state.siteSeo, ...documents["site-seo"] };
  if (documents["creator-tools"]) {
    state.creatorTools = { ...state.creatorTools, ...documents["creator-tools"] };
    state.creatorTools.segments = (state.creatorTools.segments || []).map((segment) => ({ ...segment, size: 0 }));
  }
  if (documents.operations) state.operations = { ...state.operations, ...documents.operations };
  if (documents["platform-settings"]) state.gatewaySettings = { ...state.gatewaySettings, ...documents["platform-settings"] };
  if (Array.isArray(documents["platform-settings"]?.translationLanguages)) state.translationLanguages = documents["platform-settings"].translationLanguages;
  if (state.user) {
    if (Array.isArray(userDocuments.saved)) state.saved = new Set(userDocuments.saved);
    if (userDocuments.following) state.following = { ...emptyFollowing(), ...userDocuments.following };
    if (Array.isArray(userDocuments["reading-history"])) state.readingHistory = userDocuments["reading-history"];
    if (userDocuments["interactive-responses"]) state.pollResponses = userDocuments["interactive-responses"];
    if (userDocuments["recommendation-profile"]) {
      state.recommendation = { ...emptyRecommendationProfile(), ...userDocuments["recommendation-profile"] };
      state.onboardingSelection = new Set(state.recommendation.selectedInterests);
    }
    if (userDocuments.preferences) state.preferences = { ...state.preferences, ...userDocuments.preferences };
    await loadSecuritySessions();
    await loadSecuritySettings();
    await loadSupportTickets();
    if (["writer", "admin"].includes(state.user.role)) {
      await loadCreatorAnalytics();
      await loadWriterPayouts();
    }
  }
  state.providerStatus = payload.providers || state.providerStatus;
  state.pushPublicKey = payload.pushPublicKey || "";
  state.socialAccounts = payload.socialAccounts || [];
  state.adCampaigns = payload.ads || state.adCampaigns;

  if (["moderator", "admin"].includes(state.user?.role)) {
    await loadAdminOperationalData();
  }
  if (state.user?.role === "admin") {
    await loadAdminCommerceData();
    if (!Array.isArray(documents.stories) || !documents.stories.length) persistAdminDocument("stories", state.stories);
    if (!Array.isArray(documents.categories) || !documents.categories.length) persistAdminDocument("categories", state.categories);
    if (!Array.isArray(documents.plans) || !documents.plans.length) persistAdminDocument("plans", state.plans);
    if (!documents["site-seo-public"]) persistAdminDocument("site-seo", state.siteSeo);
    persistAdminDocument("creator-tools", state.creatorTools);
    persistAdminDocument("operations", state.operations);
  }
}

async function loadAdminCommerceData() {
  if (state.user?.role !== "admin") return;
  try {
    const [ads, discounts, payouts] = await Promise.all([
      apiRequest("/api/admin/ads"),
      apiRequest("/api/admin/discounts"),
      apiRequest("/api/admin/payouts"),
    ]);
    state.adCampaigns = ads.campaigns || state.adCampaigns;
    state.discounts = discounts.discounts || [];
    state.adminPayouts = payouts.payouts || [];
    state.payoutAccounts = payouts.accounts || [];
  } catch (error) {
    state.userMessage = error.message;
  }
}

async function loadSecuritySettings() {
  if (!state.user) return;
  try {
    const payload = await apiRequest("/api/me/security");
    state.securitySettings = {
      emailVerified: Boolean(payload.emailVerified),
      twoFactorEnabled: Boolean(payload.twoFactorEnabled),
      loginAlertsEnabled: Boolean(payload.loginAlertsEnabled),
    };
    state.user = { ...state.user, emailVerified: Boolean(payload.emailVerified) };
  } catch (error) {
    state.securityMessage = error.message;
  }
}

async function loadSecuritySessions() {
  if (!state.user) return;
  try {
    const payload = await apiRequest("/api/me/sessions");
    state.currentSessionId = payload.currentSessionId || "";
    state.securitySessions = payload.sessions || [];
  } catch (error) {
    state.securityMessage = error.message;
  }
}

async function loadSupportTickets() {
  if (!state.user) return;
  try {
    const payload = await apiRequest("/api/support/tickets");
    state.operations.tickets = (payload.tickets || []).map((ticket) => ({
      id: ticket.id,
      subject: ticket.subject,
      category: ticket.category,
      priority: ticket.priority,
      status: ticket.status,
      owner: ticket.owner,
      age: new Date(ticket.created_at).toLocaleString(),
    }));
  } catch (error) {
    state.supportMessage = error.message;
  }
}

async function loadCreatorAnalytics() {
  if (!["writer", "admin"].includes(state.user?.role)) return;
  try {
    state.creatorAnalytics = await apiRequest("/api/me/creator-analytics");
  } catch (error) {
    state.userMessage = error.message;
  }
}

async function loadWriterPayouts() {
  if (!["writer", "admin"].includes(state.user?.role)) return;
  try {
    const payload = await apiRequest("/api/me/payouts");
    state.payoutAccount = payload.account;
    state.payoutSummary = {
      availableAmount: payload.availableAmount || 0,
      availableTips: payload.availableTips || [],
      payouts: payload.payouts || [],
    };
  } catch (error) {
    state.userMessage = error.message;
  }
}

async function loadAdminOperationalData() {
  if (!["moderator", "admin"].includes(state.user?.role)) return;
  try {
    const moderation = await apiRequest("/api/admin/moderation");
    if (state.user.role === "admin") state.adminAnalytics = await apiRequest("/api/admin/analytics");
    if (state.user.role === "admin") state.adminRecommendationStatus = await apiRequest("/api/admin/recommendations/status");
    state.operations.moderation = (moderation.cases || []).map((item) => ({
      id: item.id,
      kind: item.kind,
      target: item.target_type,
      subject: item.subject,
      risk: item.risk,
      status: item.status,
      assignee: item.assignee_name || "Unassigned",
      evidence: JSON.parse(item.evidence_json || "[]"),
    }));
  } catch (error) {
    state.userMessage = error.message;
  }
}

async function loadMediaAssets() {
  if (state.user?.role !== "admin") return;
  try {
    const payload = await apiRequest("/api/admin/media");
    state.mediaAssets = payload.assets || [];
  } catch (error) {
    state.mediaMessage = error.message;
  }
}

async function loadRecommendationFeed() {
  if (!state.user || !state.preferences.personalized) return;
  try {
    const payload = await apiRequest("/api/recommendations/feed?limit=48");
    state.serverRecommendations = payload.feed || [];
    state.serverRecommendationMap = Object.fromEntries(state.serverRecommendations.map((item) => [item.storySlug, item]));
    state.recommendationModel = payload.profile || state.recommendationModel;
  } catch (error) {
    state.recommendationMessage = `Server recommendations unavailable: ${error.message}`;
  }
}

async function sendRecommendationFeedback(type, story, metadata = {}) {
  if (!state.user || !story) return;
  try {
    const payload = await apiRequest("/api/recommendations/feedback", {
      method: "POST",
      body: JSON.stringify({
        type,
        storySlug: story.slug,
        metadata: { topic: story.topic, author: story.author, tags: story.tags || [], ...metadata },
      }),
    });
    state.serverRecommendations = payload.feed || state.serverRecommendations;
    state.serverRecommendationMap = Object.fromEntries(state.serverRecommendations.map((item) => [item.storySlug, item]));
    if (payload.training) state.recommendationModel = { ...state.recommendationModel, signalsCount: payload.training.signals, lastTrainedAt: payload.training.trainedAt, modelVersion: payload.training.modelVersion };
  } catch {
    // Local scoring remains available if the recommendation service is unreachable.
  }
}

async function loadAdminUsers() {
  if (state.user?.role !== "admin") return;
  try {
    const payload = await apiRequest(`/api/admin/users?q=${encodeURIComponent(state.userSearch)}`);
    state.users = payload.users;
  } catch (error) {
    state.userMessage = error.message;
  }
  render();
}

async function updateUser(userId, patch) {
  try {
    const payload = await apiRequest(`/api/admin/users/${encodeURIComponent(userId)}`, {
      method: "PATCH",
      body: JSON.stringify(patch),
    });
    state.users = state.users.map((user) => (user.id === userId ? payload.user : user));
    state.userMessage = `${payload.user.name} updated.`;
  } catch (error) {
    state.userMessage = error.message;
  }
  render();
}

async function deleteUser(userId) {
  const removed = state.users.find((user) => user.id === userId);
  try {
    await apiRequest(`/api/admin/users/${encodeURIComponent(userId)}`, { method: "DELETE" });
    state.users = state.users.filter((user) => user.id !== userId);
    state.userMessage = `${removed?.name || "User"} deleted.`;
  } catch (error) {
    state.userMessage = error.message;
  }
  render();
}

const creatorSeed = {
  calendarView: "calendar",
  calendarItems: [
    { id: "cal-1", title: "Creator economy field guide", type: "Post", status: "Scheduled", date: "2026-06-12", owner: "Meera Iyer", channel: "Web" },
    { id: "cal-2", title: "Friday member briefing", type: "Newsletter", status: "Review", date: "2026-06-13", owner: "Arjun Rao", channel: "Email" },
    { id: "cal-3", title: "AI desk launch campaign", type: "Campaign", status: "Draft", date: "2026-06-15", owner: "Kavya Menon", channel: "Social" },
    { id: "cal-4", title: "Design systems interview", type: "Post", status: "Scheduled", date: "2026-06-18", owner: "Dev Shah", channel: "Web" },
    { id: "cal-5", title: "Member renewal sequence", type: "Campaign", status: "Approved", date: "2026-06-20", owner: "Naina Kapoor", channel: "Email" },
  ],
  segments: [
    { id: "seg-1", name: "Highly engaged AI readers", rules: "Interest is AI · 3+ completions in 30 days", size: 0, channel: "Draft" },
    { id: "seg-2", name: "At-risk annual members", rules: "Annual plan · No visit in 21 days", size: 0, channel: "Draft" },
    { id: "seg-3", name: "India creator prospects", rules: "Location India · Source organic · Free plan", size: 0, channel: "Draft" },
  ],
  promotions: [
    { id: "SAVE20", kind: "Coupon", value: "20% off", audience: "New members", uses: 842, active: true },
    { id: "STUDENT50", kind: "Student", value: "50% off", audience: "Verified students", uses: 319, active: true },
    { id: "TEAM10", kind: "Corporate", value: "10 seats", audience: "Organizations", uses: 47, active: true },
  ],
  tipCommission: 8,
  tipsEnabled: true,
};

const operationsSeed = {
  moderation: [
    { id: "MOD-2048", kind: "Misinformation", target: "Post", subject: "Unsupported health claim in productivity story", risk: "High", status: "Open", assignee: "Sara Ali" },
    { id: "MOD-2047", kind: "Harassment", target: "Comment", subject: "Repeated personal attacks in discussion", risk: "High", status: "Investigating", assignee: "Dev Shah" },
    { id: "MOD-2046", kind: "Spam", target: "Profile", subject: "Automated affiliate-link account", risk: "Medium", status: "Queued", assignee: "Unassigned" },
    { id: "MOD-2045", kind: "Plagiarism", target: "Post", subject: "Likely copied reporting from external publisher", risk: "High", status: "Evidence requested", assignee: "Sara Ali" },
  ],
  copyright: [
    { id: "COPY-318", title: "Copied interview transcript", claimant: "Northline Media", match: 87, status: "Admin review", evidence: 4 },
    { id: "COPY-317", title: "Reused market chart", claimant: "Data Ledger", match: 64, status: "Counter-notice", evidence: 2 },
    { id: "COPY-316", title: "Syndicated essay", claimant: "Author supplied license", match: 92, status: "Resolved", evidence: 3 },
  ],
  tickets: [
    { id: "TKT-8821", subject: "Annual payment charged twice", category: "Payment", priority: "Urgent", status: "Open", age: "18 min", owner: "Billing" },
    { id: "TKT-8820", subject: "Cannot access gifted membership", category: "Subscription", priority: "High", status: "Waiting", age: "1 hr", owner: "Support" },
    { id: "TKT-8819", subject: "Publication verification request", category: "Account", priority: "Normal", status: "In progress", age: "3 hr", owner: "Trust" },
  ],
};

function loadProductState(key, fallback) {
  return structuredClone(fallback);
}

function persistProductState(key, value) {
  if (key === "preferences") persistUserDocument("preferences", value);
  if (["creator-tools", "operations"].includes(key)) persistAdminDocument(key, value);
}

const initialCreatorTools = loadProductState("creator-tools", creatorSeed);
const initialOperations = loadProductState("operations", operationsSeed);
const initialPreferences = loadProductState("preferences", {
  focusMode: false,
  fontFamily: "serif",
  textScale: 100,
  tracking: true,
  personalized: true,
  cookies: { essential: true, analytics: true, advertising: false },
  locale: "en-IN",
  speechRate: 1,
});

const state = {
  path: window.location.pathname,
  theme: localStorage.getItem("inkriver-theme") || "day",
  query: "",
  activeTopic: "For you",
  saved: new Set(loadSavedSlugs()),
  stories: initialStories,
  translations: {},
  translationLanguages: [
    { locale: "hi-IN", language: "Hindi" },
    { locale: "es-ES", language: "Spanish" },
    { locale: "ar-SA", language: "Arabic" },
  ],
  mediaAssets: [],
  mediaMessage: "",
  resetToken: new URLSearchParams(window.location.search).get("reset_token") || "",
  resetPassword: "",
  categories: initialCategories,
  claps: Object.fromEntries(initialStories.map((story) => [story.slug, story.claps])),
  role: "subscriber",
  isMember: false,
  user: initialUser,
  loginOpen: false,
  loginMessage: "",
  authMode: "login",
  authForm: { name: "", email: "", password: "", twoFactorCode: "" },
  authBusy: false,
  sessionReady: false,
  onboardingOpen: Boolean(initialUser && !initialRecommendation.completedOnboarding),
  onboardingSelection: new Set(initialRecommendation.selectedInterests),
  onboardingMessage: "",
  recommendation: initialRecommendation,
  recommendationMessage: "",
  articleSession: null,
  following: initialFollowing,
  readingHistory: Array.isArray(initialReadingHistory) ? initialReadingHistory : [],
  historyQuery: "",
  dashboardSection: "overview",
  pollResponses: initialPollResponses,
  commentsByStory: loadComments(),
  commentSort: "top",
  commentDraft: "",
  commentReplyTo: "",
  commentEditId: "",
  pendingResumeSlug: "",
  searchOpen: false,
  searchFilters: { topic: "all", author: "all", access: "all", maxMinutes: "all", sort: "relevance" },
  checkoutPlan: null,
  copiedShare: "",
  paymentMessage: "",
  gatewaySettingsSaved: false,
  providerStatus: {
    payments: { razorpay: false, paypal: false, payu: false, cashfree: false },
    social: { google: false, facebook: false },
    ai: false,
    email: false,
    push: false,
  },
  pushPublicKey: "",
  socialAccounts: [],
  adCampaigns: [],
  observedAds: new Set(),
  discounts: [],
  adminPayouts: [],
  payoutAccounts: [],
  payoutAccount: null,
  payoutSummary: { availableAmount: 0, availableTips: [], payouts: [] },
  checkoutDiscountCode: "",
  checkoutDiscount: null,
  gateway: "razorpay",
  currency: localStorage.getItem("inkriver-currency") || "INR",
  rates: { INR: 1 },
  rateStatus: "Using INR base prices",
  rateUpdatedAt: "",
  gatewaySettings: loadGatewaySettings(),
  siteSeo: loadSiteSeo(),
  siteSeoMessage: "",
  plans: loadSubscriptionPlans(),
  users: [],
  adminAnalytics: { eventCounts: [], storyCounts: [], dailyEvents: [], revenueByStory: [], revenue: 0, activeSubscriptions: 0, tickets: [], moderation: [] },
  adminRecommendationStatus: { profiles: 0, signals: 0, scores: 0, lastTrainedAt: "" },
  creatorAnalytics: { stories: [], eventRows: [], commentRows: [], tipRows: [] },
  userSearch: "",
  userMessage: "",
  securitySessions: [],
  securitySettings: { emailVerified: false, twoFactorEnabled: false, loginAlertsEnabled: false },
  currentSessionId: "",
  editingBlogId: "",
  blogForm: emptyBlogForm(),
  blogMessage: "",
  editingCategoryId: "",
  categoryForm: emptyCategoryForm(),
  categoryMessage: "",
  editingPlanId: "",
  planForm: emptyPlanForm(),
  planMessage: "",
  mobileOpen: false,
  draft: {
    title: "A better launch plan for expert-led newsletters",
    subtitle: "What to publish before, during, and after your first paid issue.",
    body: "Start with a sharp promise, then show the reader a clear path. Add examples, context, and a strong closing note.",
    topic: "Marketing",
    paywalled: true,
    ads: true,
    earning: true,
  },
  draftPreview: false,
  draftMessage: "",
  queue: [
    { title: "Comment flagged for personal attack", status: "Open", priority: "High" },
    { title: "Story submitted to Growth Weekly", status: "Review", priority: "Medium" },
    { title: "Ad creative waiting for approval", status: "Open", priority: "Low" },
  ],
  creatorTools: initialCreatorTools,
  operations: initialOperations,
  preferences: initialPreferences,
  aiPanel: "assistant",
  aiResult: "",
  aiRunning: false,
  recommendationPanel: "",
  serverRecommendations: [],
  serverRecommendationMap: {},
  recommendationModel: { signalsCount: 0, lastTrainedAt: "", modelVersion: 0 },
  tipStory: "",
  tipAmount: 200,
  tipMessage: "",
  segmentQuery: "",
  opsFilter: "all",
  supportMessage: "",
  privacyMessage: "",
  securityMessage: "",
  calendarMessage: "",
  speechActive: false,
};

const recommendationSignals = {
  open: 1,
  read_midpoint: 2,
  read_complete: 4,
  long_read: 3,
  clap: 3,
  save: 6,
  unsave: -4,
  share: 3,
  not_interested: -9,
  more_like_this: 9,
  less_like_this: -9,
  hide_topic: -14,
};

function addScore(scoreMap, key, amount) {
  if (!key || !Number.isFinite(amount)) return;
  scoreMap[key] = Math.max(-30, Math.min(60, Number(scoreMap[key] || 0) + amount));
}

function storyTags(story) {
  return [...new Set([story.topic, ...(story.tags || [])].filter(Boolean))];
}

function recordRecommendationActivity(story, type) {
  if (!story || !recommendationSignals[type]) return;
  const weight = recommendationSignals[type];
  addScore(state.recommendation.topicScores, story.topic, weight);
  addScore(state.recommendation.authorScores, story.author, weight * 0.45);
  addScore(state.recommendation.storyScores, story.slug, weight);
  storyTags(story).forEach((tag) => addScore(state.recommendation.tagScores, tag, weight * 0.6));

  if (type === "not_interested") {
    state.recommendation.hiddenStories = [...new Set([...state.recommendation.hiddenStories, story.slug])];
  } else if (weight > 0) {
    state.recommendation.hiddenStories = state.recommendation.hiddenStories.filter((slug) => slug !== story.slug);
  }

  state.recommendation.activity = [
    {
      id: `${Date.now()}-${type}-${story.slug}`,
      type,
      slug: story.slug,
      title: story.title,
      topic: story.topic,
      at: new Date().toISOString(),
    },
    ...state.recommendation.activity,
  ].slice(0, 80);
  persistRecommendationProfile();
  if (state.user) sendRecommendationFeedback(type, story);
  else recordServerEvent(type, story.slug, null, { topic: story.topic, author: story.author, tags: story.tags || [] });
}

function updateInterestPreference(topic, enabled, source = "dashboard") {
  const selected = new Set(state.recommendation.selectedInterests);
  if (enabled) selected.add(topic);
  else selected.delete(topic);
  state.recommendation.selectedInterests = [...selected];
  addScore(state.recommendation.topicScores, topic, enabled ? 8 : -8);
  state.recommendation.activity = [
    {
      id: `${Date.now()}-interest-${topic}`,
      type: enabled ? "interest_added" : "interest_removed",
      title: topic,
      topic,
      source,
      at: new Date().toISOString(),
    },
    ...state.recommendation.activity,
  ].slice(0, 80);
  state.onboardingSelection = new Set(state.recommendation.selectedInterests);
  persistRecommendationProfile();
  if (state.user) {
    apiRequest("/api/recommendations/feedback", {
      method: "POST",
      body: JSON.stringify({ type: enabled ? "interest_added" : "interest_removed", storySlug: "", metadata: { topic, source } }),
    }).then((payload) => {
      state.serverRecommendations = payload.feed || state.serverRecommendations;
      state.serverRecommendationMap = Object.fromEntries(state.serverRecommendations.map((item) => [item.storySlug, item]));
      render();
    }).catch(() => {});
  } else recordServerEvent(enabled ? "interest_added" : "interest_removed", "", null, { topic, source });
}

function resetRecommendationProfile() {
  const completedOnboarding = Boolean(state.user);
  state.recommendation = {
    ...emptyRecommendationProfile(),
    completedOnboarding,
  };
  state.onboardingSelection = new Set();
  state.recommendationMessage = "Recommendation history cleared. Choose interests to rebuild your feed.";
  state.serverRecommendations = [];
  state.serverRecommendationMap = {};
  persistRecommendationProfile();
  if (state.user) {
    apiRequest("/api/recommendations/feedback", {
      method: "POST",
      body: JSON.stringify({ type: "reset_recommendations", storySlug: "", metadata: { reset: true } }),
    }).then(() => loadRecommendationFeed()).catch(() => {});
  }
}

function recommendationScore(story) {
  if (state.serverRecommendationMap[story.slug]) return Number(state.serverRecommendationMap[story.slug].score || 0);
  const selectedBoost = state.recommendation.selectedInterests.includes(story.topic) ? 28 : 0;
  const topicScore = Number(state.recommendation.topicScores[story.topic] || 0) * 2.4;
  const tagScore = storyTags(story).reduce((total, tag) => total + Number(state.recommendation.tagScores[tag] || 0), 0) * 0.55;
  const authorScore = Number(state.recommendation.authorScores[story.author] || 0) * 0.8;
  const storyScore = Number(state.recommendation.storyScores[story.slug] || 0) * 1.2;
  const popularity = Math.log10(Math.max(10, story.reads || 10)) * 2 + Math.log10(Math.max(10, state.claps[story.slug] || story.claps || 10));
  const discovery = story.slug.split("").reduce((total, char) => total + char.charCodeAt(0), 0) % 7;
  return selectedBoost + topicScore + tagScore + authorScore + storyScore + followedStoryBoost(story) + popularity + discovery;
}

function recommendationReason(story) {
  if (state.serverRecommendationMap[story.slug]?.reason) return state.serverRecommendationMap[story.slug].reason;
  if (isFollowing("writers", story.author)) return `New from ${story.author}, whom you follow`;
  if (isFollowing("publications", story.publication)) return `From ${story.publication}, which you follow`;
  if (isFollowing("topics", story.topic)) return `Because you follow ${story.topic}`;
  const followedTag = (story.tags || []).find((tag) => isFollowing("tags", tag));
  if (followedTag) return `Matched to the tag ${followedTag}`;
  if (state.recommendation.selectedInterests.includes(story.topic)) return `Because ${story.topic} is one of your interests`;
  if (Number(state.recommendation.topicScores[story.topic] || 0) >= 4) return `More from ${story.topic}, based on your reading`;
  const strongestTag = storyTags(story)
    .filter((tag) => tag !== story.topic)
    .sort((a, b) => Number(state.recommendation.tagScores[b] || 0) - Number(state.recommendation.tagScores[a] || 0))[0];
  if (strongestTag && Number(state.recommendation.tagScores[strongestTag] || 0) >= 3) return `Matched to your interest in ${strongestTag}`;
  return "Recommended from reader engagement";
}

function topRecommendationInterests(limit = 4) {
  return state.categories
    .map((category) => ({
      name: category.name,
      score: Number(state.recommendation.topicScores[category.name] || 0)
        + (state.recommendation.selectedInterests.includes(category.name) ? 12 : 0),
    }))
    .sort((a, b) => b.score - a.score)
    .slice(0, limit);
}

function hasPersonalizationSignals() {
  return state.recommendation.selectedInterests.length > 0 || state.recommendation.activity.length > 0;
}

function relatedStories(story, limit = 3) {
  return publishedStories()
    .filter((candidate) => candidate.slug !== story.slug && !state.recommendation.hiddenStories.includes(candidate.slug))
    .sort((a, b) => {
      const aTopic = a.topic === story.topic ? 12 : 0;
      const bTopic = b.topic === story.topic ? 12 : 0;
      return recommendationScore(b) + bTopic - recommendationScore(a) - aTopic;
    })
    .slice(0, limit);
}

function recommendationActivityLabel(activity) {
  const labels = {
    open: "Opened",
    read_midpoint: "Read halfway",
    read_complete: "Read deeply",
    long_read: "Spent time reading",
    clap: "Liked",
    save: "Saved",
    unsave: "Removed save",
    share: "Shared",
    not_interested: "Asked to show less",
    interest_added: "Added interest",
    interest_removed: "Removed interest",
  };
  return labels[activity.type] || "Interacted with";
}

function beginArticleSession(story) {
  if (!story || state.articleSession?.slug === story.slug) return;
  finalizeArticleSession();
  state.articleSession = {
    slug: story.slug,
    startedAt: Date.now(),
    midpoint: false,
    complete: false,
  };
  upsertReadingHistory(story, { openedAt: state.readingHistory.find((entry) => entry.slug === story.slug)?.openedAt || new Date().toISOString() });
  recordRecommendationActivity(story, "open");
  loadStoryComments(story.slug).then(() => {
    if (state.path.includes(`/stories/${story.slug}`)) render();
  });
}

function finalizeArticleSession() {
  if (!state.articleSession) return;
  const story = state.stories.find((item) => item.slug === state.articleSession.slug);
  const elapsed = Date.now() - state.articleSession.startedAt;
  if (story && elapsed >= 45000) recordRecommendationActivity(story, "long_read");
  state.articleSession = null;
}

function trackArticleDepth() {
  if (!state.articleSession) return;
  const article = document.querySelector(".article-page");
  const story = state.stories.find((item) => item.slug === state.articleSession.slug);
  if (!article || !story) return;
  const rect = article.getBoundingClientRect();
  const articleTop = window.scrollY + rect.top;
  const readableDistance = Math.max(article.offsetHeight - window.innerHeight, 1);
  const progress = Math.max(0, Math.min(1, (window.scrollY - articleTop) / readableDistance));
  const historyEntry = state.readingHistory.find((entry) => entry.slug === story.slug);
  const nextProgress = Math.max(historyEntry?.progress || 0, Math.round(progress * 100));
  const nextPosition = Math.max(0, Math.round(window.scrollY - articleTop));
  upsertReadingHistory(story, { progress: nextProgress, position: nextPosition, completed: nextProgress >= 80 });
  const progressBar = document.getElementById("articleProgressBar");
  if (progressBar) progressBar.style.width = `${nextProgress}%`;
  if (progress >= 0.4 && !state.articleSession.midpoint) {
    state.articleSession.midpoint = true;
    recordRecommendationActivity(story, "read_midpoint");
  }
  if (progress >= 0.8 && !state.articleSession.complete) {
    state.articleSession.complete = true;
    recordRecommendationActivity(story, "read_complete");
  }
}

function isFollowing(type, value) {
  return state.following[type]?.includes(value) || false;
}

function toggleFollow(type, value) {
  if (!state.following[type] || !value) return;
  const following = new Set(state.following[type]);
  const wasFollowing = following.has(value);
  wasFollowing ? following.delete(value) : following.add(value);
  state.following[type] = [...following];
  persistFollowing();
  recordServerEvent(wasFollowing ? "unfollow" : "follow", "", null, { type, value });
}

function followLabel(type, value) {
  return isFollowing(type, value) ? "Following" : "Follow";
}

function followedStoryBoost(story) {
  let score = 0;
  if (isFollowing("writers", story.author)) score += 45;
  if (isFollowing("publications", story.publication)) score += 34;
  if (isFollowing("topics", story.topic)) score += 30;
  score += (story.tags || []).filter((tag) => isFollowing("tags", tag)).length * 20;
  score += defaultCuratedLists.filter((list) => isFollowing("lists", list.id) && list.slugs.includes(story.slug)).length * 25;
  return score;
}

function upsertReadingHistory(story, patch = {}) {
  if (!story) return;
  const existing = state.readingHistory.find((entry) => entry.slug === story.slug);
  const entry = {
    slug: story.slug,
    title: story.title,
    topic: story.topic,
    author: story.author,
    readTime: story.readTime,
    progress: 0,
    position: 0,
    completed: false,
    openedAt: new Date().toISOString(),
    lastReadAt: new Date().toISOString(),
    ...existing,
    ...patch,
    lastReadAt: new Date().toISOString(),
  };
  state.readingHistory = [entry, ...state.readingHistory.filter((item) => item.slug !== story.slug)].slice(0, 120);
  persistReadingHistory();
}

function removeHistoryEntry(slug) {
  state.readingHistory = state.readingHistory.filter((entry) => entry.slug !== slug);
  persistReadingHistory();
}

function filteredHistory() {
  const query = state.historyQuery.trim().toLowerCase();
  return state.readingHistory.filter((entry) => !query || `${entry.title} ${entry.author} ${entry.topic}`.toLowerCase().includes(query));
}

function continueReadingEntries(limit = 4) {
  return state.readingHistory.filter((entry) => entry.progress > 0 && entry.progress < 80).slice(0, limit);
}

function parseReadMinutes(story) {
  return Number.parseInt(story.readTime, 10) || 5;
}

function fullTextForStory(story) {
  return `${story.title} ${story.dek} ${story.author} ${story.publication} ${story.topic} ${(story.tags || []).join(" ")} ${(story.body || []).join(" ")}`.toLowerCase();
}

function searchScore(story, query) {
  if (!query) return recommendationScore(story);
  const terms = query.toLowerCase().split(/\s+/).filter(Boolean);
  const title = story.title.toLowerCase();
  const dek = story.dek.toLowerCase();
  const full = fullTextForStory(story);
  return terms.reduce((score, term) => score + (title.includes(term) ? 12 : 0) + (dek.includes(term) ? 6 : 0) + (full.includes(term) ? 2 : 0), 0);
}

function advancedSearchResults() {
  const query = state.query.trim().toLowerCase();
  const filters = state.searchFilters;
  const results = publishedStories().filter((story) => {
    if (query && !fullTextForStory(story).includes(query)) return false;
    if (filters.topic !== "all" && story.topic !== filters.topic) return false;
    if (filters.author !== "all" && story.author !== filters.author) return false;
    if (filters.access === "free" && story.premium) return false;
    if (filters.access === "premium" && !story.premium) return false;
    if (filters.maxMinutes !== "all" && parseReadMinutes(story) > Number(filters.maxMinutes)) return false;
    return true;
  });
  return results.sort((a, b) => {
    if (filters.sort === "popular") return (b.reads + state.claps[b.slug]) - (a.reads + state.claps[a.slug]);
    if (filters.sort === "newest") return new Date(b.publishedAt) - new Date(a.publishedAt);
    if (filters.sort === "shortest") return parseReadMinutes(a) - parseReadMinutes(b);
    return searchScore(b, query) - searchScore(a, query);
  });
}

function searchSuggestions() {
  const query = state.query.trim().toLowerCase();
  if (query.length < 2) return [];
  const storySuggestions = publishedStories()
    .filter((story) => fullTextForStory(story).includes(query))
    .slice(0, 5)
    .map((story) => ({ type: "Story", label: story.title, meta: `${story.author} · ${story.topic}`, route: `/stories/${story.slug}` }));
  const writerSuggestions = Object.entries(defaultProfiles)
    .filter(([, profile]) => `${profile.name} ${profile.expertise.join(" ")}`.toLowerCase().includes(query))
    .slice(0, 3)
    .map(([slug, profile]) => ({ type: "Writer", label: profile.name, meta: profile.expertise[0], route: `/@${slug}` }));
  return [...storySuggestions, ...writerSuggestions].slice(0, 7);
}

function profileForSlug(slug) {
  if (defaultProfiles[slug]) return { slug, ...defaultProfiles[slug] };
  const story = publishedStories().find((item) => slugifyName(item.author) === slug);
  return story ? { slug, name: story.author, bio: `Writer covering ${story.topic.toLowerCase()} and related ideas.`, expertise: [story.topic, ...(story.tags || []).slice(0, 2)], followers: 1200, badge: "Writer", website: "", social: "", publication: story.publication } : null;
}

function currentCommentAuthor() {
  return state.user?.name || "Guest Reader";
}

function storyComments(storySlug) {
  const comments = [...(state.commentsByStory[storySlug] || [])];
  return comments.sort((a, b) => {
    if (a.pinned !== b.pinned) return a.pinned ? -1 : 1;
    if (state.commentSort === "newest") return new Date(b.createdAt) - new Date(a.createdAt);
    return b.likes - a.likes;
  });
}

async function loadStoryComments(storySlug) {
  try {
    const payload = await apiRequest(`/api/stories/${encodeURIComponent(storySlug)}/comments`);
    state.commentsByStory[storySlug] = payload.comments || [];
  } catch {
    // Keep the current on-screen state if the service is temporarily unavailable.
  }
}

async function saveComment(storySlug) {
  const text = state.commentDraft.trim();
  if (!text) return;
  if (!state.user) {
    state.loginOpen = true;
    state.loginMessage = "Sign in to join the discussion.";
    return;
  }
  const comments = state.commentsByStory[storySlug] || [];
  try {
    const payload = state.commentEditId
      ? await apiRequest(`/api/comments/${encodeURIComponent(state.commentEditId)}`, {
        method: "PATCH",
        body: JSON.stringify({ text }),
      })
      : await apiRequest(`/api/stories/${encodeURIComponent(storySlug)}/comments`, {
        method: "POST",
        body: JSON.stringify({ text, parentId: state.commentReplyTo || null }),
      });
    state.commentsByStory[storySlug] = payload.comments || comments;
  } catch (error) {
    state.userMessage = error.message;
    return;
  }
  state.commentDraft = "";
  state.commentReplyTo = "";
  state.commentEditId = "";
}

function formatINR(value) {
  return `INR ${value.toLocaleString("en-IN")}`;
}

function activeCurrency() {
  return supportedCurrencies.find((currency) => currency.code === state.currency) || supportedCurrencies[0];
}

function convertedAmount(inrAmount) {
  const rate = state.rates[state.currency] || 1;
  return inrAmount * rate;
}

function formatMoneyFromINR(inrAmount) {
  const currency = activeCurrency();
  const amount = convertedAmount(inrAmount);
  const fractionDigits = currency.code === "INR" ? 0 : 2;
  return `${currency.symbol} ${amount.toLocaleString("en-IN", {
    minimumFractionDigits: fractionDigits,
    maximumFractionDigits: fractionDigits,
  })}`;
}

function escapeHtml(value) {
  return String(value ?? "").replace(/[&<>"']/g, (char) => ({
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#39;",
  })[char]);
}

function safeImageUrl(value) {
  const url = String(value || "").trim();
  if (/^https?:\/\//i.test(url) || /^data:image\/(png|jpe?g|gif|webp);base64,/i.test(url)) return url;
  return "";
}

function sanitizeRichHtml(value) {
  const template = document.createElement("template");
  template.innerHTML = String(value || "");
  const allowedTags = new Set(["P", "BR", "H2", "H3", "H4", "STRONG", "B", "EM", "I", "U", "S", "BLOCKQUOTE", "A", "IMG", "UL", "OL", "LI", "TABLE", "THEAD", "TBODY", "TR", "TH", "TD", "HR"]);
  const allowedAttributes = {
    A: new Set(["href", "target", "rel"]),
    IMG: new Set(["src", "alt"]),
    TH: new Set(["colspan", "rowspan"]),
    TD: new Set(["colspan", "rowspan"]),
  };
  [...template.content.querySelectorAll("*")].forEach((element) => {
    if (!allowedTags.has(element.tagName)) {
      element.replaceWith(...element.childNodes);
      return;
    }
    [...element.attributes].forEach((attribute) => {
      if (!allowedAttributes[element.tagName]?.has(attribute.name)) element.removeAttribute(attribute.name);
    });
    if (element.tagName === "A") {
      const href = element.getAttribute("href") || "";
      if (!/^(https?:|mailto:|\/|#)/i.test(href)) element.removeAttribute("href");
      element.setAttribute("rel", "noopener noreferrer");
    }
    if (element.tagName === "IMG") {
      const src = safeImageUrl(element.getAttribute("src"));
      if (!src) element.remove();
      else element.setAttribute("src", src);
    }
  });
  return template.innerHTML.trim();
}

function richTextParagraphs(value) {
  const container = document.createElement("div");
  container.innerHTML = sanitizeRichHtml(value);
  return [...container.querySelectorAll("p, h2, h3, h4, blockquote, li")]
    .map((element) => element.textContent.trim())
    .filter(Boolean);
}

function storyContentHtml(story, locked) {
  const sanitized = sanitizeRichHtml(story.contentHtml || story.body.map((paragraph) => `<p>${escapeHtml(paragraph)}</p>`).join(""));
  if (!locked) return sanitized;
  const container = document.createElement("div");
  container.innerHTML = sanitized;
  return [...container.children].slice(0, 2).map((element) => element.outerHTML).join("");
}

function currentArticleLocale() {
  return state.preferences.locale === "en-IN" ? "" : state.preferences.locale;
}

function translatedStory(story) {
  const locale = currentArticleLocale();
  const translation = locale ? state.translations?.[story.slug]?.[locale] : null;
  if (!translation || translation.status === "failed") return story;
  return {
    ...story,
    title: translation.title || story.title,
    dek: translation.dek || story.dek,
    body: Array.isArray(translation.body) && translation.body.length ? translation.body : story.body,
    contentHtml: translation.contentHtml || story.contentHtml,
    seo: { ...(story.seo || {}), ...(translation.seo || {}) },
    translatedFrom: story.title,
    translatedLocale: locale,
  };
}

function translationStatusLabel(story) {
  const locale = currentArticleLocale();
  if (!locale) return "";
  const labels = Object.fromEntries(state.translationLanguages.map((item) => [item.locale, item.language]));
  return state.translations?.[story.slug]?.[locale]
    ? `Showing ${labels[locale] || locale} translation`
    : `${labels[locale] || locale} translation is being prepared. Showing original for now.`;
}

function readerLanguageOptions() {
  return [{ locale: "en-IN", language: "English" }, ...state.translationLanguages]
    .filter((item, index, list) => item.locale && list.findIndex((candidate) => candidate.locale === item.locale) === index);
}

function postSeoScore() {
  const form = state.blogForm;
  const seo = form.seo;
  const contentText = richTextParagraphs(form.contentHtml || "").join(" ");
  const phrase = seo.focusKeyphrase.trim().toLowerCase();
  const checks = [
    Boolean(phrase),
    Boolean(seo.seoTitle.trim()),
    seo.seoTitle.trim().length >= 30 && seo.seoTitle.trim().length <= 60,
    seo.metaDescription.trim().length >= 120 && seo.metaDescription.trim().length <= 160,
    Boolean(form.slug.trim()),
    phrase ? form.title.toLowerCase().includes(phrase) : false,
    phrase ? seo.metaDescription.toLowerCase().includes(phrase) : false,
    phrase ? contentText.toLowerCase().includes(phrase) : false,
    Boolean(form.imageUrl || seo.socialImage),
    contentText.split(/\s+/).filter(Boolean).length >= 200,
  ];
  return Math.round((checks.filter(Boolean).length / checks.length) * 100);
}

function rateLine() {
  if (state.currency === "INR") return "Base currency: INR";
  const rate = state.rates[state.currency];
  return rate ? `1 INR = ${rate.toFixed(4)} ${state.currency}` : state.rateStatus;
}

async function fetchCurrencyRates() {
  state.rateStatus = "Updating live rates...";
  render();
  try {
    const response = await fetch("https://open.er-api.com/v6/latest/INR", { cache: "no-store" });
    const data = await response.json();
    if (!response.ok || !data.rates) throw new Error("Rate API unavailable");
    state.rates = { INR: 1, ...data.rates };
    state.rateUpdatedAt = data.time_last_update_utc || new Date().toUTCString();
    state.rateStatus = "Live rates updated";
  } catch {
    state.rates = { INR: 1, USD: 0.012, EUR: 0.011, GBP: 0.0095, AED: 0.044, SGD: 0.015, AUD: 0.018, CAD: 0.016 };
    state.rateUpdatedAt = "Fallback estimate";
    state.rateStatus = "Live rates unavailable. Showing fallback estimates.";
  }
  render();
}

function loadScript(src) {
  return new Promise((resolve, reject) => {
    const existing = document.querySelector(`script[src="${src}"]`);
    if (existing) {
      resolve();
      return;
    }
    const script = document.createElement("script");
    script.src = src;
    script.async = true;
    script.onload = resolve;
    script.onerror = reject;
    document.head.appendChild(script);
  });
}

function urlBase64ToUint8Array(value) {
  const padding = "=".repeat((4 - value.length % 4) % 4);
  const base64 = (value + padding).replace(/-/g, "+").replace(/_/g, "/");
  return Uint8Array.from(atob(base64), (character) => character.charCodeAt(0));
}

async function enablePushNotifications() {
  if (!state.user) {
    state.loginOpen = true;
    state.loginMessage = "Sign in before enabling notifications.";
    render();
    return;
  }
  if (!state.providerStatus.push || !state.pushPublicKey) {
    state.userMessage = "Push notifications are not configured on the server.";
    render();
    return;
  }
  try {
    const permission = await Notification.requestPermission();
    if (permission !== "granted") throw new Error("Notification permission was not granted.");
    const registration = await navigator.serviceWorker.ready;
    const subscription = await registration.pushManager.subscribe({
      userVisibleOnly: true,
      applicationServerKey: urlBase64ToUint8Array(state.pushPublicKey),
    });
    await apiRequest("/api/me/push-subscriptions", {
      method: "POST",
      body: JSON.stringify(subscription.toJSON()),
    });
    state.userMessage = "Push notifications enabled on this device.";
  } catch (error) {
    state.userMessage = error.message;
  }
  render();
}

async function startGatewayPayment(plan, purchase = {}) {
  const gateway = paymentGateways.find((item) => item.id === state.gateway) || paymentGateways[0];
  if (!state.user) {
    state.checkoutPlan = null;
    state.loginOpen = true;
    state.loginMessage = "Sign in before purchasing a membership.";
    render();
    return;
  }
  if (!state.providerStatus.payments[gateway.id]) {
    state.paymentMessage = `${gateway.name} is not configured on the server. Add its environment credentials before accepting payments.`;
    render();
    return;
  }
  state.paymentMessage = `Creating a secure ${gateway.name} order...`;
  render();
  try {
    const order = await apiRequest("/api/payments/orders", {
      method: "POST",
      body: JSON.stringify({
        provider: gateway.id,
        amount: Math.round(convertedAmount(plan.price) * 100),
        currency: state.currency,
        discountCode: state.checkoutDiscountCode,
        purpose: purchase.purpose || `${plan.name} membership`,
        metadata: { planId: plan.id, planName: plan.name, period: plan.period, ...(purchase.metadata || {}) },
      }),
    });
    if (gateway.id === "razorpay") {
      await loadScript("https://checkout.razorpay.com/v1/checkout.js");
      const checkout = new window.Razorpay({
        key: order.checkout.key,
        order_id: order.checkout.orderId,
        name: "InkRiver",
        description: `${plan.name} membership`,
        prefill: { name: state.user.name, email: state.user.email },
        theme: { color: "#1a7f48" },
        handler: async (result) => {
          try {
            const verified = await apiRequest("/api/payments/razorpay/verify", {
              method: "POST",
              body: JSON.stringify({ paymentId: order.paymentId, ...result }),
            });
            if (purchase.metadata?.kind !== "gift") applyAuthenticatedUser(verified.user, false);
            state.checkoutPlan = null;
            state.paymentMessage = purchase.metadata?.kind === "gift"
              ? "Payment verified. The gift membership is scheduled."
              : "Payment verified. Membership is active.";
            setRoute("/dashboard");
          } catch (error) {
            state.paymentMessage = error.message;
            render();
          }
        },
      });
      checkout.open();
      state.paymentMessage = "Complete payment in the secure Razorpay window.";
      render();
      return;
    }
    if (gateway.id === "paypal" && order.checkout.approveUrl) {
      window.location.assign(order.checkout.approveUrl);
      return;
    }
    if (gateway.id === "cashfree" && order.checkout.paymentSessionId) {
      await loadScript("https://sdk.cashfree.com/js/v3/cashfree.js");
      const cashfree = window.Cashfree({ mode: state.gatewaySettings.cashfreeEnvironment === "production" ? "production" : "sandbox" });
      await cashfree.checkout({ paymentSessionId: order.checkout.paymentSessionId, redirectTarget: "_self" });
      return;
    }
    if (gateway.id === "payu" && order.checkout.action) {
      const form = document.createElement("form");
      form.method = "POST";
      form.action = order.checkout.action;
      Object.entries(order.checkout.fields).forEach(([name, value]) => {
        const input = document.createElement("input");
        input.type = "hidden";
        input.name = name;
        input.value = value;
        form.appendChild(input);
      });
      document.body.appendChild(form);
      form.submit();
      return;
    }
    state.paymentMessage = `${gateway.name} did not return a usable checkout session.`;
    render();
  } catch (error) {
    state.paymentMessage = error.message;
    render();
  }
}

async function startTipPayment(story) {
  const gateway = paymentGateways.find((item) => item.id === state.gateway) || paymentGateways[0];
  if (!state.user) {
    state.loginOpen = true;
    state.loginMessage = "Sign in before sending a tip.";
    render();
    return;
  }
  if (!state.providerStatus.payments[gateway.id]) {
    state.tipMessage = `${gateway.name} is not configured on the server.`;
    render();
    return;
  }
  try {
    const order = await apiRequest("/api/payments/orders", {
      method: "POST",
      body: JSON.stringify({
        provider: gateway.id,
        amount: Math.round(convertedAmount(state.tipAmount) * 100),
        currency: state.currency,
        purpose: `Tip for ${story.title}`,
        metadata: {
          kind: "tip",
          storySlug: story.slug,
          writerName: story.author,
          commission: state.creatorTools.tipCommission,
        },
      }),
    });
    if (gateway.id === "razorpay") {
      await loadScript("https://checkout.razorpay.com/v1/checkout.js");
      const checkout = new window.Razorpay({
        key: order.checkout.key,
        order_id: order.checkout.orderId,
        name: "InkRiver",
        description: `Tip for ${story.author}`,
        prefill: { name: state.user.name, email: state.user.email },
        theme: { color: "#1a7f48" },
        handler: async (result) => {
          try {
            await apiRequest("/api/payments/razorpay/verify", {
              method: "POST",
              body: JSON.stringify({ paymentId: order.paymentId, ...result }),
            });
            state.tipMessage = `${formatMoneyFromINR(state.tipAmount)} tip paid to ${story.author}.`;
          } catch (error) {
            state.tipMessage = error.message;
          }
          render();
        },
      });
      checkout.open();
      return;
    }
    if (gateway.id === "paypal" && order.checkout.approveUrl) window.location.assign(order.checkout.approveUrl);
    else if (gateway.id === "cashfree" && order.checkout.paymentSessionId) {
      await loadScript("https://sdk.cashfree.com/js/v3/cashfree.js");
      await window.Cashfree({ mode: state.gatewaySettings.cashfreeEnvironment === "production" ? "production" : "sandbox" })
        .checkout({ paymentSessionId: order.checkout.paymentSessionId, redirectTarget: "_self" });
    } else if (gateway.id === "payu" && order.checkout.action) {
      const form = document.createElement("form");
      form.method = "POST";
      form.action = order.checkout.action;
      Object.entries(order.checkout.fields).forEach(([name, value]) => {
        const input = document.createElement("input");
        input.type = "hidden";
        input.name = name;
        input.value = value;
        form.appendChild(input);
      });
      document.body.appendChild(form);
      form.submit();
    }
  } catch (error) {
    state.tipMessage = error.message;
    render();
  }
}

function enabledSocialProviders() {
  return [
    state.gatewaySettings.googleLoginEnabled !== false && { id: "google", name: "Google", appField: "googleClientId" },
    state.gatewaySettings.facebookLoginEnabled !== false && { id: "facebook", name: "Facebook", appField: "facebookAppId" },
  ].filter(Boolean);
}

async function socialLogin(providerId) {
  if (!state.providerStatus.social[providerId]) {
    state.loginMessage = `${providerId === "google" ? "Google" : "Facebook"} login is not configured on the server.`;
    render();
    return;
  }
  window.location.assign(`/api/auth/oauth/${providerId}/start`);
}

function applyAuthenticatedUser(user, newAccount = false) {
  state.user = user;
  state.role = user.role;
  state.dashboardSection = "overview";
  state.isMember = !["Free", "Staff"].includes(user.subscription);
  state.recommendation = loadRecommendationProfile(state.user);
  state.following = loadReaderData("following", emptyFollowing(), state.user);
  state.readingHistory = loadReaderData("reading-history", [], state.user);
  state.pollResponses = loadReaderData("interactive-responses", {}, state.user);
  state.onboardingSelection = new Set(state.recommendation.selectedInterests);
  state.onboardingOpen = newAccount && !state.recommendation.completedOnboarding;
  state.loginOpen = false;
  state.authForm = { name: "", email: "", password: "", twoFactorCode: "" };
}

async function submitAuthentication() {
  state.authBusy = true;
  state.loginMessage = "";
  render();
  try {
    const registering = state.authMode === "register";
    const payload = await apiRequest(`/api/auth/${registering ? "register" : "login"}`, {
      method: "POST",
      body: JSON.stringify(state.authForm),
    });
    applyAuthenticatedUser(payload.user, registering);
    await hydratePlatformState();
    await loadRecommendationFeed();
    state.loginMessage = registering ? "Account created securely." : "Signed in successfully.";
    if (payload.user.role === "admin") await loadAdminUsers();
    if (!state.onboardingOpen) setRoute(payload.user.role === "admin" ? "/admin" : "/dashboard");
    else render();
  } catch (error) {
    state.loginMessage = error.message;
    state.loginOpen = true;
    render();
  } finally {
    state.authBusy = false;
  }
}

async function requestPasswordReset() {
  const email = state.authForm.email || window.prompt("Enter your account email address:");
  if (!email?.trim()) return;
  state.authBusy = true;
  state.loginMessage = "";
  render();
  try {
    const payload = await apiRequest("/api/auth/password/forgot", {
      method: "POST",
      body: JSON.stringify({ email: email.trim() }),
    });
    state.loginMessage = payload.resetUrl
      ? `Reset link created: ${payload.resetUrl}`
      : "If that email exists, a password reset link has been sent.";
  } catch (error) {
    state.loginMessage = error.message;
  } finally {
    state.authBusy = false;
    state.loginOpen = true;
    render();
  }
}

async function submitPasswordReset() {
  state.authBusy = true;
  state.loginMessage = "";
  render();
  try {
    await apiRequest("/api/auth/password/reset", {
      method: "POST",
      body: JSON.stringify({ token: state.resetToken, password: state.resetPassword }),
    });
    state.resetToken = "";
    state.resetPassword = "";
    state.authMode = "login";
    state.loginOpen = true;
    state.loginMessage = "Password updated. Sign in with your new password.";
    window.history.replaceState({}, "", "/");
  } catch (error) {
    state.loginMessage = error.message;
  } finally {
    state.authBusy = false;
    render();
  }
}

async function logoutUser() {
  try {
    await apiRequest("/api/auth/logout", { method: "POST", body: "{}" });
  } catch {
    // Clear local state even if the session already expired.
  }
  finalizeArticleSession();
  state.user = null;
  state.role = "reader";
  state.users = [];
  state.recommendation = loadRecommendationProfile(null);
  state.following = loadReaderData("following", emptyFollowing(), null);
  state.readingHistory = loadReaderData("reading-history", [], null);
  state.pollResponses = loadReaderData("interactive-responses", {}, null);
  state.serverRecommendations = [];
  state.serverRecommendationMap = {};
  state.recommendationModel = { signalsCount: 0, lastTrainedAt: "", modelVersion: 0 };
  state.onboardingSelection = new Set(state.recommendation.selectedInterests);
  state.loginMessage = "Signed out.";
  state.loginOpen = false;
  window.history.replaceState({}, "", "/");
  render();
}

function storyUrl(story) {
  return `${window.location.origin}/stories/${story.slug}`;
}

function shareUrls(story) {
  const url = encodeURIComponent(storyUrl(story));
  const title = encodeURIComponent(story.title);
  const text = encodeURIComponent(story.dek);
  return {
    facebook: `https://www.facebook.com/sharer/sharer.php?u=${url}`,
    linkedin: `https://www.linkedin.com/sharing/share-offsite/?url=${url}`,
    whatsapp: `https://wa.me/?text=${title}%20${url}`,
    x: `https://twitter.com/intent/tweet?text=${title}&url=${url}`,
    mail: `mailto:?subject=${title}&body=${text}%0A%0A${url}`,
  };
}

function icon(name, size = 17) {
  const paths = {
    search: '<circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.8-3.8"></path>',
    bell: '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"></path><path d="M13.7 21a2 2 0 0 1-3.4 0"></path>',
    menu: '<path d="M4 6h16"></path><path d="M4 12h16"></path><path d="M4 18h16"></path>',
    close: '<path d="M18 6 6 18"></path><path d="m6 6 12 12"></path>',
    pen: '<path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path>',
    lock: '<rect x="4" y="11" width="16" height="10" rx="2"></rect><path d="M8 11V7a4 4 0 0 1 8 0v4"></path>',
    heart: '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"></path>',
    comment: '<path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4Z"></path>',
    bookmark: '<path d="M19 21 12 17 5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2Z"></path>',
    share: '<circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><path d="m8.6 10.8 6.8-4.6"></path><path d="m8.6 13.2 6.8 4.6"></path>',
    link: '<path d="M10 13a5 5 0 0 0 7.1 0l2-2a5 5 0 0 0-7.1-7.1l-1.1 1.1"></path><path d="M14 11a5 5 0 0 0-7.1 0l-2 2a5 5 0 0 0 7.1 7.1l1.1-1.1"></path>',
    check: '<path d="m20 6-11 11-5-5"></path>',
    spark: '<path d="M12 3 14 9l6 3-6 3-2 6-2-6-6-3 6-3Z"></path>',
    trend: '<path d="m3 17 6-6 4 4 7-8"></path><path d="M14 7h6v6"></path>',
    money: '<circle cx="12" cy="12" r="9"></circle><path d="M8 12h8"></path><path d="M12 8v8"></path>',
    card: '<rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="M3 10h18"></path>',
    users: '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.9"></path><path d="M16 3.1a4 4 0 0 1 0 7.8"></path>',
    chart: '<path d="M3 3v18h18"></path><rect x="7" y="12" width="3" height="5"></rect><rect x="12" y="8" width="3" height="9"></rect><rect x="17" y="5" width="3" height="12"></rect>',
    gauge: '<path d="M12 14l4-4"></path><path d="M3.3 19a9 9 0 1 1 17.4 0"></path>',
    shield: '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"></path><path d="m9 12 2 2 4-4"></path>',
    eye: '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path><circle cx="12" cy="12" r="3"></circle>',
    filter: '<path d="M4 4h16l-6 7v6l-4 2v-8Z"></path>',
    play: '<path d="m8 5 11 7-11 7Z"></path>',
    sun: '<circle cx="12" cy="12" r="4"></circle><path d="M12 2v2"></path><path d="M12 20v2"></path><path d="m4.93 4.93 1.41 1.41"></path><path d="m17.66 17.66 1.41 1.41"></path><path d="M2 12h2"></path><path d="M20 12h2"></path><path d="m6.34 17.66-1.41 1.41"></path><path d="m19.07 4.93-1.41 1.41"></path>',
    moon: '<path d="M20.9 15.2A8.6 8.6 0 0 1 8.8 3.1 8.6 8.6 0 1 0 20.9 15.2Z"></path>',
  };
  return `<svg aria-hidden="true" width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${paths[name] || paths.spark}</svg>`;
}

function setRoute(to) {
  if ((to.startsWith("/dashboard") || to === "/me") && !state.user) {
    state.authMode = "login";
    state.loginMessage = "Sign in to open your dashboard.";
    state.loginOpen = true;
    render();
    return;
  }
  if (to.startsWith("/admin") && state.user?.role !== "admin") {
    state.userMessage = "Administrator access is required.";
    if (!state.user) {
      state.authMode = "login";
      state.loginOpen = true;
      state.loginMessage = "Sign in with an administrator account.";
      render();
    } else {
      setRoute("/dashboard");
    }
    return;
  }
  if (state.articleSession && !to.includes(`/stories/${state.articleSession.slug}`)) finalizeArticleSession();
  if (to === "/admin/blogs/new") {
    state.editingBlogId = "";
    state.blogForm = emptyBlogForm();
    state.blogMessage = "";
  }
  if (to === "/") state.activeTopic = "For you";
  if (to.startsWith("/category/")) {
    const category = state.categories.find((item) => `/category/${item.slug}` === to);
    if (category) state.activeTopic = category.name;
  }
  window.history.pushState({}, "", to);
  state.path = to;
  state.mobileOpen = false;
  state.loginOpen = false;
  window.scrollTo({ top: 0, behavior: "smooth" });
  render();
  if (to.startsWith("/admin/users")) loadAdminUsers();
}

function filteredStories(topic = state.activeTopic) {
  const query = state.query.toLowerCase();
  const matches = publishedStories().filter((story) => {
    const matchesTopic = topic === "For you" || story.topic === topic;
    return matchesTopic && `${story.title} ${story.dek} ${story.author} ${story.topic}`.toLowerCase().includes(query);
  });
  if (topic !== "For you" || query) return matches;
  if (state.serverRecommendations.length) {
    const bySlug = new Map(matches.map((story) => [story.slug, story]));
    const ranked = state.serverRecommendations
      .map((item) => bySlug.get(item.storySlug))
      .filter(Boolean)
      .filter((story) => !state.recommendation.hiddenStories.includes(story.slug));
    const rankedSlugs = new Set(ranked.map((story) => story.slug));
    return [...ranked, ...matches.filter((story) => !rankedSlugs.has(story.slug) && !state.recommendation.hiddenStories.includes(story.slug))]
      .slice(0, matches.length);
  }
  const relevantMatches = state.recommendation.selectedInterests.length
    ? matches.filter((story) => (
      state.recommendation.selectedInterests.includes(story.topic)
      || Number(state.recommendation.topicScores[story.topic] || 0) >= 4
      || storyTags(story).some((tag) => Number(state.recommendation.tagScores[tag] || 0) >= 3)
    ))
    : matches;
  return (relevantMatches.length ? relevantMatches : matches)
    .filter((story) => !state.recommendation.hiddenStories.includes(story.slug))
    .sort((a, b) => recommendationScore(b) - recommendationScore(a));
}

function appTemplate() {
  const selectedStory = state.stories.find((story) => story.status === "published" && state.path.includes(`/stories/${story.slug}`));
  const selectedCategory = state.categories.find((category) => state.path === `/category/${category.slug}`);
  const selectedProfile = state.path.startsWith("/@") ? profileForSlug(state.path.slice(2)) : null;
  const selectedList = state.path.startsWith("/lists/") ? defaultCuratedLists.find((list) => list.id === state.path.split("/").pop()) : null;
  return `
    <div class="app-shell">
      ${headerTemplate()}
      ${
        selectedStory
          ? storyPageTemplate(selectedStory)
          : selectedProfile
            ? profileTemplate(selectedProfile)
          : selectedList
            ? curatedListTemplate(selectedList)
          : selectedCategory
            ? homeTemplate(selectedCategory)
          : state.path === "/me"
            ? readerProfileTemplate()
          : state.path.startsWith("/search")
            ? searchPageTemplate()
          : state.path.startsWith("/privacy")
            ? privacyCenterTemplate()
          : state.path.startsWith("/security")
            ? securityCenterTemplate()
          : state.path.startsWith("/support")
            ? supportCenterTemplate()
          : state.path.startsWith("/admin")
            ? adminRouteTemplate()
            : state.path.startsWith("/dashboard")
              ? dashboardTemplate()
              : state.path.startsWith("/write")
                ? writeTemplate()
                : state.path.startsWith("/pricing")
                  ? pricingTemplate()
                  : homeTemplate()
      }
      ${footerTemplate()}
      ${state.checkoutPlan ? checkoutTemplate(state.checkoutPlan) : ""}
      ${state.loginOpen ? loginTemplate() : ""}
      ${state.onboardingOpen ? onboardingTemplate() : ""}
    </div>
  `;
}

function adminRouteTemplate() {
  if (state.user?.role !== "admin") return accessDeniedTemplate("Administrator access is required.");
  if (state.path === "/admin/blogs/new") {
    if (state.editingBlogId) {
      state.editingBlogId = "";
      state.blogForm = emptyBlogForm();
    }
    return blogEditorPageTemplate();
  }
  if (state.path.startsWith("/admin/blogs/edit/")) {
    const blogId = decodeURIComponent(state.path.split("/").pop() || "");
    const blog = state.stories.find((story) => story.id === blogId);
    if (blog && state.editingBlogId !== blog.id) {
      state.editingBlogId = blog.id;
      state.blogForm = {
        ...blog,
        body: blog.body.join("\n\n"),
        contentHtml: blog.contentHtml || blog.body.map((paragraph) => `<p>${escapeHtml(paragraph)}</p>`).join(""),
        tagsText: (blog.tags || []).join(", "),
        publication: blog.publication || blog.role || "InkRiver",
        interactiveBlocks: structuredClone(blog.interactiveBlocks || []),
        seo: { ...defaultPostSeo(blog), ...(blog.seo || {}) },
      };
    }
    return blogEditorPageTemplate();
  }
  if (state.path.startsWith("/admin/blogs/categories")) return adminCategoriesTemplate();
  if (state.path.startsWith("/admin/blogs")) return adminBlogsTemplate();
  if (state.path.startsWith("/admin/users")) return adminUsersTemplate();
  if (state.path.startsWith("/admin/settings")) return adminSettingsTemplate();
  if (state.path.startsWith("/admin/seo")) return adminSeoTemplate();
  if (state.path.startsWith("/admin/creator")) return creatorStudioTemplate();
  if (state.path.startsWith("/admin/moderation")) return moderationCenterTemplate();
  if (state.path.startsWith("/admin/copyright")) return copyrightCenterTemplate();
  if (state.path.startsWith("/admin/support")) return adminSupportTemplate();
  if (state.path.startsWith("/admin/health")) return platformHealthTemplate();
  if (state.path.startsWith("/admin/security")) return adminSecurityTemplate();
  return adminTemplate();
}

function headerTemplate() {
  const links = [
    ["Topics", "/"],
    ["Membership", "/pricing"],
    ["Dashboard", state.user?.role === "admin" ? "/admin" : "/dashboard"],
  ];
  return `
    <header class="site-header">
      <div class="header-inner">
        <button class="brand" data-route="/" aria-label="InkRiver home"><span class="brand-mark">IR</span><span>InkRiver</span></button>
        <div class="search-shell">
          <label class="search-box">${icon("search")}<input id="searchInput" value="${escapeHtml(state.query)}" placeholder="Search stories, writers, topics" autocomplete="off" /></label>
          ${state.searchOpen ? searchAutocompleteTemplate() : ""}
        </div>
        <nav class="desktop-nav" aria-label="Primary navigation">
          ${links.map(([label, to]) => `<button class="nav-link ${state.path === to ? "active" : ""}" data-route="${to}">${label}</button>`).join("")}
        </nav>
        <div class="header-actions">
          <button class="icon-button theme-button" data-action="toggle-theme" aria-label="Switch to ${state.theme === "day" ? "night" : "day"} mode" title="Switch to ${state.theme === "day" ? "night" : "day"} mode">
            ${icon(state.theme === "day" ? "moon" : "sun")}
          </button>
          <button class="icon-button notification-button" data-action="toggle-push" aria-label="Notifications" title="Notifications">${icon("bell")}</button>
          <button class="member-pill" data-action="${state.user ? "open-account" : "open-login"}">${state.user ? state.user.name.split(" ")[0] : "Join"}</button>
          <button class="menu-button" data-action="toggle-menu" aria-label="Open menu">${icon(state.mobileOpen ? "close" : "menu", 20)}</button>
        </div>
      </div>
      ${state.mobileOpen ? `<div class="mobile-panel">${links.map(([label, to]) => `<button data-route="${to}">${label}</button>`).join("")}<button data-action="${state.user ? "open-account" : "open-login"}">${state.user ? `Account: ${state.user.name}` : "Sign in / Join"}</button></div>` : ""}
    </header>
  `;
}

function searchAutocompleteTemplate() {
  const suggestions = searchSuggestions();
  return `
    <div class="search-autocomplete">
      ${
        suggestions.length
          ? suggestions.map((suggestion) => `
            <button data-route="${suggestion.route}">
              <span>${icon(suggestion.type === "Writer" ? "users" : "search", 15)}</span>
              <span><strong>${escapeHtml(suggestion.label)}</strong><small>${escapeHtml(suggestion.type)} · ${escapeHtml(suggestion.meta)}</small></span>
            </button>
          `).join("")
          : `<span class="search-autocomplete-empty">Press Enter to search all content</span>`
      }
      <button class="search-all-result" data-route="/search">${icon("filter", 15)}View all results and filters</button>
    </div>
  `;
}

function accessDeniedTemplate(message) {
  return `<main class="account-center"><section class="access-denied"><span>${icon("lock", 24)}</span><h1>Access restricted</h1><p>${escapeHtml(message)}</p><button class="primary-button" data-route="${state.user ? "/dashboard" : "/"}">${state.user ? "Open my dashboard" : "Return home"}</button></section></main>`;
}

function searchPageTemplate() {
  const results = advancedSearchResults();
  const authors = [...new Set(publishedStories().map((story) => story.author))].sort();
  return `
    <main class="search-page">
      <section class="search-page-heading">
        <div>
          <h1>Search and discover</h1>
          <p>Search the full article library and narrow results by topic, writer, access, reading time, or popularity.</p>
        </div>
        <label class="search-page-input">${icon("search", 19)}<input id="searchPageInput" value="${escapeHtml(state.query)}" placeholder="Search titles, article text, tags, writers..." /></label>
      </section>
      <section class="search-filter-bar">
        <label><span>Topic</span><select data-search-filter="topic"><option value="all">All topics</option>${state.categories.map((category) => `<option value="${escapeHtml(category.name)}" ${state.searchFilters.topic === category.name ? "selected" : ""}>${escapeHtml(category.name)}</option>`).join("")}</select></label>
        <label><span>Writer</span><select data-search-filter="author"><option value="all">All writers</option>${authors.map((author) => `<option value="${escapeHtml(author)}" ${state.searchFilters.author === author ? "selected" : ""}>${escapeHtml(author)}</option>`).join("")}</select></label>
        <label><span>Access</span><select data-search-filter="access">${[["all", "Free and premium"], ["free", "Free only"], ["premium", "Member-only"]].map(([value, label]) => `<option value="${value}" ${state.searchFilters.access === value ? "selected" : ""}>${label}</option>`).join("")}</select></label>
        <label><span>Reading time</span><select data-search-filter="maxMinutes">${[["all", "Any length"], ["5", "5 minutes or less"], ["7", "7 minutes or less"], ["10", "10 minutes or less"]].map(([value, label]) => `<option value="${value}" ${state.searchFilters.maxMinutes === value ? "selected" : ""}>${label}</option>`).join("")}</select></label>
        <label><span>Sort</span><select data-search-filter="sort">${[["relevance", "Most relevant"], ["popular", "Most popular"], ["newest", "Newest"], ["shortest", "Shortest"]].map(([value, label]) => `<option value="${value}" ${state.searchFilters.sort === value ? "selected" : ""}>${label}</option>`).join("")}</select></label>
        <button class="secondary-button" data-action="clear-search-filters">${icon("close", 15)}Clear</button>
      </section>
      <div class="search-results-summary"><strong>${results.length}</strong><span>result${results.length === 1 ? "" : "s"}${state.query ? ` for "${escapeHtml(state.query)}"` : ""}</span></div>
      <section class="search-results-layout">
        <div class="story-list">
          ${results.length ? results.map((story) => storyCardTemplate(story, false)).join("") : `<div class="empty-state">No stories match these filters. Try a broader topic, longer reading time, or fewer keywords.</div>`}
        </div>
        <aside class="search-discovery-rail">
          <section class="rail-panel">
            <div class="panel-title">${icon("users")}<h2>Writers to follow</h2></div>
            ${Object.entries(defaultProfiles).slice(0, 4).map(([slug, profile]) => `
              <div class="discovery-follow-row">
                <button data-route="/@${slug}"><span class="avatar">${profile.name[0]}</span><span><strong>${profile.name}</strong><small>${profile.expertise[0]}</small></span></button>
                <button class="compact-follow ${isFollowing("writers", profile.name) ? "active" : ""}" data-follow-type="writers" data-follow-value="${escapeHtml(profile.name)}">${followLabel("writers", profile.name)}</button>
              </div>
            `).join("")}
          </section>
          <section class="rail-panel">
            <div class="panel-title">${icon("bookmark")}<h2>Curated lists</h2></div>
            ${defaultCuratedLists.map((list) => `<button class="discovery-list-row" data-route="/lists/${list.id}"><strong>${escapeHtml(list.name)}</strong><span>${list.slugs.length} stories · ${list.followers.toLocaleString("en-IN")} followers</span></button>`).join("")}
          </section>
        </aside>
      </section>
    </main>
  `;
}

function profileTemplate(profile) {
  const stories = publishedStories().filter((story) => story.author === profile.name);
  const featured = stories.sort((a, b) => b.reads - a.reads)[0];
  const publicLists = defaultCuratedLists.filter((list) => list.owner === profile.name);
  const followerCount = profile.followers + (isFollowing("writers", profile.name) ? 1 : 0);
  return `
    <main class="profile-page">
      <section class="profile-hero">
        <div class="profile-avatar">${escapeHtml(profile.name.split(" ").map((part) => part[0]).join("").slice(0, 2))}</div>
        <div class="profile-intro">
          <div class="profile-title-row"><div><h1>${escapeHtml(profile.name)}</h1><span class="profile-badge">${icon("check", 13)}${escapeHtml(profile.badge)}</span></div><button class="primary-button ${isFollowing("writers", profile.name) ? "following" : ""}" data-follow-type="writers" data-follow-value="${escapeHtml(profile.name)}">${followLabel("writers", profile.name)}</button></div>
          <p>${escapeHtml(profile.bio)}</p>
          <div class="profile-meta"><strong>${followerCount.toLocaleString("en-IN")} followers</strong><span>${stories.length} stories</span><span>${escapeHtml(profile.publication)}</span></div>
          <div class="profile-links">${profile.website ? `<a href="${profile.website}" target="_blank" rel="noopener noreferrer">${icon("link", 14)}Website</a>` : ""}${profile.social ? `<span>${escapeHtml(profile.social)}</span>` : ""}</div>
        </div>
      </section>
      <section class="profile-content-grid">
        <div>
          ${featured ? `<section class="profile-featured"><span>Featured story</span>${storyCardTemplate(featured, false)}</section>` : ""}
          <section class="profile-stories"><div class="section-heading"><h2>Latest stories</h2><span>${stories.length} published</span></div>${stories.map((story) => storyCardTemplate(story, false)).join("") || `<div class="empty-state">No published stories yet.</div>`}</section>
        </div>
        <aside class="profile-sidebar">
          <section class="rail-panel"><div class="panel-title">${icon("spark")}<h2>Expertise</h2></div><div class="profile-expertise">${profile.expertise.map((item) => `<button class="${isFollowing("tags", item) ? "active" : ""}" data-follow-type="tags" data-follow-value="${escapeHtml(item)}">${escapeHtml(item)}${isFollowing("tags", item) ? " · Following" : ""}</button>`).join("")}</div></section>
          <section class="rail-panel"><div class="panel-title">${icon("pen")}<h2>Publication</h2></div><strong>${escapeHtml(profile.publication)}</strong><p>Stories and notes curated by ${escapeHtml(profile.name)}.</p><button class="secondary-button wide-button ${isFollowing("publications", profile.publication) ? "active" : ""}" data-follow-type="publications" data-follow-value="${escapeHtml(profile.publication)}">${followLabel("publications", profile.publication)} publication</button></section>
          ${publicLists.length ? `<section class="rail-panel"><div class="panel-title">${icon("bookmark")}<h2>Public lists</h2></div>${publicLists.map((list) => `<button class="discovery-list-row" data-route="/lists/${list.id}"><strong>${escapeHtml(list.name)}</strong><span>${list.slugs.length} stories</span></button>`).join("")}</section>` : ""}
          <section class="rail-panel"><div class="panel-title">${icon("trend")}<h2>Recent activity</h2></div><div class="profile-activity"><span>Published ${stories[0] ? escapeHtml(stories[0].title) : "a new note"}</span><span>Added stories to ${publicLists[0] ? escapeHtml(publicLists[0].name) : "a public reading list"}</span><span>Followed ${escapeHtml(profile.expertise[0])}</span></div></section>
        </aside>
      </section>
    </main>
  `;
}

function curatedListTemplate(list) {
  const stories = list.slugs.map((slug) => state.stories.find((story) => story.slug === slug && story.status === "published")).filter(Boolean);
  return `
    <main class="curated-list-page">
      <section class="curated-list-header">
        <div><span>Curated reading list</span><h1>${escapeHtml(list.name)}</h1><p>${escapeHtml(list.description)}</p><div><strong>By ${escapeHtml(list.owner)}</strong><span>${stories.length} stories</span><span>${(list.followers + (isFollowing("lists", list.id) ? 1 : 0)).toLocaleString("en-IN")} followers</span></div></div>
        <button class="primary-button ${isFollowing("lists", list.id) ? "following" : ""}" data-follow-type="lists" data-follow-value="${list.id}">${followLabel("lists", list.id)} list</button>
      </section>
      <section class="curated-list-layout"><div class="story-list">${stories.map((story) => storyCardTemplate(story, true)).join("")}</div><aside class="right-rail">${recommendationRailTemplate()}${trendingTemplate()}</aside></section>
    </main>
  `;
}

function readerProfileTemplate() {
  const name = state.user?.name || "Guest Reader";
  const savedStories = publishedStories().filter((story) => state.saved.has(story.slug));
  const completed = state.readingHistory.filter((entry) => entry.completed);
  return `
    <main class="profile-page">
      <section class="profile-hero reader-profile-hero">
        <div class="profile-avatar">${escapeHtml(name.split(" ").map((part) => part[0]).join("").slice(0, 2))}</div>
        <div class="profile-intro">
          <div class="profile-title-row"><div><h1>${escapeHtml(name)}</h1><span class="profile-badge">${icon("bookmark", 13)}Reader profile</span></div><button class="secondary-button" data-route="/dashboard">Edit interests</button></div>
          <p>Curious reader following ${state.following.topics.slice(0, 3).map(escapeHtml).join(", ") || "new ideas across InkRiver"}.</p>
          <div class="profile-meta"><strong>${Object.values(state.following).reduce((sum, items) => sum + items.length, 0)} followed</strong><span>${completed.length} completed stories</span><span>${savedStories.length} saved</span></div>
        </div>
      </section>
      <section class="reader-profile-grid">
        <div>
          <section class="profile-stories"><div class="section-heading"><div><h2>Recent reading activity</h2><p>Public highlights from this reader's recent activity.</p></div></div>${state.readingHistory.slice(0, 5).map((entry) => {
            const story = state.stories.find((item) => item.slug === entry.slug);
            return story ? storyCardTemplate(story, false) : "";
          }).join("") || `<div class="empty-state">No public reading activity yet.</div>`}</section>
        </div>
        <aside class="profile-sidebar">
          <section class="rail-panel"><div class="panel-title">${icon("spark")}<h2>Interests</h2></div><div class="profile-expertise">${state.recommendation.selectedInterests.map((interest) => `<span>${escapeHtml(interest)}</span>`).join("") || `<p>No interests selected yet.</p>`}</div></section>
          <section class="rail-panel"><div class="panel-title">${icon("users")}<h2>Following</h2></div><div class="profile-activity">${state.following.writers.slice(0, 4).map((writer) => `<span>Follows ${escapeHtml(writer)}</span>`).join("") || `<span>Not following writers yet</span>`}</div></section>
          <section class="rail-panel"><div class="panel-title">${icon("bookmark")}<h2>Public reading list</h2></div><p>${savedStories.length} saved stories selected for later reading.</p>${savedStories.slice(0, 3).map((story) => `<button class="discovery-list-row" data-route="/stories/${story.slug}"><strong>${escapeHtml(story.title)}</strong><span>${escapeHtml(story.topic)}</span></button>`).join("")}</section>
        </aside>
      </section>
    </main>
  `;
}

function homeTemplate(selectedCategory = null) {
  const activeTopic = selectedCategory?.name || state.activeTopic;
  return `
    <main>
      <section class="home-grid">
        <div class="feed-column">
          <div class="feed-topline">
            <div>
              <h1>${selectedCategory ? escapeHtml(selectedCategory.name) : "Ideas worth returning to."}</h1>
              ${selectedCategory?.description ? `<p class="category-intro">${escapeHtml(selectedCategory.description)}</p>` : ""}
            </div>
            <button class="primary-button" data-route="/write">${icon("pen")}Start writing</button>
          </div>
          <div class="topic-strip" aria-label="Topic filters">
            <button class="topic-chip ${activeTopic === "For you" ? "active" : ""}" data-route="/">For you</button>
            ${state.categories.map((category) => `<button class="topic-chip ${activeTopic === category.name ? "active" : ""}" data-route="/category/${escapeHtml(category.slug)}">${escapeHtml(category.name)}</button>`).join("")}
          </div>
          ${selectedCategory ? `<div class="category-follow-row"><span>${escapeHtml(selectedCategory.description)}</span><button class="secondary-button ${isFollowing("topics", selectedCategory.name) ? "active" : ""}" data-follow-type="topics" data-follow-value="${escapeHtml(selectedCategory.name)}">${followLabel("topics", selectedCategory.name)} topic</button></div>` : ""}
          ${!selectedCategory ? personalizedFeedStatusTemplate() : ""}
          ${!selectedCategory && continueReadingEntries(3).length ? continueReadingTemplate("feed") : ""}
          ${adTemplate("leaderboard", "Leaderboard ad 728 x 90")}
          <div class="story-list">
            ${filteredStories(activeTopic).map((story, index) => storyCardTemplate(story, activeTopic === "For you", index)).join("")}
          </div>
        </div>
        <aside class="right-rail" aria-label="Discovery and account widgets">
          ${subscriptionTemplate()}
          ${recommendationRailTemplate()}
          ${trendingTemplate()}
          ${adTemplate("square", "Premium square ad 300 x 250")}
          ${earnPanelTemplate()}
          ${adTemplate("native", "Native sponsored story")}
        </aside>
      </section>
    </main>
  `;
}

function personalizedFeedStatusTemplate() {
  const signals = state.recommendation.activity.length;
  const interests = state.recommendation.selectedInterests;
  return `
    <section class="personalized-feed-status">
      <span class="personalized-feed-icon">${icon("spark", 17)}</span>
      <div>
        <strong>${hasPersonalizationSignals() ? "Your feed is personalized" : "Your feed will learn as you read"}</strong>
        <span>${
          interests.length
            ? `Prioritizing ${interests.slice(0, 3).map(escapeHtml).join(", ")}${interests.length > 3 ? ` and ${interests.length - 3} more` : ""}.`
            : signals
              ? `Using ${signals} reading signal${signals === 1 ? "" : "s"} to rank these stories.`
              : "Choose interests or start reading to improve your recommendations."
        }</span>
      </div>
      <button data-route="/dashboard">Tune feed</button>
    </section>
  `;
}

function storyCardTemplate(story, personalized = false, index = 0) {
  const displayStory = translatedStory(story);
  const saved = state.saved.has(story.slug);
  const imageUrl = safeImageUrl(story.imageUrl);
  return `
    <article class="story-card">
      <button class="thumbnail ${story.color} ${imageUrl ? "has-image" : ""}" data-route="/stories/${escapeHtml(story.slug)}" aria-label="${escapeHtml(displayStory.title)}">
        ${imageUrl ? `<img src="${escapeHtml(imageUrl)}" alt="" loading="lazy" decoding="async" />` : ""}
        <span>${escapeHtml(story.topic)}</span>
      </button>
      <div class="story-body">
        <div class="author-row"><button class="author-link" data-route="/@${slugifyName(story.author)}"><span class="avatar">${escapeHtml(story.author[0] || "I")}</span><span>${escapeHtml(story.author)}</span></button><span class="muted">in ${escapeHtml(story.publication)}</span></div>
        ${personalized ? `<div class="recommendation-reason">${icon("spark", 13)}<span>${index === 0 ? "Top match: " : ""}${escapeHtml(recommendationReason(story))}</span></div>` : ""}
        <button class="story-title" data-route="/stories/${escapeHtml(story.slug)}">${escapeHtml(displayStory.title)}</button>
        <p>${escapeHtml(displayStory.dek)}</p>
        <div class="story-meta">
          ${story.premium ? `<span class="premium-label">${icon("lock", 13)}Member-only</span>` : ""}
          <span>${escapeHtml(story.readTime)}</span>
          <button data-clap="${story.slug}">${icon("heart", 15)}${state.claps[story.slug].toLocaleString("en-IN")}</button>
          <button>${icon("comment", 15)}${story.comments}</button>
          <button data-save="${story.slug}">${icon("bookmark", 15)}${saved ? "In Read Later" : "Read later"}</button>
          ${personalized ? `<button data-not-interested="${story.slug}">${icon("eye", 15)}Show less</button>` : ""}
        </div>
      </div>
    </article>
  `;
}

function continueReadingTemplate(context = "dashboard") {
  const entries = continueReadingEntries(context === "feed" ? 3 : 5);
  if (!entries.length) return "";
  return `
    <section class="continue-reading ${context}">
      <div class="continue-reading-heading"><div class="panel-title">${icon("play", 16)}<h2>Continue reading</h2></div>${context === "feed" ? `<button data-route="/dashboard">View history</button>` : ""}</div>
      <div class="continue-reading-list">
        ${entries.map((entry) => {
          const story = state.stories.find((item) => item.slug === entry.slug);
          if (!story) return "";
          return `
            <button data-resume-story="${story.slug}">
              <span class="continue-reading-art ${story.color}">${escapeHtml(story.topic.slice(0, 2).toUpperCase())}</span>
              <span><strong>${escapeHtml(story.title)}</strong><small>${entry.progress}% read · ${escapeHtml(story.readTime)}</small><i><span style="width:${entry.progress}%"></span></i></span>
            </button>
          `;
        }).join("")}
      </div>
    </section>
  `;
}

function recommendationRailTemplate() {
  const topInterests = topRecommendationInterests(3).filter((interest) => interest.score > 0);
  return `
    <section class="rail-panel recommendation-rail">
      <div class="panel-title">${icon("spark")}<h2>For you</h2></div>
      <p>${hasPersonalizationSignals() ? "Ranked from your selected interests and reading behavior." : "Your recommendations become more precise as you read, save, and react."}</p>
      <div class="recommendation-rail-topics">
        ${
          topInterests.length
            ? topInterests.map((interest) => `<span><strong>${escapeHtml(interest.name)}</strong><i style="--interest-strength:${Math.max(12, Math.min(100, interest.score * 5))}%"></i></span>`).join("")
            : `<span class="recommendation-empty">No reading profile yet</span>`
        }
      </div>
      <button class="secondary-button wide-button" data-route="/dashboard">Manage interests</button>
    </section>
  `;
}

function subscriptionTemplate() {
  return `
    <section class="rail-panel subscribe-panel">
      <div class="panel-icon">${icon("spark")}</div>
      <h2>${state.isMember ? "Your membership is active" : "Read without limits"}</h2>
      <p>${state.isMember ? "Member-only articles, cleaner reading, and writer support are enabled." : "Unlock member stories, remove reader ads, and support writers from INR 299/month."}</p>
      <button class="full-button" data-checkout="${state.plans[0]?.id || ""}">${state.isMember ? "Manage plan" : "Become a member"}</button>
    </section>
  `;
}

function trendingTemplate() {
  return `
    <section class="rail-panel">
      <div class="panel-title">${icon("trend")}<h2>Trending now</h2></div>
      ${publishedStories().slice(0, 3).map((story, index) => `
        <button class="trend-row" data-route="/stories/${story.slug}">
          <span>${String(index + 1).padStart(2, "0")}</span><strong>${escapeHtml(story.title)}</strong>
        </button>
      `).join("")}
    </section>
  `;
}

function earnPanelTemplate() {
  return `
    <section class="rail-panel earn-panel">
      <div class="panel-title">${icon("money")}<h2>Write to earn</h2></div>
      <div class="earn-total">${formatINR(42830)}</div>
      <p>Projected partner payout from member read time, engagement, and conversion bonuses this month.</p>
      <div class="mini-bars" aria-label="Monthly earnings chart">${[35, 54, 48, 72, 64, 88].map((height) => `<span style="height:${height}%"></span>`).join("")}</div>
    </section>
  `;
}

function storyPageTemplate(story) {
  const displayStory = translatedStory(story);
  const locked = story.premium && !state.isMember;
  const saved = state.saved.has(story.slug);
  const shares = shareUrls(displayStory);
  const imageUrl = safeImageUrl(story.imageUrl);
  const contentHtml = storyContentHtml(displayStory, locked);
  const history = state.readingHistory.find((entry) => entry.slug === story.slug);
  return `
    <main class="article-shell">
      ${state.preferences.focusMode ? `<button class="focus-exit-floating" data-reader-mode="focus" aria-label="Exit focus mode">${icon("close", 16)}<span>Exit focus</span></button>` : ""}
      <article class="article-page">
        <div class="article-progress-track"><span id="articleProgressBar" style="width:${history?.progress || 0}%"></span></div>
        ${readerToolbarTemplate(story, history)}
        ${translationStatusLabel(story) ? `<div class="translation-status">${escapeHtml(translationStatusLabel(story))}</div>` : ""}
        <details class="reader-toc"><summary>${icon("filter", 14)}Table of contents</summary><nav><a href="#article-start">Introduction</a><a href="#article-body">Article</a>${story.interactiveBlocks?.length ? `<a href="#article-interactive">Reader questions</a>` : ""}<a href="#comments">Discussion</a></nav></details>
        <button class="back-link" data-route="/">Back to feed</button>
        <div class="article-kicker" id="article-start">${escapeHtml(story.topic)}</div>
        <h1>${escapeHtml(displayStory.title)}</h1>
        <p class="article-dek">${escapeHtml(displayStory.dek)}</p>
        <div class="article-author-row">
          <button class="article-author" data-route="/@${slugifyName(story.author)}"><span class="avatar large">${escapeHtml(story.author[0] || "I")}</span><div><strong>${escapeHtml(story.author)}</strong><span>${escapeHtml(story.publication)} · ${escapeHtml(story.readTime)}</span></div></button>
          <div class="article-follow-actions">
            <button class="secondary-button ${isFollowing("writers", story.author) ? "active" : ""}" data-follow-type="writers" data-follow-value="${escapeHtml(story.author)}">${followLabel("writers", story.author)} writer</button>
            <button class="secondary-button ${isFollowing("publications", story.publication) ? "active" : ""}" data-follow-type="publications" data-follow-value="${escapeHtml(story.publication)}">${followLabel("publications", story.publication)} publication</button>
          </div>
        </div>
        <div class="article-visual ${story.color} ${imageUrl ? "has-image" : ""}">
          ${imageUrl ? `<img src="${escapeHtml(imageUrl)}" alt="" loading="eager" decoding="async" fetchpriority="high" />` : ""}
          <span>${escapeHtml(story.topic)}</span>
        </div>
        <div class="article-actions">
          <button data-clap="${story.slug}">${icon("heart")}${state.claps[story.slug].toLocaleString("en-IN")}</button>
          <button>${icon("comment")}${story.comments}</button>
          <button data-save="${story.slug}">${icon("bookmark")}${saved ? "In Read Later" : "Read later"}</button>
          <button data-not-interested="${story.slug}">${icon("eye")}Show less like this</button>
          <button data-recommendation-panel="${story.slug}">${icon("spark")}Why this?</button>
        </div>
        ${state.recommendationPanel === story.slug ? recommendationTransparencyTemplate(story) : ""}
        ${shareTemplate(displayStory, shares)}
        ${articleInsightTemplate(displayStory)}
        <div class="article-content ${locked ? "faded" : ""}" id="article-body">
          ${contentHtml}
        </div>
        ${!locked ? interactiveBlocksTemplate(story) : ""}
        <div class="article-tags">
          ${(story.tags || []).map((tag) => `<button class="${isFollowing("tags", tag) ? "active" : ""}" data-follow-type="tags" data-follow-value="${escapeHtml(tag)}">#${escapeHtml(tag)}${isFollowing("tags", tag) ? " · Following" : ""}</button>`).join("")}
        </div>
        ${locked ? `
          <section class="paywall-card">
            ${icon("lock", 22)}
            <h2>Continue reading with membership</h2>
            <p>This story is behind the member paywall. Subscribe to unlock the full library and support writers.</p>
            <div class="paywall-actions">
              <button class="primary-button" data-checkout="${state.plans[0]?.id || ""}">Unlock from ${formatMoneyFromINR(state.plans[0]?.price || 299)}</button>
              <button class="secondary-button">Use friend link</button>
            </div>
          </section>
        ` : ""}
        ${!locked && state.creatorTools.tipsEnabled ? writerTipTemplate(story) : ""}
        ${commentsTemplate(story)}
        ${articleRecommendationsTemplate(story)}
      </article>
    </main>
  `;
}

function readerToolbarTemplate(story, history) {
  const minutes = Math.max(1, Math.ceil((100 - (history?.progress || 0)) / 100 * Number.parseInt(story.readTime, 10)));
  return `<div class="reader-toolbar">
    <button class="${state.preferences.focusMode ? "active exit-focus" : ""}" data-reader-mode="focus" title="${state.preferences.focusMode ? "Exit focus mode" : "Enter focus mode"}" aria-label="${state.preferences.focusMode ? "Exit focus mode" : "Enter focus mode"}">${icon(state.preferences.focusMode ? "close" : "eye", 15)}<span>${state.preferences.focusMode ? "Exit focus" : "Focus"}</span></button>
    <div class="segmented-control compact"><button class="${state.preferences.fontFamily === "serif" ? "active" : ""}" data-reader-font="serif">Serif</button><button class="${state.preferences.fontFamily === "sans" ? "active" : ""}" data-reader-font="sans">Sans</button></div>
    <button data-reader-scale="-10" title="Decrease text">A−</button><button data-reader-scale="10" title="Increase text">A+</button>
    <button data-action="toggle-speech">${icon(state.speechActive ? "pause" : "play", 15)}<span>${state.speechActive ? "Pause" : "Listen"}</span></button>
    <span class="remaining-time">${minutes} min remaining</span>
    <select data-locale aria-label="Article language">${readerLanguageOptions().map((item) => `<option value="${escapeHtml(item.locale)}" ${state.preferences.locale === item.locale ? "selected" : ""}>${escapeHtml(item.language)}</option>`).join("")}</select>
  </div>`;
}

function articleInsightTemplate(story) {
  const points = [
    story.dek,
    `The central idea connects ${story.topic.toLowerCase()} with practical decisions readers can apply.`,
    `Estimated difficulty: ${Number.parseInt(story.readTime, 10) > 7 ? "Intermediate" : "Accessible"}.`,
  ];
  return `<details class="article-insights" open><summary>${icon("spark", 16)}Article overview and key concepts</summary><p>${escapeHtml(story.dek)}</p><ul>${points.slice(1).map((point) => `<li>${escapeHtml(point)}</li>`).join("")}</ul><div><span>Reading level: General audience</span><span>Key concepts: ${(story.tags || [story.topic]).slice(0,3).map(escapeHtml).join(", ")}</span></div></details>`;
}

function recommendationTransparencyTemplate(story) {
  const server = state.serverRecommendationMap[story.slug];
  const factors = server?.factors?.length
    ? server.factors.map((factor) => `<span><strong>${escapeHtml(factor.label)}</strong>${escapeHtml(factor.kind)} · ${Number(factor.weight || 0).toFixed(1)}</span>`).join("")
    : `<span><strong>${story.topic}</strong>Selected interest</span><span><strong>${story.author}</strong>${isFollowing("writers", story.author) ? "Followed writer" : "Related writer"}</span><span><strong>${story.publication}</strong>${isFollowing("publications", story.publication) ? "Followed publication" : "Relevant publication"}</span>`;
  return `<section class="recommendation-explainer"><header><div><h2>Why you are seeing this</h2><p>${escapeHtml(recommendationReason(story))}. ${server ? `Server model v${server.modelVersion} last scored this story at ${Number(server.score || 0).toFixed(1)}.` : "Your feed also considers reading completion, saves, follows, and recent activity."}</p></div><button data-recommendation-panel="${story.slug}">${icon("close", 15)}</button></header><div class="recommendation-factors">${factors}</div><div class="recommendation-controls"><button data-action-story="more" data-story-slug="${story.slug}">${icon("heart",14)}More like this</button><button data-action-story="less" data-story-slug="${story.slug}">${icon("eye",14)}Less like this</button><button data-action-story="hide-topic" data-story-slug="${story.slug}">Hide ${story.topic}</button><button data-action-story="unfollow-author" data-story-slug="${story.slug}">Unfollow ${story.author}</button><button data-action="reset-recommendations">Reset recommendations</button></div></section>`;
}

function writerTipTemplate(story) {
  return `<section class="writer-tip"><div><span>${icon("money", 19)}</span><div><h2>Support ${escapeHtml(story.author)}</h2><p>Send a one-time tip. ${state.creatorTools.tipCommission}% supports platform operations; the rest is included in the writer's next payout.</p></div></div><div class="tip-amounts">${[100,200,500,1000].map((amount) => `<button class="${state.tipAmount === amount ? "active" : ""}" data-tip-amount="${amount}">${formatINR(amount)}</button>`).join("")}</div><button class="primary-button" data-tip-story="${story.slug}">Tip with ${paymentGateways.find((gateway) => gateway.id === state.gateway)?.name || "Razorpay"}</button>${state.tipMessage && state.tipStory === story.slug ? `<span>${escapeHtml(state.tipMessage)}</span>` : ""}</section>`;
}

function interactiveBlocksTemplate(story) {
  if (!story.interactiveBlocks?.length) return "";
  return `
    <section class="interactive-blocks" id="article-interactive">
      ${story.interactiveBlocks.map((block) => interactiveBlockTemplate(story, block)).join("")}
    </section>
  `;
}

function interactiveBlockTemplate(story, block) {
  const responseKey = `${story.slug}:${block.id}`;
  const response = state.pollResponses[responseKey];
  const votes = block.options.map((_, index) => 18 + ((story.slug.length * (index + 3) + block.id.length) % 37) + (response?.selected?.includes(index) ? 1 : 0));
  const total = votes.reduce((sum, value) => sum + value, 0);
  const answered = Boolean(response?.submitted);
  return `
    <section class="interactive-card ${block.type}">
      <div class="interactive-card-heading"><span>${block.type === "quiz" ? "Knowledge check" : block.type === "survey" ? "Reader survey" : "Reader poll"}</span><small>${answered ? "Response saved" : block.multiple ? "Select all that apply" : "Choose one answer"}</small></div>
      <h2>${escapeHtml(block.question)}</h2>
      <div class="interactive-options">
        ${block.options.map((option, index) => {
          const selected = response?.selected?.includes(index);
          const correct = block.type === "quiz" && answered && index === block.correctIndex;
          const incorrect = block.type === "quiz" && answered && selected && index !== block.correctIndex;
          return `
            <button class="${selected ? "selected" : ""} ${correct ? "correct" : ""} ${incorrect ? "incorrect" : ""}" data-interactive-story="${story.slug}" data-interactive-id="${block.id}" data-interactive-option="${index}" data-interactive-multiple="${block.multiple ? "true" : "false"}">
              <span>${block.type === "survey" && block.multiple ? (selected ? icon("check", 14) : "") : String.fromCharCode(65 + index)}</span>
              <strong>${escapeHtml(option)}</strong>
              ${answered && block.type !== "quiz" ? `<i><span style="width:${Math.round((votes[index] / total) * 100)}%"></span></i><em>${Math.round((votes[index] / total) * 100)}%</em>` : ""}
            </button>
          `;
        }).join("")}
      </div>
      ${answered && block.type === "quiz" ? `<div class="quiz-result ${response.selected.includes(block.correctIndex) ? "correct" : "incorrect"}"><strong>${response.selected.includes(block.correctIndex) ? "Correct" : "Not quite"}</strong><span>${escapeHtml(block.explanation || "Review the article and try again.")}</span></div>` : ""}
      ${block.multiple && !answered ? `<button class="primary-button" data-submit-interactive="${story.slug}:${block.id}">Submit response</button>` : ""}
      ${answered ? `<button class="text-button" data-reset-interactive="${story.slug}:${block.id}">Change response</button>` : ""}
    </section>
  `;
}

function commentsTemplate(story) {
  const comments = storyComments(story.slug);
  const roots = comments.filter((comment) => !comment.parentId);
  return `
    <section class="comments-section" id="comments">
      <div class="comments-heading"><div><h2>Discussion</h2><span>${comments.length} response${comments.length === 1 ? "" : "s"}</span></div><label>Sort<select id="commentSort"><option value="top" ${state.commentSort === "top" ? "selected" : ""}>Top</option><option value="newest" ${state.commentSort === "newest" ? "selected" : ""}>Newest</option></select></label></div>
      <div class="comment-composer">
        <span class="avatar">${escapeHtml(currentCommentAuthor()[0])}</span>
        <div>
          ${state.commentReplyTo ? `<span class="comment-context">Replying to ${escapeHtml(comments.find((comment) => comment.id === state.commentReplyTo)?.author || "reader")} <button data-action="cancel-comment">Cancel</button></span>` : ""}
          ${state.commentEditId ? `<span class="comment-context">Editing your response <button data-action="cancel-comment">Cancel</button></span>` : ""}
          <textarea id="commentDraft" placeholder="Add to the discussion. Use @name to mention someone.">${escapeHtml(state.commentDraft)}</textarea>
          <div><small>Be specific, constructive, and kind.</small><button class="primary-button" data-comment-submit="${story.slug}">${state.commentEditId ? "Save edit" : state.commentReplyTo ? "Post reply" : "Post response"}</button></div>
        </div>
      </div>
      <div class="comment-list">
        ${roots.length ? roots.map((comment) => commentThreadTemplate(story.slug, comment, comments)).join("") : `<div class="empty-state">Start the discussion with a thoughtful response.</div>`}
      </div>
    </section>
  `;
}

function commentThreadTemplate(storySlug, comment, comments) {
  const replies = comments.filter((item) => item.parentId === comment.id);
  const ownComment = comment.author === currentCommentAuthor();
  const canModerate = ["moderator", "admin"].includes(state.role);
  const liked = comment.likedBy?.includes(state.user?.email || "guest");
  return `
    <article class="comment-thread ${comment.pinned ? "pinned" : ""} ${comment.reported ? "reported" : ""}">
      <div class="comment-main">
        <span class="avatar">${escapeHtml(comment.author[0])}</span>
        <div>
          <div class="comment-author-line"><strong>${escapeHtml(comment.author)}</strong>${comment.writerReply ? `<span>Writer</span>` : ""}${comment.pinned ? `<span>${icon("bookmark", 12)}Pinned</span>` : ""}<small>${new Date(comment.createdAt).toLocaleDateString("en-IN", { day: "numeric", month: "short" })}${comment.editedAt ? " · Edited" : ""}</small></div>
          <p>${escapeHtml(comment.text).replace(/(@[\w ]+)/g, "<mark>$1</mark>")}</p>
          <div class="comment-actions">
            <button class="${liked ? "active" : ""}" data-comment-like="${storySlug}:${comment.id}">${icon("heart", 14)}${comment.likes}</button>
            <button data-comment-reply="${comment.id}">${icon("comment", 14)}Reply</button>
            ${ownComment ? `<button data-comment-edit="${storySlug}:${comment.id}">${icon("pen", 14)}Edit</button>` : ""}
            <button data-comment-report="${storySlug}:${comment.id}">${icon("shield", 14)}${comment.reported ? "Reported" : "Report"}</button>
            ${canModerate ? `<button data-comment-pin="${storySlug}:${comment.id}">${icon("bookmark", 14)}${comment.pinned ? "Unpin" : "Pin"}</button><button class="danger-text" data-comment-delete="${storySlug}:${comment.id}">Delete</button>` : ""}
          </div>
        </div>
      </div>
      ${replies.length ? `<div class="comment-replies">${replies.map((reply) => commentThreadTemplate(storySlug, reply, comments)).join("")}</div>` : ""}
    </article>
  `;
}

function articleRecommendationsTemplate(story) {
  return `
    <section class="article-recommendations">
      <div class="article-recommendations-heading">
        <div>
          <h2>Continue with your interests</h2>
          <p>Selected from your reading profile and this story.</p>
        </div>
        <button data-route="/dashboard">Tune recommendations</button>
      </div>
      <div class="article-recommendation-list">
        ${relatedStories(story).map((candidate) => `
          <button class="article-recommendation" data-route="/stories/${candidate.slug}">
            <span class="dot ${candidate.color}"></span>
            <span>
              <small>${escapeHtml(recommendationReason(candidate))}</small>
              <strong>${escapeHtml(candidate.title)}</strong>
              <em>${escapeHtml(candidate.topic)} - ${escapeHtml(candidate.readTime)}</em>
            </span>
          </button>
        `).join("")}
      </div>
    </section>
  `;
}

function shareTemplate(story, shares) {
  return `
    <section class="share-panel" aria-label="Share this story">
      <div class="panel-title">${icon("share")}<h2>Share this story</h2></div>
      <div class="share-actions">
        <a class="share-button facebook" href="${shares.facebook}" target="_blank" rel="noopener noreferrer" aria-label="Share on Facebook" data-share-signal="${story.slug}"><span>f</span>Facebook</a>
        <a class="share-button linkedin" href="${shares.linkedin}" target="_blank" rel="noopener noreferrer" aria-label="Share on LinkedIn" data-share-signal="${story.slug}"><span>in</span>LinkedIn</a>
        <a class="share-button whatsapp" href="${shares.whatsapp}" target="_blank" rel="noopener noreferrer" aria-label="Share on WhatsApp" data-share-signal="${story.slug}"><span>WA</span>WhatsApp</a>
        <a class="share-button more-share" href="${shares.x}" target="_blank" rel="noopener noreferrer" aria-label="Share on X" data-share-signal="${story.slug}"><span>X</span>More</a>
        <a class="share-button email-share" href="${shares.mail}" aria-label="Share by email" data-share-signal="${story.slug}"><span>@</span>Email</a>
        <button class="share-button copy-link ${state.copiedShare === story.slug ? "copied" : ""}" data-copy-link="${story.slug}" data-copy-url="${storyUrl(story)}">${icon("link", 15)}${state.copiedShare === story.slug ? "Copied" : "Copy link"}</button>
        <button class="share-button native-share" data-native-share="${story.slug}" data-share-url="${storyUrl(story)}" data-share-title="${story.title}" data-share-text="${story.dek}">${icon("share", 15)}Share</button>
      </div>
    </section>
  `;
}

function pricingTemplate() {
  return `
    <main class="pricing-page">
      <section class="page-heading">
        <h1>Membership plans</h1>
        <p>Subscriptions unlock member-only content, reduce advertising, and fund the writer earning pool.</p>
        ${currencyControlTemplate("pricing")}
      </section>
      <section class="pricing-grid">
        ${state.plans.map((plan, index) => `
          <article class="plan-card ${index === 1 ? "featured" : ""}">
            <h2>${plan.name}</h2>
            <div class="plan-price">${formatMoneyFromINR(plan.price)}<span>/${plan.period}</span></div>
            <small class="base-price">Base price ${formatINR(plan.price)}</small>
            <p>${plan.note}</p>
            <ul>${plan.features.map((feature) => `<li>${icon("check", 15)}${feature}</li>`).join("")}</ul>
            <button class="full-button" data-checkout="${plan.id}">Choose ${plan.name}</button>
          </article>
        `).join("")}
      </section>
      <section class="gateway-strip">
        ${paymentGateways.map((item) => `<div>${icon("card")}<strong>${item.name}</strong><span>${item.type}</span></div>`).join("")}
      </section>
    </main>
  `;
}

function currencyControlTemplate(context) {
  return `
    <div class="currency-control">
      <label>
        <span>Display currency</span>
        <select data-currency-select="${context}">
          ${supportedCurrencies.map((currency) => `<option value="${currency.code}" ${state.currency === currency.code ? "selected" : ""}>${currency.code} - ${currency.label}</option>`).join("")}
        </select>
      </label>
      <div>
        <strong>${rateLine()}</strong>
        <span>${state.rateStatus}${state.rateUpdatedAt ? ` - ${state.rateUpdatedAt}` : ""}</span>
      </div>
      <button class="secondary-button" data-action="refresh-rates">Refresh rates</button>
    </div>
  `;
}

function writeTemplate() {
  if (!["writer", "admin"].includes(state.user?.role)) {
    return accessDeniedTemplate("Writing tools are available from the dashboard for approved writer accounts.");
  }
  const estimated = state.draft.earning ? 11800 : 0;
  return `
    <main class="studio-page">
      <section class="studio-main">
        <div class="studio-toolbar">
          <button class="secondary-button" data-action="save-writer-draft">${icon("pen", 16)}Save draft</button>
          <button class="secondary-button" data-action="toggle-writer-preview">${icon("eye", 16)}${state.draftPreview ? "Close preview" : "Preview"}</button>
          <button class="primary-button" data-action="publish-writer-story">${icon("play", 16)}Publish</button>
        </div>
        <input class="title-input" id="draftTitle" value="${state.draft.title}" aria-label="Story title" />
        <textarea class="subtitle-input" id="draftSubtitle" aria-label="Story subtitle">${state.draft.subtitle}</textarea>
        <textarea class="body-editor" id="draftBody" aria-label="Story body">${escapeHtml(state.draft.body)}</textarea>
        ${state.draftMessage ? `<div class="payment-message">${escapeHtml(state.draftMessage)}</div>` : ""}
        ${state.draftPreview ? `<article class="article-page writer-preview"><div class="article-kicker">${escapeHtml(state.draft.topic)}</div><h1>${escapeHtml(state.draft.title)}</h1><p class="article-dek">${escapeHtml(state.draft.subtitle)}</p><div class="article-content">${state.draft.body.split(/\n{2,}/).map((paragraph) => `<p>${escapeHtml(paragraph)}</p>`).join("")}</div></article>` : ""}
      </section>
      <aside class="studio-side">
        <h2>Publish settings</h2>
        <label><span>Topic</span><select id="draftTopic">${categoryNames().map((topic) => `<option ${state.draft.topic === topic ? "selected" : ""}>${escapeHtml(topic)}</option>`).join("")}</select></label>
        ${toggleTemplate("Paywall this story", "paywalled", state.draft.paywalled)}
        ${toggleTemplate("Allow ad placements", "ads", state.draft.ads)}
        ${toggleTemplate("Eligible for write to earn", "earning", state.draft.earning)}
        <div class="earn-preview"><span>Estimated monthly upside</span><strong>${formatINR(estimated)}</strong></div>
      </aside>
    </main>
  `;
}

function legacyDashboardTemplate() {
  if (!state.user) return accessDeniedTemplate("Sign in to open your dashboard.");
  const visibleStories = publishedStories();
  const readLaterStories = visibleStories.filter((story) => state.saved.has(story.slug));
  const writerStories = state.user.role === "writer"
    ? visibleStories.filter((story) => story.author.toLowerCase() === state.user.name.toLowerCase())
    : [];
  return `
    <main class="dashboard-page">
      ${dashboardHeaderTemplate("User dashboard")}
      <section class="dashboard-grid">
        ${metricTemplate("eye", "Articles opened", String(state.readingHistory.length), "Your history")}
        ${metricTemplate("bookmark", "Read later", String(readLaterStories.length), "Saved")}
        ${metricTemplate("users", "Following", String(Object.values(state.following).reduce((sum, items) => sum + items.length, 0)), "People and interests")}
        ${metricTemplate("shield", "Membership", escapeHtml(state.user.subscription), state.isMember ? "Active" : "Free plan")}
      </section>
      ${continueReadingTemplate("dashboard")}
      ${interestManagerTemplate()}
      ${followingManagerTemplate()}
      <section class="dashboard-columns">
        <div class="work-panel">
          <div class="panel-title">${icon(state.user.role === "writer" ? "chart" : "play")}<h2>${state.user.role === "writer" ? "Your published stories" : "Recent reading"}</h2></div>
          ${
            state.user.role === "writer"
              ? writerStories.map((story) => `
                  <button class="dashboard-story" data-route="/stories/${story.slug}">
                    <span class="dot ${story.color}"></span>
                    <span><strong>${escapeHtml(story.title)}</strong><small>${story.reads.toLocaleString("en-IN")} reads · ${formatINR(story.revenue)} earned</small></span>
                  </button>
                `).join("") || `<div class="empty-state">Stories published under your account will appear here.</div>`
              : state.readingHistory.slice(0, 6).map((entry) => `
                  <button class="dashboard-story" data-resume-story="${entry.slug}">
                    <span class="history-state ${entry.completed ? "complete" : ""}">${entry.completed ? icon("check", 14) : `${entry.progress}%`}</span>
                    <span><strong>${escapeHtml(entry.title)}</strong><small>${entry.completed ? "Completed" : `${entry.progress}% read`} · ${escapeHtml(entry.topic)}</small></span>
                  </button>
                `).join("") || `<div class="empty-state">Your reading activity will appear here.</div>`
          }
        </div>
        <div class="work-panel">
          <div class="panel-title">${icon("card")}<h2>Subscription</h2></div>
          <div class="subscription-state"><strong>${state.isMember ? "Annual Plus" : "Free reader"}</strong><span>${state.isMember ? "Renews in 214 days" : "Upgrade to unlock member stories"}</span></div>
          <button class="full-button" data-route="/pricing">Manage membership</button>
        </div>
        ${readingProfileTemplate()}
        <div class="work-panel read-later-panel">
          <div class="panel-title">${icon("bookmark")}<h2>Read later</h2></div>
          ${
            readLaterStories.length
              ? readLaterStories.map((story) => `
                <button class="dashboard-story" data-route="/stories/${story.slug}">
                  <span class="dot ${story.color}"></span>
                  <span><strong>${escapeHtml(story.title)}</strong><small>${escapeHtml(story.topic)} - ${escapeHtml(story.readTime)}</small></span>
                </button>
              `).join("")
              : `<div class="empty-state">Save stories from the feed or article page and they will appear here.</div>`
          }
        </div>
      </section>
      ${readingHistoryTemplate()}
    </main>
  `;
}

function dashboardTemplate() {
  if (!state.user) return accessDeniedTemplate("Sign in to open your dashboard.");
  const role = state.user.role;
  const section = state.dashboardSection;
  const roleLabel = role === "writer" ? "Writer workspace" : role === "moderator" ? "Moderator workspace" : role === "subscriber" ? "Member workspace" : "Reader workspace";
  return `
    <main class="admin-app member-app">
      <aside class="admin-sidebar member-sidebar">
        <div class="admin-sidebar-brand"><span class="admin-sidebar-brand-icon">${icon(role === "writer" ? "pen" : role === "moderator" ? "shield" : "users", 20)}</span><span><strong>${roleLabel}</strong><small>InkRiver account</small></span></div>
        <div class="admin-sidebar-heading">My workspace</div>
        <nav aria-label="${roleLabel} navigation">${dashboardNavItems(role).map(([iconName, label, id]) => `<button class="admin-nav-item ${section === id ? "active" : ""}" data-dashboard-section="${id}"><span class="admin-nav-icon">${icon(iconName, 18)}</span><span>${label}</span></button>`).join("")}</nav>
        <div class="admin-sidebar-footer">
          <button class="admin-sidebar-site-link" data-route="/">${icon("eye", 17)}<span>Return to feed</span></button>
          <button class="admin-sidebar-account" data-action="open-account"><span class="admin-sidebar-avatar">${escapeHtml(state.user.name[0] || "U")}</span><span><strong>${escapeHtml(state.user.name)}</strong><small>${escapeHtml(role)} · ${escapeHtml(state.user.subscription)}</small></span></button>
        </div>
      </aside>
      <section class="admin-main member-main">
        <header class="admin-page-header member-page-header"><div><h1>${escapeHtml(dashboardSectionTitle(section))}</h1><p>${escapeHtml(dashboardSectionDescription(section, role))}</p></div><div class="member-header-actions">${role === "writer" ? `<button class="primary-button" data-route="/write">${icon("pen", 16)}Write story</button>` : ""}<button class="secondary-button" data-action="open-account">${icon("users", 15)}Account</button></div></header>
        ${dashboardSectionTemplate(section)}
      </section>
    </main>
  `;
}

function dashboardNavItems(role) {
  const items = [["chart", "Overview", "overview"], ["play", "Reading", "reading"], ["spark", "Interests", "interests"], ["users", "Following", "following"], ["eye", "History", "history"], ["card", "Membership", "membership"]];
  if (role === "writer") items.splice(1, 0, ["pen", "My stories", "stories"], ["chart", "Analytics", "analytics"], ["money", "Earnings", "earnings"]);
  if (role === "moderator") items.splice(1, 0, ["shield", "Moderation queue", "moderation"], ["filter", "Reports", "reports"]);
  items.push(["lock", "Security", "security"], ["users", "Profile", "profile"]);
  return items;
}

function dashboardSectionTitle(section) {
  return { overview: "Dashboard overview", reading: "Reading workspace", interests: "Reading interests", following: "Following", history: "Reading history", membership: "Membership and billing", stories: "My stories", analytics: "Creator analytics", earnings: "Writer earnings", moderation: "Moderation queue", reports: "Reports and activity", security: "Security", profile: "Profile and account" }[section] || "Dashboard overview";
}

function dashboardSectionDescription(section, role) {
  return {
    overview: `A focused summary of your ${role} account, reading, and recent activity.`,
    reading: "Continue articles, manage saved stories, and return to recent reading.",
    interests: "Control the topics that shape your personalized feed.",
    following: "Manage followed writers, publications, topics, tags, and lists.",
    history: "Search opened and completed articles or resume from your saved position.",
    membership: "Review your current plan and manage membership access.",
    stories: "Review content published under your writer account.",
    analytics: "Understand your article readership and engagement.",
    earnings: "Track attributed article revenue and estimated payouts.",
    moderation: "Review content and discussion reports assigned to you.",
    reports: "See moderation outcomes and recent operational activity.",
    security: "Manage account protection, sessions, and recovery methods.",
    profile: "Review your public identity and account details.",
  }[section] || "Your InkRiver workspace.";
}

function dashboardSectionTemplate(section) {
  if (section === "reading") return dashboardReadingTemplate();
  if (section === "interests") return interestManagerTemplate();
  if (section === "following") return followingManagerTemplate();
  if (section === "history") return readingHistoryTemplate();
  if (section === "membership") return dashboardMembershipTemplate();
  if (section === "stories") return dashboardWriterStoriesTemplate();
  if (section === "analytics") return dashboardWriterAnalyticsTemplate();
  if (section === "earnings") return dashboardWriterEarningsTemplate();
  if (section === "moderation") return dashboardModeratorQueueTemplate();
  if (section === "reports") return dashboardModeratorReportsTemplate();
  if (section === "security") return securityWorkspaceTemplate(false);
  if (section === "profile") return dashboardProfileTemplate();
  return dashboardOverviewTemplate();
}

function dashboardOverviewTemplate() {
  const readLater = publishedStories().filter((story) => state.saved.has(story.slug));
  const following = Object.values(state.following).reduce((sum, items) => sum + items.length, 0);
  return `<section class="dashboard-grid member-metrics">${metricTemplate("eye", "Articles opened", String(state.readingHistory.length), "Your history")}${metricTemplate("bookmark", "Read later", String(readLater.length), "Saved")}${metricTemplate("users", "Following", String(following), "People and interests")}${metricTemplate("shield", "Membership", escapeHtml(state.user.subscription), state.isMember ? "Active" : "Free plan")}</section>${continueReadingTemplate("dashboard")}<section class="member-overview-grid"><div class="work-panel"><div class="panel-title">${icon("play")}<h2>Recent reading</h2></div>${dashboardRecentReadingRows(5)}<button class="full-button" data-dashboard-section="reading">Open reading workspace</button></div><div class="work-panel"><div class="panel-title">${icon("spark")}<h2>Personalization</h2></div>${readingProfileCompactTemplate()}<button class="full-button" data-dashboard-section="interests">Tune interests</button></div><div class="work-panel"><div class="panel-title">${icon("card")}<h2>Membership</h2></div><div class="subscription-state"><strong>${escapeHtml(state.user.subscription)}</strong><span>${state.isMember ? "Member access is active" : "Free reader account"}</span></div><button class="full-button" data-dashboard-section="membership">Manage membership</button></div><div class="work-panel member-shortcuts"><div class="panel-title">${icon("trend")}<h2>Quick actions</h2></div><button data-dashboard-section="following">${icon("users", 16)}Manage following</button><button data-dashboard-section="history">${icon("eye", 16)}Search history</button><button data-dashboard-section="security">${icon("lock", 16)}Security settings</button><button data-dashboard-section="profile">${icon("users", 16)}Edit profile</button></div></section>`;
}

function dashboardRecentReadingRows(limit = 6) {
  return state.readingHistory.slice(0, limit).map((entry) => `<button class="dashboard-story" data-resume-story="${entry.slug}"><span class="history-state ${entry.completed ? "complete" : ""}">${entry.completed ? icon("check", 14) : `${entry.progress}%`}</span><span><strong>${escapeHtml(entry.title)}</strong><small>${entry.completed ? "Completed" : `${entry.progress}% read`} · ${escapeHtml(entry.topic)}</small></span></button>`).join("") || `<div class="empty-state">Your reading activity will appear here.</div>`;
}

function readingProfileCompactTemplate() {
  return `<div class="compact-interest-list">${topRecommendationInterests(4).map((interest) => `<div><span><strong>${escapeHtml(interest.name)}</strong><small>${interest.score > 0 ? "Relevant" : "Exploring"}</small></span><i><b style="width:${Math.max(8, Math.min(100, interest.score * 5))}%"></b></i></div>`).join("")}</div>`;
}

function dashboardReadingTemplate() {
  const saved = publishedStories().filter((story) => state.saved.has(story.slug));
  return `${continueReadingTemplate("dashboard")}<section class="member-two-column"><div class="work-panel"><div class="panel-title">${icon("play")}<h2>Recent reading</h2></div>${dashboardRecentReadingRows(10)}</div><div class="work-panel"><div class="panel-title">${icon("bookmark")}<h2>Read later</h2></div>${saved.length ? saved.map((story) => `<button class="dashboard-story" data-route="/stories/${story.slug}"><span class="dot ${story.color}"></span><span><strong>${escapeHtml(story.title)}</strong><small>${escapeHtml(story.topic)} · ${escapeHtml(story.readTime)}</small></span></button>`).join("") : `<div class="empty-state">Saved stories will appear here.</div>`}</div></section>`;
}

function dashboardMembershipTemplate() {
  return `<section class="member-two-column"><div class="work-panel"><div class="panel-title">${icon("card")}<h2>Current plan</h2></div><div class="subscription-state"><strong>${escapeHtml(state.user.subscription)}</strong><span>${state.isMember ? "Full member access is active." : "You are currently using the free reader plan."}</span></div><button class="primary-button" data-route="/pricing">${state.isMember ? "Review available plans" : "Upgrade membership"}</button></div><div class="work-panel"><div class="panel-title">${icon("check")}<h2>Account access</h2></div><ul class="member-benefit-list">${roleMap[state.user.role].map((permission) => `<li>${icon("check", 14)}${permission}</li>`).join("")}</ul><button class="secondary-button wide-button" data-route="/support">Get billing support</button></div></section>`;
}

function userWriterStories() {
  return state.stories.filter((story) =>
    story.authorUserId === state.user.id
    || String(story.author || "").toLowerCase() === state.user.name.toLowerCase()
  );
}

function writerStoryMetric(slug, type) {
  return Number(state.creatorAnalytics.eventRows.find((item) => item.story_slug === slug && item.event_type === type)?.count || 0);
}

function writerStoryTips(slug) {
  return Number(state.creatorAnalytics.tipRows.find((item) => item.story_slug === slug)?.amount || 0) / 100;
}

function dashboardWriterStoriesTemplate() {
  const stories = userWriterStories();
  return `<section class="work-panel"><div class="panel-title">${icon("pen")}<h2>Stories under your account</h2></div>${stories.length ? stories.map((story) => `<button class="dashboard-story writer-story-row" data-route="${story.status === "published" ? `/stories/${story.slug}` : "/write"}"><span class="dot ${story.color}"></span><span><strong>${escapeHtml(story.title)}</strong><small>${writerStoryMetric(story.slug, "open").toLocaleString("en-IN")} reads · ${formatINR(writerStoryTips(story.slug))} captured tips</small></span><span class="status-pill">${story.status}</span></button>`).join("") : `<div class="empty-state">No stories are assigned to ${escapeHtml(state.user.name)} yet.</div>`}<button class="primary-button" data-route="/write">${icon("pen", 15)}Write a new story</button></section>`;
}

function dashboardWriterAnalyticsTemplate() {
  const stories = userWriterStories();
  const reads = stories.reduce((sum, story) => sum + writerStoryMetric(story.slug, "open"), 0);
  const saves = stories.reduce((sum, story) => sum + writerStoryMetric(story.slug, "save"), 0);
  const completions = stories.reduce((sum, story) => sum + writerStoryMetric(story.slug, "complete") + writerStoryMetric(story.slug, "read_complete"), 0);
  const comments = Object.fromEntries(state.creatorAnalytics.commentRows.map((item) => [item.story_slug, Number(item.count || 0)]));
  return `<section class="dashboard-grid member-metrics">${metricTemplate("eye", "Total reads", reads.toLocaleString("en-IN"), `${stories.length} stories`)}${metricTemplate("bookmark", "Saves", saves.toLocaleString("en-IN"), "Recorded")}${metricTemplate("chart", "Completion", reads ? `${Math.round(completions / reads * 100)}%` : "—", "Recorded")}${metricTemplate("comment", "Comments", Object.values(comments).reduce((sum, count) => sum + count, 0).toLocaleString("en-IN"), "Published")}</section><section class="work-panel"><div class="panel-title">${icon("chart")}<h2>Article performance</h2></div>${stories.length ? stories.map((story) => { const storyReads = writerStoryMetric(story.slug, "open"); const completed = writerStoryMetric(story.slug, "complete") + writerStoryMetric(story.slug, "read_complete"); return `<div class="writer-analytics-row"><strong>${escapeHtml(story.title)}</strong><span>${storyReads.toLocaleString("en-IN")} reads</span><span>${storyReads ? Math.round(completed / storyReads * 100) : 0}% complete</span><span>${comments[story.slug] || 0} comments</span></div>`; }).join("") : `<div class="empty-state">Analytics will appear after stories are published under this account.</div>`}</section>`;
}

function dashboardWriterEarningsTemplate() {
  const stories = userWriterStories();
  const tips = stories.reduce((sum, story) => sum + writerStoryTips(story.slug), 0);
  const available = Number(state.payoutSummary.availableAmount || 0) / 100;
  const paidOut = state.payoutSummary.payouts.filter((item) => item.status === "paid").reduce((sum, item) => sum + Number(item.amount || 0), 0) / 100;
  const pending = state.payoutSummary.payouts.filter((item) => item.status !== "paid").reduce((sum, item) => sum + Number(item.amount || 0), 0) / 100;
  const account = state.payoutAccount;
  const accountStatus = account ? String(account.status || "pending_review").replaceAll("_", " ") : "not configured";
  return `<section class="dashboard-grid member-metrics">${metricTemplate("heart", "Captured tips", formatINR(tips), "After platform commission")}${metricTemplate("card", "Available payout", formatINR(available), `${state.payoutSummary.availableTips.length} unpaid tips`)}${metricTemplate("money", "Paid out", formatINR(paidOut), `${state.payoutSummary.payouts.length} payout records`)}${metricTemplate("trend", "Payout status", accountStatus, pending ? `${formatINR(pending)} in progress` : "Current")}</section><section class="member-two-column"><div class="work-panel"><div class="panel-title">${icon("money")}<h2>Earnings by story</h2></div>${stories.length ? stories.map((story) => `<div class="earnings-row"><span><strong>${escapeHtml(story.title)}</strong><small>${writerStoryMetric(story.slug, "open").toLocaleString("en-IN")} recorded reads</small></span><b>${formatINR(writerStoryTips(story.slug))}</b></div>`).join("") : `<div class="empty-state">No writer earnings are available yet.</div>`}</div><div class="work-panel"><div class="panel-title">${icon("card")}<h2>Payout account</h2></div><div class="subscription-state"><strong>${account ? escapeHtml(account.account_holder || state.user.name) : "Not configured"}</strong><span>${account ? `${escapeHtml(account.bank_name || "Payout provider")} · ${escapeHtml(accountStatus)}` : "Add bank, payout method, and tax references before transfers can be reviewed."}</span></div><button class="primary-button" data-action="setup-payout-account">${account ? "Update payout account" : "Set up payout account"}</button>${state.payoutSummary.payouts.length ? `<div class="promotion-list">${state.payoutSummary.payouts.map((payout) => `<article><span><strong>${escapeHtml(payout.id)}</strong><small>${new Date(payout.created_at).toLocaleDateString()}</small></span><b>${formatINR(Number(payout.amount || 0) / 100)}</b><span class="status-pill">${escapeHtml(payout.status)}</span></article>`).join("")}</div>` : `<div class="empty-state">Payout batches will appear after an administrator creates them.</div>`}</div></section>`;
}

function dashboardModeratorQueueTemplate() {
  const assigned = state.operations.moderation.filter((item) => item.assignee === state.user.name || item.assignee === "Unassigned");
  return `${operationsSummaryTemplate([["Assigned", String(assigned.length)], ["High risk", String(assigned.filter((item) => item.risk === "High").length)], ["Resolved", String(assigned.filter((item) => item.status === "Resolved").length)], ["Source", "Database"]])}<div class="ops-table moderation-table"><div class="ops-table-head"><span>Case</span><span>Type</span><span>Report</span><span>Risk</span><span>Status</span><span>Assignee</span></div>${assigned.map((item) => `<div class="ops-table-row"><strong>${item.id}</strong><span>${item.target} · ${item.kind}</span><span>${escapeHtml(item.subject)}</span><span class="risk ${item.risk.toLowerCase()}">${item.risk}</span><span class="status-pill">${item.status}</span><button data-resolve-mod="${item.id}">${item.assignee}</button></div>`).join("") || `<div class="empty-state">No moderation cases are assigned to you.</div>`}</div>`;
}

function dashboardModeratorReportsTemplate() {
  return `<section class="member-two-column"><div class="work-panel"><div class="panel-title">${icon("shield")}<h2>Recent outcomes</h2></div>${state.operations.moderation.slice(0, 6).map((item) => `<div class="moderator-activity-row"><span><strong>${item.id} · ${item.kind}</strong><small>${escapeHtml(item.subject)}</small></span><span class="status-pill">${item.status}</span></div>`).join("")}</div><div class="work-panel"><div class="panel-title">${icon("filter")}<h2>Review standards</h2></div><ul class="member-benefit-list"><li>${icon("check", 14)}Review evidence before enforcement</li><li>${icon("check", 14)}Escalate legal and safety risks</li><li>${icon("check", 14)}Record a clear moderation reason</li><li>${icon("check", 14)}Avoid changing platform settings</li></ul></div></section>`;
}

function dashboardProfileTemplate() {
  return `<section class="member-two-column"><div class="work-panel"><div class="panel-title">${icon("users")}<h2>Account identity</h2></div><div class="account-card"><strong>${escapeHtml(state.user.name)}</strong><span>${escapeHtml(state.user.email)}</span><small>User ID: ${escapeHtml(state.user.id)} · Role: ${escapeHtml(state.user.role)}</small></div><button class="secondary-button" data-route="/me">Open public reader profile</button></div><div class="work-panel"><div class="panel-title">${icon("link")}<h2>Account tools</h2></div><button class="dashboard-setting-link" data-route="/privacy">${icon("eye", 15)}Privacy center</button><button class="dashboard-setting-link" data-route="/security">${icon("lock", 15)}Security controls</button><button class="dashboard-setting-link" data-route="/support">${icon("comment", 15)}Help and support</button></div></section>`;
}

function followingManagerTemplate() {
  const writers = Object.values(defaultProfiles).slice(0, 6);
  const publications = [...new Set(publishedStories().map((story) => story.publication))].slice(0, 6);
  const tags = [...new Set(publishedStories().flatMap((story) => story.tags || []))].slice(0, 10);
  return `
    <section class="following-manager">
      <div class="section-heading"><div><h2>Following</h2><p>Followed writers, publications, topics, tags, and lists receive a strong boost in your personalized feed.</p></div></div>
      <div class="following-columns">
        <div><h3>Writers</h3>${writers.map((profile) => followManagerRow("writers", profile.name, profile.expertise[0], `/@${slugifyName(profile.name)}`)).join("")}</div>
        <div><h3>Publications</h3>${publications.map((publication) => followManagerRow("publications", publication, "Publication", "")).join("")}</div>
        <div><h3>Topics</h3>${state.categories.map((category) => followManagerRow("topics", category.name, category.description, `/category/${category.slug}`)).join("")}</div>
        <div><h3>Tags</h3><div class="follow-tag-cloud">${tags.map((tag) => `<button class="${isFollowing("tags", tag) ? "active" : ""}" data-follow-type="tags" data-follow-value="${escapeHtml(tag)}">#${escapeHtml(tag)}</button>`).join("")}</div></div>
        <div class="following-lists"><h3>Curated lists</h3>${defaultCuratedLists.map((list) => followManagerRow("lists", list.id, `${list.name} · ${list.slugs.length} stories`, `/lists/${list.id}`, list.name)).join("")}</div>
      </div>
    </section>
  `;
}

function followManagerRow(type, value, meta, route = "", label = value) {
  return `
    <div class="follow-manager-row">
      ${route ? `<button data-route="${route}"><strong>${escapeHtml(label)}</strong><small>${escapeHtml(meta)}</small></button>` : `<span><strong>${escapeHtml(label)}</strong><small>${escapeHtml(meta)}</small></span>`}
      <button class="compact-follow ${isFollowing(type, value) ? "active" : ""}" data-follow-type="${type}" data-follow-value="${escapeHtml(value)}">${followLabel(type, value)}</button>
    </div>
  `;
}

function readingHistoryTemplate() {
  const history = filteredHistory();
  return `
    <section class="reading-history">
      <div class="reading-history-heading">
        <div><h2>Reading history</h2><p>Opened and completed articles are stored with your latest reading position.</p></div>
        <div><label>${icon("search", 15)}<input id="historySearch" value="${escapeHtml(state.historyQuery)}" placeholder="Search reading history" /></label><button class="secondary-button" data-action="clear-history" ${state.readingHistory.length ? "" : "disabled"}>Clear all</button></div>
      </div>
      <div class="history-list">
        ${history.length ? history.map((entry) => `
          <article>
            <button class="history-main" data-resume-story="${entry.slug}">
              <span class="history-state ${entry.completed ? "complete" : ""}">${entry.completed ? icon("check", 16) : `${entry.progress}%`}</span>
              <span><strong>${escapeHtml(entry.title)}</strong><small>${escapeHtml(entry.author)} · ${escapeHtml(entry.topic)} · Last read ${new Date(entry.lastReadAt).toLocaleDateString("en-IN", { day: "numeric", month: "short" })}</small><i><span style="width:${entry.progress}%"></span></i></span>
            </button>
            <span class="history-status">${entry.completed ? "Completed" : `${entry.progress}% read`}</span>
            <button class="icon-button" data-history-delete="${entry.slug}" aria-label="Remove ${escapeHtml(entry.title)} from history" title="Remove from history">${icon("close", 15)}</button>
          </article>
        `).join("") : `<div class="empty-state">${state.historyQuery ? "No reading history matches this search." : "Articles you open will appear here with reading progress."}</div>`}
      </div>
    </section>
  `;
}

function interestManagerTemplate() {
  return `
    <section class="interest-manager">
      <div class="interest-manager-heading">
        <div>
          <span class="interest-manager-icon">${icon("spark", 19)}</span>
          <div>
            <h2>Your interests</h2>
            <p>Select or deselect topics at any time. These choices have the strongest influence on your For You feed.</p>
          </div>
        </div>
        <button class="secondary-button" data-action="reset-recommendations">Reset learning</button>
      </div>
      <div class="interest-selector" aria-label="Reading interests">
        ${state.categories.map((category) => {
          const selected = state.recommendation.selectedInterests.includes(category.name);
          return `
            <button class="interest-option ${selected ? "selected" : ""}" data-interest-topic="${escapeHtml(category.name)}" aria-pressed="${selected}">
              <span class="interest-option-swatch ${category.color}"></span>
              <span><strong>${escapeHtml(category.name)}</strong><small>${escapeHtml(category.description)}</small></span>
              <span class="interest-option-check">${selected ? icon("check", 15) : ""}</span>
            </button>
          `;
        }).join("")}
      </div>
      <div class="interest-manager-footer">
        <span>${escapeHtml(state.recommendationMessage || `${state.recommendation.selectedInterests.length} interests selected. Changes update your feed immediately.`)}</span>
        <button class="primary-button" data-route="/">View personalized feed</button>
      </div>
    </section>
  `;
}

function readingProfileTemplate() {
  const topInterests = topRecommendationInterests(5);
  const maxScore = Math.max(1, ...topInterests.map((interest) => Math.max(0, interest.score)));
  return `
    <div class="work-panel reading-profile-panel">
      <div class="panel-title">${icon("spark")}<h2>What your feed has learned</h2></div>
      <div class="reading-profile-bars">
        ${topInterests.map((interest) => `
          <div>
            <span><strong>${escapeHtml(interest.name)}</strong><small>${interest.score > 0 ? "Relevant" : "Exploring"}</small></span>
            <i><span style="width:${Math.max(6, (Math.max(0, interest.score) / maxScore) * 100)}%"></span></i>
          </div>
        `).join("")}
      </div>
      <div class="recent-signals">
        <strong>Recent signals</strong>
        ${
          state.recommendation.activity.length
            ? state.recommendation.activity.slice(0, 4).map((activity) => `
              <span><i>${icon(activity.type === "not_interested" ? "eye" : "check", 13)}</i>${escapeHtml(recommendationActivityLabel(activity))} ${escapeHtml(activity.title)}</span>
            `).join("")
            : `<span>Your reading activity will appear here.</span>`
        }
      </div>
    </div>
  `;
}

function creatorStudioTemplate() {
  return adminShellTemplate(
    "Creator studio",
    "Plan publishing, understand audience behavior, build segments, and manage reader revenue.",
    `
      <div class="workspace-tabs">
        ${[["calendar", "Editorial calendar"], ["analytics", "Analytics"], ["segments", "Audience segments"], ["commerce", "Promotions & tips"]].map(([id, label]) => `<button class="${state.creatorTools.activeTab === id || (!state.creatorTools.activeTab && id === "calendar") ? "active" : ""}" data-creator-tab="${id}">${label}</button>`).join("")}
      </div>
      ${creatorWorkspaceTemplate(state.creatorTools.activeTab || "calendar")}
    `,
    `<button class="primary-button" data-action="add-calendar-item">${icon("pen", 16)}Schedule content</button>`,
  );
}

function creatorWorkspaceTemplate(tab) {
  if (tab === "analytics") return creatorAnalyticsTemplate();
  if (tab === "segments") return audienceSegmentsTemplate();
  if (tab === "commerce") return creatorCommerceTemplate();
  return editorialCalendarTemplate();
}

function editorialCalendarTemplate() {
  const views = ["calendar", "list", "pipeline"];
  return `
    <section class="creator-workspace">
      <div class="workspace-commandbar">
        <div class="segmented-control">${views.map((view) => `<button class="${state.creatorTools.calendarView === view ? "active" : ""}" data-calendar-view="${view}">${view[0].toUpperCase() + view.slice(1)}</button>`).join("")}</div>
        <div class="calendar-range"><button class="icon-button">${icon("chevron-left", 16)}</button><strong>June 2026</strong><button class="icon-button">${icon("chevron-right", 16)}</button><button class="secondary-button">Today</button></div>
      </div>
      ${state.calendarMessage ? `<div class="payment-message">${escapeHtml(state.calendarMessage)}</div>` : ""}
      ${state.creatorTools.calendarView === "calendar" ? calendarGridTemplate() : state.creatorTools.calendarView === "pipeline" ? pipelineTemplate() : calendarListTemplate()}
    </section>
  `;
}

function calendarGridTemplate() {
  const days = Array.from({ length: 30 }, (_, index) => index + 1);
  return `
    <div class="editorial-calendar">
      ${["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"].map((day) => `<strong class="calendar-day-name">${day}</strong>`).join("")}
      ${days.map((day) => {
        const date = `2026-06-${String(day).padStart(2, "0")}`;
        const items = state.creatorTools.calendarItems.filter((item) => item.date === date);
        return `<div class="calendar-cell ${day === 11 ? "today" : ""}"><span>${day}</span>${items.map((item) => `<button class="calendar-event ${item.type.toLowerCase()}" data-calendar-item="${item.id}"><small>${item.channel}</small><strong>${escapeHtml(item.title)}</strong><em>${item.status}</em></button>`).join("")}</div>`;
      }).join("")}
    </div>
  `;
}

function calendarListTemplate() {
  return `<div class="ops-table">
    <div class="ops-table-head"><span>Date</span><span>Content</span><span>Type</span><span>Owner</span><span>Status</span><span>Channel</span></div>
    ${state.creatorTools.calendarItems.map((item) => `<div class="ops-table-row"><strong>${new Date(`${item.date}T00:00:00`).toLocaleDateString("en-IN", { day: "numeric", month: "short" })}</strong><span>${escapeHtml(item.title)}</span><span>${item.type}</span><span>${item.owner}</span><span class="status-pill">${item.status}</span><span>${item.channel}</span></div>`).join("")}
  </div>`;
}

function pipelineTemplate() {
  const statuses = ["Draft", "Review", "Approved", "Scheduled"];
  return `<div class="pipeline-board">${statuses.map((status) => `<section><header><strong>${status}</strong><span>${state.creatorTools.calendarItems.filter((item) => item.status === status).length}</span></header>${state.creatorTools.calendarItems.filter((item) => item.status === status).map((item) => `<article><small>${item.type} · ${item.channel}</small><strong>${escapeHtml(item.title)}</strong><span>${item.owner}</span><time>${new Date(`${item.date}T00:00:00`).toLocaleDateString("en-IN", { day: "numeric", month: "short" })}</time><button data-advance-calendar="${item.id}">Move forward ${icon("chevron-right", 14)}</button></article>`).join("") || `<div class="pipeline-empty">Nothing here</div>`}</section>`).join("")}</div>`;
}

function creatorAnalyticsTemplate() {
  const eventCount = (type) => Number(state.adminAnalytics.eventCounts.find((item) => item.event_type === type)?.count || 0);
  const impressions = eventCount("open");
  const saves = eventCount("save");
  const follows = eventCount("follow");
  const completions = eventCount("complete") + eventCount("read_complete");
  const shares = eventCount("share");
  const completionRate = impressions ? Math.round(completions / impressions * 100) : 0;
  const totalEvents = state.adminAnalytics.eventCounts.reduce((sum, item) => sum + Number(item.count || 0), 0);
  const metrics = [
    ["Impressions", impressions.toLocaleString("en-IN"), "Recorded article opens"],
    ["Completion", `${completionRate}%`, `${completions.toLocaleString("en-IN")} completed reads`],
    ["Saves", saves.toLocaleString("en-IN"), impressions ? `${(saves / impressions * 100).toFixed(1)}% of opens` : "No opens recorded"],
    ["New follows", follows.toLocaleString("en-IN"), "Recorded follow actions"],
    ["Shares", shares.toLocaleString("en-IN"), "Recorded share actions"],
    ["Active members", Number(state.adminAnalytics.activeSubscriptions || 0).toLocaleString("en-IN"), "Paid subscriptions"],
    ["Revenue", formatINR(Number(state.adminAnalytics.revenue || 0) / 100), "Captured payments"],
  ];
  const storyMetrics = state.adminAnalytics.storyCounts.reduce((groups, item) => {
    (groups[item.story_slug] ||= []).push(item);
    return groups;
  }, {});
  const storyRevenue = Object.fromEntries((state.adminAnalytics.revenueByStory || []).map((item) => [item.story_slug, Number(item.amount || 0) / 100]));
  const dailyMaximum = Math.max(1, ...(state.adminAnalytics.dailyEvents || []).map((item) => Number(item.count || 0)));
  const articleRows = publishedStories().map((story) => {
    const rows = storyMetrics[story.slug] || [];
    const count = (type) => Number(rows.find((item) => item.event_type === type)?.count || 0);
    const opens = count("open");
    const completed = count("complete") + count("read_complete");
    return { story, opens, completion: opens ? Math.round(completed / opens * 100) : 0, saves: count("save"), revenue: storyRevenue[story.slug] || 0 };
  }).filter((row) => row.opens || row.saves || row.revenue).sort((a, b) => b.opens - a.opens);
  return `
    <section class="creator-workspace analytics-workspace">
      <div class="analytics-toolbar"><div><strong>Performance overview</strong><span>Last 30 days and lifetime totals</span></div><button class="secondary-button" data-action="export-analytics">Export CSV</button></div>
      <div class="analytics-metric-grid">${metrics.map(([label, value, detail]) => `<article><span>${label}</span><strong>${value}</strong><small>${detail}</small></article>`).join("")}</div>
      <div class="analytics-split">
        <section class="analytics-chart"><header><div><h2>Recorded engagement</h2><p>Daily events received by the production analytics endpoint.</p></div></header><div class="line-chart">${(state.adminAnalytics.dailyEvents || []).map((item) => `<i style="height:${Math.max(8, Number(item.count) / dailyMaximum * 100)}%"><em>${new Date(`${item.day}T00:00:00`).toLocaleDateString("en-IN", { day: "numeric", month: "short" })}</em></i>`).join("") || `<div class="empty-state">No engagement has been recorded in the last 30 days.</div>`}</div></section>
        <section class="analytics-breakdown"><h2>Event distribution</h2>${state.adminAnalytics.eventCounts.map((item) => { const value = Number(item.count || 0); const percent = Math.round(value / Math.max(1, totalEvents) * 100); return `<div><span><strong>${escapeHtml(item.event_type.replaceAll("_", " "))}</strong><em>${value.toLocaleString("en-IN")}</em></span><i><b style="width:${percent}%"></b></i></div>`; }).join("") || `<div class="empty-state">No event data is available yet.</div>`}</section>
      </div>
      <div class="ops-table article-analytics"><div class="ops-table-head"><span>Article</span><span>Impressions</span><span>Completion</span><span>Saves</span><span>Revenue</span><span>RPM</span></div>${articleRows.map(({ story, opens, completion, saves: storySaves, revenue }) => `<div class="ops-table-row"><strong>${escapeHtml(story.title)}</strong><span>${opens.toLocaleString("en-IN")}</span><span>${completion}%</span><span>${storySaves.toLocaleString("en-IN")}</span><span>${formatINR(revenue)}</span><span>${formatINR(opens ? revenue / opens * 1000 : 0)}</span></div>`).join("") || `<div class="empty-state">Article analytics will appear after readers interact with published stories.</div>`}</div>
    </section>
  `;
}

function audienceSegmentsTemplate() {
  const segments = state.creatorTools.segments.filter((segment) => `${segment.name} ${segment.rules}`.toLowerCase().includes(state.segmentQuery.toLowerCase()));
  return `
    <section class="creator-workspace">
      <div class="segment-builder">
        <div><h2>Audience segments</h2><p>Combine reader attributes and behaviors into reusable audiences.</p></div>
        <label>${icon("search", 15)}<input id="segmentSearch" value="${escapeHtml(state.segmentQuery)}" placeholder="Search segments" /></label>
        <button class="primary-button" data-action="create-segment">Create segment</button>
      </div>
      <div class="rule-library">${["Interest", "Plan", "Engagement", "Location", "Source", "Reading behavior", "Custom tag"].map((rule) => `<button data-add-rule="${rule}">${icon("filter", 14)}${rule}</button>`).join("")}</div>
      <div class="segment-list">${segments.map((segment) => `<article><div><span class="segment-icon">${icon("users", 18)}</span><span><strong>${escapeHtml(segment.name)}</strong><small>${escapeHtml(segment.rules)}</small></span></div><span><strong>${segment.size.toLocaleString("en-IN")}</strong><small>readers</small></span><span class="status-pill">${segment.channel}</span><div><button class="secondary-button" data-preview-segment="${segment.id}">Preview</button><button class="secondary-button" data-use-segment="${segment.id}">Use segment</button></div></article>`).join("")}</div>
    </section>
  `;
}

function creatorCommerceTemplate() {
  return `
    <section class="creator-workspace commerce-workspace">
      <div class="commerce-columns">
        <section><header><div><h2>Gifts and promotions</h2><p>Trials, coupons, student, corporate, and regional offers.</p></div><button class="primary-button" data-action="create-promotion">New offer</button></header><div class="promotion-list">${state.creatorTools.promotions.map((promo) => `<article><span><strong>${promo.id}</strong><small>${promo.kind} · ${promo.audience}</small></span><b>${promo.value}</b><span>${promo.uses.toLocaleString("en-IN")} uses</span><button class="toggle ${promo.active ? "active" : ""}" data-toggle-promo="${promo.id}" aria-label="Toggle ${promo.id}"><span></span></button></article>`).join("")}</div><div class="gift-membership"><span>${icon("card", 20)}</span><div><strong>Gift memberships</strong><p>Send 1, 3, 6, or 12 months with a personalized message and scheduled delivery.</p></div><button class="secondary-button" data-action="gift-preview">Preview flow</button></div></section>
        <section class="tip-settings"><h2>Writer tipping</h2><p>Accept one-time reader support through enabled payment gateways.</p><label class="checkbox-field"><input type="checkbox" id="tipsEnabled" ${state.creatorTools.tipsEnabled ? "checked" : ""}><span>Enable tips on articles</span></label><label><span>Platform commission</span><div class="number-affix"><input id="tipCommission" type="number" min="0" max="30" value="${state.creatorTools.tipCommission}"><b>%</b></div></label><div class="tip-example"><span>INR 500 reader tip</span><strong>Writer receives ${formatINR(500 * (1 - state.creatorTools.tipCommission / 100))}</strong><small>Platform commission ${formatINR(500 * state.creatorTools.tipCommission / 100)} before gateway fees.</small></div><div class="gateway-mini-list">${paymentGateways.map((gateway) => `<span>${icon("card", 14)}${gateway.name}</span>`).join("")}</div><button class="primary-button" data-action="save-tip-settings">Save payout rules</button></section>
      </div>
    </section>
  `;
}

function moderationCenterTemplate() {
  const cases = state.operations.moderation.filter((item) => state.opsFilter === "all" || item.kind.toLowerCase() === state.opsFilter);
  const openCases = state.operations.moderation.filter((item) => item.status !== "Resolved" && item.status !== "Dismissed");
  return adminShellTemplate("Moderation center", "Review reports across posts, comments, profiles, messages, ads, spam, harassment, and misinformation.", `
    ${operationsSummaryTemplate([["Open cases", String(openCases.length)], ["High risk", String(openCases.filter((item) => item.risk === "High").length)], ["Resolved", String(state.operations.moderation.filter((item) => item.status === "Resolved").length)], ["Source", "Database"]])}
    <div class="ops-filterbar"><div class="segmented-control">${["all", "spam", "harassment", "misinformation", "plagiarism"].map((filter) => `<button class="${state.opsFilter === filter ? "active" : ""}" data-ops-filter="${filter}">${filter}</button>`).join("")}</div></div>
    <div class="ops-table moderation-table"><div class="ops-table-head"><span>Case</span><span>Type</span><span>Report</span><span>Risk</span><span>Status</span><span>Assignee</span></div>${cases.map((item) => `<div class="ops-table-row"><strong>${item.id}</strong><span>${item.target} · ${item.kind}</span><span>${escapeHtml(item.subject)}</span><span class="risk ${item.risk.toLowerCase()}">${item.risk}</span><span class="status-pill">${item.status}</span><button data-resolve-mod="${item.id}">${item.assignee}</button></div>`).join("")}</div>
    ${abuseProtectionTemplate()}
  `);
}

function abuseProtectionTemplate() {
  return `<section class="protection-grid"><div><h2>Automated protection</h2><p>Only controls backed by current server behavior are marked active.</p></div>${[["Login rate limits", "Active", "Five failures trigger a 15-minute block"], ["Origin validation", "Active", "State-changing requests require the application origin"], ["Session protection", "Active", "HTTP-only SameSite cookies"], ["Bot scoring", "Unavailable", "No bot-scoring provider configured"], ["Duplicate checks", "Unavailable", "No similarity service configured"], ["Blocked words", "Unavailable", "No policy dictionary configured"]].map(([name, status, meta]) => `<article><span>${icon(status === "Active" ? "shield" : "gauge", 16)}</span><div><strong>${name}</strong><small>${meta}</small></div><b>${status}</b></article>`).join("")}</section>`;
}

function copyrightCenterTemplate() {
  const cases = state.operations.moderation.filter((item) => ["copyright", "plagiarism"].includes(item.kind.toLowerCase()));
  const open = cases.filter((item) => !["Resolved", "Dismissed"].includes(item.status));
  const evidenceCount = cases.reduce((sum, item) => sum + (item.evidence?.length || 0), 0);
  return adminShellTemplate("Copyright and plagiarism", "Track similarity matches, takedown requests, counter-notices, evidence, and administrator decisions.", `
    ${operationsSummaryTemplate([["Open claims", String(open.length)], ["High risk", String(open.filter((item) => item.risk === "High").length)], ["Evidence records", String(evidenceCount)], ["Resolved", String(cases.filter((item) => item.status === "Resolved").length)]])}
    <div class="copyright-workflow"><span class="active">Detection</span><i></i><span class="active">Evidence</span><i></i><span>Notice</span><i></i><span>Counter-notice</span><i></i><span>Decision</span></div>
    <div class="ops-table copyright-table"><div class="ops-table-head"><span>Case</span><span>Work</span><span>Type</span><span>Risk</span><span>Evidence</span><span>Status</span></div>${cases.map((item) => `<div class="ops-table-row"><strong>${item.id}</strong><span>${escapeHtml(item.subject)}</span><span>${escapeHtml(item.kind)}</span><span class="risk ${item.risk.toLowerCase()}">${item.risk}</span><button data-upload-evidence="${item.id}">${item.evidence?.length || 0} records</button><span class="status-pill">${item.status}</span></div>`).join("") || `<div class="empty-state">No copyright or plagiarism cases are stored.</div>`}</div>
    <section class="evidence-panel"><div><h2>Evidence storage</h2><p>Add a source URL, content hash, license reference, or storage location to an existing case.</p></div><button class="primary-button" data-action="create-copyright-case">Create copyright case</button></section>
  `);
}

function operationsSummaryTemplate(items) {
  return `<section class="operations-summary">${items.map(([label, value]) => `<article><span>${label}</span><strong>${value}</strong></article>`).join("")}</section>`;
}

function adminSupportTemplate() {
  return adminShellTemplate("Customer support", "Manage account, subscription, payment, publishing, and technical support with internal notes and SLA tracking.", supportWorkspaceTemplate(true));
}

function supportWorkspaceTemplate(admin = false) {
  const openTickets = state.operations.tickets.filter((ticket) => ticket.status !== "Resolved" && ticket.status !== "Closed").length;
  return `
    ${admin ? operationsSummaryTemplate([["Open tickets", String(openTickets)], ["Total tickets", String(state.operations.tickets.length)], ["Provider", state.providerStatus.email ? "Email ready" : "Email unavailable"], ["Storage", "Database"]]) : ""}
    <section class="support-layout">
      <div class="ticket-list"><header><div><h2>${admin ? "Support queue" : "Your requests"}</h2><p>Sorted by urgency and response target.</p></div><button class="primary-button" data-action="new-ticket">New ticket</button></header>${state.operations.tickets.map((ticket) => `<button class="ticket-row ${ticket.priority.toLowerCase()}"><span><strong>${ticket.id} · ${escapeHtml(ticket.subject)}</strong><small>${ticket.category} · ${ticket.owner}</small></span><span><b>${ticket.status}</b><small>${ticket.age}</small></span></button>`).join("")}</div>
      <aside class="help-library"><h2>Help articles</h2>${["Manage a membership", "Fix a failed payment", "Recover your account", "Publication verification", "Writer payout schedule"].map((title, index) => `<button><span>${icon(index === 2 ? "lock" : "link", 15)}</span><span><strong>${title}</strong><small>${18-index*2} articles</small></span></button>`).join("")}<div class="support-contact"><strong>Still need help?</strong><p>Send account, payment, or publishing details to the support team.</p><button class="secondary-button" data-action="new-ticket">Contact support</button></div></aside>
    </section>
  `;
}

function supportCenterTemplate() {
  return `<main class="account-center"><header><h1>Help and support</h1><p>Find answers or track a request with the InkRiver team.</p></header>${supportWorkspaceTemplate(false)}</main>`;
}

function platformHealthTemplate() {
  const configuredPayments = Object.values(state.providerStatus.payments).filter(Boolean).length;
  const eventTotal = state.adminAnalytics.eventCounts.reduce((sum, item) => sum + Number(item.count || 0), 0);
  const rec = state.adminRecommendationStatus || {};
  const openModeration = state.operations.moderation.filter((item) => item.status !== "Resolved" && item.status !== "Dismissed").length;
  const openTickets = state.operations.tickets.filter((ticket) => ticket.status !== "Resolved" && ticket.status !== "Closed").length;
  const health = [["Publishing API", "Operational", `${publishedStories().length} published`], ["Payments", configuredPayments ? "Operational" : "Unavailable", `${configuredPayments}/4 configured`], ["Email delivery", state.providerStatus.email ? "Operational" : "Unavailable", state.providerStatus.email ? "Configured" : "No provider"], ["AI provider", state.providerStatus.ai ? "Operational" : "Unavailable", state.providerStatus.ai ? "Configured" : "No provider"], ["Push notifications", state.providerStatus.push ? "Operational" : "Unavailable", state.providerStatus.push ? "Configured" : "No VAPID keys"], ["Recommendations", Number(rec.profiles || 0) ? "Operational" : "Collecting", `${Number(rec.profiles || 0).toLocaleString("en-IN")} profiles · ${Number(rec.scores || 0).toLocaleString("en-IN")} scores`]];
  return adminShellTemplate("Platform health", "Monitor reliability, delivery, APIs, indexing, moderation load, recommendations, storage, and payment operations.", `
    <section class="health-banner"><span class="health-pulse"></span><div><strong>Health is derived from current configuration and database records</strong><p>Unavailable providers remain disabled until their server credentials are supplied.</p></div></section>
    <div class="health-service-grid">${health.map(([name, status, detail]) => `<article class="${status === "Unavailable" ? "degraded" : ""}"><span>${icon(status === "Operational" ? "check" : "gauge", 17)}</span><div><strong>${name}</strong><small>${status}</small></div><b>${detail}</b></article>`).join("")}</div>
    <div class="analytics-split"><section class="analytics-chart"><header><div><h2>Recorded engagement</h2><p>Events stored by the production analytics endpoint.</p></div><strong>${eventTotal} events</strong></header><div class="line-chart health-chart">${state.adminAnalytics.eventCounts.map((item) => `<i style="height:${Math.min(100, Math.max(8, Number(item.count) * 8))}%"><em>${escapeHtml(item.event_type)}</em></i>`).join("") || `<div class="empty-state">No engagement events recorded yet.</div>`}</div></section><section class="health-queue"><h2>Operational queues</h2>${[["Moderation backlog", openModeration], ["Support tickets", openTickets], ["Active subscriptions", state.adminAnalytics.activeSubscriptions], ["Payment revenue", state.adminAnalytics.revenue / 100], ["Recommendation signals", Number(rec.signals || 0)], ["Last model train", rec.lastTrainedAt ? new Date(rec.lastTrainedAt).toLocaleString("en-IN") : "Pending"]].map(([name, count]) => `<div><span><strong>${name}</strong><em>${count}</em></span></div>`).join("")}<button class="secondary-button" data-action="rebuild-recommendations">Rebuild recommendation model</button></section></div>
  `);
}

function adminSecurityTemplate() {
  return adminShellTemplate("Security and access", "Control authentication, role permissions, audit events, backups, recovery, and suspicious activity.", securityWorkspaceTemplate(true));
}

function sessionDeviceLabel(userAgent = "") {
  const browser = /Edg\//.test(userAgent) ? "Edge" : /Firefox\//.test(userAgent) ? "Firefox" : /Chrome\//.test(userAgent) ? "Chrome" : /Safari\//.test(userAgent) ? "Safari" : "Browser";
  const device = /Android/i.test(userAgent) ? "Android" : /iPhone|iPad/i.test(userAgent) ? "iOS" : /Mac OS/i.test(userAgent) ? "macOS" : /Windows/i.test(userAgent) ? "Windows" : "Device";
  return `${device} · ${browser}`;
}

function securityWorkspaceTemplate(admin = false) {
  const emailVerified = Boolean(state.securitySettings.emailVerified || state.user?.emailVerified);
  const twoFactorEnabled = Boolean(state.securitySettings.twoFactorEnabled);
  const score = 35 + (emailVerified ? 25 : 0) + (twoFactorEnabled ? 25 : 0) + (state.securitySessions.length ? 15 : 0);
  const sessions = state.securitySessions.length ? state.securitySessions : [];
  return `
    <section class="security-grid">
      <div class="security-main">
        <section class="security-score"><div><span>${icon("shield", 24)}</span><strong>Security score ${Math.min(100, score)}</strong><p>${twoFactorEnabled ? "Two-factor authentication is active for this account." : emailVerified ? "Email is verified. Add an authenticator app for stronger sign-in protection." : "Verify your email before enabling stronger recovery controls."}</p></div><button class="primary-button" data-action="${twoFactorEnabled ? "disable-totp" : "setup-totp"}">${twoFactorEnabled ? "Disable 2FA" : "Set up 2FA"}</button></section>
        <section class="security-controls">
          <article><span>${icon(emailVerified ? "check" : "lock", 16)}</span><div><strong>Email verification</strong><small>${emailVerified ? "Verified" : state.providerStatus.email ? "Send verification email" : "Use local verification link until email API is configured"}</small></div><button class="secondary-button" data-action="request-email-verification" ${emailVerified ? "disabled" : ""}>${emailVerified ? "Verified" : "Verify"}</button></article>
          <article><span>${icon(twoFactorEnabled ? "check" : "lock", 16)}</span><div><strong>Two-factor authentication</strong><small>${twoFactorEnabled ? "Authenticator app required during sign-in" : "Generate a secret, then confirm the 6-digit code"}</small></div><button class="secondary-button" data-action="${twoFactorEnabled ? "disable-totp" : "setup-totp"}">${twoFactorEnabled ? "Disable" : "Set up"}</button></article>
          <article><span>${icon("lock", 16)}</span><div><strong>Login alerts</strong><small>${state.providerStatus.email ? "Email provider available for alerts" : "Email API key required for delivery"}</small></div><button class="secondary-button" disabled>${state.providerStatus.email ? "Ready" : "Needs email API"}</button></article>
          <article><span>${icon(emailVerified ? "check" : "lock", 16)}</span><div><strong>Account recovery</strong><small>${emailVerified ? "Verified email can receive recovery links" : "Verify email first"}</small></div><button class="secondary-button" disabled>${emailVerified ? "Enabled" : "Unavailable"}</button></article>
        </section>
        <section class="session-panel"><header><h2>Sessions and devices</h2><button class="secondary-button" data-action="signout-other-sessions" ${sessions.length > 1 ? "" : "disabled"}>Sign out others</button></header>${sessions.map((session) => `<article><span>${icon("gauge", 16)}</span><div><strong>${escapeHtml(sessionDeviceLabel(session.user_agent))}</strong><small>${escapeHtml(session.ip_address || "Unknown network")} · Last active ${new Date(session.last_seen_at).toLocaleString()}</small></div>${session.id === state.currentSessionId ? `<b>Current</b>` : `<button class="text-button" data-session-revoke="${session.id}">Revoke</button>`}</article>`).join("") || `<div class="empty-state">No active sessions could be loaded.</div>`}</section>
      </div>
      <aside class="audit-panel"><h2>${admin ? "Security status" : "Recent security activity"}</h2><article><span></span><div><strong>${sessions.length} active session${sessions.length === 1 ? "" : "s"}</strong><small>Loaded from the session database</small><time>${new Date().toLocaleString()}</time></div></article><article><span></span><div><strong>Email ${emailVerified ? "verified" : "not verified"}</strong><small>${escapeHtml(state.user?.email || "")}</small><time>Account status</time></div></article>${admin ? `<div class="backup-state"><strong>Backup status unavailable</strong><span>Configure infrastructure monitoring to report backups.</span><small>No fabricated backup claims are shown.</small></div>` : ""}</aside>
    </section>
  `;
}

function securityCenterTemplate() {
  return `<main class="account-center"><header><h1>Security</h1><p>Protect your account, review devices, and manage recovery methods.</p></header>${state.securityMessage ? `<div class="payment-message">${escapeHtml(state.securityMessage)}</div>` : ""}${securityWorkspaceTemplate(false)}</main>`;
}

function privacyCenterTemplate() {
  const connectedAccounts = ["google", "facebook"].map((provider) => ({
    provider,
    name: provider === "google" ? "Google" : "Facebook",
    account: state.socialAccounts.find((item) => item.provider === provider),
  }));
  return `<main class="account-center"><header><h1>Privacy center</h1><p>Control your data, cookies, recommendations, connected accounts, and account lifecycle.</p></header>
    ${state.privacyMessage ? `<div class="payment-message">${escapeHtml(state.privacyMessage)}</div>` : ""}
    <section class="privacy-layout"><div class="privacy-controls">
      ${privacyToggleTemplate("Personalized recommendations", "personalized", "Use reading activity, follows, saves, and interests to rank your feed.", state.preferences.personalized)}
      ${privacyToggleTemplate("Activity tracking", "tracking", "Store reading progress, history, and engagement signals.", state.preferences.tracking)}
      <section><h2>Cookie preferences</h2><p>Essential cookies remain enabled for security and account access.</p>${Object.entries(state.preferences.cookies).map(([key, value]) => `<label><span><strong>${key[0].toUpperCase()+key.slice(1)}</strong><small>${key === "essential" ? "Required for sign-in and payments" : "Optional measurement and personalization"}</small></span><input type="checkbox" data-cookie="${key}" ${value ? "checked" : ""} ${key === "essential" ? "disabled" : ""}></label>`).join("")}</section>
      <section><h2>Connected accounts</h2>${connectedAccounts.map(({ provider, name, account }) => `<article><span>${name[0]}</span><div><strong>${name}</strong><small>${account ? `Connected · ${escapeHtml(account.email || "")}` : state.providerStatus.social[provider] ? "Available to connect" : "Provider not configured"}</small></div><button class="secondary-button" ${account ? `data-social-disconnect="${provider}"` : `data-social-login="${provider}"`} ${!account && !state.providerStatus.social[provider] ? "disabled" : ""}>${account ? "Disconnect" : "Connect"}</button></article>`).join("")}</section>
    </div><aside class="privacy-actions"><h2>Your data</h2><button data-action="download-data">${icon("link", 16)}<span><strong>Download your data</strong><small>Articles, comments, history, follows, and account details</small></span></button><button data-action="clear-history">${icon("eye", 16)}<span><strong>Clear reading history</strong><small>Remove opened stories and saved reading positions</small></span></button><button class="danger-zone" data-action="request-account-deletion">${icon("close", 16)}<span><strong>Delete account</strong><small>Start the verified account deletion process</small></span></button><div class="privacy-note">Privacy changes are recorded in your security activity log.</div></aside></section>
  </main>`;
}

function privacyToggleTemplate(title, key, description, enabled) {
  return `<section><label><span><strong>${title}</strong><small>${description}</small></span><input type="checkbox" data-privacy-toggle="${key}" ${enabled ? "checked" : ""}></label></section>`;
}

function adminTemplate() {
  const publishedCount = state.stories.filter((story) => story.status === "published").length;
  const draftCount = state.stories.length - publishedCount;
  const seoConfigured = [state.siteSeo.homepageSeoTitle, state.siteSeo.homepageMetaDescription, state.siteSeo.organizationName]
    .filter((value) => String(value || "").trim()).length;
  const moderationOpen = state.operations.moderation.filter((item) => item.status !== "Resolved" && item.status !== "Dismissed");
  return `
    ${adminShellTemplate("Dashboard", "A focused overview of publishing, members, revenue, moderation, and search health.", `
      <section class="dashboard-grid">
        ${metricTemplate("users", "Users", state.users.length.toLocaleString("en-IN"), `${state.users.filter((user) => user.status === "active").length} active`)}
        ${metricTemplate("pen", "Published blogs", String(publishedCount), `${draftCount} drafts`)}
        ${metricTemplate("card", "Collected revenue", formatINR(Math.round(state.adminAnalytics.revenue / 100)), `${state.adminAnalytics.activeSubscriptions} active subscriptions`)}
        ${metricTemplate("chart", "SEO setup", `${seoConfigured}/3`, state.siteSeo.enableSchema ? "Schema enabled" : "Schema disabled")}
      </section>
      <section class="admin-overview-grid">
        ${adminShortcutTemplate("pen", "Blog management", "Edit, draft, publish, delete, and review every story.", "/admin/blogs", `${state.stories.length} posts`)}
        ${adminShortcutTemplate("users", "User management", "Search members and manage roles, plans, suspensions, or deletion.", "/admin/users", `${state.users.length} users`)}
        ${adminShortcutTemplate("gauge", "Site SEO", "Control search appearance, sitemaps, schema, robots, and verification.", "/admin/seo", "Site-wide")}
        ${adminShortcutTemplate("card", "Settings", "Manage memberships, payments, currencies, and social sign-in.", "/admin/settings", "Platform")}
      </section>
      <section class="admin-dashboard-columns">
        <div class="work-panel">
          <div class="panel-title">${icon("filter")}<h2>Moderation queue</h2></div>
          ${moderationOpen.slice(0, 5).map((item) => `<div class="queue-row"><div><strong>${escapeHtml(item.subject)}</strong><span>${item.risk} risk - ${item.status}</span></div><button class="secondary-button" data-resolve-mod="${item.id}">Resolve</button></div>`).join("") || `<div class="empty-state">No open moderation cases.</div>`}
        </div>
        <div class="work-panel">
          <div class="panel-title">${icon("spark")}<h2>Ad inventory</h2></div>
          ${["Leaderboard", "Medium rectangle", "Native story", "Mobile sticky"].map((slot) => `<div class="inventory-row"><span>${slot}</span><strong>Available</strong></div>`).join("")}
          <button class="full-button" data-route="/">View live placements</button>
        </div>
      </section>
    `)}
  `;
}

function adminShellTemplate(title, description, content, actions = "") {
  const navItems = [
    ["chart", "Dashboard", "/admin"],
    ["pen", "Blogs", "/admin/blogs"],
    ["spark", "Creator studio", "/admin/creator"],
    ["users", "Users", "/admin/users"],
    ["shield", "Moderation", "/admin/moderation"],
    ["link", "Copyright", "/admin/copyright"],
    ["comment", "Support", "/admin/support"],
    ["gauge", "Platform health", "/admin/health"],
    ["lock", "Security", "/admin/security"],
    ["gauge", "Site SEO", "/admin/seo"],
    ["card", "Settings", "/admin/settings"],
  ];
  return `
    <main class="admin-app">
      <aside class="admin-sidebar">
        <div class="admin-sidebar-brand">
          <span class="admin-sidebar-brand-icon">${icon("shield", 20)}</span>
          <span>
            <strong>Admin console</strong>
            <small>InkRiver operations</small>
          </span>
        </div>
        <div class="admin-sidebar-heading">Workspace</div>
        <nav aria-label="Admin navigation">
          ${navItems.map(([iconName, label, route]) => `
            <button class="admin-nav-item ${state.path === route || (route !== "/admin" && state.path.startsWith(route)) ? "active" : ""}" data-route="${route}">
              <span class="admin-nav-icon">${icon(iconName, 18)}</span>
              <span>${label}</span>
            </button>
          `).join("")}
        </nav>
        <div class="admin-sidebar-footer">
          <button class="admin-sidebar-site-link" data-route="/">
            ${icon("eye", 17)}
            <span>View live site</span>
          </button>
          <div class="admin-sidebar-account">
            <span class="admin-sidebar-avatar">A</span>
            <span>
              <strong>Administrator</strong>
              <small>Full access</small>
            </span>
          </div>
        </div>
      </aside>
      <section class="admin-main">
        <header class="admin-page-header">
          <div><h1>${escapeHtml(title)}</h1><p>${escapeHtml(description)}</p></div>
          ${actions}
        </header>
        ${content}
      </section>
    </main>
  `;
}

function adminShortcutTemplate(iconName, title, description, route, meta) {
  return `
    <button class="admin-shortcut" data-route="${route}">
      <span>${icon(iconName, 20)}</span>
      <strong>${title}</strong>
      <p>${description}</p>
      <small>${meta}</small>
    </button>
  `;
}

function adminUsersTemplate() {
  return adminShellTemplate(
    "User management",
    "Search every account by name, email, or user ID and manage access from one dedicated workspace.",
    userManagementTemplate(),
  );
}

function adminSettingsTemplate() {
  return adminShellTemplate(
    "Platform settings",
    "Manage subscription packages, payment credentials, currency conversion, and social login.",
    `<section class="admin-settings-stack">${subscriptionManagerTemplate()}${gatewaySettingsTemplate()}</section>`,
  );
}

function adminBlogsTemplate() {
  return adminShellTemplate(
    "Blog management",
    "Manage published stories and drafts without mixing editorial work into the main dashboard.",
    blogListTemplate(),
    `<div class="admin-header-actions"><button class="secondary-button" data-route="/admin/blogs/categories">${icon("filter", 16)}Categories</button><button class="primary-button" data-route="/admin/blogs/new">${icon("pen", 16)}Create new blog</button></div>`,
  );
}

function adminCategoriesTemplate() {
  return adminShellTemplate(
    "Blog categories",
    "Create and organize reusable categories for blog posts, public filters, category URLs, and search appearance.",
    categoryManagementTemplate(),
    `<button class="secondary-button" data-route="/admin/blogs">${icon("pen", 16)}Back to blogs</button>`,
  );
}

function categoryManagementTemplate() {
  return `
    <section class="category-management-layout">
      <form class="category-editor-panel" id="categoryEditor">
        <div class="panel-title">${icon("filter")}<h2>${state.editingCategoryId ? "Edit category" : "Create category"}</h2></div>
        <label><span>Name</span><input id="categoryName" value="${escapeHtml(state.categoryForm.name)}" placeholder="Category name" /></label>
        <label><span>Slug</span><input id="categorySlug" value="${escapeHtml(state.categoryForm.slug)}" placeholder="category-slug" /></label>
        <label><span>Description</span><textarea id="categoryDescription" placeholder="What belongs in this category?">${escapeHtml(state.categoryForm.description)}</textarea></label>
        <label><span>Visual tone</span><select id="categoryColor">${["mint", "blue", "rose", "amber"].map((color) => `<option value="${color}" ${state.categoryForm.color === color ? "selected" : ""}>${color}</option>`).join("")}</select></label>
        <div class="category-seo-fields">
          <strong>Search appearance</strong>
          <label><span>SEO title</span><input id="categorySeoTitle" value="${escapeHtml(state.categoryForm.seoTitle)}" placeholder="Category title for search results" /></label>
          <label><span>Meta description</span><textarea id="categoryMetaDescription" placeholder="Category description for search results">${escapeHtml(state.categoryForm.metaDescription)}</textarea></label>
        </div>
        <div class="settings-actions">
          <button class="primary-button" type="button" data-action="save-category">${state.editingCategoryId ? "Update category" : "Create category"}</button>
          <button class="secondary-button" type="button" data-action="reset-category-form">Clear form</button>
        </div>
        <span class="form-message">${escapeHtml(state.categoryMessage || "Categories appear in the blog editor and public topic filters.")}</span>
      </form>
      <div class="category-list-panel">
        <div class="category-table-header"><span>Category</span><span>Posts</span><span>Search preview</span><span>Actions</span></div>
        ${state.categories.map((category) => {
          const count = state.stories.filter((story) => story.topic === category.name).length;
          return `
            <article class="category-row">
              <div class="category-identity">
                <span class="category-swatch ${category.color}"></span>
                <div><strong>${escapeHtml(category.name)}</strong><small>/${escapeHtml(category.slug)}</small><p>${escapeHtml(category.description || "No description")}</p></div>
              </div>
              <strong class="category-count">${count}</strong>
              <div class="category-search-preview"><strong>${escapeHtml(category.seoTitle)}</strong><span>${escapeHtml(category.metaDescription || category.description)}</span></div>
              <div class="category-actions">
                <button class="secondary-button" data-edit-category="${category.id}">Edit</button>
                <button class="secondary-button danger-button" data-delete-category="${category.id}">Delete</button>
              </div>
            </article>
          `;
        }).join("")}
      </div>
    </section>
  `;
}

function userManagementTemplate() {
  const users = filteredUsers();
  const activeCount = state.users.filter((user) => user.status === "active").length;
  const suspendedCount = state.users.length - activeCount;
  return `
    <div class="work-panel user-management-panel">
      <div class="panel-title">${icon("users")}<h2>User management</h2></div>
      <div class="user-search-row">
        <label>
          <span>Search users</span>
          <input id="userSearch" value="${state.userSearch}" placeholder="Search by name, email, or user ID" />
        </label>
        <div class="user-counts">
          <span><strong>${state.users.length}</strong><em>Total users</em></span>
          <span><strong>${activeCount}</strong><em>Active</em></span>
          <span><strong>${suspendedCount}</strong><em>Suspended</em></span>
        </div>
      </div>
      <div class="user-admin-list">
        ${users.length ? users.map((user) => `
          <article class="user-admin-row">
            <div class="user-identity">
              <strong>${user.name}</strong>
              <span>${user.email}</span>
              <small>${user.id} - Joined ${user.joined}</small>
            </div>
            <span class="status-pill ${user.status === "suspended" ? "suspended" : ""}">${user.status}</span>
            <div class="user-controls">
              <label>
                <span>Role</span>
                <select data-user-role="${user.id}">
                  ${Object.keys(roleMap).map((role) => `<option value="${role}" ${user.role === role ? "selected" : ""}>${role}</option>`).join("")}
                </select>
              </label>
              <label>
                <span>Subscription</span>
                <select data-user-subscription="${user.id}">
                  ${subscriptionOptions().map((planName) => `<option value="${planName}" ${user.subscription === planName ? "selected" : ""}>${planName}</option>`).join("")}
                </select>
              </label>
            </div>
            <div class="user-actions">
              <button class="secondary-button" data-toggle-user-status="${user.id}">${user.status === "suspended" ? "Restore" : "Suspend"}</button>
              <button class="secondary-button danger-button" data-delete-user="${user.id}">Delete</button>
            </div>
          </article>
        `).join("") : `<div class="empty-users">No users match that search.</div>`}
      </div>
      <div class="settings-actions"><span>${state.userMessage || "Search, promote, suspend, delete, or manage subscriptions from one place."}</span></div>
    </div>
  `;
}

function blogListTemplate() {
  const publishedCount = state.stories.filter((story) => story.status === "published").length;
  const draftCount = state.stories.length - publishedCount;
  return `
    <section class="blog-list-page">
      <div class="list-summary">
        <span><strong>${state.stories.length}</strong>Total</span>
        <span><strong>${publishedCount}</strong>Published</span>
        <span><strong>${draftCount}</strong>Drafts</span>
      </div>
      ${state.blogMessage ? `<div class="payment-message">${escapeHtml(state.blogMessage)}</div>` : ""}
      <div class="blog-admin-list">
        ${state.stories.map((story) => blogAdminRowTemplate(story)).join("")}
      </div>
    </section>
  `;
}

function blogAdminRowTemplate(story) {
  const imageUrl = safeImageUrl(story.imageUrl);
  return `
    <article class="blog-admin-row">
      <div class="blog-admin-media ${story.color} ${imageUrl ? "has-image" : ""}">
        ${imageUrl ? `<img src="${escapeHtml(imageUrl)}" alt="" loading="lazy" decoding="async" />` : `<span>${escapeHtml(story.topic)}</span>`}
      </div>
      <div class="blog-admin-copy">
        <strong>${escapeHtml(story.title)}</strong>
        <span>/${escapeHtml(story.slug)} - ${escapeHtml(story.topic)} - ${escapeHtml(story.readTime)}</span>
        <small>${escapeHtml(story.dek)}</small>
      </div>
      <span class="status-pill ${story.status === "draft" ? "draft" : ""}">${escapeHtml(story.status)}</span>
      <div class="blog-admin-actions">
        <button class="secondary-button" data-edit-blog="${story.id}">Edit</button>
        <button class="secondary-button" data-blog-status="${story.id}" data-next-status="${story.status === "published" ? "draft" : "published"}">${story.status === "published" ? "Draft" : "Publish"}</button>
        <button class="secondary-button danger-button" data-delete-blog="${story.id}">Delete</button>
      </div>
    </article>
  `;
}

function blogEditorPageTemplate() {
  const isEditing = Boolean(state.editingBlogId);
  const formImage = safeImageUrl(state.blogForm.imageUrl);
  const seo = state.blogForm.seo;
  const score = postSeoScore();
  return adminShellTemplate(
    isEditing ? "Edit blog" : "Create new blog",
    "Write and format the article, manage its media, and complete search and social metadata before publishing.",
    `
      <section class="post-editor-layout">
        <div class="post-editor-main">
          <div class="editor-title-fields">
            <input id="blogTitle" class="post-title-input" value="${escapeHtml(state.blogForm.title)}" placeholder="Add title" aria-label="Blog title" />
            <div class="slug-editor">
              <span>${window.location.origin}/stories/</span>
              <input id="blogSlug" value="${escapeHtml(state.blogForm.slug)}" placeholder="story-url-slug" aria-label="Pretty URL slug" />
            </div>
            <textarea id="blogDek" class="post-excerpt-input" placeholder="Write a concise excerpt for feeds and previews" aria-label="Blog excerpt">${escapeHtml(state.blogForm.dek)}</textarea>
          </div>
          <div class="rich-editor-shell">
            ${richEditorToolbarTemplate()}
            <div id="richBlogEditor" class="rich-blog-editor" contenteditable="true" role="textbox" aria-multiline="true" aria-label="Blog content">${sanitizeRichHtml(state.blogForm.contentHtml)}</div>
          </div>
          <section class="editor-panel">
            <div class="panel-title">${icon("eye")}<h2>Featured image</h2></div>
            <div class="featured-image-controls">
              <label><span>Image URL</span><input id="blogImageUrl" value="${escapeHtml(state.blogForm.imageUrl)}" placeholder="https://example.com/image.jpg" /></label>
              <label class="file-field"><span>Upload image</span><input id="blogImageFile" type="file" accept="image/png,image/jpeg,image/gif,image/webp" /></label>
              ${state.mediaMessage ? `<div class="payment-message">${escapeHtml(state.mediaMessage)}</div>` : ""}
              ${formImage ? `<div class="blog-image-preview"><img src="${escapeHtml(formImage)}" alt="" loading="lazy" decoding="async" /><button class="secondary-button" type="button" data-action="remove-blog-image">Remove image</button></div>` : ""}
              ${state.mediaAssets.length ? `<div class="media-picker"><strong>Recent uploads</strong>${state.mediaAssets.slice(0, 8).map((asset) => `<button type="button" data-media-url="${escapeHtml(asset.url)}"><img src="${escapeHtml(asset.url)}" alt="" loading="lazy" decoding="async" /><span>${escapeHtml(asset.originalName)}</span></button>`).join("")}</div>` : ""}
            </div>
          </section>
          ${interactiveEditorTemplate()}
          ${aiCreatorPanelTemplate()}
          ${postSeoEditorTemplate(seo, score)}
        </div>
        <aside class="post-editor-sidebar">
          <section class="editor-panel publish-panel">
            <div class="panel-title">${icon("play")}<h2>Publish</h2></div>
            <div class="publish-status"><span>Status</span><strong>${isEditing ? escapeHtml(state.stories.find((story) => story.id === state.editingBlogId)?.status || "draft") : "New draft"}</strong></div>
            <button class="primary-button wide-button" data-action="save-blog-published">${isEditing ? "Update and publish" : "Publish blog"}</button>
            <button class="secondary-button wide-button" data-action="save-blog-draft">Save as draft</button>
            <button class="text-button wide-button" data-route="/admin/blogs">Cancel</button>
            ${state.blogMessage ? `<div class="payment-message">${escapeHtml(state.blogMessage)}</div>` : ""}
          </section>
          <section class="editor-panel post-settings-panel">
            <div class="panel-title">${icon("filter")}<h2>Post settings</h2></div>
            <label><span>Author</span><input id="blogAuthor" value="${escapeHtml(state.blogForm.author)}" placeholder="Author name" /></label>
            <label><span>Author role</span><input id="blogRole" value="${escapeHtml(state.blogForm.role)}" placeholder="Publication or role" /></label>
            <label><span>Publication</span><input id="blogPublication" value="${escapeHtml(state.blogForm.publication)}" placeholder="Publication name" /></label>
            <label><span>Topic</span><select id="blogTopic">${categoryNames().map((topic) => `<option value="${escapeHtml(topic)}" ${state.blogForm.topic === topic ? "selected" : ""}>${escapeHtml(topic)}</option>`).join("")}</select></label>
            <label><span>Tags</span><input id="blogTags" value="${escapeHtml(state.blogForm.tagsText)}" placeholder="AI, workflow, research" /></label>
            <label><span>Read time</span><input id="blogReadTime" value="${escapeHtml(state.blogForm.readTime)}" placeholder="6 min read" /></label>
            <label><span>Visual tone</span><select id="blogColor">${["mint", "blue", "rose", "amber"].map((color) => `<option value="${color}" ${state.blogForm.color === color ? "selected" : ""}>${color}</option>`).join("")}</select></label>
            <label class="checkbox-field"><input id="blogPremium" type="checkbox" ${state.blogForm.premium ? "checked" : ""} /><span>Member-only paywall</span></label>
          </section>
        </aside>
      </section>
    `,
  );
}

function interactiveEditorTemplate() {
  return `
    <section class="editor-panel interactive-editor-panel">
      <div class="interactive-editor-heading">
        <div class="panel-title">${icon("comment")}<h2>Interactive blocks</h2></div>
        <div>
          <button class="secondary-button" data-add-interactive="poll">Add poll</button>
          <button class="secondary-button" data-add-interactive="survey">Add survey</button>
          <button class="secondary-button" data-add-interactive="quiz">Add quiz</button>
        </div>
      </div>
      <p class="settings-note">Polls, surveys, and quizzes appear after the article body and store each reader's response.</p>
      <div class="interactive-editor-list">
        ${state.blogForm.interactiveBlocks.length ? state.blogForm.interactiveBlocks.map((block, index) => `
          <article class="interactive-editor-row">
            <div class="interactive-editor-type">${block.type}</div>
            <label><span>Question</span><input data-interactive-field="${index}:question" value="${escapeHtml(block.question)}" /></label>
            <label><span>Options, one per line</span><textarea data-interactive-field="${index}:options">${escapeHtml(block.options.join("\n"))}</textarea></label>
            ${block.type === "quiz" ? `<label><span>Correct option</span><select data-interactive-field="${index}:correctIndex">${block.options.map((option, optionIndex) => `<option value="${optionIndex}" ${block.correctIndex === optionIndex ? "selected" : ""}>${optionIndex + 1}. ${escapeHtml(option)}</option>`).join("")}</select></label><label><span>Answer explanation</span><textarea data-interactive-field="${index}:explanation">${escapeHtml(block.explanation || "")}</textarea></label>` : ""}
            ${block.type === "survey" ? `<label class="checkbox-field"><input type="checkbox" data-interactive-field="${index}:multiple" ${block.multiple ? "checked" : ""} /><span>Allow multiple answers</span></label>` : ""}
            <button class="secondary-button danger-button" data-remove-interactive="${index}">Remove</button>
          </article>
        `).join("") : `<div class="empty-state">No interactive blocks yet. Add a poll, survey, or quiz to invite reader participation.</div>`}
      </div>
    </section>
  `;
}

function aiCreatorPanelTemplate() {
  const assistantTools = [
    ["outline", "Brainstorm outline"], ["headline", "Improve headline"], ["summary", "Summarize draft"],
    ["tone", "Change tone"], ["simplify", "Simplify text"], ["excerpt", "Generate excerpt"], ["weakness", "Find weak sections"],
  ];
  const reviewChecks = ["Grammar", "Readability", "Duplication", "Claims", "Broken links", "Accessibility", "Brand voice", "Policy"];
  return `<section class="editor-panel ai-creator-panel">
    <div class="ai-panel-header"><div class="panel-title">${icon("spark")}<div><h2>AI editorial studio</h2><p>${state.providerStatus.ai ? "Suggestions remain optional and visible until you choose to apply them." : "Configure the server AI provider to run editorial tools."}</p></div></div><div class="segmented-control"><button class="${state.aiPanel === "assistant" ? "active" : ""}" data-ai-panel="assistant">Writing assistant</button><button class="${state.aiPanel === "review" ? "active" : ""}" data-ai-panel="review">Quality review</button><button class="${state.aiPanel === "metadata" ? "active" : ""}" data-ai-panel="metadata">Smart metadata</button></div></div>
    ${state.aiPanel === "assistant" ? `<div class="ai-tools">${assistantTools.map(([id,label]) => `<button data-ai-tool="${id}" ${state.providerStatus.ai ? "" : "disabled"}>${icon("spark", 14)}${label}</button>`).join("")}</div>` : ""}
    ${state.aiPanel === "review" ? `<div class="quality-review-grid">${reviewChecks.map((label) => `<article><span>${icon("gauge", 15)}</span><div><strong>${label}</strong><small>Not run</small></div><button data-ai-tool="review-${label.toLowerCase().replace(" ","-")}" ${state.providerStatus.ai ? "" : "disabled"}>Run check</button></article>`).join("")}</div>` : ""}
    ${state.aiPanel === "metadata" ? `<div class="metadata-suggestions"><section><strong>Related internal links</strong>${publishedStories().filter((story) => story.topic === state.blogForm.topic).slice(0,3).map((story) => `<button data-smart-value="link:${story.slug}">${icon("link",14)}${escapeHtml(story.title)}</button>`).join("") || `<span>No related published stories found.</span>`}</section><button class="primary-button" data-ai-tool="generate-seo" ${state.providerStatus.ai ? "" : "disabled"}>Generate metadata suggestions</button></div>` : ""}
    ${state.aiRunning ? `<div class="ai-result loading"><span></span>Reviewing the draft…</div>` : state.aiResult ? `<div class="ai-result"><header><strong>AI suggestion</strong><span>Nothing changes until you apply it.</span></header><p>${escapeHtml(state.aiResult)}</p><div><button class="primary-button" data-action="apply-ai-result">Apply suggestion</button><button class="secondary-button" data-action="dismiss-ai-result">Dismiss</button></div></div>` : ""}
  </section>`;
}

function richEditorToolbarTemplate() {
  return `
    <div class="rich-editor-toolbar" role="toolbar" aria-label="Text formatting">
      <select id="editorBlockFormat" aria-label="Text style">
        <option value="p">Paragraph</option>
        <option value="h2">Heading 2</option>
        <option value="h3">Heading 3</option>
        <option value="h4">Heading 4</option>
      </select>
      <button type="button" data-editor-command="bold" title="Bold" aria-label="Bold"><strong>B</strong></button>
      <button type="button" data-editor-command="italic" title="Italic" aria-label="Italic"><em>I</em></button>
      <button type="button" data-editor-command="underline" title="Underline" aria-label="Underline"><u>U</u></button>
      <button type="button" data-editor-command="strikeThrough" title="Strikethrough" aria-label="Strikethrough"><s>S</s></button>
      <span class="toolbar-divider"></span>
      <button type="button" data-editor-command="insertUnorderedList" title="Bulleted list" aria-label="Bulleted list">• List</button>
      <button type="button" data-editor-command="insertOrderedList" title="Numbered list" aria-label="Numbered list">1. List</button>
      <button type="button" data-editor-command="formatBlock" data-editor-value="blockquote" title="Quote" aria-label="Insert quote">“”</button>
      <button type="button" data-editor-insert="link" title="Insert link" aria-label="Insert link">${icon("link", 16)}</button>
      <button type="button" data-editor-insert="image" title="Insert image" aria-label="Insert image">${icon("eye", 16)}</button>
      <button type="button" data-editor-insert="table" title="Insert table" aria-label="Insert table">Table</button>
      <span class="toolbar-divider"></span>
      <button type="button" data-editor-command="undo" title="Undo" aria-label="Undo">↶</button>
      <button type="button" data-editor-command="redo" title="Redo" aria-label="Redo">↷</button>
      <button type="button" data-editor-command="removeFormat" title="Clear formatting" aria-label="Clear formatting">Clear</button>
    </div>
  `;
}

function postSeoEditorTemplate(seo, score) {
  const previewTitle = seo.seoTitle || state.blogForm.title || "SEO title preview";
  const previewDescription = seo.metaDescription || state.blogForm.dek || "Your meta description will appear here.";
  const previewSlug = state.blogForm.slug || "story-url-slug";
  return `
    <section class="post-seo-panel">
      <div class="seo-panel-header">
        <div><h2>Post SEO</h2><p>Search appearance, keyphrases, indexing, schema, breadcrumbs, and social sharing.</p></div>
        <div class="seo-score ${score >= 80 ? "good" : score >= 50 ? "okay" : "poor"}"><strong>${score}</strong><span>SEO score</span></div>
      </div>
      <div class="search-preview">
        <small>${escapeHtml(window.location.origin)} › stories › ${escapeHtml(previewSlug)}</small>
        <strong>${escapeHtml(previewTitle)}</strong>
        <p>${escapeHtml(previewDescription)}</p>
      </div>
      <div class="seo-section-grid">
        <fieldset class="seo-fieldset">
          <legend>Focus and search appearance</legend>
          ${seoField("Focus keyphrase", "seoFocusKeyphrase", seo.focusKeyphrase, "primary phrase")}
          ${seoField("Additional keyphrases / keywords", "seoAdditionalKeyphrases", seo.additionalKeyphrases, "keyword one, keyword two")}
          ${seoField("SEO title", "seoTitle", seo.seoTitle, "Title shown in search results")}
          ${seoTextarea("Meta description", "seoMetaDescription", seo.metaDescription, "Aim for a clear 120-160 character summary")}
          <div class="field-counters"><span>Title: ${seo.seoTitle.length}/60</span><span>Description: ${seo.metaDescription.length}/160</span></div>
        </fieldset>
        <fieldset class="seo-fieldset">
          <legend>Advanced indexing</legend>
          ${seoField("Canonical URL", "seoCanonicalUrl", seo.canonicalUrl, "https://example.com/original-page")}
          ${seoCheckbox("Allow search engines to index this post", "seoRobotsIndex", seo.robotsIndex)}
          ${seoCheckbox("Allow search engines to follow links", "seoRobotsFollow", seo.robotsFollow)}
          ${seoCheckbox("Mark as cornerstone content", "seoCornerstone", seo.cornerstone)}
          <label><span>Maximum snippet length</span><input id="seoMaxSnippet" type="number" value="${seo.maxSnippet}" /></label>
          <label><span>Maximum image preview</span><select id="seoMaxImagePreview">${["none", "standard", "large"].map((value) => `<option value="${value}" ${seo.maxImagePreview === value ? "selected" : ""}>${value}</option>`).join("")}</select></label>
          <label><span>Maximum video preview seconds</span><input id="seoMaxVideoPreview" type="number" value="${seo.maxVideoPreview}" /></label>
        </fieldset>
        <fieldset class="seo-fieldset">
          <legend>Schema and breadcrumbs</legend>
          <label><span>Page type</span><select id="seoSchemaPageType">${["WebPage", "AboutPage", "ContactPage", "FAQPage", "ProfilePage", "CollectionPage"].map((value) => `<option value="${value}" ${seo.schemaPageType === value ? "selected" : ""}>${value}</option>`).join("")}</select></label>
          <label><span>Article type</span><select id="seoSchemaArticleType">${["Article", "BlogPosting", "NewsArticle", "TechArticle", "ScholarlyArticle"].map((value) => `<option value="${value}" ${seo.schemaArticleType === value ? "selected" : ""}>${value}</option>`).join("")}</select></label>
          ${seoField("Breadcrumb title", "seoBreadcrumbTitle", seo.breadcrumbTitle, "Short navigation title")}
        </fieldset>
        <fieldset class="seo-fieldset">
          <legend>Social appearance</legend>
          ${seoField("Social title", "seoSocialTitle", seo.socialTitle, "Title for Facebook, LinkedIn, X, and WhatsApp")}
          ${seoTextarea("Social description", "seoSocialDescription", seo.socialDescription, "Description for shared cards")}
          ${seoField("Social image URL", "seoSocialImage", seo.socialImage, "https://example.com/social-image.jpg")}
        </fieldset>
      </div>
    </section>
  `;
}

function seoField(label, id, value, placeholder = "") {
  return `<label><span>${label}</span><input id="${id}" value="${escapeHtml(value)}" placeholder="${escapeHtml(placeholder)}" /></label>`;
}

function seoTextarea(label, id, value, placeholder = "") {
  return `<label><span>${label}</span><textarea id="${id}" placeholder="${escapeHtml(placeholder)}">${escapeHtml(value)}</textarea></label>`;
}

function seoCheckbox(label, id, checked) {
  return `<label class="checkbox-field"><input id="${id}" type="checkbox" ${checked ? "checked" : ""} /><span>${label}</span></label>`;
}

function adminSeoTemplate() {
  const seo = state.siteSeo;
  return adminShellTemplate(
    "Site SEO",
    "Control how InkRiver appears in search engines, social networks, structured data, feeds, and webmaster platforms.",
    `
      <form class="site-seo-form" id="siteSeoForm">
        <fieldset class="seo-settings-section">
          <legend>Site basics and homepage</legend>
          <div class="settings-form-grid">
            ${siteSeoField("Site title", "siteTitle", seo.siteTitle)}
            ${siteSeoField("Tagline", "tagline", seo.tagline)}
            ${siteSeoField("Title separator", "titleSeparator", seo.titleSeparator)}
            ${siteSeoField("Homepage SEO title", "homepageSeoTitle", seo.homepageSeoTitle)}
            ${siteSeoTextarea("Homepage meta description", "homepageMetaDescription", seo.homepageMetaDescription)}
            ${siteSeoField("Default social image URL", "defaultSocialImage", seo.defaultSocialImage)}
          </div>
        </fieldset>
        <fieldset class="seo-settings-section">
          <legend>Site representation and Knowledge Graph</legend>
          <div class="settings-form-grid">
            <label><span>Site represents</span><select data-site-seo="representationType"><option value="organization" ${seo.representationType === "organization" ? "selected" : ""}>Organization</option><option value="person" ${seo.representationType === "person" ? "selected" : ""}>Person</option></select></label>
            ${siteSeoField("Organization name", "organizationName", seo.organizationName)}
            ${siteSeoField("Alternate organization name", "alternateName", seo.alternateName)}
            ${siteSeoField("Organization logo URL", "organizationLogo", seo.organizationLogo)}
            ${siteSeoField("Person name", "personName", seo.personName)}
            ${siteSeoField("Facebook page", "facebookPage", seo.facebookPage)}
            ${siteSeoField("X profile", "xProfile", seo.xProfile)}
            ${siteSeoField("LinkedIn page", "linkedinPage", seo.linkedinPage)}
          </div>
        </fieldset>
        <fieldset class="seo-settings-section">
          <legend>Content search appearance</legend>
          <div class="settings-form-grid">
            ${siteSeoField("Post title template", "postTitleTemplate", seo.postTitleTemplate)}
            ${siteSeoField("Post description template", "postDescriptionTemplate", seo.postDescriptionTemplate)}
            ${siteSeoField("Category title template", "categoryTitleTemplate", seo.categoryTitleTemplate)}
            ${siteSeoField("Category description template", "categoryDescriptionTemplate", seo.categoryDescriptionTemplate)}
          </div>
          <div class="settings-toggle-grid">
            ${siteSeoCheckbox("Show posts in search results", "indexPosts", seo.indexPosts)}
            ${siteSeoCheckbox("Show categories in search results", "indexCategories", seo.indexCategories)}
            ${siteSeoCheckbox("Show tags in search results", "indexTags", seo.indexTags)}
            ${siteSeoCheckbox("Index author archives", "indexAuthorArchives", seo.indexAuthorArchives)}
            ${siteSeoCheckbox("Index date archives", "indexDateArchives", seo.indexDateArchives)}
          </div>
        </fieldset>
        <fieldset class="seo-settings-section">
          <legend>XML sitemap and breadcrumbs</legend>
          <div class="settings-toggle-grid">
            ${siteSeoCheckbox("Enable XML sitemap", "enableXmlSitemap", seo.enableXmlSitemap)}
            ${siteSeoCheckbox("Include posts", "sitemapPosts", seo.sitemapPosts)}
            ${siteSeoCheckbox("Include pages", "sitemapPages", seo.sitemapPages)}
            ${siteSeoCheckbox("Include categories", "sitemapCategories", seo.sitemapCategories)}
            ${siteSeoCheckbox("Include tags", "sitemapTags", seo.sitemapTags)}
            ${siteSeoCheckbox("Enable breadcrumbs", "enableBreadcrumbs", seo.enableBreadcrumbs)}
          </div>
          <div class="settings-form-grid">
            ${siteSeoField("Breadcrumb separator", "breadcrumbSeparator", seo.breadcrumbSeparator)}
            ${siteSeoField("Home label", "breadcrumbHomeLabel", seo.breadcrumbHomeLabel)}
          </div>
        </fieldset>
        <fieldset class="seo-settings-section">
          <legend>Webmaster verification</legend>
          <div class="settings-form-grid">
            ${siteSeoField("Google verification code", "googleVerification", seo.googleVerification)}
            ${siteSeoField("Bing verification code", "bingVerification", seo.bingVerification)}
            ${siteSeoField("Pinterest verification code", "pinterestVerification", seo.pinterestVerification)}
            ${siteSeoField("Yandex verification code", "yandexVerification", seo.yandexVerification)}
          </div>
        </fieldset>
        <fieldset class="seo-settings-section">
          <legend>Robots, feeds, and schema defaults</legend>
          <div class="settings-form-grid">
            ${siteSeoTextarea("robots.txt editor", "robotsTxt", seo.robotsTxt)}
            ${siteSeoTextarea("Content before each RSS post", "rssBefore", seo.rssBefore)}
            ${siteSeoTextarea("Content after each RSS post", "rssAfter", seo.rssAfter)}
            <label><span>Default page schema</span><select data-site-seo="defaultPageSchema">${["WebPage", "AboutPage", "ContactPage", "CollectionPage"].map((value) => `<option value="${value}" ${seo.defaultPageSchema === value ? "selected" : ""}>${value}</option>`).join("")}</select></label>
            <label><span>Default article schema</span><select data-site-seo="defaultArticleSchema">${["Article", "BlogPosting", "NewsArticle", "TechArticle"].map((value) => `<option value="${value}" ${seo.defaultArticleSchema === value ? "selected" : ""}>${value}</option>`).join("")}</select></label>
          </div>
        </fieldset>
        <fieldset class="seo-settings-section">
          <legend>SEO features</legend>
          <div class="settings-toggle-grid">
            ${siteSeoCheckbox("SEO analysis", "enableSeoAnalysis", seo.enableSeoAnalysis)}
            ${siteSeoCheckbox("Readability analysis", "enableReadabilityAnalysis", seo.enableReadabilityAnalysis)}
            ${siteSeoCheckbox("Cornerstone content", "enableCornerstone", seo.enableCornerstone)}
            ${siteSeoCheckbox("Open Graph metadata", "enableOpenGraph", seo.enableOpenGraph)}
            ${siteSeoCheckbox("Schema output", "enableSchema", seo.enableSchema)}
            ${siteSeoCheckbox("Generate llms.txt", "enableLlmsTxt", seo.enableLlmsTxt)}
          </div>
        </fieldset>
        <div class="sticky-settings-actions">
          <button class="primary-button" type="button" data-action="save-site-seo">Save SEO settings</button>
          <span>${escapeHtml(state.siteSeoMessage || "Settings are stored locally in this prototype.")}</span>
        </div>
      </form>
    `,
  );
}

function siteSeoField(label, key, value) {
  return `<label><span>${label}</span><input data-site-seo="${key}" value="${escapeHtml(value)}" /></label>`;
}

function siteSeoTextarea(label, key, value) {
  return `<label class="wide-field"><span>${label}</span><textarea data-site-seo="${key}">${escapeHtml(value)}</textarea></label>`;
}

function siteSeoCheckbox(label, key, checked) {
  return `<label class="checkbox-field"><input data-site-seo="${key}" type="checkbox" ${checked ? "checked" : ""} /><span>${label}</span></label>`;
}

function subscriptionManagerTemplate() {
  return `
    <div class="work-panel subscription-manager-panel">
      <div class="panel-title">${icon("card")}<h2>Subscription packages</h2></div>
      <p class="settings-note">Create, edit, or delete membership packages. Prices are entered in base INR and automatically convert on the pricing page.</p>
      <div class="plan-manager-layout">
        <form class="plan-editor" id="planEditor">
          <label>
            <span>Package name</span>
            <input id="planName" value="${state.planForm.name}" placeholder="Premium Monthly" />
          </label>
          <label>
            <span>Base price in INR</span>
            <input id="planPrice" type="number" min="1" step="1" value="${state.planForm.price}" />
          </label>
          <label>
            <span>Billing period</span>
            <select id="planPeriod">
              ${["month", "quarter", "year"].map((period) => `<option value="${period}" ${state.planForm.period === period ? "selected" : ""}>${period}</option>`).join("")}
            </select>
          </label>
          <label>
            <span>Short note</span>
            <input id="planNote" value="${state.planForm.note}" placeholder="Best for regular readers" />
          </label>
          <label class="wide-field">
            <span>Features, one per line</span>
            <textarea id="planFeatures" placeholder="Unlimited member stories&#10;Ad-free reading">${state.planForm.features}</textarea>
          </label>
          <div class="settings-actions">
            <button class="primary-button" type="button" data-action="save-plan">${state.editingPlanId ? "Update package" : "Create package"}</button>
            <button class="secondary-button" type="button" data-action="reset-plan-form">Clear form</button>
            <span>${state.planMessage || "No package changes yet"}</span>
          </div>
        </form>
        <div class="plan-admin-list">
          ${state.plans.map((plan) => `
            <article class="plan-admin-row">
              <div>
                <strong>${plan.name}</strong>
                <span>${formatINR(plan.price)} / ${plan.period} - ${plan.note}</span>
                <small>${plan.features.join(" · ")}</small>
              </div>
              <div>
                <button class="secondary-button" data-edit-plan="${plan.id}">Edit</button>
                <button class="secondary-button danger-button" data-delete-plan="${plan.id}">Delete</button>
              </div>
            </article>
          `).join("")}
        </div>
      </div>
    </div>
  `;
}

function gatewaySettingsTemplate() {
  return `
    <div class="work-panel gateway-settings-panel">
      <div class="panel-title">${icon("card")}<h2>Provider configuration</h2></div>
      <p class="settings-note">Production credentials are read from server environment variables. Secrets are never exposed to this page or browser storage.</p>
      <div class="gateway-settings-grid">
        <fieldset class="gateway-fieldset social-auth-fieldset">
          <legend>Social login</legend>
          <small>Enable or disable providers after their server OAuth credentials and callbacks are configured.</small>
          <button class="toggle-row" data-boolean-setting="googleLoginEnabled" aria-pressed="${state.gatewaySettings.googleLoginEnabled !== false}">
            <span>Enable Google login</span>
            <span class="toggle ${state.gatewaySettings.googleLoginEnabled !== false ? "on" : ""}"><span></span></span>
          </button>
          <div class="gateway-status ${state.providerStatus.social.google ? "ready" : "needs-key"}"><strong>Google ${state.providerStatus.social.google ? "configured" : "unavailable"}</strong><span>GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET</span></div>
          <button class="toggle-row" data-boolean-setting="facebookLoginEnabled" aria-pressed="${state.gatewaySettings.facebookLoginEnabled !== false}">
            <span>Enable Facebook login</span>
            <span class="toggle ${state.gatewaySettings.facebookLoginEnabled !== false ? "on" : ""}"><span></span></span>
          </button>
          <div class="gateway-status ${state.providerStatus.social.facebook ? "ready" : "needs-key"}"><strong>Facebook ${state.providerStatus.social.facebook ? "configured" : "unavailable"}</strong><span>FACEBOOK_APP_ID and FACEBOOK_APP_SECRET</span></div>
        </fieldset>
        ${paymentGateways.map((gateway) => `
          <fieldset class="gateway-fieldset">
            <legend>${gateway.name}</legend>
            <small>${gateway.serverNote}</small>
            <div class="gateway-status ${state.providerStatus.payments[gateway.id] ? "ready" : "needs-key"}"><strong>${state.providerStatus.payments[gateway.id] ? "Server configured" : "Credentials missing"}</strong><span>${gateway.fields.map(([key]) => key.replace(/([A-Z])/g, "_$1").toUpperCase()).join(" and ")}</span></div>
          </fieldset>
        `).join("")}
        <fieldset class="gateway-fieldset">
          <legend>Currency API</legend>
          <small>The public exchange-rate endpoint is called directly and falls back to cached estimates when unavailable.</small>
          <label>
            <span>Cashfree environment</span>
            <select data-setting="cashfreeEnvironment">
              <option value="sandbox" ${state.gatewaySettings.cashfreeEnvironment === "sandbox" ? "selected" : ""}>Sandbox</option>
              <option value="production" ${state.gatewaySettings.cashfreeEnvironment === "production" ? "selected" : ""}>Production</option>
            </select>
          </label>
        </fieldset>
      </div>
      <div class="settings-actions">
        <button class="primary-button" data-action="save-gateway-settings">Save public provider preferences</button>
        <button class="secondary-button" data-action="refresh-rates">Test currency API</button>
        <span>${state.gatewaySettingsSaved ? "Public preferences saved" : "Restart the server after changing provider environment variables."}</span>
      </div>
    </div>
    ${adCampaignManagerTemplate()}
    ${discountManagerTemplate()}
    ${payoutManagerTemplate()}
    ${translationManagerTemplate()}
  `;
}

function adCampaignManagerTemplate() {
  const placementLabel = { leaderboard: "Leaderboard", square: "Square", native: "Native story" };
  return `<div class="work-panel"><div class="panel-title">${icon("spark")}<h2>Advertisement campaigns</h2></div><p class="settings-note">Campaigns shown here are served into live ad placements and record impressions and clicks.</p><div class="settings-actions"><button class="primary-button" data-action="create-ad-campaign">Create ad campaign</button><button class="secondary-button" data-action="refresh-commerce">Refresh ad stats</button></div><div class="promotion-list">${state.adCampaigns.length ? state.adCampaigns.map((campaign) => `<article><span><strong>${escapeHtml(campaign.name)}</strong><small>${escapeHtml(placementLabel[campaign.placement] || campaign.placement)} · ${escapeHtml(campaign.sponsor)} · ${Number(campaign.impressions || 0).toLocaleString("en-IN")} views · ${Number(campaign.clicks || 0).toLocaleString("en-IN")} clicks</small></span><b>${escapeHtml(campaign.status)}</b><button class="toggle ${campaign.status === "active" ? "active" : ""}" data-toggle-ad="${campaign.id}" aria-label="Toggle ${escapeHtml(campaign.name)}"><span></span></button></article>`).join("") : `<div class="empty-state">No ad campaigns have been created yet.</div>`}</div></div>`;
}

function discountManagerTemplate() {
  return `<div class="work-panel"><div class="panel-title">${icon("card")}<h2>Discounts and promotions</h2></div><p class="settings-note">Active discount codes are validated by checkout before the payment order is created.</p><div class="settings-actions"><button class="primary-button" data-action="create-discount">Create discount code</button></div><div class="promotion-list">${state.discounts.length ? state.discounts.map((discount) => `<article><span><strong>${escapeHtml(discount.code)}</strong><small>${escapeHtml(discount.audience || "All readers")} · ${discount.discountType === "amount" ? formatINR(Number(discount.discountValue || 0) / 100) : `${discount.discountValue}%`} · ${Number(discount.redeemedCount || 0).toLocaleString("en-IN")} used</small></span><b>${discount.active ? "Active" : "Inactive"}</b><button class="toggle ${discount.active ? "active" : ""}" data-toggle-discount="${discount.id}" aria-label="Toggle ${escapeHtml(discount.code)}"><span></span></button></article>`).join("") : `<div class="empty-state">No discount codes have been created yet.</div>`}</div></div>`;
}

function payoutManagerTemplate() {
  return `<div class="work-panel"><div class="panel-title">${icon("money")}<h2>Writer payouts</h2></div><p class="settings-note">Verified payout accounts can be batched from paid tips and tracked through processing states.</p><div class="settings-actions"><button class="primary-button" data-action="create-payout">Create payout batch</button></div><div class="member-two-column"><div><h3>Payout accounts</h3><div class="promotion-list">${state.payoutAccounts.length ? state.payoutAccounts.map((account) => `<article><span><strong>${escapeHtml(account.name || account.account_holder)}</strong><small>${escapeHtml(account.email || "")} · ${escapeHtml(account.bank_name || "Provider")} · ${escapeHtml(String(account.status || "").replaceAll("_", " "))}</small></span><button class="secondary-button" data-verify-payout-account="${account.user_id}" ${account.status === "verified" ? "disabled" : ""}>${account.status === "verified" ? "Verified" : "Verify"}</button></article>`).join("") : `<div class="empty-state">Writers will appear here after they add payout details.</div>`}</div></div><div><h3>Payout batches</h3><div class="promotion-list">${state.adminPayouts.length ? state.adminPayouts.map((payout) => `<article><span><strong>${escapeHtml(payout.writer_name || payout.writer_user_id)}</strong><small>${escapeHtml(payout.id)} · ${new Date(payout.created_at).toLocaleDateString()}</small></span><b>${formatINR(Number(payout.amount || 0) / 100)}</b><select data-payout-status="${payout.id}">${["pending", "processing", "paid", "failed", "cancelled"].map((status) => `<option value="${status}" ${payout.status === status ? "selected" : ""}>${status}</option>`).join("")}</select></article>`).join("") : `<div class="empty-state">No payout batches have been created yet.</div>`}</div></div></div></div>`;
}

function translationManagerTemplate() {
  const translatedCount = Object.values(state.translations || {}).reduce((sum, locales) => sum + Object.keys(locales || {}).length, 0);
  return `<div class="work-panel"><div class="panel-title">${icon("spark")}<h2>Article translations</h2></div><p class="settings-note">Published articles are translated once with OpenAI, stored, and served from the database when readers choose a configured language.</p><div class="gateway-status ${state.providerStatus.ai ? "ready" : "needs-key"}"><strong>${state.providerStatus.ai ? "OpenAI configured" : "OpenAI key missing"}</strong><span>${state.providerStatus.ai ? `${translatedCount} saved translations available` : "Add OPENAI_API_KEY on the server, then restart."}</span></div><div class="promotion-list">${state.translationLanguages.map((item) => `<article><span><strong>${escapeHtml(item.language)}</strong><small>${escapeHtml(item.locale)}</small></span><button class="secondary-button danger-button" data-remove-translation-language="${escapeHtml(item.locale)}">Remove</button></article>`).join("")}</div><div class="settings-actions"><button class="secondary-button" data-action="add-translation-language">Add language</button><button class="primary-button" data-action="run-translation-backfill" ${state.providerStatus.ai ? "" : "disabled"}>Translate published articles</button><span>${state.translationLanguages.length} translation languages configured</span></div></div>`;
}

function dashboardHeaderTemplate(title, locked = false) {
  return `
    <section class="dashboard-header">
      <div><h1>${title}</h1><p>Signed in as ${escapeHtml(state.user?.name || "Reader")} · ${escapeHtml(state.user?.role || "reader")}</p></div>
      <button class="secondary-button" data-action="open-account">${icon("users", 15)}Account</button>
    </section>
  `;
}

function metricTemplate(iconName, label, value, delta) {
  return `<article class="metric-card"><span>${icon(iconName, 19)}</span><small>${label}</small><strong>${value}</strong><em>${delta}</em></article>`;
}

function toggleTemplate(label, key, checked) {
  return `<button class="toggle-row" data-toggle="${key}" aria-pressed="${checked}"><span>${label}</span><span class="toggle ${checked ? "on" : ""}"><span></span></span></button>`;
}

function adTemplate(size, label) {
  const campaign = state.adCampaigns.find((item) => item.placement === size && item.status === "active");
  if (!campaign) return `<aside class="ad-block ${size}" aria-label="${label}"><span>Ad slot</span><strong>No active campaign</strong></aside>`;
  const impressionKey = `${campaign.id}:${size}:${state.path}`;
  if (!state.observedAds.has(impressionKey)) {
    state.observedAds.add(impressionKey);
    window.setTimeout(() => recordAdEvent(campaign.id, "impression", size), 0);
  }
  return `<aside class="ad-block ${size}" aria-label="${label}"><span>Sponsored by ${escapeHtml(campaign.sponsor)}</span><strong>${escapeHtml(campaign.headline)}</strong>${campaign.creativeUrl ? `<img src="${escapeHtml(campaign.creativeUrl)}" alt="" loading="lazy" decoding="async" />` : ""}<button data-ad-click="${campaign.id}" data-ad-url="${escapeHtml(campaign.targetUrl)}">${icon("link", 14)}Visit sponsor</button></aside>`;
}

function recordAdEvent(campaignId, eventType, placement, storySlug = "") {
  apiRequest("/api/ads/events", {
    method: "POST",
    body: JSON.stringify({ campaignId, eventType, placement, storySlug }),
  }).catch(() => {});
}

function checkoutTemplate(plan) {
  const gateway = paymentGateways.find((item) => item.id === state.gateway) || paymentGateways[0];
  const configured = Boolean(state.providerStatus.payments[gateway.id]);
  return `
    <div class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="checkout-title">
      <section class="checkout-modal">
        <button class="close-button" data-action="close-checkout" aria-label="Close checkout">${icon("close")}</button>
        <h2 id="checkout-title">Complete membership</h2>
        <p>${plan.name} plan - ${formatMoneyFromINR(plan.price)}/${plan.period}. Converted from INR in real time.</p>
        ${currencyControlTemplate("checkout")}
        <div class="gateway-list">
          ${paymentGateways.map((item) => `
            <button class="gateway-card ${state.gateway === item.id ? "active" : ""}" data-gateway="${item.id}">
              ${icon("card")}<span><strong>${item.name}</strong><small>${item.type}</small></span>
            </button>
          `).join("")}
        </div>
        <div class="gateway-status ${configured ? "ready" : "needs-key"}">
          <strong>${gateway.name} ${configured ? "configured" : "needs API keys"}</strong>
          <span>${configured ? "Checkout handoff is ready for backend order creation." : "Add credentials to the server environment before going live."}</span>
          <small>${gateway.serverNote}</small>
        </div>
        ${state.paymentMessage ? `<div class="payment-message">${state.paymentMessage}</div>` : ""}
        <label class="discount-field"><span>Discount code</span><div><input id="discountCode" value="${escapeHtml(state.checkoutDiscountCode)}" placeholder="STUDENT50" /><button class="secondary-button" data-action="apply-discount" ${state.checkoutDiscountCode.trim() ? "" : "disabled"}>Apply</button></div></label>
        <div class="checkout-summary">
          <span>Subtotal</span><strong>${formatMoneyFromINR(plan.price)}</strong>
          ${state.checkoutDiscount ? `<span>Discount ${escapeHtml(state.checkoutDiscount.code)}</span><strong>- ${formatMoneyFromINR(state.checkoutDiscount.discountAmount / 100)}</strong>` : ""}
          <span>Base INR</span><strong>${formatINR(plan.price)}</strong>
          <span>Gateway</span><strong>${gateway.name}</strong>
          <span>Tax mode</span><strong>Calculated by provider</strong>
        </div>
        <button class="primary-button wide-button" data-payment-submit="${plan.id}" ${configured ? "" : "disabled"}>${configured ? "Continue to gateway" : "Gateway not configured"}</button>
      </section>
    </div>
  `;
}

function loginTemplate() {
  if (state.resetToken) {
    return `
      <div class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="reset-title">
        <section class="checkout-modal auth-modal">
          <button class="close-button" data-action="close-login" aria-label="Close password reset">${icon("close")}</button>
          <h2 id="reset-title">Set a new password</h2>
          <p>Enter a new password for your InkRiver account. Existing sessions will be signed out.</p>
          <form class="auth-form" id="resetPasswordForm">
            <label><span>New password</span><input id="resetPassword" name="password" type="password" autocomplete="new-password" minlength="10" maxlength="128" required /></label>
            <small>Use at least 10 characters.</small>
            <button class="primary-button wide-button" type="submit" ${state.authBusy ? "disabled" : ""}>${state.authBusy ? "Updating..." : "Reset password"}</button>
          </form>
          ${state.loginMessage ? `<div class="payment-message">${state.loginMessage}</div>` : ""}
        </section>
      </div>
    `;
  }
  if (state.user) {
    return `
      <div class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="account-title">
        <section class="checkout-modal auth-modal">
          <button class="close-button" data-action="close-login" aria-label="Close account">${icon("close")}</button>
          <h2 id="account-title">Your account</h2>
          <div class="account-card">
            <strong>${escapeHtml(state.user.name)}</strong>
            <span>${escapeHtml(state.user.email)}</span>
            <small>${escapeHtml(state.user.role)} · ${escapeHtml(state.user.subscription)}</small>
          </div>
          <button class="primary-button wide-button" data-route="${state.user.role === "admin" ? "/admin" : "/dashboard"}">Open my dashboard</button>
          <button class="secondary-button wide-button" data-route="/me">View reader profile</button>
          <button class="secondary-button wide-button" data-action="logout">Sign out</button>
        </section>
      </div>
    `;
  }
  const providers = enabledSocialProviders();
  return `
    <div class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="login-title">
      <section class="checkout-modal auth-modal">
        <button class="close-button" data-action="close-login" aria-label="Close login">${icon("close")}</button>
        <div class="auth-tabs">
          <button class="${state.authMode === "login" ? "active" : ""}" data-auth-mode="login">Sign in</button>
          <button class="${state.authMode === "register" ? "active" : ""}" data-auth-mode="register">Create account</button>
        </div>
        <h2 id="login-title">${state.authMode === "register" ? "Create your InkRiver account" : "Welcome back"}</h2>
        <p>${state.authMode === "register" ? "New accounts start with the reader role. Staff permissions can only be granted by an administrator." : "Sign in to access your personal dashboard, reading history, subscriptions, and saved stories."}</p>
        <form class="auth-form" id="authForm">
          ${state.authMode === "register" ? `<label><span>Full name</span><input id="authName" name="name" autocomplete="name" maxlength="80" value="${escapeHtml(state.authForm.name)}" required /></label>` : ""}
          <label><span>Email address</span><input id="authEmail" name="email" type="email" autocomplete="email" maxlength="254" value="${escapeHtml(state.authForm.email)}" required /></label>
          <label><span>Password</span><input id="authPassword" name="password" type="password" autocomplete="${state.authMode === "register" ? "new-password" : "current-password"}" minlength="10" maxlength="128" required /></label>
          ${state.authMode === "login" ? `<label><span>Authenticator or recovery code</span><input id="authTwoFactorCode" name="twoFactorCode" inputmode="numeric" autocomplete="one-time-code" maxlength="20" value="${escapeHtml(state.authForm.twoFactorCode || "")}" placeholder="Only needed when 2FA is enabled" /></label>` : ""}
          ${state.authMode === "register" ? `<small>Use at least 10 characters with uppercase, lowercase, and a number.</small>` : ""}
          <button class="primary-button wide-button" type="submit" ${state.authBusy ? "disabled" : ""}>${state.authBusy ? "Please wait…" : state.authMode === "register" ? "Create secure account" : "Sign in"}</button>
        </form>
        ${state.authMode === "login" ? `<button class="text-button auth-forgot" data-action="forgot-password">Forgot password?</button>` : ""}
        <div class="auth-divider"><span>or</span></div>
        <div class="social-login-list">
          ${
            providers.length
              ? providers.map((provider) => `
                <button class="social-login-button ${provider.id}" data-social-login="${provider.id}">
                  <span>${provider.id === "google" ? "G" : "f"}</span>
                  Continue with ${provider.name}
                  <small>${state.providerStatus.social[provider.id] ? "Secure OAuth sign-in" : "Unavailable until server credentials are configured"}</small>
                </button>
              `).join("")
              : `<div class="empty-state">Social login is disabled by the admin. Enable Google, Facebook, or both from the Admin dashboard.</div>`
          }
        </div>
        ${state.loginMessage ? `<div class="payment-message">${state.loginMessage}</div>` : ""}
        <div class="auth-note">Passwords are hashed on the server. Sessions use an HTTP-only cookie and cannot be read by page scripts.</div>
      </section>
    </div>
  `;
}

function onboardingTemplate() {
  const selectedCount = state.onboardingSelection.size;
  return `
    <div class="modal-backdrop onboarding-backdrop" role="dialog" aria-modal="true" aria-labelledby="onboarding-title">
      <section class="onboarding-modal">
        <div class="onboarding-progress"><span></span><span></span><span></span></div>
        <div class="onboarding-heading">
          <span class="onboarding-mark">${icon("spark", 22)}</span>
          <div>
            <small>Welcome to InkRiver</small>
            <h2 id="onboarding-title">What do you want to read?</h2>
            <p>Choose at least three interests. Your feed will start here, then adapt as you read, save, like, share, or ask to see less.</p>
          </div>
        </div>
        <div class="onboarding-interest-grid">
          ${state.categories.map((category) => {
            const selected = state.onboardingSelection.has(category.name);
            return `
              <button class="onboarding-interest ${selected ? "selected" : ""}" data-onboarding-interest="${escapeHtml(category.name)}" aria-pressed="${selected}">
                <span class="onboarding-interest-visual ${category.color}">${escapeHtml(category.name.slice(0, 2).toUpperCase())}</span>
                <span><strong>${escapeHtml(category.name)}</strong><small>${escapeHtml(category.description)}</small></span>
                <span class="onboarding-interest-check">${selected ? icon("check", 15) : ""}</span>
              </button>
            `;
          }).join("")}
        </div>
        <div class="onboarding-footer">
          <div>
            <strong>${selectedCount} selected</strong>
            <span>${escapeHtml(state.onboardingMessage || (selectedCount < 3 ? `Choose ${3 - selectedCount} more to continue` : "Your first personalized feed is ready"))}</span>
          </div>
          <button class="primary-button" data-action="complete-onboarding" ${selectedCount < 3 ? "disabled" : ""}>Build my feed ${icon("trend", 16)}</button>
        </div>
      </section>
    </div>
  `;
}

function footerTemplate() {
  return `
    <footer class="site-footer">
      <button class="brand compact" data-route="/"><span class="brand-mark">IR</span><span>InkRiver</span></button>
      <div>
        <button data-route="/pricing">Membership</button>
        <button data-route="/dashboard">Dashboard</button>
        <button data-route="/admin">Admin</button>
        <button data-route="/privacy">Privacy</button>
        <button data-route="/security">Security</button>
        <button data-route="/support">Support</button>
      </div>
    </footer>
  `;
}

function setMetaTag(selector, attributes) {
  let element = document.head.querySelector(selector);
  if (!element) {
    element = document.createElement("meta");
    document.head.appendChild(element);
  }
  Object.entries(attributes).forEach(([key, value]) => element.setAttribute(key, value));
}

function applyDocumentSeo() {
  const story = state.stories.find((item) => item.status === "published" && state.path.includes(`/stories/${item.slug}`));
  const category = state.categories.find((item) => state.path === `/category/${item.slug}`);
  const seo = story?.seo || {};
  const title = story ? (seo.seoTitle || story.title) : category ? category.seoTitle : state.siteSeo.homepageSeoTitle;
  const description = story ? (seo.metaDescription || story.dek) : category ? (category.metaDescription || category.description) : state.siteSeo.homepageMetaDescription;
  document.title = title;
  setMetaTag('meta[name="description"]', { name: "description", content: description });
  setMetaTag('meta[name="robots"]', {
    name: "robots",
    content: story ? `${seo.robotsIndex === false ? "noindex" : "index"},${seo.robotsFollow === false ? "nofollow" : "follow"},max-snippet:${seo.maxSnippet ?? -1},max-image-preview:${seo.maxImagePreview || "large"},max-video-preview:${seo.maxVideoPreview ?? -1}` : "index,follow",
  });
  setMetaTag('meta[property="og:title"]', { property: "og:title", content: story ? (seo.socialTitle || title) : title });
  setMetaTag('meta[property="og:description"]', { property: "og:description", content: story ? (seo.socialDescription || description) : description });
  setMetaTag('meta[property="og:type"]', { property: "og:type", content: story ? "article" : "website" });
  const socialImage = story ? (seo.socialImage || story.imageUrl) : state.siteSeo.defaultSocialImage;
  if (socialImage) setMetaTag('meta[property="og:image"]', { property: "og:image", content: socialImage });
  let canonical = document.head.querySelector('link[rel="canonical"]');
  if (!canonical) {
    canonical = document.createElement("link");
    canonical.rel = "canonical";
    document.head.appendChild(canonical);
  }
  canonical.href = story && seo.canonicalUrl ? seo.canonicalUrl : window.location.href.split("?")[0];
  document.getElementById("inkriver-schema")?.remove();
  if (state.siteSeo.enableSchema) {
    const schema = document.createElement("script");
    schema.id = "inkriver-schema";
    schema.type = "application/ld+json";
    schema.textContent = JSON.stringify(story ? {
      "@context": "https://schema.org",
      "@type": seo.schemaArticleType || "Article",
      headline: story.title,
      description,
      image: story.imageUrl || undefined,
      author: { "@type": "Person", name: story.author },
      mainEntityOfPage: seo.canonicalUrl || storyUrl(story),
    } : category ? {
      "@context": "https://schema.org",
      "@type": "CollectionPage",
      name: category.name,
      description,
      url: `${window.location.origin}/category/${category.slug}`,
    } : {
      "@context": "https://schema.org",
      "@type": state.siteSeo.representationType === "person" ? "Person" : "Organization",
      name: state.siteSeo.representationType === "person" ? state.siteSeo.personName : state.siteSeo.organizationName,
      alternateName: state.siteSeo.alternateName || undefined,
      logo: state.siteSeo.organizationLogo || undefined,
      url: window.location.origin,
    });
    document.head.appendChild(schema);
  }
}

async function runAiTool(tool) {
  state.aiRunning = true;
  state.aiResult = "";
  render();
  try {
    const payload = await apiRequest("/api/ai/assist", {
      method: "POST",
      body: JSON.stringify({
        task: tool,
        title: state.blogForm.title || state.draft.title,
        excerpt: state.blogForm.dek || state.draft.subtitle,
        content: state.blogForm.contentHtml || "",
      }),
    });
    state.aiResult = payload.result || "The AI provider returned an empty response.";
  } catch (error) {
    state.aiResult = `AI unavailable: ${error.message}`;
  } finally {
    state.aiRunning = false;
    render();
  }
}

function render() {
  document.documentElement.dataset.theme = state.theme;
  document.documentElement.lang = state.preferences.locale.split("-")[0];
  document.documentElement.dir = state.preferences.locale.startsWith("ar") ? "rtl" : "ltr";
  document.documentElement.style.setProperty("--reader-scale", `${state.preferences.textScale / 100}`);
  document.body.classList.toggle("focus-reading", state.preferences.focusMode && state.path.startsWith("/stories/"));
  document.body.classList.toggle("reader-sans", state.preferences.fontFamily === "sans");
  document.getElementById("root").innerHTML = appTemplate();
  bindInputs();
  applyDocumentSeo();
  const selectedStory = state.stories.find((story) => story.status === "published" && state.path.includes(`/stories/${story.slug}`));
  if (selectedStory) {
    beginArticleSession(selectedStory);
    if (state.pendingResumeSlug === selectedStory.slug) {
      const position = state.readingHistory.find((entry) => entry.slug === selectedStory.slug)?.position || 0;
      state.pendingResumeSlug = "";
      window.setTimeout(() => {
        const article = document.querySelector(".article-page");
        if (article) window.scrollTo({ top: window.scrollY + article.getBoundingClientRect().top + position, behavior: "smooth" });
      }, 60);
    }
  }
}

function bindInputs() {
  const authForm = document.getElementById("authForm");
  authForm?.addEventListener("submit", (event) => {
    event.preventDefault();
    submitAuthentication();
  });
  const resetPasswordForm = document.getElementById("resetPasswordForm");
  resetPasswordForm?.addEventListener("submit", (event) => {
    event.preventDefault();
    submitPasswordReset();
  });
  document.getElementById("resetPassword")?.addEventListener("input", (event) => {
    state.resetPassword = event.target.value;
  });
  document.getElementById("blogImageFile")?.addEventListener("change", (event) => {
    uploadBlogImage(event.target.files?.[0]);
  });
  document.querySelectorAll("[data-media-url]").forEach((button) => {
    button.addEventListener("click", () => {
      state.blogForm.imageUrl = button.dataset.mediaUrl || "";
      state.blogForm.seo.socialImage = state.blogForm.seo.socialImage || state.blogForm.imageUrl;
      state.mediaMessage = "Media asset attached to this post.";
      render();
    });
  });
  [["authName", "name"], ["authEmail", "email"], ["authPassword", "password"], ["authTwoFactorCode", "twoFactorCode"]].forEach(([id, key]) => {
    document.getElementById(id)?.addEventListener("input", (event) => {
      state.authForm[key] = event.target.value;
    });
  });
  const searchInput = document.getElementById("searchInput");
  if (searchInput) {
    searchInput.addEventListener("input", (event) => {
      state.query = event.target.value;
      state.searchOpen = true;
      render();
      const next = document.getElementById("searchInput");
      next?.focus();
      next?.setSelectionRange(state.query.length, state.query.length);
    });
    searchInput.addEventListener("keydown", (event) => {
      if (event.key === "Enter") {
        state.searchOpen = false;
        setRoute("/search");
      }
      if (event.key === "Escape") {
        state.searchOpen = false;
        render();
      }
    });
  }
  const searchPageInput = document.getElementById("searchPageInput");
  searchPageInput?.addEventListener("input", (event) => {
    state.query = event.target.value;
    render();
    const next = document.getElementById("searchPageInput");
    next?.focus();
    next?.setSelectionRange(state.query.length, state.query.length);
  });
  document.querySelectorAll("[data-search-filter]").forEach((field) => {
    field.addEventListener("change", (event) => {
      state.searchFilters[event.target.dataset.searchFilter] = event.target.value;
      render();
    });
  });
  const historySearch = document.getElementById("historySearch");
  historySearch?.addEventListener("input", (event) => {
    state.historyQuery = event.target.value;
    render();
    const next = document.getElementById("historySearch");
    next?.focus();
    next?.setSelectionRange(state.historyQuery.length, state.historyQuery.length);
  });
  const segmentSearch = document.getElementById("segmentSearch");
  segmentSearch?.addEventListener("input", (event) => {
    state.segmentQuery = event.target.value;
    render();
    const next = document.getElementById("segmentSearch");
    next?.focus();
    next?.setSelectionRange(state.segmentQuery.length, state.segmentQuery.length);
  });
  document.getElementById("tipCommission")?.addEventListener("input", (event) => {
    state.creatorTools.tipCommission = Math.max(0, Math.min(30, Number(event.target.value) || 0));
  });
  document.getElementById("discountCode")?.addEventListener("input", (event) => {
    state.checkoutDiscountCode = event.target.value.toUpperCase();
    state.checkoutDiscount = null;
  });
  document.getElementById("tipsEnabled")?.addEventListener("change", (event) => {
    state.creatorTools.tipsEnabled = event.target.checked;
  });
  document.querySelectorAll("[data-privacy-toggle]").forEach((field) => field.addEventListener("change", (event) => {
    state.preferences[event.target.dataset.privacyToggle] = event.target.checked;
    persistProductState("preferences", state.preferences);
    render();
  }));
  document.querySelectorAll("[data-cookie]").forEach((field) => field.addEventListener("change", (event) => {
    state.preferences.cookies[event.target.dataset.cookie] = event.target.checked;
    persistProductState("preferences", state.preferences);
    render();
  }));
  document.querySelector("[data-locale]")?.addEventListener("change", (event) => {
    state.preferences.locale = event.target.value;
    persistProductState("preferences", state.preferences);
    render();
  });
  const commentDraft = document.getElementById("commentDraft");
  commentDraft?.addEventListener("input", (event) => {
    state.commentDraft = event.target.value;
  });
  document.getElementById("commentSort")?.addEventListener("change", (event) => {
    state.commentSort = event.target.value;
    render();
  });
  const userSearchInput = document.getElementById("userSearch");
  if (userSearchInput) {
    userSearchInput.addEventListener("input", (event) => {
      state.userSearch = event.target.value;
      window.clearTimeout(state.userSearchTimer);
      state.userSearchTimer = window.setTimeout(() => loadAdminUsers(), 220);
    });
  }

  document.getElementById("draftTitle")?.addEventListener("input", (event) => {
    state.draft.title = event.target.value;
  });
  document.getElementById("draftSubtitle")?.addEventListener("input", (event) => {
    state.draft.subtitle = event.target.value;
  });
  document.getElementById("draftBody")?.addEventListener("input", (event) => {
    state.draft.body = event.target.value;
  });
  document.getElementById("draftTopic")?.addEventListener("change", (event) => {
    state.draft.topic = event.target.value;
  });
  const planBindings = [
    ["planName", "name"],
    ["planPrice", "price"],
    ["planPeriod", "period"],
    ["planNote", "note"],
    ["planFeatures", "features"],
  ];
  planBindings.forEach(([id, key]) => {
    document.getElementById(id)?.addEventListener("input", (event) => {
      state.planForm[key] = key === "price" ? Number(event.target.value) : event.target.value;
    });
    document.getElementById(id)?.addEventListener("change", (event) => {
      state.planForm[key] = key === "price" ? Number(event.target.value) : event.target.value;
    });
  });
  const categoryBindings = [
    ["categoryName", "name"],
    ["categorySlug", "slug"],
    ["categoryDescription", "description"],
    ["categoryColor", "color"],
    ["categorySeoTitle", "seoTitle"],
    ["categoryMetaDescription", "metaDescription"],
  ];
  categoryBindings.forEach(([id, key]) => {
    const field = document.getElementById(id);
    const update = (event) => {
      state.categoryForm[key] = event.target.value;
    };
    field?.addEventListener("input", update);
    field?.addEventListener("change", update);
  });
  const blogBindings = [
    ["blogTitle", "title"],
    ["blogSlug", "slug"],
    ["blogDek", "dek"],
    ["blogAuthor", "author"],
    ["blogRole", "role"],
    ["blogPublication", "publication"],
    ["blogTags", "tagsText"],
    ["blogTopic", "topic"],
    ["blogReadTime", "readTime"],
    ["blogColor", "color"],
    ["blogImageUrl", "imageUrl"],
  ];
  blogBindings.forEach(([id, key]) => {
    const field = document.getElementById(id);
    const update = (event) => {
      state.blogForm[key] = event.target.value;
    };
    field?.addEventListener("input", update);
    field?.addEventListener("change", update);
  });
  document.getElementById("blogPremium")?.addEventListener("change", (event) => {
    state.blogForm.premium = event.target.checked;
  });
  document.querySelectorAll("[data-interactive-field]").forEach((field) => {
    const update = (event) => {
      const [indexText, key] = event.target.dataset.interactiveField.split(":");
      const block = state.blogForm.interactiveBlocks[Number(indexText)];
      if (!block) return;
      if (key === "options") block.options = event.target.value.split("\n").map((option) => option.trim()).filter(Boolean);
      else if (key === "correctIndex") block.correctIndex = Number(event.target.value);
      else if (key === "multiple") block.multiple = event.target.checked;
      else block[key] = event.target.value;
    };
    field.addEventListener("input", update);
    field.addEventListener("change", update);
  });
  const richEditor = document.getElementById("richBlogEditor");
  richEditor?.addEventListener("input", (event) => {
    state.blogForm.contentHtml = event.currentTarget.innerHTML;
  });
  document.getElementById("editorBlockFormat")?.addEventListener("change", (event) => {
    richEditor?.focus();
    document.execCommand("formatBlock", false, event.target.value);
    state.blogForm.contentHtml = richEditor?.innerHTML || "";
  });
  document.querySelectorAll("[data-editor-command]").forEach((button) => {
    button.addEventListener("click", () => {
      richEditor?.focus();
      document.execCommand(button.dataset.editorCommand, false, button.dataset.editorValue || null);
      state.blogForm.contentHtml = richEditor?.innerHTML || "";
    });
  });
  document.querySelectorAll("[data-editor-insert]").forEach((button) => {
    button.addEventListener("click", () => {
      richEditor?.focus();
      const type = button.dataset.editorInsert;
      if (type === "link") {
        const url = window.prompt("Enter the link URL");
        if (url && /^(https?:|mailto:|\/|#)/i.test(url)) document.execCommand("createLink", false, url);
      }
      if (type === "image") {
        const url = window.prompt("Enter the image URL");
        const safeUrl = safeImageUrl(url);
        if (safeUrl) document.execCommand("insertHTML", false, `<img src="${escapeHtml(safeUrl)}" alt="" loading="lazy" decoding="async" />`);
      }
      if (type === "table") {
        document.execCommand("insertHTML", false, "<table><thead><tr><th>Heading 1</th><th>Heading 2</th></tr></thead><tbody><tr><td>Cell</td><td>Cell</td></tr><tr><td>Cell</td><td>Cell</td></tr></tbody></table><p><br></p>");
      }
      state.blogForm.contentHtml = richEditor?.innerHTML || "";
    });
  });
  const seoBindings = [
    ["seoFocusKeyphrase", "focusKeyphrase"],
    ["seoAdditionalKeyphrases", "additionalKeyphrases"],
    ["seoTitle", "seoTitle"],
    ["seoMetaDescription", "metaDescription"],
    ["seoCanonicalUrl", "canonicalUrl"],
    ["seoMaxSnippet", "maxSnippet"],
    ["seoMaxImagePreview", "maxImagePreview"],
    ["seoMaxVideoPreview", "maxVideoPreview"],
    ["seoSchemaPageType", "schemaPageType"],
    ["seoSchemaArticleType", "schemaArticleType"],
    ["seoBreadcrumbTitle", "breadcrumbTitle"],
    ["seoSocialTitle", "socialTitle"],
    ["seoSocialDescription", "socialDescription"],
    ["seoSocialImage", "socialImage"],
  ];
  seoBindings.forEach(([id, key]) => {
    const field = document.getElementById(id);
    const update = (event) => {
      state.blogForm.seo[key] = ["maxSnippet", "maxVideoPreview"].includes(key) ? Number(event.target.value) : event.target.value;
    };
    field?.addEventListener("input", update);
    field?.addEventListener("change", update);
  });
  [
    ["seoRobotsIndex", "robotsIndex"],
    ["seoRobotsFollow", "robotsFollow"],
    ["seoCornerstone", "cornerstone"],
  ].forEach(([id, key]) => {
    document.getElementById(id)?.addEventListener("change", (event) => {
      state.blogForm.seo[key] = event.target.checked;
    });
  });
  document.querySelectorAll("[data-site-seo]").forEach((field) => {
    const update = (event) => {
      state.siteSeo[event.target.dataset.siteSeo] = event.target.type === "checkbox" ? event.target.checked : event.target.value;
      state.siteSeoMessage = "";
    };
    field.addEventListener("input", update);
    field.addEventListener("change", update);
  });
  document.querySelectorAll("[data-currency-select]").forEach((select) => {
    select.addEventListener("change", (event) => {
      state.currency = event.target.value;
      localStorage.setItem("inkriver-currency", state.currency);
      if (!state.rates[state.currency] && state.rateStatus !== "Updating live rates...") {
        fetchCurrencyRates();
      } else {
        render();
      }
    });
  });
  document.querySelectorAll("[data-setting]").forEach((field) => {
    const update = (event) => {
      state.gatewaySettings[event.target.dataset.setting] = event.target.value;
      state.gatewaySettingsSaved = false;
    };
    field.addEventListener("input", update);
    field.addEventListener("change", update);
  });
  document.querySelectorAll("[data-user-role]").forEach((select) => {
    select.addEventListener("change", (event) => {
      updateUser(event.target.dataset.userRole, { role: event.target.value });
    });
  });
  document.querySelectorAll("[data-user-subscription]").forEach((select) => {
    select.addEventListener("change", (event) => {
      updateUser(event.target.dataset.userSubscription, { subscription: event.target.value });
    });
  });
  document.querySelectorAll("[data-payout-status]").forEach((select) => {
    select.addEventListener("change", async (event) => {
      try {
        await apiRequest(`/api/admin/payouts/${encodeURIComponent(event.target.dataset.payoutStatus)}`, {
          method: "PATCH",
          body: JSON.stringify({ status: event.target.value }),
        });
        await loadAdminCommerceData();
        state.userMessage = "Payout status updated.";
      } catch (error) {
        state.userMessage = error.message;
      }
      render();
    });
  });
}

document.addEventListener("click", async (event) => {
  const shareSignalTarget = event.target.closest("[data-share-signal]");
  if (shareSignalTarget) {
    const story = state.stories.find((item) => item.slug === shareSignalTarget.dataset.shareSignal);
    if (story) recordRecommendationActivity(story, "share");
  }

  const target = event.target.closest("button");
  if (!target) return;

  const route = target.dataset.route;
  const topic = target.dataset.topic;
  const save = target.dataset.save;
  const notInterested = target.dataset.notInterested;
  const interestTopic = target.dataset.interestTopic;
  const onboardingInterest = target.dataset.onboardingInterest;
  const followType = target.dataset.followType;
  const followValue = target.dataset.followValue;
  const resumeStory = target.dataset.resumeStory;
  const historyDelete = target.dataset.historyDelete;
  const interactiveStory = target.dataset.interactiveStory;
  const interactiveId = target.dataset.interactiveId;
  const interactiveOption = target.dataset.interactiveOption;
  const submitInteractive = target.dataset.submitInteractive;
  const resetInteractive = target.dataset.resetInteractive;
  const commentSubmit = target.dataset.commentSubmit;
  const commentReply = target.dataset.commentReply;
  const commentEdit = target.dataset.commentEdit;
  const commentLike = target.dataset.commentLike;
  const commentReport = target.dataset.commentReport;
  const commentPin = target.dataset.commentPin;
  const commentDelete = target.dataset.commentDelete;
  const addInteractive = target.dataset.addInteractive;
  const removeInteractive = target.dataset.removeInteractive;
  const copyLink = target.dataset.copyLink;
  const nativeShare = target.dataset.nativeShare;
  const clap = target.dataset.clap;
  const checkout = target.dataset.checkout;
  const paymentSubmit = target.dataset.paymentSubmit;
  const gateway = target.dataset.gateway;
  const socialProvider = target.dataset.socialLogin;
  const socialDisconnect = target.dataset.socialDisconnect;
  const booleanSetting = target.dataset.booleanSetting;
  const editPlan = target.dataset.editPlan;
  const deletePlanId = target.dataset.deletePlan;
  const editBlog = target.dataset.editBlog;
  const deleteBlogId = target.dataset.deleteBlog;
  const blogStatus = target.dataset.blogStatus;
  const nextStatus = target.dataset.nextStatus;
  const editCategory = target.dataset.editCategory;
  const deleteCategoryId = target.dataset.deleteCategory;
  const toggleUserStatus = target.dataset.toggleUserStatus;
  const deleteUserId = target.dataset.deleteUser;
  const toggle = target.dataset.toggle;
  const resolve = target.dataset.resolve;
  const action = target.dataset.action;
  const creatorTab = target.dataset.creatorTab;
  const calendarView = target.dataset.calendarView;
  const advanceCalendar = target.dataset.advanceCalendar;
  const opsFilter = target.dataset.opsFilter;
  const resolveMod = target.dataset.resolveMod;
  const aiPanel = target.dataset.aiPanel;
  const aiTool = target.dataset.aiTool;
  const smartValue = target.dataset.smartValue;
  const recommendationPanel = target.dataset.recommendationPanel;
  const actionStory = target.dataset.actionStory;
  const storySlug = target.dataset.storySlug;
  const readerMode = target.dataset.readerMode;
  const readerFont = target.dataset.readerFont;
  const readerScale = target.dataset.readerScale;
  const tipAmount = target.dataset.tipAmount;
  const tipStory = target.dataset.tipStory;
  const togglePromo = target.dataset.togglePromo;
  const toggleAd = target.dataset.toggleAd;
  const toggleDiscount = target.dataset.toggleDiscount;
  const verifyPayoutAccount = target.dataset.verifyPayoutAccount;
  const removeTranslationLanguage = target.dataset.removeTranslationLanguage;
  const addRule = target.dataset.addRule;
  const previewSegment = target.dataset.previewSegment;
  const useSegment = target.dataset.useSegment;
  const uploadEvidence = target.dataset.uploadEvidence;
  const authMode = target.dataset.authMode;
  const dashboardSection = target.dataset.dashboardSection;
  const sessionRevoke = target.dataset.sessionRevoke;
  const adClick = target.dataset.adClick;
  const adUrl = target.dataset.adUrl;

  if (dashboardSection) {
    const allowed = new Set(dashboardNavItems(state.user?.role || "reader").map((item) => item[2]));
    state.dashboardSection = allowed.has(dashboardSection) ? dashboardSection : "overview";
    render();
  }

  if (adClick) {
    recordAdEvent(adClick, "click", "", state.path.startsWith("/stories/") ? state.path.split("/").pop() : "");
    if (adUrl && adUrl !== "#") window.open(adUrl, "_blank", "noopener,noreferrer");
  }

  if (authMode) {
    state.authMode = authMode;
    state.loginMessage = "";
      state.authForm.password = "";
      state.authForm.twoFactorCode = "";
    render();
  }

  if (creatorTab) {
    state.creatorTools.activeTab = creatorTab;
    persistProductState("creator-tools", state.creatorTools);
    render();
  }
  if (calendarView) {
    state.creatorTools.calendarView = calendarView;
    persistProductState("creator-tools", state.creatorTools);
    render();
  }
  if (advanceCalendar) {
    const order = ["Draft", "Review", "Approved", "Scheduled"];
    state.creatorTools.calendarItems = state.creatorTools.calendarItems.map((item) => item.id === advanceCalendar ? { ...item, status: order[Math.min(order.length - 1, order.indexOf(item.status) + 1)] } : item);
    persistProductState("creator-tools", state.creatorTools);
    render();
  }
  if (addRule) {
    const criterion = window.prompt(`Enter the ${addRule.toLowerCase()} condition for this segment:`);
    if (criterion?.trim()) {
      state.creatorTools.segments.push({
        id: `seg-${Date.now()}`,
        name: `${addRule}: ${criterion.trim()}`,
        rules: `${addRule} is ${criterion.trim()}`,
        size: 0,
        channel: "Draft",
      });
      state.calendarMessage = "Audience rule saved. The segment starts at zero until matching reader profiles are recorded.";
      persistProductState("creator-tools", state.creatorTools);
      render();
    }
  }
  if (previewSegment) {
    const segment = state.creatorTools.segments.find((item) => item.id === previewSegment);
    if (segment) {
      try {
        const payload = await apiRequest("/api/admin/segments/preview", {
          method: "POST",
          body: JSON.stringify({ rules: segment.rules }),
        });
        segment.size = Number(payload.count || 0);
        state.calendarMessage = `${segment.name}: ${segment.size.toLocaleString("en-IN")} matched active readers from stored profiles and activity.`;
        persistProductState("creator-tools", state.creatorTools);
      } catch (error) {
        state.calendarMessage = error.message;
      }
      render();
    }
  }
  if (useSegment) {
    const segment = state.creatorTools.segments.find((item) => item.id === useSegment);
    if (segment) {
      segment.channel = "Active";
      state.calendarMessage = `${segment.name} is now available to campaigns and newsletter targeting.`;
      persistProductState("creator-tools", state.creatorTools);
      render();
    }
  }
  if (uploadEvidence) {
    const reference = window.prompt("Evidence URL, content hash, license reference, or storage location:");
    if (reference?.trim()) {
      try {
        await apiRequest(`/api/admin/moderation/${encodeURIComponent(uploadEvidence)}`, {
          method: "PATCH",
          body: JSON.stringify({ evidence: { type: "reference", value: reference.trim() } }),
        });
        await loadAdminOperationalData();
        state.userMessage = "Evidence reference attached to the case.";
      } catch (error) {
        state.userMessage = error.message;
      }
      render();
    }
  }
  if (opsFilter) {
    state.opsFilter = opsFilter;
    render();
  }
  if (resolveMod) {
    try {
      await apiRequest(`/api/admin/moderation/${encodeURIComponent(resolveMod)}`, {
        method: "PATCH",
        body: JSON.stringify({ status: "Resolved", assignToSelf: true }),
      });
      await loadAdminOperationalData();
    } catch (error) {
      state.userMessage = error.message;
    }
    render();
  }
  if (aiPanel) {
    state.aiPanel = aiPanel;
    state.aiResult = "";
    render();
  }
  if (aiTool) {
    await runAiTool(aiTool);
  }
  if (smartValue) {
    const [type, value] = smartValue.split(":");
    if (type === "topic") state.blogForm.topic = value;
    if (type === "tag") state.blogForm.tagsText = [...new Set([...state.blogForm.tagsText.split(",").map((tag) => tag.trim()).filter(Boolean), value])].join(", ");
    if (type === "link") state.aiResult = `Suggested internal link: ${value}. Add it where the draft introduces a closely related concept.`;
    render();
  }
  if (recommendationPanel) {
    state.recommendationPanel = state.recommendationPanel === recommendationPanel ? "" : recommendationPanel;
    render();
  }
  if (actionStory && storySlug) {
    const story = state.stories.find((item) => item.slug === storySlug);
    if (story && actionStory === "more") recordRecommendationActivity(story, "more_like_this");
    if (story && actionStory === "less") recordRecommendationActivity(story, "less_like_this");
    if (story && actionStory === "hide-topic") {
      updateInterestPreference(story.topic, false);
      state.recommendation.hiddenStories = [...new Set([...state.recommendation.hiddenStories, ...state.stories.filter((item) => item.topic === story.topic).map((item) => item.slug)])];
      persistRecommendationProfile();
      if (state.user) sendRecommendationFeedback("hide_topic", story, { topic: story.topic });
    }
    if (story && actionStory === "unfollow-author" && isFollowing("writers", story.author)) toggleFollow("writers", story.author);
    state.recommendationPanel = "";
    render();
  }
  if (readerMode === "focus") {
    state.preferences.focusMode = !state.preferences.focusMode;
    persistProductState("preferences", state.preferences);
    render();
  }
  if (readerFont) {
    state.preferences.fontFamily = readerFont;
    persistProductState("preferences", state.preferences);
    render();
  }
  if (readerScale) {
    state.preferences.textScale = Math.max(80, Math.min(140, state.preferences.textScale + Number(readerScale)));
    persistProductState("preferences", state.preferences);
    render();
  }
  if (tipAmount) {
    state.tipAmount = Number(tipAmount);
    render();
  }
  if (tipStory) {
    const story = state.stories.find((item) => item.slug === tipStory);
    state.tipStory = tipStory;
    if (story) await startTipPayment(story);
  }
  if (togglePromo) {
    state.creatorTools.promotions = state.creatorTools.promotions.map((promo) => promo.id === togglePromo ? { ...promo, active: !promo.active } : promo);
    persistProductState("creator-tools", state.creatorTools);
    render();
  }
  if (sessionRevoke) {
    try {
      await apiRequest(`/api/me/sessions/${encodeURIComponent(sessionRevoke)}`, { method: "DELETE" });
      await loadSecuritySessions();
      state.securityMessage = "The selected session was revoked.";
    } catch (error) {
      state.securityMessage = error.message;
    }
    render();
  }

  if (route) {
    state.searchOpen = false;
    setRoute(route);
  }
  if (followType && followValue) {
    toggleFollow(followType, followValue);
    render();
  }
  if (resumeStory) {
    state.pendingResumeSlug = resumeStory;
    setRoute(`/stories/${resumeStory}`);
  }
  if (historyDelete) {
    removeHistoryEntry(historyDelete);
    render();
  }
  if (topic) {
    state.activeTopic = topic;
    render();
  }
  if (save) {
    const story = state.stories.find((item) => item.slug === save);
    const adding = !state.saved.has(save);
    adding ? state.saved.add(save) : state.saved.delete(save);
    persistSavedSlugs();
    if (adding) {
      navigator.serviceWorker?.controller?.postMessage({ type: "CACHE_ARTICLE", url: `/stories/${encodeURIComponent(save)}` });
    }
    if (story) recordRecommendationActivity(story, adding ? "save" : "unsave");
    render();
  }
  if (notInterested) {
    const story = state.stories.find((item) => item.slug === notInterested);
    if (story) {
      recordRecommendationActivity(story, "not_interested");
      state.recommendationMessage = `${story.title} will appear less often.`;
      if (state.path.includes(`/stories/${story.slug}`)) setRoute("/");
      else render();
    }
  }
  if (interestTopic) {
    const enabled = !state.recommendation.selectedInterests.includes(interestTopic);
    updateInterestPreference(interestTopic, enabled);
    state.recommendationMessage = enabled
      ? `${interestTopic} added. Your feed has been re-ranked.`
      : `${interestTopic} removed from your selected interests.`;
    render();
  }
  if (onboardingInterest) {
    state.onboardingSelection.has(onboardingInterest)
      ? state.onboardingSelection.delete(onboardingInterest)
      : state.onboardingSelection.add(onboardingInterest);
    state.onboardingMessage = "";
    render();
  }
  if (interactiveStory && interactiveId && interactiveOption !== undefined) {
    const key = `${interactiveStory}:${interactiveId}`;
    const optionIndex = Number(interactiveOption);
    const multiple = target.dataset.interactiveMultiple === "true";
    const current = state.pollResponses[key] || { selected: [], submitted: false };
    const selected = new Set(current.selected);
    if (multiple) selected.has(optionIndex) ? selected.delete(optionIndex) : selected.add(optionIndex);
    else {
      selected.clear();
      selected.add(optionIndex);
    }
    state.pollResponses[key] = { selected: [...selected], submitted: !multiple, answeredAt: new Date().toISOString() };
    persistPollResponses();
    render();
  }
  if (submitInteractive) {
    if (state.pollResponses[submitInteractive]?.selected?.length) {
      state.pollResponses[submitInteractive].submitted = true;
      persistPollResponses();
      render();
    }
  }
  if (resetInteractive) {
    delete state.pollResponses[resetInteractive];
    persistPollResponses();
    render();
  }
  if (commentSubmit) {
    await saveComment(commentSubmit);
    render();
  }
  if (commentReply) {
    state.commentReplyTo = commentReply;
    state.commentEditId = "";
    state.commentDraft = `@${(state.commentsByStory[state.path.split("/").pop()] || []).find((comment) => comment.id === commentReply)?.author || ""} `;
    render();
  }
  if (commentEdit) {
    const [storySlug, commentId] = commentEdit.split(":");
    const comment = (state.commentsByStory[storySlug] || []).find((item) => item.id === commentId);
    if (comment) {
      state.commentEditId = commentId;
      state.commentReplyTo = "";
      state.commentDraft = comment.text;
      render();
    }
  }
  if (commentLike || commentReport || commentPin || commentDelete) {
    const operation = commentLike ? "like" : commentReport ? "report" : commentPin ? "pin" : "delete";
    const value = commentLike || commentReport || commentPin || commentDelete;
    const [storySlug, commentId] = value.split(":");
    if (!state.user) {
      state.loginOpen = true;
      state.loginMessage = "Sign in to interact with comments.";
      render();
    } else {
      try {
        let payload;
        if (operation === "like") {
          payload = await apiRequest(`/api/comments/${encodeURIComponent(commentId)}/like`, { method: "POST", body: "{}" });
        } else if (operation === "delete") {
          payload = await apiRequest(`/api/comments/${encodeURIComponent(commentId)}`, { method: "DELETE" });
        } else {
          const current = (state.commentsByStory[storySlug] || []).find((comment) => comment.id === commentId);
          payload = await apiRequest(`/api/comments/${encodeURIComponent(commentId)}`, {
            method: "PATCH",
            body: JSON.stringify(operation === "report"
              ? { status: current?.reported ? "published" : "reported" }
              : { pinned: !current?.pinned }),
          });
        }
        state.commentsByStory[storySlug] = payload.comments || [];
      } catch (error) {
        state.userMessage = error.message;
      }
      render();
    }
  }
  if (addInteractive) {
    const options = addInteractive === "quiz" ? ["First answer", "Second answer", "Third answer"] : ["Option one", "Option two", "Option three"];
    state.blogForm.interactiveBlocks.push({
      id: `${addInteractive}-${Date.now()}`,
      type: addInteractive,
      question: addInteractive === "quiz" ? "What did readers learn from this article?" : "Ask your readers a question",
      options,
      multiple: addInteractive === "survey",
      correctIndex: 0,
      explanation: addInteractive === "quiz" ? "Explain why the answer is correct." : "",
    });
    render();
  }
  if (removeInteractive !== undefined) {
    state.blogForm.interactiveBlocks.splice(Number(removeInteractive), 1);
    render();
  }
  if (copyLink) {
    navigator.clipboard?.writeText(target.dataset.copyUrl);
    state.copiedShare = copyLink;
    const story = state.stories.find((item) => item.slug === copyLink);
    if (story) recordRecommendationActivity(story, "share");
    render();
  }
  if (nativeShare) {
    if (navigator.share) {
      navigator.share({
        title: target.dataset.shareTitle,
        text: target.dataset.shareText,
        url: target.dataset.shareUrl,
      }).catch(() => {});
    } else {
      navigator.clipboard?.writeText(target.dataset.shareUrl);
      state.copiedShare = nativeShare;
    }
    const story = state.stories.find((item) => item.slug === nativeShare);
    if (story) recordRecommendationActivity(story, "share");
    render();
  }
  if (clap) {
    state.claps[clap] = (state.claps[clap] || 0) + 1;
    const story = state.stories.find((item) => item.slug === clap);
    if (story) recordRecommendationActivity(story, "clap");
    render();
  }
  if (checkout) {
    state.checkoutPlan = state.plans.find((plan) => plan.id === checkout);
    state.paymentMessage = "";
    render();
  }
  if (paymentSubmit) {
    const plan = state.plans.find((item) => item.id === paymentSubmit);
    startGatewayPayment(plan);
  }
  if (socialProvider) {
    socialLogin(socialProvider);
  }
  if (socialDisconnect) {
    try {
      await apiRequest(`/api/me/social-accounts/${encodeURIComponent(socialDisconnect)}`, { method: "DELETE" });
      state.socialAccounts = state.socialAccounts.filter((item) => item.provider !== socialDisconnect);
      state.privacyMessage = `${socialDisconnect === "google" ? "Google" : "Facebook"} disconnected.`;
    } catch (error) {
      state.privacyMessage = error.message;
    }
    render();
  }
  if (booleanSetting) {
    state.gatewaySettings[booleanSetting] = state.gatewaySettings[booleanSetting] === false;
    state.gatewaySettingsSaved = false;
    render();
  }
  if (editPlan) {
    const plan = state.plans.find((item) => item.id === editPlan);
    if (plan) populatePlanForm(plan);
  }
  if (deletePlanId) {
    deletePlan(deletePlanId);
  }
  if (editBlog) {
    const blog = state.stories.find((item) => item.id === editBlog);
    if (blog) populateBlogForm(blog);
  }
  if (deleteBlogId) {
    deleteBlog(deleteBlogId);
  }
  if (blogStatus && nextStatus) {
    setBlogStatus(blogStatus, nextStatus);
  }
  if (editCategory) {
    const category = state.categories.find((item) => item.id === editCategory);
    if (category) populateCategoryForm(category);
  }
  if (deleteCategoryId) {
    deleteCategory(deleteCategoryId);
  }
  if (toggleUserStatus) {
    const user = state.users.find((item) => item.id === toggleUserStatus);
    if (user) updateUser(toggleUserStatus, { status: user.status === "suspended" ? "active" : "suspended" });
  }
  if (deleteUserId) {
    deleteUser(deleteUserId);
  }
  if (gateway) {
    state.gateway = gateway;
    state.paymentMessage = "";
    render();
  }
  if (toggle) {
    state.draft[toggle] = !state.draft[toggle];
    render();
  }
  if (resolve !== undefined) {
    state.queue[Number(resolve)].status = "Resolved";
    render();
  }
  if (action === "toggle-menu") {
    state.mobileOpen = !state.mobileOpen;
    render();
  }
  if (action === "toggle-theme") {
    state.theme = state.theme === "day" ? "night" : "day";
    localStorage.setItem("inkriver-theme", state.theme);
    render();
  }
  if (action === "toggle-push") {
    await enablePushNotifications();
  }
  if (action === "save-writer-draft") {
    await saveWriterStory("draft");
  }
  if (action === "forgot-password") {
    await requestPasswordReset();
  }
  if (action === "rebuild-recommendations") {
    try {
      state.userMessage = "Rebuilding recommendation profiles...";
      render();
      const payload = await apiRequest("/api/admin/recommendations/rebuild", { method: "POST", body: "{}" });
      state.adminRecommendationStatus = await apiRequest("/api/admin/recommendations/status");
      state.userMessage = `Recommendation model rebuilt for ${payload.rebuilt || 0} users.`;
    } catch (error) {
      state.userMessage = error.message;
    }
    render();
  }
  if (action === "toggle-writer-preview") {
    state.draftPreview = !state.draftPreview;
    render();
  }
  if (action === "publish-writer-story") {
    await saveWriterStory("published");
  }
  if (action === "open-login" || action === "open-account") {
    state.loginOpen = true;
    render();
  }
  if (action === "close-login") {
    state.loginOpen = false;
    state.resetToken = "";
    state.resetPassword = "";
    if (new URLSearchParams(window.location.search).get("reset_token")) window.history.replaceState({}, "", "/");
    render();
  }
  if (action === "logout") {
    await logoutUser();
  }
  if (action === "complete-onboarding") {
    if (state.onboardingSelection.size < 3) {
      state.onboardingMessage = "Choose at least three interests to continue.";
      render();
    } else {
      const previous = new Set(state.recommendation.selectedInterests);
      [...state.onboardingSelection].forEach((topicName) => {
        if (!previous.has(topicName)) updateInterestPreference(topicName, true, "onboarding");
      });
      [...previous].forEach((topicName) => {
        if (!state.onboardingSelection.has(topicName)) updateInterestPreference(topicName, false, "onboarding");
      });
      state.recommendation.completedOnboarding = true;
      state.onboardingOpen = false;
      state.onboardingMessage = "";
      state.recommendationMessage = "Your feed is ready and will keep learning from your reading activity.";
      persistRecommendationProfile();
      setRoute("/");
    }
  }
  if (action === "reset-recommendations") {
    resetRecommendationProfile();
    render();
  }
  if (action === "apply-ai-result") {
    if (state.aiPanel === "metadata" || state.aiResult.startsWith("SEO title:")) {
      state.blogForm.seo.seoTitle = (state.blogForm.title || "Editorial strategy").slice(0, 58);
      state.blogForm.seo.metaDescription = (state.blogForm.dek || "A practical editorial guide for modern publishing teams.").slice(0, 155);
    } else if (state.aiResult.startsWith("Headline option:")) {
      state.blogForm.title = state.aiResult.replace("Headline option: ", "");
    } else if (state.aiResult.startsWith("Summary:")) {
      state.blogForm.dek = state.aiResult.replace("Summary: ", "");
    } else {
      state.blogForm.contentHtml = `${state.blogForm.contentHtml}<p>${escapeHtml(state.aiResult)}</p>`;
    }
    state.aiResult = "";
    state.blogMessage = "AI suggestion applied. You can continue editing or undo it manually.";
    render();
  }
  if (action === "dismiss-ai-result") {
    state.aiResult = "";
    render();
  }
  if (action === "add-calendar-item") {
    state.creatorTools.calendarItems.push({ id: `cal-${Date.now()}`, title: "Untitled scheduled story", type: "Post", status: "Draft", date: "2026-06-22", owner: state.user?.name || "Administrator", channel: "Web" });
    state.creatorTools.activeTab = "calendar";
    state.calendarMessage = "New draft added to 22 June. Open Pipeline view to advance it.";
    persistProductState("creator-tools", state.creatorTools);
    render();
  }
  if (action === "create-segment") {
    const name = window.prompt("Segment name:");
    const rules = name?.trim() ? window.prompt("Describe the audience rule:") : "";
    if (name?.trim() && rules?.trim()) {
      state.creatorTools.segments.push({ id: `seg-${Date.now()}`, name: name.trim(), rules: rules.trim(), size: 0, channel: "Draft" });
      state.calendarMessage = "Segment saved. Its count will grow as matching reader data is collected.";
      persistProductState("creator-tools", state.creatorTools);
      render();
    }
  }
  if (action === "create-promotion") {
    state.creatorTools.promotions.push({ id: `LAUNCH${state.creatorTools.promotions.length + 1}`, kind: "Campaign", value: "15% off", audience: "Selected segment", uses: 0, active: true });
    persistProductState("creator-tools", state.creatorTools);
    render();
  }
  if (action === "refresh-commerce") {
    await loadAdminCommerceData();
    state.userMessage = "Commerce data refreshed.";
    render();
  }
  if (action === "create-ad-campaign") {
    const name = window.prompt("Campaign name:");
    const sponsor = name?.trim() ? window.prompt("Sponsor name:") : "";
    const headline = sponsor?.trim() ? window.prompt("Ad headline:") : "";
    const targetUrl = headline?.trim() ? window.prompt("Target URL:", "https://") : "";
    const placement = targetUrl?.trim() ? window.prompt("Placement: leaderboard, square, or native", "leaderboard") : "";
    if (name?.trim() && sponsor?.trim() && headline?.trim() && targetUrl?.trim()) {
      try {
        await apiRequest("/api/admin/ads", {
          method: "POST",
          body: JSON.stringify({ name, sponsor, headline, targetUrl, placement }),
        });
        await loadAdminCommerceData();
        state.userMessage = "Ad campaign created and connected to live placements.";
      } catch (error) {
        state.userMessage = error.message;
      }
      render();
    }
  }
  if (toggleAd) {
    const campaign = state.adCampaigns.find((item) => item.id === toggleAd);
    if (campaign) {
      try {
        await apiRequest(`/api/admin/ads/${encodeURIComponent(campaign.id)}`, {
          method: "PATCH",
          body: JSON.stringify({ status: campaign.status === "active" ? "paused" : "active" }),
        });
        await loadAdminCommerceData();
        state.userMessage = "Ad campaign status updated.";
      } catch (error) {
        state.userMessage = error.message;
      }
      render();
    }
  }
  if (action === "create-discount") {
    const code = window.prompt("Discount code:");
    const discountType = code?.trim() ? window.prompt("Type percent or amount:", "percent") : "";
    const discountValue = discountType ? Number(window.prompt(discountType === "amount" ? "Amount in paise:" : "Percentage discount:", "10")) : 0;
    const audience = discountValue ? window.prompt("Audience label:", "All readers") : "";
    if (code?.trim() && discountValue > 0) {
      try {
        await apiRequest("/api/admin/discounts", {
          method: "POST",
          body: JSON.stringify({ code, discountType, discountValue, audience }),
        });
        await loadAdminCommerceData();
        state.userMessage = "Discount code created and available during checkout.";
      } catch (error) {
        state.userMessage = error.message;
      }
      render();
    }
  }
  if (toggleDiscount) {
    const discount = state.discounts.find((item) => item.id === toggleDiscount);
    if (discount) {
      try {
        await apiRequest(`/api/admin/discounts/${encodeURIComponent(discount.id)}`, {
          method: "PATCH",
          body: JSON.stringify({ active: !discount.active }),
        });
        await loadAdminCommerceData();
        state.userMessage = "Discount status updated.";
      } catch (error) {
        state.userMessage = error.message;
      }
      render();
    }
  }
  if (action === "setup-payout-account") {
    const accountHolder = window.prompt("Account holder name:", state.payoutAccount?.account_holder || state.user?.name || "");
    const bankName = accountHolder?.trim() ? window.prompt("Bank or payout provider:", state.payoutAccount?.bank_name || "") : "";
    const payoutMethod = bankName?.trim() ? window.prompt("Payout method: bank, upi, paypal, or manual", state.payoutAccount?.payout_method || "bank") : "";
    const accountReference = payoutMethod?.trim() ? window.prompt("Account, UPI, or payout reference:") : "";
    const taxIdReference = accountReference?.trim() ? window.prompt("Tax reference, PAN, GST, or declaration ID:") : "";
    if (accountHolder?.trim() && bankName?.trim() && payoutMethod?.trim() && accountReference?.trim()) {
      try {
        await apiRequest("/api/me/payout-account", {
          method: "PUT",
          body: JSON.stringify({ accountHolder, bankName, payoutMethod, accountReference, taxIdReference }),
        });
        await loadWriterPayouts();
        state.userMessage = "Payout account saved for admin review.";
      } catch (error) {
        state.userMessage = error.message;
      }
      render();
    }
  }
  if (verifyPayoutAccount) {
    try {
      await apiRequest(`/api/admin/payout-accounts/${encodeURIComponent(verifyPayoutAccount)}`, {
        method: "PATCH",
        body: JSON.stringify({ status: "verified" }),
      });
      await loadAdminCommerceData();
      state.userMessage = "Payout account verified.";
    } catch (error) {
      state.userMessage = error.message;
    }
    render();
  }
  if (action === "create-payout") {
    const writerUserId = window.prompt("Writer user ID:");
    if (writerUserId?.trim()) {
      try {
        await apiRequest("/api/admin/payouts", {
          method: "POST",
          body: JSON.stringify({ writerUserId: writerUserId.trim() }),
        });
        await loadAdminCommerceData();
        state.userMessage = "Payout batch created from unpaid writer tips.";
      } catch (error) {
        state.userMessage = error.message;
      }
      render();
    }
  }
  if (action === "run-translation-backfill") {
    try {
      const payload = await apiRequest("/api/admin/translations/run", { method: "POST", body: "{}" });
      state.userMessage = `${payload.queued || 0} published articles queued for translation. Refresh after processing completes.`;
    } catch (error) {
      state.userMessage = error.message;
    }
    render();
  }
  if (action === "add-translation-language") {
    const language = window.prompt("Language name, for example French:");
    const locale = language?.trim() ? window.prompt("Locale code, for example fr-FR:") : "";
    const cleanLocale = String(locale || "").trim();
    if (language?.trim() && /^[a-z]{2,3}(-[A-Z]{2})?$/.test(cleanLocale) && cleanLocale !== "en-IN") {
      const exists = state.translationLanguages.some((item) => item.locale === cleanLocale);
      if (!exists) state.translationLanguages.push({ locale: cleanLocale, language: language.trim() });
      persistGatewaySettings();
      state.gatewaySettingsSaved = true;
      state.userMessage = exists ? "That language is already configured." : "Translation language added. Run backfill to translate existing articles.";
    } else if (language) {
      state.userMessage = "Use a valid locale such as fr-FR, ta-IN, or de-DE.";
    }
    render();
  }
  if (removeTranslationLanguage) {
    state.translationLanguages = state.translationLanguages.filter((item) => item.locale !== removeTranslationLanguage);
    if (state.preferences.locale === removeTranslationLanguage) state.preferences.locale = "en-IN";
    persistGatewaySettings();
    persistProductState("preferences", state.preferences);
    state.gatewaySettingsSaved = true;
    state.userMessage = "Translation language removed from future reader options.";
    render();
  }
  if (action === "request-email-verification") {
    try {
      const payload = await apiRequest("/api/me/security/email-verification", { method: "POST", body: "{}" });
      state.securityMessage = payload.verificationUrl
        ? `Verification link created: ${payload.verificationUrl}`
        : "Verification email sent.";
      await loadSecuritySettings();
    } catch (error) {
      state.securityMessage = error.message;
    }
    render();
  }
  if (action === "setup-totp") {
    try {
      const payload = await apiRequest("/api/me/security/totp/setup", { method: "POST", body: "{}" });
      const code = window.prompt(`Add this secret to your authenticator app, then enter the 6-digit code:\n${payload.secret}`);
      if (code?.trim()) {
        const enabled = await apiRequest("/api/me/security/totp/enable", {
          method: "POST",
          body: JSON.stringify({ code: code.trim() }),
        });
        state.securityMessage = `Two-factor authentication enabled. Recovery codes: ${(enabled.recoveryCodes || []).join(", ")}`;
        await loadSecuritySettings();
      } else {
        state.securityMessage = `Authenticator secret generated: ${payload.secret}`;
      }
    } catch (error) {
      state.securityMessage = error.message;
    }
    render();
  }
  if (action === "disable-totp") {
    if (window.confirm("Disable two-factor authentication for this account?")) {
      try {
        await apiRequest("/api/me/security/totp", { method: "DELETE" });
        await loadSecuritySettings();
        state.securityMessage = "Two-factor authentication disabled.";
      } catch (error) {
        state.securityMessage = error.message;
      }
      render();
    }
  }
  if (action === "gift-preview") {
    if (!state.user) {
      state.loginOpen = true;
      state.loginMessage = "Sign in before purchasing a gift membership.";
      render();
    } else {
      const recipientEmail = window.prompt("Recipient email address:");
      const months = recipientEmail?.includes("@") ? Number(window.prompt("Gift duration in months (1, 3, 6, or 12):", "3")) : 0;
      const validMonths = [1, 3, 6, 12].includes(months);
      const message = validMonths ? window.prompt("Personal message (optional):", "") || "" : "";
      const deliverAt = validMonths ? window.prompt("Delivery date (YYYY-MM-DD):", new Date().toISOString().slice(0, 10)) : "";
      if (recipientEmail?.includes("@") && validMonths && /^\d{4}-\d{2}-\d{2}$/.test(deliverAt || "")) {
        const monthlyPlan = state.plans.find((plan) => plan.period === "month") || state.plans[0];
        await startGatewayPayment(
          { ...monthlyPlan, id: `gift-${monthlyPlan.id}-${months}`, name: `${months}-month gift`, price: monthlyPlan.price * months, period: "month" },
          {
            purpose: `${months}-month gift membership`,
            metadata: { kind: "gift", recipientEmail: recipientEmail.trim().toLowerCase(), months, message, deliverAt: new Date(`${deliverAt}T09:00:00`).toISOString(), planId: monthlyPlan.id },
          },
        );
      } else if (recipientEmail) {
        state.calendarMessage = "Enter a valid recipient, supported duration, and delivery date.";
        render();
      }
    }
  }
  if (action === "create-copyright-case") {
    const subject = window.prompt("Describe the work and the copyright concern:");
    const targetId = subject?.trim() ? window.prompt("Article slug, URL, or content identifier:") : "";
    if (subject?.trim() && targetId?.trim()) {
      try {
        await apiRequest("/api/admin/moderation", {
          method: "POST",
          body: JSON.stringify({ subject: subject.trim(), targetId: targetId.trim(), targetType: "post", kind: "Copyright", risk: "Medium" }),
        });
        await loadAdminOperationalData();
        state.userMessage = "Copyright case created and assigned.";
      } catch (error) {
        state.userMessage = error.message;
      }
      render();
    }
  }
  if (action === "save-tip-settings") {
    persistProductState("creator-tools", state.creatorTools);
    state.calendarMessage = "Tip settings and payout commission saved.";
    render();
  }
  if (action === "export-analytics") {
    const headers = ["story", "slug", "event_type", "count", "average_value"];
    const storyTitle = Object.fromEntries(state.stories.map((story) => [story.slug, story.title]));
    const rows = state.adminAnalytics.storyCounts.map((item) => [
      storyTitle[item.story_slug] || item.story_slug,
      item.story_slug,
      item.event_type,
      item.count,
      item.average_value,
    ]);
    const escapeCsv = (value) => `"${String(value ?? "").replaceAll('"', '""')}"`;
    const csv = [headers, ...rows].map((row) => row.map(escapeCsv).join(",")).join("\r\n");
    const link = document.createElement("a");
    link.href = URL.createObjectURL(new Blob([csv], { type: "text/csv;charset=utf-8" }));
    link.download = `inkriver-analytics-${new Date().toISOString().slice(0, 10)}.csv`;
    link.click();
    URL.revokeObjectURL(link.href);
    state.calendarMessage = "Analytics CSV exported from stored engagement records.";
    render();
  }
  if (action === "download-data") {
    try {
      const exportData = await apiRequest("/api/me/export");
      const link = document.createElement("a");
      link.href = URL.createObjectURL(new Blob([JSON.stringify(exportData, null, 2)], { type: "application/json" }));
      link.download = "inkriver-data-export.json";
      link.click();
      URL.revokeObjectURL(link.href);
      state.privacyMessage = "Your complete server data export has been prepared.";
    } catch (error) {
      state.privacyMessage = error.message;
    }
    render();
  }
  if (action === "request-account-deletion") {
    try {
      const payload = await apiRequest("/api/me/deletion-request", { method: "POST", body: "{}" });
      state.privacyMessage = `Deletion requested. Scheduled for ${new Date(payload.scheduledFor).toLocaleDateString()} after identity verification.`;
    } catch (error) {
      state.privacyMessage = error.message;
    }
    render();
  }
  if (action === "enable-passkey") {
    state.securityMessage = "Passkeys remain disabled until WebAuthn relying-party settings and verified email delivery are configured.";
    render();
  }
  if (action === "signout-other-sessions") {
    try {
      const payload = await apiRequest("/api/me/sessions/others", { method: "DELETE" });
      await loadSecuritySessions();
      state.securityMessage = `${payload.revoked} other session${payload.revoked === 1 ? "" : "s"} revoked.`;
    } catch (error) {
      state.securityMessage = error.message;
    }
    render();
  }
  if (action === "new-ticket") {
    if (!state.user) {
      state.loginOpen = true;
      state.loginMessage = "Sign in before creating a support request.";
    } else {
      try {
        await apiRequest("/api/support/tickets", {
          method: "POST",
          body: JSON.stringify({ subject: "New support request", category: "Account", priority: "Normal" }),
        });
        await loadSupportTickets();
        state.supportMessage = "Support ticket created and saved.";
      } catch (error) {
        state.supportMessage = error.message;
      }
    }
    render();
  }
  if (action === "toggle-speech") {
    const story = state.stories.find((item) => state.path.includes(`/stories/${item.slug}`));
    if (window.speechSynthesis?.speaking) {
      window.speechSynthesis.cancel();
      state.speechActive = false;
    } else if (story && "speechSynthesis" in window) {
      const utterance = new SpeechSynthesisUtterance(`${story.title}. ${story.dek}. ${story.body.join(" ")}`);
      utterance.rate = state.preferences.speechRate;
      utterance.onend = () => { state.speechActive = false; render(); };
      window.speechSynthesis.speak(utterance);
      state.speechActive = true;
    }
    render();
  }
  if (action === "clear-history") {
    state.readingHistory = [];
    persistReadingHistory();
    render();
  }
  if (action === "clear-search-filters") {
    state.searchFilters = { topic: "all", author: "all", access: "all", maxMinutes: "all", sort: "relevance" };
    render();
  }
  if (action === "cancel-comment") {
    state.commentDraft = "";
    state.commentReplyTo = "";
    state.commentEditId = "";
    render();
  }
  if (action === "refresh-rates") {
    fetchCurrencyRates();
  }
  if (action === "apply-discount") {
    if (!state.checkoutPlan) return;
    try {
      const amount = Math.round(convertedAmount(state.checkoutPlan.price) * 100);
      const payload = await apiRequest("/api/discounts/validate", {
        method: "POST",
        body: JSON.stringify({ code: state.checkoutDiscountCode, planId: state.checkoutPlan.id, amount }),
      });
      state.checkoutDiscount = payload.valid ? {
        code: payload.discount.code,
        discountAmount: payload.discountAmount,
        finalAmount: payload.finalAmount,
      } : null;
      state.paymentMessage = payload.valid ? "Discount applied to checkout." : "No discount applied.";
    } catch (error) {
      state.checkoutDiscount = null;
      state.paymentMessage = error.message;
    }
    render();
  }
  if (action === "save-gateway-settings") {
    persistGatewaySettings();
    state.gatewaySettingsSaved = true;
    render();
  }
  if (action === "save-plan") {
    savePlanFromForm();
  }
  if (action === "reset-plan-form") {
    state.editingPlanId = "";
    state.planForm = emptyPlanForm();
    state.planMessage = "Form cleared.";
    render();
  }
  if (action === "save-blog-published") {
    saveBlogFromForm("published");
  }
  if (action === "save-blog-draft") {
    saveBlogFromForm("draft");
  }
  if (action === "reset-blog-form") {
    state.editingBlogId = "";
    state.blogForm = emptyBlogForm();
    state.blogMessage = "Blog form cleared.";
    render();
  }
  if (action === "remove-blog-image") {
    state.blogForm.imageUrl = "";
    state.blogMessage = "Image removed from the blog form.";
    render();
  }
  if (action === "save-category") {
    saveCategoryFromForm();
  }
  if (action === "reset-category-form") {
    state.editingCategoryId = "";
    state.categoryForm = emptyCategoryForm();
    state.categoryMessage = "Category form cleared.";
    render();
  }
  if (action === "save-site-seo") {
    persistSiteSeo();
    state.siteSeoMessage = "Site SEO settings saved.";
    render();
  }
  if (action === "close-checkout") {
    state.checkoutPlan = null;
    render();
  }
});

window.addEventListener("popstate", () => {
  finalizeArticleSession();
  const nextPath = window.location.pathname;
  if ((nextPath.startsWith("/dashboard") || nextPath === "/me") && !state.user) {
    window.history.replaceState({}, "", "/");
    state.path = "/";
    state.loginOpen = true;
    state.loginMessage = "Sign in to continue.";
  } else if (nextPath.startsWith("/admin") && state.user?.role !== "admin") {
    window.history.replaceState({}, "", state.user ? "/dashboard" : "/");
    state.path = state.user ? "/dashboard" : "/";
  } else {
    state.path = nextPath;
  }
  render();
});

window.addEventListener("scroll", trackArticleDepth, { passive: true });
window.addEventListener("beforeunload", finalizeArticleSession);
window.addEventListener("keydown", (event) => {
  if (event.key !== "Escape" || !state.preferences.focusMode || !state.path.startsWith("/stories/")) return;
  state.preferences.focusMode = false;
  persistProductState("preferences", state.preferences);
  render();
});

async function bootstrapApp() {
  persistCurrentUser();
  try {
    const payload = await apiRequest(`/api/auth/session?fresh=${Date.now()}`);
    if (payload.user) applyAuthenticatedUser(payload.user, false);
  } catch {
    state.user = null;
    state.role = "reader";
  }
  try {
    await hydratePlatformState();
    await loadRecommendationFeed();
  } catch (error) {
    state.userMessage = `Platform data could not be synchronized: ${error.message}`;
  }
  state.sessionReady = true;
  const protectedUserRoute = state.path.startsWith("/dashboard") || state.path === "/me";
  const protectedAdminRoute = state.path.startsWith("/admin");
  if (protectedUserRoute && !state.user) {
    window.history.replaceState({}, "", "/");
    state.path = "/";
    state.loginOpen = true;
    state.loginMessage = "Sign in to continue.";
  } else if (protectedAdminRoute && state.user?.role !== "admin") {
    const fallback = state.user ? "/dashboard" : "/";
    window.history.replaceState({}, "", fallback);
    state.path = fallback;
    state.loginOpen = !state.user;
    state.loginMessage = "Administrator access is required.";
  }
  if (new URLSearchParams(window.location.search).get("login") === "required" && !state.user) {
    state.loginOpen = true;
    state.loginMessage = "Sign in to continue.";
    window.history.replaceState({}, "", "/");
  }
  if (state.resetToken) {
    state.loginOpen = true;
    state.loginMessage = "";
  }
  render();
  if (state.user?.role === "admin") {
    await loadAdminUsers();
    await loadMediaAssets();
  }
  fetchCurrencyRates();
}

bootstrapApp();
if ("serviceWorker" in navigator) {
  window.addEventListener("load", () => navigator.serviceWorker.register("/sw.js").catch(() => {}));
}
