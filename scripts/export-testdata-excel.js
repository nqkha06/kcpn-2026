const fs = require('node:fs');
const path = require('node:path');
const ExcelJS = require('exceljs');

const DEFAULT_INPUT_DIR = path.resolve(__dirname, '../docs/_DataTest');
const DEFAULT_OUTPUT_FILE = path.resolve(__dirname, '../docs/_Reports/TestData.xlsx');

const COLORS = {
  navy: '16324F',
  blue: '1F4E78',
  sky: 'D9EAF7',
  ice: 'F4F8FB',
  white: 'FFFFFF',
  text: '1F2937',
  muted: '64748B',
  border: 'CBD5E1',
  green: 'DCFCE7',
  greenText: '166534',
  amber: 'FEF3C7',
  amberText: '92400E',
  red: 'FEE2E2',
  redText: '991B1B',
  gray: 'E2E8F0',
  grayText: '475569',
};

const TEST_CASE_COLUMNS = [
  { header: 'No.', key: 'number', width: 7 },
  { header: 'Test Case ID', key: 'caseId', width: 30 },
  { header: 'Test Type', key: 'testType', width: 20 },
  { header: 'Scenario', key: 'scenario', width: 38 },
  { header: 'Actor', key: 'actor', width: 14 },
  { header: 'Preconditions', key: 'preconditions', width: 30 },
  { header: 'Method', key: 'method', width: 11 },
  { header: 'Endpoint', key: 'endpoint', width: 42 },
  { header: 'Test Data', key: 'testData', width: 48 },
  { header: 'Execution Steps', key: 'steps', width: 46 },
  { header: 'Expected HTTP', key: 'expectedStatus', width: 16 },
  { header: 'Expected Result', key: 'expectedResult', width: 52 },
  { header: 'Execution Status', key: 'executionStatus', width: 18 },
  { header: 'Actual Result', key: 'actualResult', width: 38 },
  { header: 'Notes', key: 'notes', width: 32 },
];

const INDEX_COLUMNS = [
  { header: 'No.', key: 'number', width: 7 },
  { header: 'Area', key: 'area', width: 16 },
  { header: 'Module', key: 'module', width: 22 },
  { header: 'Feature', key: 'feature', width: 22 },
  { header: 'Source file', key: 'sourceFile', width: 42 },
  { header: 'Worksheet', key: 'worksheet', width: 30 },
  { header: 'Cases', key: 'cases', width: 11 },
  { header: '2xx / 3xx', key: 'positive', width: 13 },
  { header: '4xx / 5xx', key: 'negative', width: 13 },
  { header: 'Open', key: 'open', width: 12 },
];

const TEST_TYPE_LABELS = [
  ['RBAC', 'Access Control'],
  ['RBVA', 'Robust Boundary'],
  ['BVA', 'Boundary Value'],
  ['EP', 'Equivalence Partition'],
  ['BUS', 'Business Rule'],
];

function getJsonFiles(directory) {
  return fs
    .readdirSync(directory, { withFileTypes: true })
    .flatMap((entry) => {
      const fullPath = path.join(directory, entry.name);

      if (entry.isDirectory()) {
        return getJsonFiles(fullPath);
      }

      return entry.isFile() && entry.name.toLowerCase().endsWith('.json')
        ? [fullPath]
        : [];
    })
    .sort((left, right) => left.localeCompare(right));
}

function sanitizeSheetName(name) {
  const sanitized = name.replace(/[:\\/?*\[\]]/g, '_').slice(0, 31);

  return sanitized || 'Test Cases';
}

function uniqueSheetName(existingNames, requestedName) {
  const baseName = sanitizeSheetName(requestedName);
  let candidate = baseName;
  let suffix = 1;

  while (existingNames.has(candidate)) {
    const ending = `_${suffix}`;
    candidate = `${baseName.slice(0, 31 - ending.length)}${ending}`;
    suffix += 1;
  }

  existingNames.add(candidate);

  return candidate;
}

function titleCase(value) {
  return String(value || '')
    .replace(/[-_]+/g, ' ')
    .replace(/\b\w/g, (character) => character.toUpperCase());
}

