'use strict';

const assert = require('node:assert/strict');
const { loadTestData } = require('../../../support/test-data');

Feature("User settings - Profile shared data");

for (const testCase of loadTestData("user/settings/profile.json")) {
    Scenario(`[${testCase.case_id}] ${testCase.description}`, () => {
        assert.equal(testCase.request.method, "PATCH");
        assert.equal(testCase.request.endpoint, "/api/v1/user/settings/profile");
        assert.ok(Number.isInteger(testCase.expected.status));
    }).tag('@shared-data');
}
