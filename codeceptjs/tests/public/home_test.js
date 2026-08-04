Feature('Public site - Home');

Scenario('shows the landing page and primary calls to action', ({ I }) => {
    I.amOnPage('/');
    I.see('Cách theo dõi tiền bạc gọn gàng, nhẹ đầu', 'h1');
    I.see('Bắt đầu miễn phí', 'a');
    I.see('Xem demo trực tiếp', 'a');
    I.see('Đủ mọi thứ bạn cần để quản lý thủ công', 'h2');
    I.see('Đăng nhập', 'a');
    I.see('Đăng ký miễn phí', 'a');
});

Scenario('opens registration from the landing page', ({ I }) => {
    I.amOnPage('/');
    I.click('a[href="/register"]');
    I.waitInUrl('/register', 10);
    I.waitForText('Create an account', 10, 'h1');
});
