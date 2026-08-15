'use strict';

const assert = require('node:assert/strict');
const test = require('node:test');
const {
    loadTestData,
    resolveAliases,
} = require('./test-data');

test('loads shared rows and resolves repeat generators', () => {
    const rows = loadTestData('_examples/example.json');

    assert.deepEqual(
        rows.map((row) => row.case_id),
        ['SHARED-DATA-BVA-001', 'SHARED-DATA-BVA-002'],
    );
    assert.equal(rows[0].request.body.note.length, 255);
    assert.equal(rows[1].request.body.note.length, 256);
});

test('resolves nested and flat aliases', () => {
    const result = resolveAliases(
        {
            user_id: '@customer.id',
            category_id: '@category.id',
        },
        {
            customer: { id: 42 },
            'category.id': 84,
        },
    );

    assert.deepEqual(result, {
        user_id: 42,
        category_id: 84,
    });
});

test('rejects traversal outside docs/_DataTest', () => {
    assert.throws(
        () => loadTestData('../_Postman/Final.postman_collection.json'),
        /must stay inside docs\/_DataTest/,
    );
});

test('reports missing aliases', () => {
    assert.throws(
        () => resolveAliases('@missing.id', {}),
        /Missing test fixture alias \[@missing\.id\]/,
    );
});