function describeSource(relativePath) {
  const withoutExtension = relativePath.replace(/\.json$/i, '');
  const parts = withoutExtension.split(/[\\/]/);
  const feature = parts.at(-1) || 'Test Cases';

  return {
    area: titleCase(parts[0] || 'General'),
    module: titleCase(parts.length > 2 ? parts[1] : parts[0] || 'General'),
    feature: titleCase(feature),
    title: parts.map(titleCase).join(' · '),
  };
}

function stringifyValue(value) {
  if (value === null) {
    return 'null';
  }

  if (typeof value === 'string') {
    return value === '' ? '(empty string)' : value;
  }

  if (typeof value === 'object') {
    return JSON.stringify(value);
  }

  return String(value);
}

function flattenObject(value, prefix = '', result = {}) {
  if (value === null || typeof value !== 'object' || Array.isArray(value)) {
    if (prefix) {
      result[prefix] = value;
    }

    return result;
  }

  for (const [key, childValue] of Object.entries(value)) {
    const nextPrefix = prefix ? `${prefix}.${key}` : key;

    if (
      childValue !== null &&
      typeof childValue === 'object' &&
      !Array.isArray(childValue)
    ) {
      flattenObject(childValue, nextPrefix, result);
    } else {
      result[nextPrefix] = childValue;
    }
  }

  return result;
}

function formatKeyValues(value) {
  const flattened = flattenObject(value);

  if (Object.keys(flattened).length === 0) {
    return '—';
  }

  return Object.entries(flattened)
    .map(([key, childValue]) => `• ${key}: ${stringifyValue(childValue)}`)
    .join('\n');
}

function formatList(items) {
  if (!Array.isArray(items) || items.length === 0) {
    return '—';
  }

  return items.map((item) => `• ${stringifyValue(item)}`).join('\n');
}

function formatSections(sections) {
  return sections
    .filter((section) => section.value && section.value !== '—')
    .map((section) => `${section.label}\n${section.value}`)
    .join('\n\n') || '—';
}

function inferTestType(caseId) {
  const normalizedCaseId = String(caseId || '').toUpperCase();
  const match = TEST_TYPE_LABELS.find(([token]) =>
    normalizedCaseId.split('-').includes(token),
  );

  return match ? match[1] : 'Functional';
}

function buildTestData(request = {}) {
  return formatSections([
    { label: 'HEADERS', value: formatKeyValues(request.headers) },
    { label: 'PATH PARAMETERS', value: formatKeyValues(request.path) },
    { label: 'QUERY PARAMETERS', value: formatKeyValues(request.query) },
    { label: 'REQUEST BODY', value: formatKeyValues(request.body) },
  ]);
}

function buildExpectedResult(expected = {}, capture = {}) {
  return formatSections([
    { label: 'RESPONSE JSON', value: formatKeyValues(expected.json_paths) },
    { label: 'ABSENT FIELDS', value: formatList(expected.json_absent) },
    { label: 'VALIDATION ERRORS', value: formatList(expected.validation_errors) },
    { label: 'DATABASE CHANGE', value: formatKeyValues(expected.database_change) },
    { label: 'CAPTURE', value: formatKeyValues(capture) },
  ]);
}

function buildExecutionSteps(testCase) {
  const preconditions = Array.isArray(testCase.preconditions)
    ? testCase.preconditions
    : [];
  const method = testCase.request?.method || 'REQUEST';
  const endpoint = testCase.request?.endpoint || '(endpoint not specified)';
  const expectedStatus = testCase.expected?.status ?? '(not specified)';
  const steps = [];

  if (preconditions.length > 0) {
    steps.push(`Prepare: ${preconditions.join(', ')}.`);
  }

  if (testCase.actor && testCase.actor !== 'guest') {
    steps.push(`Authenticate as ${testCase.actor}.`);
  } else {
    steps.push('Continue without authentication.');
  }

  steps.push(`Send ${method} request to ${endpoint}.`);
  steps.push(`Verify HTTP status ${expectedStatus} and response contract.`);

  if (testCase.expected?.database_change) {
    steps.push('Verify the expected database state.');
  }

  return steps.map((step, index) => `${index + 1}. ${step}`).join('\n');
}

