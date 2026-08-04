Feature('User - Settings');

Before(({ I }) => {
    I.loginAsUser();
});

Scenario('shows profile and financial preference forms', ({ I }) => {
    I.amOnPage('/settings');
    I.waitForText('Cài đặt', 10, 'h1');
    I.see('Thông tin hồ sơ', 'h2');
    I.see('Họ và tên');
    I.see('Địa chỉ email');
    I.see('Tùy chọn tài chính', 'h2');
    I.see('Tiền tệ');
    I.see('Cập nhật hồ sơ', 'button');
    I.see('Lưu tiền tệ', 'button');
});
