const { chromium } = require('playwright');

const NAME = 'Demo User';
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

  await page.getByRole('button', { name: 'Sign up' }).first().click();
  await page.waitForTimeout(600);
  await page.locator('input[type="text"]').fill(NAME);
  await page.locator('input[type="email"]').fill(EMAIL);
  await page.locator('input[type="password"]').fill(PASS);
  await page.getByRole('button', { name: 'Create Account' }).first().click();

  await page.waitForTimeout(8000);
  console.log('URL after signup:', page.url());
  const b1 = await page.locator('body').innerText().catch(() => '');
  console.log('BODY after signup (600):\n', b1.slice(0, 600));

  // Now try to sign in
  await page.goto('https://marcel-market-intelligence.web.app/login', { waitUntil: 'networkidle' });
  await page.waitForTimeout(800);
  await page.locator('input[type="email"]').fill(EMAIL);
  await page.locator('input[type="password"]').fill(PASS);
  await page.getByRole('button', { name: 'Sign In' }).first().click();
  await page.waitForTimeout(10000);
  console.log('\nURL after sign-in:', page.url());
  const b2 = await page.locator('body').innerText().catch(() => '');
  console.log('DASHBOARD BODY (2500):\n', b2.slice(0, 2500));

  await page.screenshot({ path: 'shots/05-dashboard-real.png' });
  await browser.close();
})().catch(e => { console.error('FAIL', e.message); process.exit(1); });
