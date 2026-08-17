const assert = require("node:assert/strict");
const { loadTestData } = require("../../support/test-data");

Feature("Authentication - Login");

for (const testCase of loadTestData("auth/login.json")) {
    Scenario(`[${testCase.case_id}] ${testCase.description}`, () => {
        assert.equal(testCase.request.endpoint, "/api/v1/auth/login");
        assert.equal(testCase.request.method, "POST");
        assert.ok(Number.isInteger(testCase.expected.status));
    }).tag("@shared-data");
}

Scenario("shows client validation for invalid credentials", ({ I }) => {
    I.amOnPage("/login");
    I.waitForElement("#email", 20);
    I.fillField("#email", "not-an-email");
    I.click("Log in");
    I.waitForText("Email không hợp lệ", 5);
    I.waitForText("Vui lòng nhập mật khẩu", 5);
});

Scenario("logs in a valid user and opens the dashboard", ({ I }) => {
    I.loginAsUser();
    I.seeInCurrentUrl("/dashboard");
    I.waitForText("Tổng quan tài chính", 10, "h1");
});
