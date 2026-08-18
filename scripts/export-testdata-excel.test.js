const assert = require('node:assert/strict');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');
const test = require('node:test');
const ExcelJS = require('exceljs');
const {
  exportTestData,
  inferTestType,
  normalizeTestCase,
  sanitizeSheetName,
  uniqueSheetName,
} = require('./export-testdata-excel');

function createFixture() {
  const temporaryDirectory = fs.mkdtempSync(
    path.join(os.tmpdir(), 'cashback-test-cases-'),
  );
  const inputDirectory = path.join(temporaryDirectory, 'input');
  const outputFile = path.join(temporaryDirectory, 'output', 'TestData.xlsx');
  const fixtureDirectory = path.join(inputDirectory, 'admin', 'budgets');

  fs.mkdirSync(fixtureDirectory, { recursive: true });
  fs.writeFileSync(
    path.join(fixtureDirectory, 'create.json'),
    JSON.stringify([
      {
        case_id: 'ADM-BDG-CREATE-BVA-001',
        description: 'accepts the minimum amount',
        actor: 'admin',
        preconditions: ['admin_exists', 'category_exists'],
        request: {
          method: 'POST',
          endpoint: '/api/v1/admin/budgets',
          headers: { Accept: 'application/json' },
          path: {},
          query: {},
          body: { amount_limit: 0.01, period: 'monthly' },
        },
        expected: {
          status: 201,
          json_paths: { success: true, 'data.amount_limit': 0.01 },
          json_absent: [],
          validation_errors: [],
          database_change: { operation: 'insert', table: 'budgets' },
        },
        capture: { budget_id: 'data.id' },
      },
      {
        case_id: 'ADM-BDG-CREATE-RBVA-002',
        description: 'rejects an amount below the minimum',
        actor: 'admin',
        preconditions: ['admin_exists'],
        request: {
          method: 'POST',
          endpoint: '/api/v1/admin/budgets',
          headers: { Accept: 'application/json' },
          path: {},
          query: {},
          body: { amount_limit: 0 },
        },
        expected: {
          status: 422,
          json_paths: { success: false },
          json_absent: [],
          validation_errors: ['amount_limit'],
          database_change: { operation: 'none', table: 'budgets' },
        },
      },
    ]),
  );

  return { temporaryDirectory, inputDirectory, outputFile };
}

test('normalizes test cases into a QA-friendly structure', () => {
  const row = normalizeTestCase(
    {
      case_id: 'AUTH-LOGIN-RBAC-001',
      description: 'guest access is rejected',
      actor: 'guest',
      preconditions: [],
      request: {
        method: 'post',
        endpoint: '/api/v1/login',
        headers: { Accept: 'application/json' },
        body: { email: 'qa@example.com' },
      },
      expected: {
        status: 401,
        json_paths: { success: false },
        validation_errors: [],
      },
    },
    0,
  );

  assert.equal(row.testType, 'Access Control');
  assert.equal(row.method, 'POST');
  assert.match(row.testData, /HEADERS/);
  assert.match(row.testData, /email: qa@example.com/);
  assert.match(row.steps, /Continue without authentication/);
  assert.match(row.expectedResult, /success: false/);
  assert.equal(row.executionStatus, 'Not Run');
});

test('creates safe and unique worksheet names', () => {
  const names = new Set();
  const longName = 'admin/categories/create:with*invalid?characters/and-more';
  const first = uniqueSheetName(names, longName);
  const second = uniqueSheetName(names, longName);

  assert.ok(first.length <= 31);
  assert.ok(second.length <= 31);
  assert.notEqual(first, second);
  assert.doesNotMatch(sanitizeSheetName(longName), /[:\\/?*\[\]]/);
  assert.equal(inferTestType('ADM-CAT-EP-001'), 'Equivalence Partition');
});

test('exports a styled and executable test case workbook', async (context) => {
  const fixture = createFixture();
  context.after(() => fs.rmSync(fixture.temporaryDirectory, { recursive: true }));

  const summary = await exportTestData({
    inputDirectory: fixture.inputDirectory,
    outputFile: fixture.outputFile,
  });

  assert.equal(summary.sourceCount, 1);
  assert.equal(summary.totalCases, 2);
  assert.equal(summary.positiveCases, 1);
  assert.equal(summary.negativeCases, 1);
  assert.ok(fs.statSync(fixture.outputFile).size > 10_000);

  const workbook = new ExcelJS.Workbook();
  await workbook.xlsx.readFile(fixture.outputFile);

  assert.deepEqual(workbook.worksheets.map((sheet) => sheet.name), [
    'INDEX',
    'admin_budgets_create',
  ]);

  const index = workbook.getWorksheet('INDEX');
  assert.equal(index.getCell('A1').value, 'CASHBACK API · TEST CASE CATALOG');
  assert.equal(index.getCell('D6').value, 2);
  assert.equal(index.views[0].state, 'frozen');
  assert.equal(index.getTables().length, 1);

  const cases = workbook.getWorksheet('admin_budgets_create');
  assert.equal(cases.getCell('B6').value, 'ADM-BDG-CREATE-BVA-001');
  assert.equal(cases.getCell('M6').value, 'Not Run');
  assert.equal(cases.getCell('M6').dataValidation.type, 'list');
  assert.equal(cases.views[0].xSplit, 2);
  assert.equal(cases.views[0].ySplit, 5);
  assert.equal(cases.getCell('A1').fill.fgColor.argb, '16324F');
  assert.equal(cases.getCell('A5').font.bold, true);
  assert.ok(cases.conditionalFormattings.length >= 2);
  assert.equal(cases.getTables().length, 1);
  assert.match(cases.getCell('I6').value, /REQUEST BODY/);
  assert.match(cases.getCell('L7').value, /VALIDATION ERRORS/);
});
