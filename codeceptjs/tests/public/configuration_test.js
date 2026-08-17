const assert = require("node:assert/strict");
const { loadTestData } = require("../../support/test-data");

Feature("Public site - Configuration");

for (const testCase of loadTestData("public/configuration.json")) {
    Scenario(`[${testCase.case_id}] ${testCase.description}`, () => {
        assert.equal(testCase.request.endpoint, "/api/v1/public/configuration");
        assert.equal(testCase.request.method, "GET");
        assert.ok(Number.isInteger(testCase.expected.status));
    }).tag("@shared-data");
}
