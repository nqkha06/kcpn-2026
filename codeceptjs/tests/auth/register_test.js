Feature('Authentication - Registration');

Scenario('validates required registration fields', ({ I }) => {
    I.amOnPage('/register');
    I.waitForElement('#name', 20);
    I.click('Create account');
    I.see('Vui lòng nhập họ tên');
    I.see('Email không hợp lệ');
    I.see('Mật khẩu phải có ít nhất 8 ký tự');
});

Scenario('registers a new account', ({ I }) => {
    const suffix = `${Date.now()}${Math.floor(Math.random() * 1000)}`;

    I.amOnPage('/register');
    I.waitForElement('#name', 20);
    I.fillField('#name', `E2E User ${suffix}`);
    I.fillField('#email', `e2e-user-${suffix}@example.com`);
    I.fillField('#password', 'Password123!');
    I.fillField('#password_confirmation', 'Password123!');
    I.click('Create account');
    I.waitInUrl('/dashboard', 10);
    I.waitForText('Tổng quan tài chính', 10, 'h1');
});
