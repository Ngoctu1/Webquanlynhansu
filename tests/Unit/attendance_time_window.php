<?php

declare(strict_types=1);

// Standalone tests: no Laravel bootstrap, vendor, .env, database or mail access.
// Run: php -n tests/Unit/attendance_time_window.php
require_once dirname(__DIR__, 2).'/app/Services/WorkTimeCalculator.php';

use App\Services\WorkTimeCalculator;

$originalTimezone = date_default_timezone_get();
date_default_timezone_set('Asia/Ho_Chi_Minh');
$timezone = new DateTimeZone('Asia/Ho_Chi_Minh');
$schedule = [
    'start_time' => '08:00:00', 'end_time' => '17:00:00',
    'break_start' => '12:00:00', 'break_end' => '13:00:00',
    'standard_minutes' => 480, 'late_grace_minutes' => 5,
    'work_days' => [1, 2, 3, 4, 5],
];
$at = static fn (string $time) => new DateTimeImmutable($time, $timezone);
$same = static function (mixed $expected, mixed $actual): void {
    if ($actual !== $expected) {
        throw new RuntimeException('Expected '.var_export($expected, true).', got '.var_export($actual, true));
    }
};
$rejects = static function (callable $operation, string $messagePart): void {
    try {
        $operation();
    } catch (InvalidArgumentException $exception) {
        if (!str_contains($exception->getMessage(), $messagePart)) { throw $exception; }
        return;
    }
    throw new RuntimeException('Expected rejection: '.$messagePart);
};
$checkIn = static fn (string $time) => WorkTimeCalculator::ensureCheckInAllowed($schedule, $at($time));
$checkOut = static fn (string $time) => WorkTimeCalculator::ensureCheckOutAllowed($schedule, '2026-10-16', '2026-10-16 08:00:00', $at($time));
$calculate = static fn (string $in, string $out, string $date = '2026-10-16') => WorkTimeCalculator::calculate($schedule, $date, $date.' '.$in, $date.' '.$out);

