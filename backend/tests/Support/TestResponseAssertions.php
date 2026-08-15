<?php

namespace Tests\Support;

use Illuminate\Testing\TestResponse;
use InvalidArgumentException;

final class TestResponseAssertions
{
    /**
     * Apply common HTTP and JSON assertions from a shared test-data row.
     *
     * @param  array<string, mixed>  $case
     */
    public static function assertForCase(TestResponse $response, array $case): TestResponse
    {
        $caseId = $case['case_id'] ?? 'unknown';
        $expected = $case['expected'] ?? null;

        if (! is_array($expected) || ! is_int($expected['status'] ?? null)) {
            throw new InvalidArgumentException("Test data row [{$caseId}] requires an integer expected.status.");
        }

        $jsonPaths = $expected['json_paths'] ?? [];
        $jsonAbsent = $expected['json_absent'] ?? [];
        $validationErrors = $expected['validation_errors'] ?? [];

        if (! is_array($jsonPaths) || ! is_array($jsonAbsent) || ! is_array($validationErrors)) {
            throw new InvalidArgumentException("Test data row [{$caseId}] has invalid expected JSON assertions.");
        }

        $response->assertStatus($expected['status']);

        foreach ($jsonPaths as $path => $value) {
            $response->assertJsonPath($path, $value);
        }

        foreach ($jsonAbsent as $path) {
            $response->assertJsonMissingPath($path);
        }

        if ($validationErrors !== []) {
            $response->assertJsonValidationErrors($validationErrors);
        }

        return $response;
    }
}
