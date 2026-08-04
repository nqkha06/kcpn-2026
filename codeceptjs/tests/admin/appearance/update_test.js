Feature('Admin - Update appearance');

Before(({ I }) => {
    I.loginAsAdmin();
});

Scenario('saves the current localized appearance values', async ({ I }) => {
    I.openAdminPage('/admin/settings/appearance', 'Appearance');
    I.click({ xpath: "//button[normalize-space()='General']" });
    I.waitForElement('input[name$=".site_name"]', 10);
    const currentName = await I.grabValueFrom('input[name$=".site_name"]');

    I.fillField('input[name$=".site_name"]', currentName || 'Spendify');
    I.click('Save changes');
    I.seeToast('Appearance settings updated successfully');
});
