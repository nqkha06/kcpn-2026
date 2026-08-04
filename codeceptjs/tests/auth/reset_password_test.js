Feature('Authentication - Reset password');

Scenario('rejects a reset page without a token', ({ I }) => {
    I.amOnPage(`/reset-password?email=${encodeURIComponent(process.env.USER_EMAIL)}`);
    I.waitForElement('#email', 20);
    I.see('Reset password', 'h1');
    I.seeInField('#email', process.env.USER_EMAIL);
    I.seeElement('#email[readonly]');
    I.seeElement('button[type="submit"][disabled]');
});

Scenario('validates password confirmation before submitting', ({ I }) => {
    I.amOnPage(`/reset-password?token=e2e-token&email=${encodeURIComponent(process.env.USER_EMAIL)}`);
    I.waitForElement('#password', 20);
    I.fillField('#password', 'Password123!');
    I.fillField('#password_confirmation', 'Different123!');
    I.click('Reset password');
    I.see('Mật khẩu xác nhận không khớp');
});
