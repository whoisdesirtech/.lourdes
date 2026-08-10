const { chromium } = require('playwright');

const EMAIL = 'demo@marcel.app';
const PASS = 'DemoDemo2026!';

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1920, height: 1080 } });
  page.on('console', m => console.log('[console]', m.type(), m.text().slice(0, 160)));

  await page.goto('https://marcel-market-intelligence.web.app/login', { waitUntil: 'networkidle', timeout: 60000 });
  await page.waitForTimeout(1500);

  await page.getByText(/No account\? Sign up/i).first().click();
  await page.waitForTimeout(800);
  const btnText = await page.locator('button[type="submit"], button:has-text("Sign")').allInnerTexts();
  console.log('submit buttons now:', btnText);
  const inpCount = await page.locator('input').count();
  console.log('input count after toggle:', inpCount);

  await page.locator('input[type="email"]').fill(EMAIL);
  await page.locator('input[type="password"]').fill(PASS);

  const submit = page.getByRole('button', { name: 'Sign up', exact: true }).first();
  console.log('submitting with label:', await submit.innerText().catch(() => '?'));
  await submit.click();

  await page.waitForTimeout(8000);
  console.log('URL after submit:', page.url());
  const body = await page.locator('body').innerText().catch(() => '');
  console.log('POST-AUTH BODY (first 1500):\n', body.slice(0, 1500));
  console.log('INPUTS NOW:', await page.locator('input').count(), 'BUTTONS:', await page.locator('button').count());

  await browser.close();
})().catch(e => { console.error('FAIL', e.message); process.exit(1); });
