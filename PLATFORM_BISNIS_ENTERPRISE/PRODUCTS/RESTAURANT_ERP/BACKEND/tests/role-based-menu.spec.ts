import { test, expect, Page } from '@playwright/test';

interface TestUser {
    username: string;
    password: string;
    level: string;
    role: string;
}

const testUsers: TestUser[] = [
    { username: 'platform', password: 'password', level: 'PLATFORM_OWNER', role: 'Administrator' },
    { username: 'admin', password: 'admin123', level: 'TENANT_OWNER', role: 'Administrator' },
    { username: 'manager', password: 'password', level: 'TENANT_MEMBER', role: 'Restaurant Manager' },
    { username: 'waiter', password: 'password', level: 'TENANT_MEMBER', role: 'Waiter' },
    { username: 'kitchen', password: 'password', level: 'TENANT_MEMBER', role: 'Kitchen Staff' },
    { username: 'cashier', password: 'password', level: 'TENANT_MEMBER', role: 'Cashier' },
    { username: 'inventory', password: 'password', level: 'TENANT_MEMBER', role: 'Inventory Manager' },
    { username: 'host', password: 'password', level: 'TENANT_MEMBER', role: 'Host/Hostess' },
];

const expectedMenus: Record<string, string[]> = {
    PLATFORM_OWNER: ['overview', 'enterprise', 'tenant', 'users', 'settings', 'reports', 'ai', 'integration'],
    TENANT_OWNER: ['overview', 'menu', 'tables', 'orders', 'inventory', 'kitchen', 'users', 'settings', 'accounting', 'reservation', 'crm', 'reports', 'hr', 'delivery', 'ai', 'integration', 'quality', 'supplychain', 'sustainability', 'location', 'maintenance', 'whatsapp'],
    Administrator: ['overview', 'menu', 'tables', 'orders', 'inventory', 'kitchen', 'users', 'settings', 'accounting', 'reservation', 'crm', 'reports', 'hr', 'delivery', 'ai', 'integration', 'quality', 'supplychain', 'sustainability', 'location', 'maintenance', 'whatsapp'],
    'Restaurant Manager': ['overview', 'menu', 'tables', 'orders', 'inventory', 'kitchen', 'reservation', 'reports', 'hr', 'crm', 'delivery', 'supplychain', 'quality', 'accounting'],
    Waiter: ['overview', 'tables', 'orders', 'reservation', 'menu'],
    'Kitchen Staff': ['overview', 'kitchen', 'orders', 'inventory', 'menu'],
    Cashier: ['overview', 'orders', 'accounting', 'reports', 'tables', 'menu'],
    'Inventory Manager': ['overview', 'inventory', 'supplychain', 'quality', 'orders', 'reports', 'menu'],
    'Host/Hostess': ['overview', 'tables', 'reservation', 'orders', 'menu'],
};

async function login(page: Page, username: string, password: string) {
    await page.goto('/');
    await page.click('#loginBtn');
    await page.fill('#username', username);
    await page.fill('#password', password);
    await page.click('#loginForm button[type="submit"]');
    await page.waitForSelector('#dashboard.active', { timeout: 10000 });
}

async function loginAsUser(page: Page, user: TestUser) {
    await page.goto('http://localhost:8000');
    await page.click('#loginBtn');
    await page.selectOption('#quickLoginSelect', JSON.stringify({
        username: user.username,
        password: user.password,
        level: user.level,
        role: user.role
    }));
    await page.waitForSelector('#dashboard.active', { timeout: 10000 });
}