$tests = [
    'Vietnam timezone is UTC+07:00' => static fn () => $same('+07:00', $at('2026-10-16 07:30:00')->format('P')),
    '07:29:59 check-in rejected' => static fn () => $rejects(fn () => $checkIn('2026-10-16 07:29:59'), '30 phút'),
    '07:30:00 check-in allowed' => static fn () => $checkIn('2026-10-16 07:30:00'),
    '08:00:00 check-in allowed' => static fn () => $checkIn('2026-10-16 08:00:00'),
    'Late check-in has no new cutoff' => static fn () => $checkIn('2026-10-16 10:00:00'),
    'Next workday uses its own check-in window' => static fn () => $checkIn('2026-10-19 07:30:00'),
    '09:00 schedule rejects 08:29:59' => static fn () => $rejects(
        fn () => WorkTimeCalculator::ensureCheckInAllowed([...$schedule, 'start_time' => '09:00:00'], $at('2026-10-16 08:29:59')), '30 phút'),
    '09:00 schedule allows 08:30:00' => static fn () => WorkTimeCalculator::ensureCheckInAllowed([...$schedule, 'start_time' => '09:00:00'], $at('2026-10-16 08:30:00')),
    '17:00:00 check-out allowed' => static fn () => $checkOut('2026-10-16 17:00:00'),
    '23:59:59 check-out allowed' => static fn () => $checkOut('2026-10-16 23:59:59'),
    '00:00:00 next day check-out rejected' => static fn () => $rejects(fn () => $checkOut('2026-10-17 00:00:00'), 'Đã quá thời gian'),
    'Month boundary check-out rejected' => static fn () => $rejects(
        fn () => WorkTimeCalculator::ensureCheckOutAllowed($schedule, '2026-10-31', '2026-10-31 08:00:00', $at('2026-11-01 00:00:00')), 'Đã quá thời gian'),
    'Check-out must be strictly after check-in' => static fn () => $rejects(fn () => $checkOut('2026-10-16 08:00:00'), 'phải sau'),
    'Check-out before check-in rejected' => static fn () => $rejects(fn () => $checkOut('2026-10-16 07:59:59'), 'phải sau'),
    'Check-in must belong to work_date' => static fn () => $rejects(
        fn () => WorkTimeCalculator::ensureCheckOutAllowed($schedule, '2026-10-16', '2026-10-15 08:00:00', $at('2026-10-16 17:00:00')), 'cùng ngày'),
    'Overnight schedule needs a separate rule' => static fn () => $rejects(
        fn () => WorkTimeCalculator::ensureCheckOutAllowed([...$schedule, 'start_time' => '22:00:00', 'end_time' => '06:00:00'], '2026-10-16', '2026-10-16 22:00:00', $at('2026-10-17 05:00:00')), 'ca qua đêm'),
    'Early arrival keeps 480 regular minutes' => static fn () => $same(480, $calculate('07:30:00', '17:00:00')['regular_minutes']),
    'Early arrival does not create OT' => static fn () => $same(0, $calculate('07:30:00', '17:00:00')['overtime_minutes']),
    'Early arrival OT status remains none' => static fn () => $same('none', $calculate('07:30:00', '17:00:00')['overtime_status']),
    'Time before shift is not paid regular work' => static fn () => $same(0, $calculate('07:30:00', '08:00:00')['regular_minutes']),
    'Five-minute grace preserves full day' => static fn () => $same('1.0000', $calculate('08:05:00', '17:00:00')['work_units']),
    'Five-minute grace has no late minutes' => static fn () => $same(0, $calculate('08:05:00', '17:00:00')['late_minutes']),
    '08:15 yields 465 minutes' => static fn () => $same(465, $calculate('08:15:00', '17:00:00')['regular_minutes']),
    '08:15 keeps existing late-minute calculation' => static fn () => $same(15, $calculate('08:15:00', '17:00:00')['late_minutes']),
    'Morning half-day remains 0.5000' => static fn () => $same('0.5000', $calculate('08:00:00', '12:00:00')['work_units']),
    'Lunch is excluded' => static fn () => $same(0, $calculate('12:00:00', '13:00:00')['regular_minutes']),
    'After-shift OT still needs HR confirmation' => static function () use ($same, $calculate): void {
        $result = $calculate('08:00:00', '19:00:00');
        $same(120, $result['overtime_minutes']);
        $same('pending', $result['overtime_status']);
    },
    'Weekend calculation remains subject to HR OT approval' => static function () use ($same, $calculate): void {
        $result = $calculate('08:00:00', '17:00:00', '2026-10-17');
        $same(0, $result['regular_minutes']);
        $same('rest_day', $result['overtime_type']);
        $same('pending', $result['overtime_status']);
    },
    'Early arrival on a rest day does not add OT minutes' => static fn () => $same(480, $calculate('07:30:00', '17:00:00', '2026-10-17')['overtime_minutes']),
    'Time entirely before a rest-day shift is not OT' => static fn () => $same(0, $calculate('07:30:00', '08:00:00', '2026-10-17')['overtime_minutes']),
    'Explicit Vietnam clock is independent of PHP default timezone' => static function () use ($schedule, $at): void {
        date_default_timezone_set('UTC');
        try { WorkTimeCalculator::ensureCheckInAllowed($schedule, $at('2026-10-16 07:30:00')); }
        finally { date_default_timezone_set('Asia/Ho_Chi_Minh'); }
    },
];

$failed = 0;
try {
    foreach ($tests as $name => $test) {
        try { $test(); echo 'PASS '.$name.PHP_EOL; }
        catch (Throwable $exception) { $failed++; fwrite(STDERR, 'FAIL '.$name.': '.$exception->getMessage().PHP_EOL); }
    }
} finally {
    date_default_timezone_set($originalTimezone);
}
echo count($tests).' tests; '.$failed.' failures.'.PHP_EOL;
exit($failed === 0 ? 0 : 1);
