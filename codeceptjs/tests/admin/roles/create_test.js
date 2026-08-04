Feature('Admin roles - Create');

Before(({ I }) => I.loginAsAdmin());

Scenario('validates and creates a role', ({ I }) => {
    const name = `e2e-role-${Date.now()}`;

    I.openAdminPage('/admin/roles/create', 'Create Role');
    I.click('Create Role');
    I.see('The role name field is required.');
    I.fillField('#name', name);
    I.click('Create Role');
    I.seeToast('Role created successfully');
    I.waitForText(name, 10, 'tbody');
});
