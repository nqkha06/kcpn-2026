<?php

namespace Tests\Support;

use InvalidArgumentException;
use JsonException;
use RuntimeException;

final class TestData
{
    /**
     * Return a Pest dataset keyed by case ID.
     *
     * @return array<string, array{0: array<string, mixed>}>
     */
    public static function load(string $relativePath): array
    {
        $dataset = [];

        foreach (self::rows($relativePath) as $caseId => $row) {
            $dataset[$caseId] = [$row];
        }

        return $dataset;
    }

    /**
     * Return resolved rows keyed by case ID.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function rows(string $relativePath): array
    {
        $path = self::resolveDataPath($relativePath);
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException("Unable to read test data file [{$relativePath}].");
        }

        try {
            $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException(
                "Test data file [{$relativePath}] contains invalid JSON: {$exception->getMessage()}",
                previous: $exception,
            );
        }

        if (! is_array($decoded) || ! array_is_list($decoded)) {
            throw new InvalidArgumentException("Test data file [{$relativePath}] must contain a JSON array.");
        }

        $rows = [];

        foreach ($decoded as $index => $row) {
            if (! is_array($row)) {
                throw new InvalidArgumentException("Test data row [{$index}] in [{$relativePath}] must be an object.");
            }

            self::validateRow($row, $index, $relativePath);

            $resolvedRow = self::resolveGenerators($row);
            $caseId = $resolvedRow['case_id'];

            if (array_key_exists($caseId, $rows)) {
                throw new InvalidArgumentException("Duplicate case_id [{$caseId}] in [{$relativePath}].");
            }

            $rows[$caseId] = $resolvedRow;
        }

        return $rows;
    }

    public static function resolveGenerators(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (($value['generator'] ?? null) === 'repeat') {
            $character = $value['character'] ?? null;
            $length = $value['length'] ?? null;

            if (! is_string($character) || $character === '') {
                throw new InvalidArgumentException('The repeat generator requires a non-empty character string.');
            }

            if (! is_int($length) || $length < 0) {
                throw new InvalidArgumentException('The repeat generator requires a non-negative integer length.');
            }

            return str_repeat($character, $length);
        }

        foreach ($value as $key => $item) {
            $value[$key] = self::resolveGenerators($item);
        }

        return $value;
    }

    /**
     * Replace values such as @customer.id with runtime fixture values.
     *
     * @param  array<string, mixed>  $aliases
     */
    public static function resolveAliases(mixed $value, array $aliases): mixed
    {
        if (is_string($value) && preg_match('/^@([A-Za-z0-9_.-]+)$/', $value, $matches) === 1) {
            return self::aliasValue($matches[1], $aliases);
        }

        if (! is_array($value)) {
            return $value;
        }

        foreach ($value as $key => $item) {
            $value[$key] = self::resolveAliases($item, $aliases);
        }

        return $value;
    }

    private static function resolveDataPath(string $relativePath): string
    {
        if ($relativePath === '' || str_contains($relativePath, "\0") || str_contains($relativePath, '\\')) {
            throw new InvalidArgumentException('Test data path must be a non-empty relative path using forward slashes.');
        }

        $segments = explode('/', $relativePath);

        if (str_starts_with($relativePath, '/') || in_array('..', $segments, true)) {
            throw new InvalidArgumentException("Test data path [{$relativePath}] must stay inside docs/_DataTest.");
        }

        $root = realpath(dirname(__DIR__, 3).'/docs/_DataTest');

        if ($root === false) {
            throw new RuntimeException('The docs/_DataTest directory does not exist.');
        }

        $path = realpath($root.DIRECTORY_SEPARATOR.$relativePath);

        if ($path === false || ! is_file($path)) {
            throw new InvalidArgumentException("Test data file [{$relativePath}] does not exist.");
        }

        if (! str_starts_with($path, $root.DIRECTORY_SEPARATOR)) {
            throw new InvalidArgumentException("Test data path [{$relativePath}] must stay inside docs/_DataTest.");
        }

        return $path;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private static function validateRow(array $row, int $index, string $relativePath): void
    {
        $caseId = $row['case_id'] ?? null;

        if (! is_string($caseId) || trim($caseId) === '') {
            throw new InvalidArgumentException("Test data row [{$index}] in [{$relativePath}] requires a case_id.");
        }

        if (! is_string($row['actor'] ?? null) || trim($row['actor']) === '') {
            throw new InvalidArgumentException("Test data row [{$caseId}] requires an actor.");
        }

        if (! is_array($row['preconditions'] ?? null) || ! array_is_list($row['preconditions'])) {
            throw new InvalidArgumentException("Test data row [{$caseId}] requires a preconditions array.");
        }

        if (! is_array($row['request'] ?? null)) {
            throw new InvalidArgumentException("Test data row [{$caseId}] requires a request object.");
        }

        if (! is_array($row['expected'] ?? null) || ! is_int($row['expected']['status'] ?? null)) {
            throw new InvalidArgumentException("Test data row [{$caseId}] requires an integer expected.status.");
        }
    }

    /**
     * @param  array<string, mixed>  $aliases
     */
    private static function aliasValue(string $reference, array $aliases): mixed
    {
        if (array_key_exists($reference, $aliases)) {
            return $aliases[$reference];
        }

        $value = $aliases;

        foreach (explode('.', $reference) as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                throw new InvalidArgumentException("Missing test fixture alias [@{$reference}].");
            }

            $value = $value[$segment];
        }

        return $value;
    }
}
