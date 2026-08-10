const { chromium } = require('playwright');

const EMAIL = 'demo@marcel.app';
const PASS = 'DemoDemo2026!';

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1920, height: 1080 } });
  page.on('console', m => { if (m.type() === 'error') console.log('[console]', m.text().slice(0, 160)); });

  await page.goto('https://marcel-market-intelligence.web.app/login', { waitUntil: 'networkidle', timeout: 60000 });
  await page.waitForTimeout(1500);

  await page.locator('input[type="email"]').fill(EMAIL);
  await page.locator('input[type="password"]').fill(PASS);
  await page.getByRole('button', { name: 'Sign In', exact: true }).first().click();

  await page.waitForTimeout(10000);
  console.log('URL after sign-in:', page.url());
  const body = await page.locator('body').innerText().catch(() => '');
  console.log('DASHBOARD BODY (first 2000):\n', body.slice(0, 2000));

  const inputs = await page.locator('input, textarea').evaluateAll(els => els.map(e => ({ tag: e.tagName, type: e.type, placeholder: e.placeholder })));
  console.log('INPUTS:', JSON.stringify(inputs));
  const buttons = await page.locator('button').evaluateAll(els => els.map(e => e.innerText.trim().slice(0, 40)).filter(Boolean).slice(0, 25));
  console.log('BUTTONS:', JSON.stringify(buttons, null, 2));

  await page.screenshot({ path: 'shots/05-dashboard-real.png' });
  await browser.close();
})().catch(e => { console.error('FAIL', e.message); process.exit(1); });
