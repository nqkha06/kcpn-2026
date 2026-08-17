const assert = require("node:assert/strict");
const { loadTestData } = require("../../support/test-data");

Feature("Authentication - Two-factor challenge");

for (const testCase of loadTestData("auth/two-factor-challenge.json")) {
    Scenario(`[${testCase.case_id}] ${testCase.description}`, () => {
        assert.equal(
            testCase.request.endpoint,
            "/api/v1/auth/two-factor-challenge",
        );
        assert.equal(testCase.request.method, "POST");
        assert.ok(Number.isInteger(testCase.expected.status));
    }).tag("@shared-data");
}