function normalizeTestCase(testCase, index) {
  return {
    number: index + 1,
    caseId: testCase.case_id || `UNSPECIFIED-${index + 1}`,
    testType: inferTestType(testCase.case_id),
    scenario: testCase.description || 'No scenario description',
    actor: titleCase(testCase.actor || 'Unspecified'),
    preconditions: formatList(testCase.preconditions),
    method: String(testCase.request?.method || '').toUpperCase(),
    endpoint: testCase.request?.endpoint || '—',
    testData: buildTestData(testCase.request),
    steps: buildExecutionSteps(testCase),
    expectedStatus: testCase.expected?.status ?? '',
    expectedResult: buildExpectedResult(testCase.expected, testCase.capture),
    executionStatus: 'Not Run',
    actualResult: '',
    notes: '',
  };
}

function applyFont(cell, overrides = {}) {
  cell.font = {
    name: 'Aptos',
    size: 10,
    color: { argb: COLORS.text },
    ...overrides,
  };
}

function applySectionTitle(cell) {
  applyFont(cell, { bold: true, size: 9, color: { argb: COLORS.muted } });
  cell.alignment = { vertical: 'middle', horizontal: 'left' };
}

function applyHeaderRow(row, fromColumn, toColumn) {
  row.height = 32;

  for (let column = fromColumn; column <= toColumn; column += 1) {
    const cell = row.getCell(column);
    applyFont(cell, { bold: true, size: 10, color: { argb: COLORS.white } });
    cell.fill = {
      type: 'pattern',
      pattern: 'solid',
      fgColor: { argb: COLORS.blue },
    };
    cell.alignment = {
      vertical: 'middle',
      horizontal: 'center',
      wrapText: true,
    };
    cell.border = {
      bottom: { style: 'medium', color: { argb: COLORS.navy } },
    };
  }
}

function estimateRowHeight(rowData) {
  const fields = [
    [rowData.scenario, 38],
    [rowData.preconditions, 30],
    [rowData.testData, 48],
    [rowData.steps, 46],
    [rowData.expectedResult, 52],
  ];
  const lineCount = Math.max(
    ...fields.map(([value, width]) =>
      String(value || '')
        .split('\n')
        .reduce(
          (total, line) => total + Math.max(1, Math.ceil(line.length / width)),
          0,
        ),
    ),
  );

  return Math.min(180, Math.max(54, lineCount * 14 + 12));
}

function styleBodyRow(row, rowData) {
  row.height = estimateRowHeight(rowData);

  row.eachCell({ includeEmpty: true }, (cell, columnNumber) => {
    applyFont(cell);
    cell.alignment = {
      vertical: 'top',
      horizontal: [1, 5, 7, 11, 13].includes(columnNumber) ? 'center' : 'left',
      wrapText: true,
    };
    cell.border = {
      bottom: { style: 'hair', color: { argb: COLORS.border } },
    };
  });

  applyFont(row.getCell(2), { bold: true, color: { argb: COLORS.blue } });
  applyFont(row.getCell(3), { color: { argb: COLORS.muted } });
  applyFont(row.getCell(7), { bold: true, color: { argb: COLORS.blue } });
  applyFont(row.getCell(11), { bold: true });
  applyFont(row.getCell(13), { bold: true, color: { argb: COLORS.grayText } });
  row.getCell(13).fill = {
    type: 'pattern',
    pattern: 'solid',
    fgColor: { argb: COLORS.gray },
  };
}

function applyWorkbookProperties(workbook) {
  workbook.creator = 'Cashback QA';
  workbook.lastModifiedBy = 'Cashback QA';
  workbook.created = new Date();
  workbook.modified = new Date();
  workbook.title = 'Cashback API Test Case Catalog';
  workbook.subject = 'Professional QA test case workbook generated from JSON fixtures';
  workbook.company = 'Cashback';
  workbook.calcProperties.fullCalcOnLoad = true;
}

function configureWorksheet(worksheet, lastRow) {
  worksheet.views = [
    {
      state: 'frozen',
      xSplit: 2,
      ySplit: 5,
      topLeftCell: 'C6',
      activeCell: 'B6',
      showGridLines: false,
      zoomScale: 85,
    },
  ];
  worksheet.pageSetup = {
    paperSize: 9,
    orientation: 'landscape',
    fitToPage: true,
    fitToWidth: 1,
    fitToHeight: 0,
    horizontalCentered: true,
    margins: {
      left: 0.25,
      right: 0.25,
      top: 0.5,
      bottom: 0.5,
      header: 0.2,
      footer: 0.2,
    },
    printArea: `A1:O${lastRow}`,
    printTitlesRow: '1:5',
  };
  worksheet.headerFooter.oddHeader = '&L&BCASHBACK QA&R&D';
  worksheet.headerFooter.oddFooter = '&LConfidential&CPage &P of &N&RTest Case Catalog';
  worksheet.properties.defaultRowHeight = 18;
}

