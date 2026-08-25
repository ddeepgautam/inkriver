import { build } from "esbuild";
import { mkdir, readFile } from "node:fs/promises";
import { brotliCompressSync, gzipSync } from "node:zlib";

const outputDirectory = "public/dist";
await mkdir(outputDirectory, { recursive: true });

await Promise.all([
  build({
    entryPoints: ["public/src/app.js"],
    outfile: `${outputDirectory}/app.min.js`,
    bundle: false,
    minify: true,
    target: ["es2022"],
    legalComments: "none",
  }),
  build({
    entryPoints: ["public/src/styles.css"],
    outfile: `${outputDirectory}/styles.min.css`,
    bundle: false,
    minify: true,
    legalComments: "none",
  }),
]);

for (const file of [`${outputDirectory}/app.min.js`, `${outputDirectory}/styles.min.css`]) {
  const contents = await readFile(file);
  const raw = (contents.length / 1024).toFixed(1);
  const gzip = (gzipSync(contents, { level: 6 }).length / 1024).toFixed(1);
  const brotli = (brotliCompressSync(contents).length / 1024).toFixed(1);
  console.log(`${file}: ${raw} KB raw, ${gzip} KB gzip, ${brotli} KB brotli`);
}
