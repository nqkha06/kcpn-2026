'use strict';

const assert = require('node:assert/strict');
const { loadTestData } = require('../../../support/test-data');

Feature("Admin budgets - Create shared data");

for (const testCase of loadTestData("admin/budgets/create.json")) {
    Scenario(`[${testCase.case_id}] ${testCase.description}`, () => {
        assert.equal(testCase.request.method, "POST");
        assert.equal(testCase.request.endpoint, "/api/v1/admin/budgets");
        assert.ok(Number.isInteger(testCase.expected.status));
    }).tag('@shared-data');
}
