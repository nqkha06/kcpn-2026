# Shared test data

`docs/_DataTest` is the single data source for Pest (Laravel UnitTest), CodeceptJS and Postman/Newman. Do not copy boundary payloads into PHP, JavaScript or Postman scripts.

## File contract

Each operation file contains a JSON array. Every row must have:

- `case_id`: unique test identifier.
- `actor`: actor fixture name such as `admin`, `user` or `guest`.
- `preconditions`: fixture states required before execution.
- `request`: `method`, `endpoint`, `headers`, `path`, `query` and `body`.
- `expected`: integer `status`, `json_paths`, `json_absent`, `validation_errors` and `database_change`.
- `capture`: optional map from collection variable name to response JSON path.

Use aliases such as `@customer.id` for runtime fixture IDs. Long boundary strings use:

```json
{ "generator": "repeat", "character": "a", "length": 255 }
```

The shared loaders reject duplicate case IDs, invalid rows, missing aliases and paths outside this directory.

## Pest (Laravel UnitTest)

```php
use Tests\Support\TestData;
use Tests\Support\TestResponseAssertions;

test('validates budget data', function (array $case) {
    $customer = User::factory()->create();
    $category = Category::factory()->create();

    $case = TestData::resolveAliases($case, [
        'customer' => ['id' => $customer->id],
        'category' => ['id' => $category->id],
    ]);

    $response = $this->postJson($case['request']['endpoint'], $case['request']['body']);

    TestResponseAssertions::assertForCase($response, $case);
})->with(TestData::load('admin/budgets/create.json'));
```

API tests remain in `backend/tests/Feature`. Run the loader contract test with:

```bash
(cd backend && php artisan test --compact tests/Unit/Support/TestDataTest.php)
```

## CodeceptJS

```javascript
const { loadTestData, resolveAliases } = require('../../support/test-data');

for (const testCase of loadTestData('admin/budgets/create.json')) {
    Scenario(`[${testCase.case_id}] ${testCase.description}`, ({ I }) => {
        const resolvedCase = resolveAliases(testCase, fixtureAliases);

        // Fill the UI with resolvedCase.request.body and assert resolvedCase.expected.
    });
}
```

CodeceptJS keeps the existing Playwright helper. Run the loader contract test with:

```bash
(cd codeceptjs && npm run test:data)
```

## Postman and Newman

For each operation:

1. Create a folder with folder variable `data_driven=true`.
2. Keep one HTTP method and endpoint per folder.
3. Use `{{base_url}}{{case_endpoint}}` as the request URL.
4. Use raw JSON body `{{case_body}}`.
5. Select the matching JSON file in Collection Runner, or run Newman with `-d`.

Runtime aliases must exist as environment or collection variables using the alias name without `@`, for example `customer.id`.

```bash
npx newman run docs/_Postman/Final.postman_collection.json \
  --folder "Data / Admin Budgets / Create" \
  -d docs/_DataTest/admin/budgets/create.json
```

The collection-level scripts assert status, JSON paths, absent paths and validation errors, then capture configured response values. Request-level scripts should only contain business assertions that cannot be represented in the data row.
