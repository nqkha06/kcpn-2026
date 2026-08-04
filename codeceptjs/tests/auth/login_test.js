Feature('Authentication - Login');

Scenario('shows client validation for invalid credentials', ({ I }) => {
    I.amOnPage('/login');
    I.waitForElement('#email', 20);
    I.fillField('#email', 'not-an-email');
    I.click('Log in');
    I.waitForText('Email không hợp lệ', 5);
    I.waitForText('Vui lòng nhập mật khẩu', 5);
});

Scenario('logs in a valid user and opens the dashboard', ({ I }) => {
    I.loginAsUser();
    I.seeInCurrentUrl('/dashboard');
    I.waitForText('Tổng quan tài chính', 10, 'h1');
});
