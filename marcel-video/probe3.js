const { chromium } = require('playwright');

const EMAIL = 'demo@marcel.app';
const PASS = 'DemoDemo2026!';

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1920, height: 1080 } });
  const logs = [];
  page.on('console', m => logs.push(m.type() + ': ' + m.text().slice(0, 120)));

  await page.goto('https://marcel-market-intelligence.web.app/', { waitUntil: 'networkidle', timeout: 60000 });
  await page.waitForTimeout(2000);

  // Go to login
  await page.locator('a[href="/login"]').first().click();
  await page.waitForTimeout(1500);
  console.log('URL after login click:', page.url());

  // Switch to signup
  const signupBtn = page.getByText(/sign up|no account|register/i).first();
  const hasSignup = await signupBtn.count();
  console.log('signup toggle found:', hasSignup);
  if (hasSignup) { await signupBtn.click(); await page.waitForTimeout(800); }

  const formText = await page.locator('body').innerText().catch(() => '');
  console.log('FORM PAGE TEXT:\n', formText.slice(0, 800));
  const inputs = await page.locator('input').evaluateAll(els => els.map(e => ({ type: e.type, name: e.name, placeholder: e.placeholder, id: e.id })));
  console.log('INPUTS:', JSON.stringify(inputs, null, 2));

  await browser.close();
})().catch(e => { console.error('FAIL', e.message); process.exit(1); });
