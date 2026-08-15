'use strict';

const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');

const collectionPath = path.resolve(
    __dirname,
    '../../docs/_Postman/Final.postman_collection.json',
);

function collection() {
    return JSON.parse(fs.readFileSync(collectionPath, 'utf8'));
}

test('shared Postman scripts compile', () => {
    const events = collection().event;

    assert.deepEqual(
        events.map((event) => event.listen),
        ['prerequest', 'test'],
    );

    for (const event of events) {
        assert.doesNotThrow(() => new Function(event.script.exec.join('\n')));
    }
});

test('shared Postman variables are present', () => {
    const variables = Object.fromEntries(
        collection().variable.map((variable) => [variable.key, variable.value]),
    );

    assert.equal(variables.data_driven, 'false');
    assert.equal(variables.case_id, '');
    assert.equal(variables.case_endpoint, '');
    assert.equal(variables.case_body, '{}');
    assert.equal(variables.max_response_time_ms, '');
});
