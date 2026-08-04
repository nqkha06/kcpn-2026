Feature('User - Update settings');

Before(({ I }) => {
    I.loginAsUser();
});

Scenario('updates the profile using the current values', async ({ I }) => {
    const nameField = { xpath: "//label[normalize-space()='Họ và tên']/following-sibling::input" };
    const emailField = { xpath: "//label[normalize-space()='Địa chỉ email']/following-sibling::input" };

    I.amOnPage('/settings');
    I.waitForElement(nameField, 10);
    const name = await I.grabValueFrom(nameField);
    const email = await I.grabValueFrom(emailField);
    I.fillField(nameField, name);
    I.fillField(emailField, email);
    I.click('Cập nhật hồ sơ');
    I.seeToast('Đã cập nhật thông tin hồ sơ.');
});

Scenario('updates the currency using the selected value', async ({ I }) => {
    const currencyField = { xpath: "//label[normalize-space()='Tiền tệ']/following-sibling::select" };

    I.amOnPage('/settings');
    I.waitForElement(currencyField, 10);
    const currency = await I.grabValueFrom(currencyField);
    I.selectOption(currencyField, currency);
    I.click('Lưu tiền tệ');
    I.seeToast('Đã cập nhật đơn vị tiền tệ.');
});
