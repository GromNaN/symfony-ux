import { expect, test } from '@playwright/test';

const uniqueReference = `ref_${Date.now()}_${Math.random().toString(36).slice(2)}`;

test('reveals the protected value on click', async ({ page }) => {
    await page.goto(`/ux-disclose?r=${uniqueReference}`);

    const trigger = page.locator('[data-disclose-target="button"]').first();
    const value = page.locator('[data-disclose-target="value"]').first();

    await expect(trigger).toHaveText('••••••');
    await trigger.click();

    await expect(value).toHaveText('the-secret');
    await expect(trigger).toBeHidden();
});

test('shows the rate-limited state after the limit is reached', async ({ page }) => {
    await page.goto(`/ux-disclose?r=${uniqueReference}`);

    const trigger = page.locator('[data-disclose-target="button"]').first();
    const error = page.locator('[data-disclose-target="error"]').first();

    // The shared e2e rate limiter allows 3 disclosures per subject, then
    // answers 429. Click until the client shows the rate-limited state.
    for (let i = 0; i < 8 && !(await error.isVisible()); i++) {
        await trigger.click({ noWaitAfter: true });
    }

    await expect(error).toBeVisible();
    await expect(error).toHaveText(/rate limit|quota/i);
});
