<?php

declare(strict_types=1);

use Composer\InstalledVersions;
use SebastianBergmann\CodeCoverage\CodeCoverage;

require __DIR__.'/vendor/autoload.php';

date_default_timezone_set('Asia/Ho_Chi_Minh');

if ($argc !== 3) {
    fwrite(STDERR, "Usage: php .coverage-report-generator.php <coverage.php> <report.md>\n");

    exit(1);
}

/** @var CodeCoverage $coverage */
$coverage = require $argv[1];
$reportPath = $argv[2];
$coverageTests = $coverage->getTests();
$lineCoverage = $coverage->getData()->lineCoverage();
$moduleByTestId = [];
$testIdsByModule = [];
$executableStatementsByFile = [];
$coveredStatementsByFile = [];
$coveredStatementsByModuleAndFile = [];

foreach (array_keys($coverageTests) as $testId) {
    $module = moduleForTest($testId);
    $moduleByTestId[$testId] = $module;
    $testIdsByModule[$module][$testId] = true;
}

foreach ($lineCoverage as $file => $lines) {
    $executableStatementsByFile[$file] = count(array_filter(
        $lines,
        static fn (?array $testIds): bool => $testIds !== null,
    ));
    $coveredStatementsByFile[$file] = count(array_filter(
        $lines,
        static fn (?array $testIds): bool => is_array($testIds) && $testIds !== [],
    ));

    foreach ($lines as $line => $testIds) {
        if ($testIds === null) {
            continue;
        }

        foreach (array_unique($testIds) as $testId) {
            $module = $moduleByTestId[$testId] ?? null;

            if ($module === null) {
                continue;
            }

            $coveredStatementsByModuleAndFile[$module][$file][$line] = true;
        }
    }
}

$rows = [];

foreach ($coveredStatementsByModuleAndFile as $module => $files) {
    $coveredStatements = array_sum(array_map('count', $files));
    $totalStatements = array_sum(array_map(
        static fn (string $file): int => $executableStatementsByFile[$file],
        array_keys($files),
    ));

    if ($coveredStatements === 0 || $totalStatements === 0) {
        continue;
    }

    $statuses = array_unique(array_map(
        static fn (string $testId): string => $coverageTests[$testId]['status'] ?? 'unknown',
        array_keys($testIdsByModule[$module]),
    ));

    $rows[] = [
        'module' => $module,
        'tests' => count($testIdsByModule[$module]),
        'covered' => $coveredStatements,
        'total' => $totalStatements,
        'percentage' => ($coveredStatements / $totalStatements) * 100,
        'result' => $statuses === ['success'] ? 'Passed' : 'Mixed',
    ];
}

usort($rows, static fn (array $left, array $right): int => strnatcasecmp($left['module'], $right['module']));

if ($rows === []) {
    fwrite(STDERR, "No covered modules were found in the coverage data.\n");

    exit(1);
}

$totalReportedTests = array_sum(array_column($rows, 'tests'));
$percentages = array_column($rows, 'percentage');
$weightedCovered = array_sum(array_column($rows, 'covered'));
$weightedTotal = array_sum(array_column($rows, 'total'));
$suiteCoveredStatements = array_sum($coveredStatementsByFile);
$suiteTotalStatements = array_sum($executableStatementsByFile);
$onlyCoveredTotalStatements = 0;

foreach ($coveredStatementsByFile as $file => $coveredStatements) {
    if ($coveredStatements > 0) {
        $onlyCoveredTotalStatements += $executableStatementsByFile[$file];
    }
}

