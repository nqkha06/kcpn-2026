Feature('Authentication - Forgot password');

Scenario('validates an invalid email address', ({ I }) => {
    I.amOnPage('/forgot-password');
    I.waitForElement('#email', 20);
    I.fillField('#email', 'invalid-email');
    I.click('Email password reset link');
    I.see('Email không hợp lệ');
});

Scenario('requests a reset link for an existing account', ({ I }) => {
    I.amOnPage('/forgot-password');
    I.waitForElement('#email', 20);
    I.fillField('#email', process.env.USER_EMAIL);
    I.click('Email password reset link');
    I.waitForElement('.text-green-600', 10);
});
