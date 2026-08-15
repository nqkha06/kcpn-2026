<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Testing\TestResponse;
use Tests\Support\TestData;
use Tests\Support\TestResponseAssertions;

test('it loads a keyed Pest dataset and resolves generators', function () {
    $dataset = TestData::load('_examples/example.json');

    expect($dataset)
        ->toHaveKeys(['SHARED-DATA-BVA-001', 'SHARED-DATA-BVA-002'])
        ->and($dataset['SHARED-DATA-BVA-001'][0]['request']['body']['note'])
        ->toHaveLength(255)
        ->and($dataset['SHARED-DATA-BVA-002'][0]['request']['body']['note'])
        ->toHaveLength(256);
});

test('it resolves nested and flat fixture aliases', function () {
    $value = TestData::resolveAliases(
        [
            'user_id' => '@customer.id',
            'category_id' => '@category.id',
        ],
        [
            'customer' => ['id' => 42],
            'category.id' => 84,
        ],
    );

    expect($value)->toBe([
        'user_id' => 42,
        'category_id' => 84,
    ]);
});

test('it rejects paths outside the shared data directory', function () {
    TestData::load('../_Postman/Final.postman_collection.json');
})->throws(\InvalidArgumentException::class, 'must stay inside docs/_DataTest');

test('it reports missing aliases with their full reference', function () {
    TestData::resolveAliases('@missing.id', []);
})->throws(\InvalidArgumentException::class, 'Missing test fixture alias [@missing.id]');

test('it applies shared status and JSON assertions', function () {
    $response = TestResponse::fromBaseResponse(new JsonResponse([
        'success' => true,
        'data' => ['id' => 42],
    ], 201));

    $result = TestResponseAssertions::assertForCase($response, [
        'case_id' => 'SHARED-ASSERT-001',
        'expected' => [
            'status' => 201,
            'json_paths' => [
                'success' => true,
                'data.id' => 42,
            ],
            'json_absent' => ['data.password'],
            'validation_errors' => [],
        ],
    ]);

    expect($result)->toBe($response);
});

test('it applies shared validation error assertions', function () {
    $response = TestResponse::fromBaseResponse(new JsonResponse([
        'message' => 'The given data was invalid.',
        'errors' => [
            'amount' => ['The amount field is required.'],
        ],
    ], 422));

    TestResponseAssertions::assertForCase($response, [
        'case_id' => 'SHARED-ASSERT-002',
        'expected' => [
            'status' => 422,
            'json_paths' => [],
            'json_absent' => [],
            'validation_errors' => ['amount'],
        ],
    ]);
});
