Feature('Admin - Dashboard');

Before(({ I }) => {
    I.loginAsAdmin();
});

Scenario('shows administrative finance metrics', ({ I }) => {
    I.openAdminPage('/admin/dashboard', 'Dashboard');
    I.see('Users');
    I.see('Wallets');
    I.see('Active Categories');
    I.see('Active Budgets');
    I.see('Income This Month');
    I.see('Expense This Month');
    I.see('Six-Month Cash Flow');
});
