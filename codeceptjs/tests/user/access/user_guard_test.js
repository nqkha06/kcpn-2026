Feature('Access control - User routes');

Scenario('redirects a guest to login with the requested user path', ({ I }) => {
    I.amOnPage('/wallets');
    I.waitInUrl('/login', 10);
    I.seeInCurrentUrl('next=%2Fwallets');
});

Scenario('allows an authenticated user to open finance pages', ({ I }) => {
    I.loginAsUser();
    I.amOnPage('/wallets');
    I.waitForText('Ví tiền', 10, 'h1');
});
