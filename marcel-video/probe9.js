const { chromium } = require('playwright');
const EMAIL = 'demo@marcel.app';
const PASS = 'DemoDemo2026!';
const log = (...a) => process.stdout.write(a.join(' ') + '\n');

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1920, height: 1080 } });

  log('STEP1 goto login');
  await page.goto('https://marcel-market-intelligence.web.app/login', { waitUntil: 'domcontentloaded', timeout: 45000 });
  log('STEP2 fill+submit');
  await page.locator('input[type="email"]').fill(EMAIL);
  await page.locator('input[type="password"]').fill(PASS);
  await page.getByRole('button', { name: 'Sign In' }).first().click();
  await page.waitForTimeout(7000);
  log('STEP3 url:', page.url());

  const dashLinks = await page.evaluate(() => [...document.querySelectorAll('a')].map(a => (a.innerText.trim().replace(/\n/g, '|').slice(0, 50) + ' => ' + a.getAttribute('href'))).filter(x => x && !x.startsWith(' =>')).slice(0, 25));
  log('STEP4 dash links:', JSON.stringify(dashLinks));

  const vd = page.getByText('View Details', { exact: false }).first();
  log('STEP5 view-details count:', await vd.count());
  if (await vd.count()) {
    await vd.click({ timeout: 15000 }).catch(e => log('click err', e.message));
    await page.waitForTimeout(7000);
    log('STEP6 detail url:', page.url());
    const det = await page.locator('body').innerText().catch(() => '');
    log('STEP7 detail body:\n', det.slice(0, 1600));
  }

  await browser.close();
  log('DONE');
})().catch(e => { log('FATAL', e.message); process.exit(1); });