function addTestCaseWorksheet(workbook, source) {
  const worksheet = workbook.addWorksheet(source.sheetName, {
    properties: { tabColor: { argb: COLORS.blue } },
  });
  const rows = source.data.map(normalizeTestCase);

  worksheet.mergeCells('A1:O1');
  worksheet.getCell('A1').value = source.description.title.toUpperCase();
  applyFont(worksheet.getCell('A1'), {
    bold: true,
    size: 18,
    color: { argb: COLORS.white },
  });
  worksheet.getCell('A1').fill = {
    type: 'pattern',
    pattern: 'solid',
    fgColor: { argb: COLORS.navy },
  };
  worksheet.getCell('A1').alignment = { vertical: 'middle', horizontal: 'left' };
  worksheet.getRow(1).height = 34;

  worksheet.mergeCells('A2:O2');
  worksheet.getCell('A2').value = `QA execution sheet • Source: ${source.relativePath}`;
  applyFont(worksheet.getCell('A2'), {
    italic: true,
    size: 10,
    color: { argb: COLORS.muted },
  });
  worksheet.getCell('A2').fill = {
    type: 'pattern',
    pattern: 'solid',
    fgColor: { argb: COLORS.ice },
  };
  worksheet.getCell('A2').alignment = { vertical: 'middle', horizontal: 'left' };
  worksheet.getRow(2).height = 24;

  const metadata = [
    ['A3', 'AREA', 'B3:C3', source.description.area],
    ['D3', 'MODULE', 'E3:F3', source.description.module],
    ['G3', 'FEATURE', 'H3:J3', source.description.feature],
    ['K3', 'TEST CASES', 'L3:M3', rows.length],
    ['N3', 'STATUS', 'O3', 'Ready for execution'],
  ];

  for (const [labelCell, label, valueRange, value] of metadata) {
    worksheet.getCell(labelCell).value = label;
    applySectionTitle(worksheet.getCell(labelCell));

    if (valueRange.includes(':')) {
      worksheet.mergeCells(valueRange);
    }

    const valueCell = worksheet.getCell(valueRange.split(':')[0]);
    valueCell.value = value;
    applyFont(valueCell, { bold: true, color: { argb: COLORS.navy } });
    valueCell.alignment = { vertical: 'middle', horizontal: 'left' };
  }

  worksheet.getRow(3).height = 24;
  worksheet.getRow(4).height = 9;

  worksheet.addTable({
    name: `Cases_${source.tableIndex}`,
    ref: 'A5',
    headerRow: true,
    totalsRow: false,
    style: {
      theme: 'TableStyleMedium2',
      showFirstColumn: false,
      showLastColumn: false,
      showRowStripes: true,
      showColumnStripes: false,
    },
    columns: TEST_CASE_COLUMNS.map((column) => ({ name: column.header })),
    rows: rows.map((row) => TEST_CASE_COLUMNS.map((column) => row[column.key])),
  });

  TEST_CASE_COLUMNS.forEach((column, index) => {
    worksheet.getColumn(index + 1).width = column.width;
  });

  applyHeaderRow(worksheet.getRow(5), 1, TEST_CASE_COLUMNS.length);

  rows.forEach((rowData, index) => {
    styleBodyRow(worksheet.getRow(index + 6), rowData);
  });

  const firstDataRow = 6;
  const lastDataRow = Math.max(firstDataRow, rows.length + 5);

  for (let rowNumber = firstDataRow; rowNumber <= lastDataRow; rowNumber += 1) {
    worksheet.getCell(`M${rowNumber}`).dataValidation = {
      type: 'list',
      allowBlank: false,
      formulae: ['"Not Run,Passed,Failed,Blocked,Skipped"'],
      showErrorMessage: true,
      errorStyle: 'stop',
      errorTitle: 'Invalid execution status',
      error: 'Select a value from the status list.',
      showInputMessage: true,
      promptTitle: 'Execution status',
      prompt: 'Update this field after executing the test case.',
    };
  }

  worksheet.addConditionalFormatting({
    ref: `M${firstDataRow}:M${lastDataRow}`,
    rules: [
      {
        type: 'containsText',
        operator: 'containsText',
        text: 'Passed',
        style: {
          fill: { type: 'pattern', pattern: 'solid', bgColor: { argb: COLORS.green } },
          font: { color: { argb: COLORS.greenText }, bold: true },
        },
      },
      {
        type: 'containsText',
        operator: 'containsText',
        text: 'Failed',
        style: {
          fill: { type: 'pattern', pattern: 'solid', bgColor: { argb: COLORS.red } },
          font: { color: { argb: COLORS.redText }, bold: true },
        },
      },
      {
        type: 'containsText',
        operator: 'containsText',
        text: 'Blocked',
        style: {
          fill: { type: 'pattern', pattern: 'solid', bgColor: { argb: COLORS.amber } },
          font: { color: { argb: COLORS.amberText }, bold: true },
        },
      },
    ],
  });

  worksheet.addConditionalFormatting({
    ref: `K${firstDataRow}:K${lastDataRow}`,
    rules: [
      {
        type: 'cellIs',
        operator: 'between',
        formulae: [200, 399],
        style: {
          fill: { type: 'pattern', pattern: 'solid', bgColor: { argb: COLORS.green } },
          font: { color: { argb: COLORS.greenText }, bold: true },
        },
      },
      {
        type: 'cellIs',
        operator: 'greaterThanOrEqual',
        formulae: [400],
        style: {
          fill: { type: 'pattern', pattern: 'solid', bgColor: { argb: COLORS.red } },
          font: { color: { argb: COLORS.redText }, bold: true },
        },
      },
    ],
  });

  configureWorksheet(worksheet, lastDataRow);

  return rows;
}

