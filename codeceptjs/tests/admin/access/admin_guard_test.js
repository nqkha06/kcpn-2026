Feature('Access control - Admin routes');

Scenario('redirects a guest to login with the requested admin path', ({ I }) => {
    I.amOnPage('/admin/dashboard');
    I.waitInUrl('/login', 10);
    I.seeInCurrentUrl('next=%2Fadmin%2Fdashboard');
});

Scenario('denies a normal user and allows an administrator', ({ I }) => {
    I.loginAsUser();
    I.amOnPage('/admin/dashboard');
    I.waitInUrl('/403', 10);
    I.see('Bạn không có quyền truy cập', 'h1');
});

Scenario('allows an administrator to open admin routes', ({ I }) => {
    I.loginAsAdmin();
    I.openAdminPage('/admin/dashboard', 'Dashboard');
});
