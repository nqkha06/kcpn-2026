Feature('Admin roles - List');

Before(({ I }) => I.loginAsAdmin());

Scenario('lists roles and their permissions', ({ I }) => {
    I.openAdminPage('/admin/roles', 'Roles');
    I.seeAdminList('Roles', 'Add Role');
    I.see('Name', 'th');
    I.see('Permissions', 'th');
    I.see('Created At', 'th');
});
