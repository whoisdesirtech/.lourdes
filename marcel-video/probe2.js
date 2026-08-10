const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1920, height: 1080 } });
  page.on('console', m => { if (m.type() === 'error') console.log('[console]', m.text().slice(0, 160)); });

  for (const path of ['/dashboard', '/analyze', '/analysis', '/ticker', '/search', '/app', '/account']) {
    try {
      const url = 'https://marcel-market-intelligence.web.app' + path;
      const resp = await page.goto(url, { waitUntil: 'networkidle', timeout: 30000 });
      await page.waitForTimeout(1200);
      const title = await page.title();
      const hasInput = await page.locator('input, textarea').count();
      const btns = await page.locator('button').count();
      const bodyHead = (await page.locator('body').innerText().catch(() => '')).slice(0, 150).replace(/\n/g, ' | ');
      console.log(`\n${path} -> status ${resp.status()} | title="${title}" | inputs=${hasInput} buttons=${btns}\n  ${bodyHead}`);
    } catch (e) {
      console.log(`\n${path} -> ERROR ${e.message.slice(0, 100)}`);
    }
  }

  await browser.close();
})().catch(e => { console.error('FAIL', e.message); process.exit(1); });
