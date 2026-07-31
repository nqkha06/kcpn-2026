Feature('Cashback application');

Scenario('opens the frontend', ({ I }) => {
    I.amOnPage('/');
    I.seeElement('body');
});
