const assert = require("node:assert/strict");
const { loadTestData } = require("../../support/test-data");

Feature("Authentication - Forgot password");

for (const testCase of loadTestData("auth/forgot-password.json")) {
    Scenario(`[${testCase.case_id}] ${testCase.description}`, () => {
        assert.equal(testCase.request.endpoint, "/api/v1/auth/forgot-password");
        assert.equal(testCase.request.method, "POST");
        assert.ok(Number.isInteger(testCase.expected.status));
    }).tag("@shared-data");
}

Scenario("validates an invalid email address", ({ I }) => {
    I.amOnPage("/forgot-password");
    I.waitForElement("#email", 20);
    I.fillField("#email", "invalid-email");
    I.click("Email password reset link");
    I.see("Email không hợp lệ");
});

Scenario("requests a reset link for an existing account", ({ I }) => {
    I.amOnPage("/forgot-password");
    I.waitForElement("#email", 20);
    I.fillField("#email", process.env.USER_EMAIL);
    I.click("Email password reset link");
    I.waitForElement(".text-green-600", 10);
});
