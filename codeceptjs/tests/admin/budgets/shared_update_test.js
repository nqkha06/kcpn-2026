'use strict';

const assert = require('node:assert/strict');
const { loadTestData } = require('../../../support/test-data');

Feature("Admin budgets - Update shared data");

for (const testCase of loadTestData("admin/budgets/update.json")) {
    Scenario(`[${testCase.case_id}] ${testCase.description}`, () => {
        assert.equal(testCase.request.method, "PATCH");
        assert.equal(testCase.request.endpoint, "/api/v1/admin/budgets/{budget}");
        assert.ok(Number.isInteger(testCase.expected.status));
    }).tag('@shared-data');
}
