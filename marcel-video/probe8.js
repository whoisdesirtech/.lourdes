const { chromium } = require('playwright');
const EMAIL = 'demo@marcel.app';
const PASS = 'DemoDemo2026!';

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1920, height: 1080 } });

  const login = async () => {
    await page.goto('https://marcel-market-intelligence.web.app/login', { waitUntil: 'domcontentloaded', timeout: 60000 });
    await page.waitForTimeout(800);
    await page.locator('input[type="email"]').fill(EMAIL);
    await page.locator('input[type="password"]').fill(PASS);
    await page.getByRole('button', { name: 'Sign In' }).first().click();
    await page.waitForTimeout(6000);
  };
  await login();
  console.log('DASHBOARD URL:', page.url());

  // Dump dashboard links/buttons for detail navigation
  const dash = await page.evaluate(() => ({
    links: [...document.querySelectorAll('a')].map(a => (a.innerText.trim().replace(/\n/g, ' | ').slice(0, 60) + ' -> ' + a.getAttribute('href'))).filter(x => x && !x.startsWith(' ->')).slice(0, 30),
    buttons: [...document.querySelectorAll('button')].map(b => b.innerText.trim().replace(/\n/g, ' | ').slice(0, 50)).filter(Boolean).slice(0, 20),
  }));
  console.log('DASH LINKS:', JSON.stringify(dash.links, null, 2));
  console.log('DASH BUTTONS:', JSON.stringify(dash.buttons, null, 2));

  // Click the first View Details
  const viewDetails = page.getByText('View Details', { exact: false }).first();
  if (await viewDetails.count()) {
    await viewDetails.click();
    await page.waitForTimeout(8000);
    console.log('\nAFTER VIEW DETAILS URL:', page.url());
    const det = await page.locator('body').innerText().catch(() => '');
    console.log('DETAIL BODY (1800):\n', det.slice(0, 1800));
    const detInputs = await page.locator('input, textarea, select').count();
    console.log('detail inputs/selects:', detInputs);
  }

  await browser.close();
})().catch(e => { console.error('FAIL', e.message); process.exit(1); });