$branch = trim((string) shell_exec('git branch --show-current'));
$commit = trim((string) shell_exec('git rev-parse HEAD'));
$pestVersion = InstalledVersions::getPrettyVersion('pestphp/pest') ?? '4';
$lines = [
    '# BÁO CÁO COVERAGE THEO BỘ TEST CASE / MODULE',
    '',
    '## 1. Thông tin thực thi',
    '',
    '| Hạng mục | Kết quả |',
    '| --- | --- |',
    '| Ngày thực thi | '.date('d/m/Y H:i:s').' (UTC+07:00) |',
    '| Phạm vi source | `backend/app` |',
    '| Test framework | Pest '.escapeCell($pestVersion).' / PHPUnit 12 |',
    '| PHP | '.escapeCell(PHP_VERSION).' |',
    '| Nhánh | `'.escapeCell($branch).'` |',
    '| Commit | `'.escapeCell($commit).'` |',
    '| Lệnh thu thập | `./vendor/bin/pest --coverage --only-covered --coverage-php=<file>` |',
    '| Coverage driver | PCOV '.escapeCell(phpversion('pcov') ?: 'unknown').' |',
    '',
    '## 2. Phương pháp tính',
    '',
    '- Mỗi dòng là một **bộ test case của một module**, không phải một test function hoặc một dataset riêng lẻ.',
    '- Statements đã cover là hợp các executable statements do toàn bộ test của module thực thi; statement trùng giữa nhiều test chỉ được tính một lần.',
    '- Coverage module = statements đã cover / tổng executable statements thuộc tập source được bộ test của module thực thi.',
    '- Không hiển thị cột hoặc danh sách source file theo yêu cầu.',
    '- Module có coverage 0%, test skipped và test todo không được đưa vào bảng.',
    '- Coverage giữa các module không cộng trực tiếp với nhau vì các module có thể cùng thực thi code dùng chung.',
    '',
    '## 3. Tổng quan',
    '',
    '| Chỉ số | Kết quả |',
    '| --- | ---: |',
    '| Bộ test case/module được báo cáo | **'.number_format(count($rows), 0, ',', '.').'** |',
    '| Test case có coverage nằm trong các module | **'.number_format($totalReportedTests, 0, ',', '.').'** |',
    '| Coverage module thấp nhất | **'.formatPercentage(min($percentages)).'%** |',
    '| Coverage module cao nhất | **'.formatPercentage(max($percentages)).'%** |',
    '| Tỷ lệ gộp có trọng số theo module | **'.formatPercentage(($weightedCovered / $weightedTotal) * 100).'%** |',
    '| Coverage tập file `only-covered` của toàn suite | **'.formatPercentage(($suiteCoveredStatements / $onlyCoveredTotalStatements) * 100).'%** |',
    '| Coverage chính thức toàn source của Pest | **'.formatPercentage(($suiteCoveredStatements / $suiteTotalStatements) * 100).'%** |',
    '',
    '## 4. Coverage theo bộ test case / module',
    '',
    '| STT | Bộ test case / module | Test case có coverage | Statements đã cover | Coverage | Kết quả |',
    '| ---: | --- | ---: | ---: | ---: | --- |',
];

foreach ($rows as $index => $row) {
    $lines[] = sprintf(
        '| %d | %s | %s | %s/%s | **%s%%** | %s |',
        $index + 1,
        escapeCell($row['module']),
        number_format($row['tests'], 0, ',', '.'),
        number_format($row['covered'], 0, ',', '.'),
        number_format($row['total'], 0, ',', '.'),
        formatPercentage($row['percentage']),
        escapeCell($row['result']),
    );
}

$lines = [
    ...$lines,
    '',
    '## 5. Kết luận',
    '',
    'Báo cáo trình bày coverage theo **'.number_format(count($rows), 0, ',', '.').' bộ test case/module**. Mỗi phần trăm phản ánh độ bao phủ hợp nhất của toàn bộ test trong module, phù hợp để đánh giá một nhóm chức năng thay vì từng test case chi tiết. Các module coverage 0%, test skipped và test todo đã được loại.',
    '',
];

if (file_put_contents($reportPath, implode("\n", $lines)) === false) {
    fwrite(STDERR, "Unable to write report: {$reportPath}\n");

    exit(1);
}

printf("Generated %s with %d modules and %d covered tests.\n", $reportPath, count($rows), $totalReportedTests);

function moduleForTest(string $testId): string
{
    $class = explode('::', preg_replace('/^P\\\\Tests\\\\/', '', $testId) ?? $testId, 2)[0];

    if (preg_match('/^Feature\\\\Api\\\\V1\\\\Admin\\\\(Permission|Role)ControllerTest$/', $class, $matches) === 1) {
        return 'Admin / '.$matches[1];
    }

    if ($class === 'Feature\\Api\\V1\\User\\SettingsControllerTest') {
        return 'User / Settings';
    }

    if (preg_match('/^Feature\\\\Admin\\\\([^\\\\]+)/', $class, $matches) === 1) {
        return 'Admin / '.$matches[1];
    }

    if (preg_match('/^Feature\\\\User\\\\([^\\\\]+)/', $class, $matches) === 1) {
        return 'User / '.$matches[1];
    }

    if (str_starts_with($class, 'Feature\\Auth\\')) {
        return 'Auth';
    }

    if (str_starts_with($class, 'Feature\\Public\\')) {
        return 'Public';
    }

    if ($class === 'Feature\\SmokeTest') {
        return 'Smoke';
    }

    if (str_starts_with($class, 'Unit\\')) {
        return 'Unit';
    }

    return str_replace('\\', ' / ', $class);
}

function escapeCell(string $value): string
{
    return str_replace('|', '\\|', $value);
}

function formatPercentage(float $percentage): string
{
    return number_format($percentage, 2, ',', '.');
}