async function verifyRole(page: Page, user: TestUser) {
    const safeName = `${user.level}_${user.role.replace(/\//g, '_').replace(/\s+/g, '_')}`;
    const screenshotDir = '/opt/lampp/htdocs/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/BACKEND/screenshots';

    // Login
    await loginAsUser(page, user);

    // Wait for role indicator
    await page.waitForSelector('#roleIndicator', { timeout: 5000 });
    const roleIndicatorText = await page.textContent('#roleIndicator');
    console.log(`\n=== ${user.level} - ${user.role} ===`);
    console.log(`Role indicator: ${roleIndicatorText}`);

    // Get all visible nav tabs
    const visibleTabs = await page.locator('.nav-tab:visible').all();
    const visibleTabNames: string[] = [];
    for (const tab of visibleTabs) {
        const tabName = await tab.getAttribute('data-tab');
        if (tabName) visibleTabNames.push(tabName);
    }

    console.log(`Visible tabs (${visibleTabNames.length}): ${visibleTabNames.join(', ')}`);

    // Determine expected tabs based on level/role
    let expectedTabs: string[] = [];
    if (user.level === 'PLATFORM_OWNER') {
        expectedTabs = expectedMenus.PLATFORM_OWNER;
    } else if (user.level === 'TENANT_OWNER') {
        expectedTabs = expectedMenus.TENANT_OWNER;
    } else {
        expectedTabs = expectedMenus[user.role] || [];
    }

    // Verify menu
    expect(visibleTabNames.sort()).toEqual(expectedTabs.sort());

    // Take screenshot of dashboard with visible tabs
    await page.screenshot({ path: `${screenshotDir}/role-${safeName}-00_dashboard.png`, fullPage: false });

    // Click each visible tab and capture content
    const tabContentReport: Record<string, { title: string; hasMessage: boolean; buttons: string[]; errors: string[] }> = {};

    for (const tabName of visibleTabNames) {
        try {
            const tab = page.locator(`.nav-tab[data-tab="${tabName}"]`);
            await tab.click();
            await page.waitForTimeout(500);

            const tabContent = page.locator(`#${tabName}Tab`);
            await expect(tabContent).toBeVisible();

            const title = await tabContent.locator('h3').first().textContent().catch(() => 'No title');
            const hasMessage = await tabContent.locator('.message').isVisible().catch(() => false);
            const buttons = await tabContent.locator('button:visible').allTextContents();
            const errors: string[] = [];

            tabContentReport[tabName] = {
                title: title || '',
                hasMessage,
                buttons: buttons.filter(b => b.trim()).slice(0, 10),
                errors
            };

            console.log(`  Tab [${tabName}]: ${title} | buttons: ${buttons.filter(b => b.trim()).slice(0, 5).join(', ')}`);

            await page.screenshot({ path: `${screenshotDir}/role-${safeName}-tab_${tabName}.png`, fullPage: false });
        } catch (e: any) {
            console.error(`  ERROR on tab ${tabName}: ${e.message}`);
            tabContentReport[tabName] = {
                title: 'ERROR',
                hasMessage: false,
                buttons: [],
                errors: [e.message]
            };
            await page.screenshot({ path: `${screenshotDir}/role-${safeName}-tab_${tabName}_ERROR.png`, fullPage: false });
        }
    }

    // Save report as attachment
    const report = {
        user,
        roleIndicator: roleIndicatorText,
        visibleTabs,
        expectedTabs,
        menuMatch: JSON.stringify(visibleTabNames.sort()) === JSON.stringify(expectedTabs.sort()),
        tabContentReport
    };
    await test.info().attach(`${safeName}-report.json`, {
        body: JSON.stringify(report, null, 2),
        contentType: 'application/json'
    });

    console.log(`Report attached for ${safeName}`);
}

test('Verify Platform Owner', async ({ page }) => await verifyRole(page, testUsers[0]));
test('Verify Tenant Owner', async ({ page }) => await verifyRole(page, testUsers[1]));
test('Verify Restaurant Manager', async ({ page }) => await verifyRole(page, testUsers[2]));
test('Verify Waiter', async ({ page }) => await verifyRole(page, testUsers[3]));
test('Verify Kitchen Staff', async ({ page }) => await verifyRole(page, testUsers[4]));
test('Verify Cashier', async ({ page }) => await verifyRole(page, testUsers[5]));
test('Verify Inventory Manager', async ({ page }) => await verifyRole(page, testUsers[6]));
test('Verify Host/Hostess', async ({ page }) => await verifyRole(page, testUsers[7]));
