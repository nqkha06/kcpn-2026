Feature('Admin categories - List');

Before(({ I }) => I.loginAsAdmin());

Scenario('lists and filters categories', ({ I }) => {
    I.openAdminPage('/admin/categories', 'Categories');
    I.seeAdminList('Categories', 'Add Category');
    I.see('Name', 'th');
    I.see('Color', 'th');
    I.see('Status', 'th');
    I.click({ xpath: "//button[normalize-space()='Filters']" });
    I.selectOption({ xpath: "//label[contains(normalize-space(), 'Status')]/select" }, 'Active');
    I.waitForText('Categories', 10, 'h2');
});
