'use strict';

const assert = require('node:assert/strict');
const { loadTestData } = require('../../../support/test-data');

Feature("User settings - Preferences shared data");

for (const testCase of loadTestData("user/settings/preferences.json")) {
    Scenario(`[${testCase.case_id}] ${testCase.description}`, () => {
        assert.equal(testCase.request.method, "PATCH");
        assert.equal(testCase.request.endpoint, "/api/v1/user/settings/preferences");
        assert.ok(Number.isInteger(testCase.expected.status));
    }).tag('@shared-data');
}
