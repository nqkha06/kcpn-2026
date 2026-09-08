<?php

declare(strict_types=1);

use SebastianBergmann\CodeCoverage\CodeCoverage;

require __DIR__.'/vendor/autoload.php';

if ($argc !== 3) {
    fwrite(STDERR, "Usage: php .coverage-module-breakdown.php <coverage.php> <module-prefix e.g. Feature\\Admin\\Budget>\n");
    exit(1);
}

/** @var CodeCoverage $coverage */
$coverage = require $argv[1];
$modulePrefix = str_replace('/', '\\', $argv[2]);
$coverageTests = $coverage->getTests();
$lineCoverage = $coverage->getData()->lineCoverage();

$moduleTestIds = [];
foreach (array_keys($coverageTests) as $testId) {
    $class = explode('::', preg_replace('/^P\\\\Tests\\\\/', '', $testId) ?? $testId, 2)[0];
    if (str_starts_with($class, $modulePrefix)) {
        $moduleTestIds[$testId] = true;
    }
}

if ($moduleTestIds === []) {
    fwrite(STDERR, "No tests found matching prefix: {$modulePrefix}\n");
    exit(1);
}

$perFile = [];

foreach ($lineCoverage as $file => $lines) {
    $total = 0;
    $covered = 0;
    $touchedByModule = false;

    foreach ($lines as $testIds) {
        if ($testIds === null) {
            continue;
        }
        $total++;
        $hitByModule = array_intersect_key($moduleTestIds, array_flip($testIds));
        if ($hitByModule !== []) {
            $covered++;
            $touchedByModule = true;
        }
    }

    if ($touchedByModule && $total > 0) {
        $perFile[$file] = ['covered' => $covered, 'total' => $total, 'pct' => $covered / $total * 100];
    }
}

uasort($perFile, static fn ($a, $b) => $a['pct'] <=> $b['pct']);

$sumCovered = array_sum(array_column($perFile, 'covered'));
$sumTotal = array_sum(array_column($perFile, 'total'));

printf("Module prefix: %s\n", $modulePrefix);
printf("Tests matched: %d\n", count($moduleTestIds));
printf("Overall: %d/%d (%.2f%%)\n\n", $sumCovered, $sumTotal, $sumTotal > 0 ? $sumCovered / $sumTotal * 100 : 0);
printf("%-90s %8s %8s %8s\n", 'File', 'Covered', 'Total', 'Pct');
printf(str_repeat('-', 118)."\n");

foreach ($perFile as $file => $stat) {
    $short = str_contains($file, 'app'.DIRECTORY_SEPARATOR) ? substr($file, strpos($file, 'app'.DIRECTORY_SEPARATOR)) : $file;
    printf("%-90s %8d %8d %7.2f%%\n", $short, $stat['covered'], $stat['total'], $stat['pct']);
}
