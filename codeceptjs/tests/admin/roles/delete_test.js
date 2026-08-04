Feature('Admin roles - Delete');

Before(({ I }) => I.loginAsAdmin());

Scenario('deletes a role created for the test', ({ I }) => {
    const name = `e2e-delete-role-${Date.now()}`;

    I.openAdminPage('/admin/roles/create', 'Create Role');
    I.fillField('#name', name);
    I.click('Create Role');
    I.seeToast('Role created successfully');
    I.clickRowAction(name, 'Delete');
    I.waitForText('Delete Role', 5, 'h2');
    I.click({ xpath: "//button[normalize-space()='Delete']" });
    I.seeToast('Role deleted successfully');
    I.dontSee(name, 'tbody');
});
