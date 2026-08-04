Feature('Admin transactions - List');

Before(({ I }) => I.loginAsAdmin());

Scenario('lists transactions and exposes detailed filters', ({ I }) => {
    I.openAdminPage('/admin/transactions', 'Transactions');
    I.seeAdminList('Transactions', 'Add Transaction');
    I.see('User', 'th');
    I.see('Wallet', 'th');
    I.see('Category', 'th');
    I.see('Amount', 'th');
    I.see('Transaction Date', 'th');
    I.click({ xpath: "//button[normalize-space()='Filters']" });
    I.see('All Types');
    I.see('All Statuses');
    I.see('From Date');
    I.see('To Date');
});
