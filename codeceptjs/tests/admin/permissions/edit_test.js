Feature('Admin permissions - Edit');

Before(({ I }) => I.loginAsAdmin());

Scenario('loads and saves an existing permission', ({ I }) => {
    I.openAdminPage('/admin/permissions', 'Permissions');
    I.clickFirstTableAction('Edit');
    I.waitForPathEnding('/edit');
    I.waitForText('Edit Permission', 10, 'h2');
    I.seeElement('#name:not([value=""])');
    I.click('Save Changes');
    I.seeToast('Permission updated successfully');
});
