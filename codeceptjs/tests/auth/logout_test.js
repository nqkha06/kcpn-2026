Feature('Authentication - Logout');

Scenario('logs out an authenticated user', ({ I }) => {
    I.loginAsUser();
    I.waitForElement('header button[aria-haspopup="menu"]', 20);
    I.click('header button[aria-haspopup="menu"]');
    I.waitForText('Đăng xuất', 5);
    I.logout();
    I.seeInCurrentUrl('/login');
    I.see('Log in to your account', 'h1');
});
