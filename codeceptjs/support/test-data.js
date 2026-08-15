'use strict';

const fs = require('node:fs');
const path = require('node:path');

const dataRoot = path.resolve(__dirname, '../../docs/_DataTest');

function loadTestData(relativePath) {
    const filePath = resolveDataPath(relativePath);
    let rows;

    try {
        rows = JSON.parse(fs.readFileSync(filePath, 'utf8'));
    } catch (error) {
        throw new Error(`Unable to parse test data file [${relativePath}]: ${error.message}`, {
            cause: error,
        });
    }

    if (!Array.isArray(rows)) {
        throw new TypeError(`Test data file [${relativePath}] must contain a JSON array.`);
    }

    const caseIds = new Set();

    return rows.map((row, index) => {
        validateRow(row, index, relativePath);

        if (caseIds.has(row.case_id)) {
            throw new TypeError(`Duplicate case_id [${row.case_id}] in [${relativePath}].`);
        }

        caseIds.add(row.case_id);

        return resolveGenerators(row);
    });
}

function resolveGenerators(value) {
    if (Array.isArray(value)) {
        return value.map(resolveGenerators);
    }

    if (!isPlainObject(value)) {
        return value;
    }

    if (value.generator === 'repeat') {
        if (typeof value.character !== 'string' || value.character.length === 0) {
            throw new TypeError('The repeat generator requires a non-empty character string.');
        }

        if (!Number.isInteger(value.length) || value.length < 0) {
            throw new TypeError('The repeat generator requires a non-negative integer length.');
        }

        return value.character.repeat(value.length);
    }

    return Object.fromEntries(
        Object.entries(value).map(([key, item]) => [key, resolveGenerators(item)]),
    );
}

function resolveAliases(value, aliases) {
    if (typeof value === 'string' && /^@[A-Za-z0-9_.-]+$/.test(value)) {
        return aliasValue(value.slice(1), aliases);
    }

    if (Array.isArray(value)) {
        return value.map((item) => resolveAliases(item, aliases));
    }

    if (!isPlainObject(value)) {
        return value;
    }

    return Object.fromEntries(
        Object.entries(value).map(([key, item]) => [key, resolveAliases(item, aliases)]),
    );
}

function resolveDataPath(relativePath) {
    if (
        typeof relativePath !== 'string' ||
        relativePath.length === 0 ||
        relativePath.includes('\0') ||
        relativePath.includes('\\') ||
        path.isAbsolute(relativePath) ||
        relativePath.split('/').includes('..')
    ) {
        throw new TypeError('Test data path must stay inside docs/_DataTest and use forward slashes.');
    }

    const filePath = path.resolve(dataRoot, relativePath);

    if (!filePath.startsWith(`${dataRoot}${path.sep}`)) {
        throw new TypeError('Test data path must stay inside docs/_DataTest.');
    }

    if (!fs.statSync(filePath, { throwIfNoEntry: false })?.isFile()) {
        throw new TypeError(`Test data file [${relativePath}] does not exist.`);
    }

    return filePath;
}

function validateRow(row, index, relativePath) {
    if (!isPlainObject(row)) {
        throw new TypeError(`Test data row [${index}] in [${relativePath}] must be an object.`);
    }

    if (typeof row.case_id !== 'string' || row.case_id.trim() === '') {
        throw new TypeError(`Test data row [${index}] in [${relativePath}] requires a case_id.`);
    }

    if (typeof row.actor !== 'string' || row.actor.trim() === '') {
        throw new TypeError(`Test data row [${row.case_id}] requires an actor.`);
    }

    if (!Array.isArray(row.preconditions)) {
        throw new TypeError(`Test data row [${row.case_id}] requires a preconditions array.`);
    }

    if (!isPlainObject(row.request)) {
        throw new TypeError(`Test data row [${row.case_id}] requires a request object.`);
    }

    if (!isPlainObject(row.expected) || !Number.isInteger(row.expected.status)) {
        throw new TypeError(`Test data row [${row.case_id}] requires an integer expected.status.`);
    }
}

function aliasValue(reference, aliases) {
    if (Object.hasOwn(aliases, reference)) {
        return aliases[reference];
    }

    let value = aliases;

    for (const segment of reference.split('.')) {
        if (!isPlainObject(value) || !Object.hasOwn(value, segment)) {
            throw new TypeError(`Missing test fixture alias [@${reference}].`);
        }

        value = value[segment];
    }

    return value;
}

function isPlainObject(value) {
    return value !== null && typeof value === 'object' && !Array.isArray(value);
}

module.exports = {
    dataRoot,
    loadTestData,
    resolveAliases,
    resolveGenerators,
};
