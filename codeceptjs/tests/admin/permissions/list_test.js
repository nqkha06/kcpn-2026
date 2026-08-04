Feature('Admin permissions - List');

Before(({ I }) => I.loginAsAdmin());

Scenario('lists permissions and related role counts', ({ I }) => {
    I.openAdminPage('/admin/permissions', 'Permissions');
    I.seeAdminList('Permissions', 'Add Permission');
    I.see('Name', 'th');
    I.see('Guard', 'th');
    I.see('Roles', 'th');
    I.see('Created At', 'th');
});
