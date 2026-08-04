Feature('Admin roles - Edit');

Before(({ I }) => I.loginAsAdmin());

Scenario('loads and saves an existing role', ({ I }) => {
    I.openAdminPage('/admin/roles', 'Roles');
    I.clickFirstTableAction('Edit');
    I.waitForPathEnding('/edit');
    I.waitForText('Edit Role', 10, 'h2');
    I.seeElement('#name:not([value=""])');
    I.see('Assign Permissions');
    I.click('Save Changes');
    I.seeToast('Role updated successfully');
});

