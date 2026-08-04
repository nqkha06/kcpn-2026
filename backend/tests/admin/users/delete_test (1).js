Feature('Admin users - List');

Before(({ I }) => I.loginAsAdmin());

Scenario('lists users and supports searching', ({ I }) => {
    I.openAdminPage('/admin/users', 'Users');
    I.seeAdminList('Users', 'Add User');
    I.fillField('input[placeholder="Search..."]', process.env.USER_EMAIL);
    I.click({ xpath: "//button[normalize-space()='Search']" });
    I.waitForText(process.env.USER_EMAIL, 10, 'tbody');
    I.see('Name', 'th');
    I.see('Email', 'th');
    I.see('Roles', 'th');
});