function styleSummaryCard(worksheet, labelRange, valueRange, label, value, fillColor) {
  worksheet.mergeCells(labelRange);
  worksheet.mergeCells(valueRange);

  const labelCell = worksheet.getCell(labelRange.split(':')[0]);
  const valueCell = worksheet.getCell(valueRange.split(':')[0]);
  labelCell.value = label;
  valueCell.value = value;

  for (const range of [labelRange, valueRange]) {
    const [start, end] = range.split(':');
    worksheet.getCell(start).fill = {
      type: 'pattern',
      pattern: 'solid',
      fgColor: { argb: fillColor },
    };
    worksheet.getCell(end).fill = {
      type: 'pattern',
      pattern: 'solid',
      fgColor: { argb: fillColor },
    };
  }

  applyFont(labelCell, { bold: true, size: 9, color: { argb: COLORS.muted } });
  applyFont(valueCell, { bold: true, size: 20, color: { argb: COLORS.navy } });
  labelCell.alignment = { vertical: 'middle', horizontal: 'center' };
  valueCell.alignment = { vertical: 'middle', horizontal: 'center' };
}

function addIndexWorksheet(workbook, sources) {
  const worksheet = workbook.addWorksheet('INDEX', {
    properties: { tabColor: { argb: COLORS.navy } },
  });
  const totalCases = sources.reduce((sum, source) => sum + source.data.length, 0);
  const positiveCases = sources.reduce(
    (sum, source) =>
      sum + source.data.filter((testCase) => testCase.expected?.status < 400).length,
    0,
  );
  const negativeCases = totalCases - positiveCases;

  worksheet.mergeCells('A1:J1');
  worksheet.getCell('A1').value = 'CASHBACK API · TEST CASE CATALOG';
  applyFont(worksheet.getCell('A1'), {
    bold: true,
    size: 20,
    color: { argb: COLORS.white },
  });
  worksheet.getCell('A1').fill = {
    type: 'pattern',
    pattern: 'solid',
    fgColor: { argb: COLORS.navy },
  };
  worksheet.getCell('A1').alignment = { vertical: 'middle', horizontal: 'left' };
  worksheet.getRow(1).height = 38;

  worksheet.mergeCells('A2:J2');
  worksheet.getCell('A2').value =
    'Central QA workbook • Navigate, execute, and record results in one place';
  applyFont(worksheet.getCell('A2'), {
    italic: true,
    size: 11,
    color: { argb: COLORS.muted },
  });
  worksheet.getCell('A2').fill = {
    type: 'pattern',
    pattern: 'solid',
    fgColor: { argb: COLORS.ice },
  };
  worksheet.getCell('A2').alignment = { vertical: 'middle', horizontal: 'left' };
  worksheet.getRow(2).height = 25;

  worksheet.mergeCells('A3:J3');
  worksheet.getCell('A3').value =
    `Source: docs/_DataTest • Generated: ${new Date().toISOString().slice(0, 10)}`;
  applyFont(worksheet.getCell('A3'), { size: 9, color: { argb: COLORS.muted } });
  worksheet.getCell('A3').alignment = { vertical: 'middle', horizontal: 'left' };
  worksheet.getRow(3).height = 22;

  styleSummaryCard(worksheet, 'A5:B5', 'A6:B6', 'JSON FILES', sources.length, COLORS.sky);
  styleSummaryCard(worksheet, 'D5:E5', 'D6:E6', 'TOTAL TEST CASES', totalCases, COLORS.sky);
  styleSummaryCard(worksheet, 'G5:H5', 'G6:H6', 'EXPECTED 2XX / 3XX', positiveCases, COLORS.green);
  styleSummaryCard(worksheet, 'I5:J5', 'I6:J6', 'EXPECTED 4XX / 5XX', negativeCases, COLORS.red);
  worksheet.getRow(5).height = 22;
  worksheet.getRow(6).height = 34;
  worksheet.getRow(7).height = 10;

  const indexRows = sources.map((source, index) => {
    const positive = source.data.filter(
      (testCase) => testCase.expected?.status < 400,
    ).length;

    return {
      number: index + 1,
      area: source.description.area,
      module: source.description.module,
      feature: source.description.feature,
      sourceFile: source.relativePath,
      worksheet: source.sheetName,
      cases: source.data.length,
      positive,
      negative: source.data.length - positive,
      open: {
        text: 'Open sheet →',
        hyperlink: `#'${source.sheetName.replace(/'/g, "''")}'!A1`,
        tooltip: `Open ${source.sheetName}`,
      },
    };
  });

  worksheet.addTable({
    name: 'TestCaseCatalog',
    ref: 'A8',
    headerRow: true,
    totalsRow: true,
    style: {
      theme: 'TableStyleMedium2',
      showFirstColumn: false,
      showLastColumn: false,
      showRowStripes: true,
      showColumnStripes: false,
    },
    columns: INDEX_COLUMNS.map((column) => {
      if (column.key === 'sourceFile') {
        return { name: column.header, totalsRowLabel: 'TOTAL' };
      }

      if (['cases', 'positive', 'negative'].includes(column.key)) {
        return { name: column.header, totalsRowFunction: 'sum' };
      }

      return { name: column.header };
    }),
    rows: indexRows.map((row) => INDEX_COLUMNS.map((column) => row[column.key])),
  });

  INDEX_COLUMNS.forEach((column, index) => {
    worksheet.getColumn(index + 1).width = column.width;
  });

  applyHeaderRow(worksheet.getRow(8), 1, INDEX_COLUMNS.length);

  for (let rowNumber = 9; rowNumber < 9 + indexRows.length; rowNumber += 1) {
    const row = worksheet.getRow(rowNumber);
    row.height = 25;
    row.eachCell({ includeEmpty: true }, (cell, columnNumber) => {
      applyFont(cell);
      cell.alignment = {
        vertical: 'middle',
        horizontal: [1, 7, 8, 9, 10].includes(columnNumber) ? 'center' : 'left',
        wrapText: true,
      };
      cell.border = {
        bottom: { style: 'hair', color: { argb: COLORS.border } },
      };
    });
    applyFont(row.getCell(10), {
      bold: true,
      color: { argb: COLORS.blue },
      underline: true,
    });
  }

  const totalsRowNumber = 9 + indexRows.length;
  const totalsRow = worksheet.getRow(totalsRowNumber);
  totalsRow.height = 27;
  totalsRow.eachCell({ includeEmpty: true }, (cell) => {
    applyFont(cell, { bold: true, color: { argb: COLORS.navy } });
    cell.fill = {
      type: 'pattern',
      pattern: 'solid',
      fgColor: { argb: COLORS.sky },
    };
    cell.alignment = { vertical: 'middle', horizontal: 'center' };
  });

  worksheet.views = [
    {
      state: 'frozen',
      ySplit: 8,
      topLeftCell: 'A9',
      activeCell: 'A9',
      showGridLines: false,
      zoomScale: 90,
    },
  ];
  worksheet.pageSetup = {
    paperSize: 9,
    orientation: 'landscape',
    fitToPage: true,
    fitToWidth: 1,
    fitToHeight: 0,
    horizontalCentered: true,
    margins: {
      left: 0.25,
      right: 0.25,
      top: 0.5,
      bottom: 0.5,
      header: 0.2,
      footer: 0.2,
    },
    printArea: `A1:J${totalsRowNumber}`,
    printTitlesRow: '1:8',
  };
  worksheet.headerFooter.oddHeader = '&L&BCASHBACK QA&R&D';
  worksheet.headerFooter.oddFooter = '&LConfidential&CPage &P of &N&RTest Case Catalog';

  return {
    totalCases,
    positiveCases,
    negativeCases,
  };
}

