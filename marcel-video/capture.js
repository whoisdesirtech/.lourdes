const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1920, height: 1080 }, deviceScaleFactor: 1 });

  const pages = [
    { url: 'http://localhost:8890/marcel-ai-hero.html', out: 'shots/01-hero' },
    { url: 'http://localhost:8890/marcel-ai-card-component.html', out: 'shots/02-dashboard' },
    { url: 'http://localhost:8890/marcel-ai.html', out: 'shots/03-full' },
  ];

  for (const p of pages) {
    await page.goto(p.url, { waitUntil: 'networkidle' });
    await page.waitForTimeout(600);
    await page.screenshot({ path: p.out + '-viewport.png' });
    await page.screenshot({ path: p.out + '-full.png', fullPage: true });
    console.log('captured', p.out);
  }

  await browser.close();
})().catch(e => { console.error('FAIL', e); process.exit(1); });
