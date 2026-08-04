Feature('Admin pages - List');

Before(({ I }) => I.loginAsAdmin());

Scenario('lists pages and exposes status filtering', ({ I }) => {
    I.openAdminPage('/admin/pages', 'Pages');
    I.seeAdminList('Pages', 'Add Page');
    I.see('Title', 'th');
    I.see('Slug', 'th');
    I.see('Status', 'th');
    I.click({ xpath: "//button[normalize-space()='Filters']" });
    I.selectOption({ xpath: "//label[contains(normalize-space(), 'Status')]/select" }, 'Published');
    I.waitForText('Pages', 10, 'h2');
});
