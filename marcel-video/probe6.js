const { chromium } = require('playwright');

const EMAIL = 'demo@marcel.app';
const PASS = 'DemoDemo2026!';

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1920, height: 1080 } });
  page.on('response', async r => {
    if (r.status() >= 400) {
      let rb = ''; try { rb = (await r.text()).slice(0, 250); } catch {}
      console.log('[HTTP', r.status(), ']', r.url().slice(0, 90), '->', rb);
    }
  });

  await page.goto('https://marcel-market-intelligence.web.app/login', { waitUntil: 'networkidle', timeout: 60000 });
  await page.waitForTimeout(1200);

  const dump = async label => {
    const buttons = await page.locator('button').evaluateAll(es => es.map(e => e.innerText.trim()).filter(Boolean));
    const inputs = await page.locator('input').evaluateAll(es => es.map(e => e.type + ':' + e.placeholder));
    const links = await page.locator('a, [role="button"], [role="tab"]').evaluateAll(es => es.map(e => (e.innerText || '').trim()).filter(Boolean));
    console.log(`\n--- ${label} ---\nBUTTONS: ${JSON.stringify(buttons)}\nINPUTS: ${JSON.stringify(inputs)}\nLINKS/TABS: ${JSON.stringify(links)}`);
  };

  await dump('initial /login');

  await page.getByText(/No account\? Sign up/i).first().click();
  await page.waitForTimeout(600);
  await dump('after No-account click');

  await page.getByText(/Sign up/i, { exact: true }).first().click();
  await page.waitForTimeout(600);
  await dump('after Sign up tab click');

  await browser.close();
})().catch(e => { console.error('FAIL', e.message); process.exit(1); });