function loadSources(inputDirectory) {
  const existingSheetNames = new Set(['INDEX']);

  return getJsonFiles(inputDirectory).map((file, index) => {
    const relativePath = path.relative(inputDirectory, file);
    const data = JSON.parse(fs.readFileSync(file, 'utf8'));

    if (!Array.isArray(data)) {
      throw new TypeError(`${relativePath} must contain a JSON array.`);
    }

    const requestedSheetName = relativePath
      .replace(/\.json$/i, '')
      .replace(/[\\/]/g, '_');

    return {
      file,
      relativePath,
      data,
      description: describeSource(relativePath),
      sheetName: uniqueSheetName(existingSheetNames, requestedSheetName),
      tableIndex: index + 1,
    };
  });
}

async function exportTestData({
  inputDirectory = DEFAULT_INPUT_DIR,
  outputFile = DEFAULT_OUTPUT_FILE,
} = {}) {
  if (!fs.existsSync(inputDirectory)) {
    throw new Error(`Input directory does not exist: ${inputDirectory}`);
  }

  const sources = loadSources(inputDirectory);

  if (sources.length === 0) {
    throw new Error(`No JSON test data files found in: ${inputDirectory}`);
  }

  const workbook = new ExcelJS.Workbook();
  applyWorkbookProperties(workbook);
  const summary = addIndexWorksheet(workbook, sources);

  for (const source of sources) {
    addTestCaseWorksheet(workbook, source);
    console.log(
      `✓ ${source.relativePath} -> ${source.sheetName} (${source.data.length} cases)`,
    );
  }

  fs.mkdirSync(path.dirname(outputFile), { recursive: true });
  await workbook.xlsx.writeFile(outputFile);

  console.log('\n==============================');
  console.log(`✓ JSON files: ${sources.length}`);
  console.log(`✓ Test cases: ${summary.totalCases}`);
  console.log(`✓ Excel: ${outputFile}`);
  console.log('==============================');

  return {
    outputFile,
    sourceCount: sources.length,
    ...summary,
  };
}

function parseArguments(argumentsList) {
  const options = {};

  for (let index = 0; index < argumentsList.length; index += 1) {
    const argument = argumentsList[index];
    const nextArgument = argumentsList[index + 1];

    if (argument === '--input' && nextArgument) {
      options.inputDirectory = path.resolve(nextArgument);
      index += 1;
    } else if (argument === '--output' && nextArgument) {
      options.outputFile = path.resolve(nextArgument);
      index += 1;
    } else {
      throw new Error(`Unknown or incomplete argument: ${argument}`);
    }
  }

  return options;
}

if (require.main === module) {
  exportTestData(parseArguments(process.argv.slice(2))).catch((error) => {
    console.error(`✗ ${error.message}`);
    process.exitCode = 1;
  });
}

module.exports = {
  buildExecutionSteps,
  buildExpectedResult,
  buildTestData,
  describeSource,
  exportTestData,
  formatKeyValues,
  inferTestType,
  normalizeTestCase,
  sanitizeSheetName,
  uniqueSheetName,
};
