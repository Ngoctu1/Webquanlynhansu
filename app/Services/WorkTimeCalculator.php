<?php
namespace App\Services;

use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;

class WorkTimeCalculator
{
    public static function ensureCheckInAllowed(array $schedule, DateTimeInterface $now): void
    {
        self::ensureSameDaySchedule($schedule);
        $localNow = DateTimeImmutable::createFromInterface($now);
        $start = new DateTimeImmutable($localNow->format('Y-m-d').' '.$schedule['start_time'], $localNow->getTimezone());
        if ($localNow < $start->modify('-30 minutes')) {
            throw new InvalidArgumentException('Bạn chỉ có thể check-in từ 30 phút trước giờ bắt đầu làm việc.');
        }
    }

    public static function ensureCheckOutAllowed(array $schedule, string $workDate, string $checkIn, DateTimeInterface $now): void
    {
        self::ensureSameDaySchedule($schedule);
        $localNow = DateTimeImmutable::createFromInterface($now);
        $in = new DateTimeImmutable($checkIn, $localNow->getTimezone());
        $deadline = (new DateTimeImmutable($workDate.' 00:00:00', $localNow->getTimezone()))->modify('+1 day');
        if ($localNow >= $deadline) {
            throw new InvalidArgumentException('Đã quá thời gian check-out của ngày làm việc này. Vui lòng liên hệ HR để được hỗ trợ điều chỉnh chấm công.');
        }
        if ($localNow <= $in || $in->format('Y-m-d') !== $workDate || $localNow->format('Y-m-d') !== $workDate) {
            throw new InvalidArgumentException('Giờ check-out phải sau giờ check-in và thuộc cùng ngày làm việc.');
        }
    }

    private static function ensureSameDaySchedule(array $schedule): void
    {
        if ($schedule['start_time'] >= $schedule['end_time']) {
            throw new InvalidArgumentException('Lịch có ca qua đêm cần quy tắc chấm công riêng. Vui lòng liên hệ HR.');
        }
    }

    public static function calculate(array $schedule, string $date, string $checkIn, string $checkOut): array
    {
        $in = new DateTimeImmutable($checkIn);
        $out = new DateTimeImmutable($checkOut);
        if ($out < $in || $in->format('Y-m-d') !== $date || $out->format('Y-m-d') !== $date) {
            throw new InvalidArgumentException('Ca qua ngày hoặc thời gian không hợp lệ cần HR xử lý riêng.');
        }
        $at = fn ($time) => new DateTimeImmutable($date.' '.$time);
        $start = $at($schedule['start_time']); $end = $at($schedule['end_time']);
        $breakStart = $at($schedule['break_start']); $breakEnd = $at($schedule['break_end']);
        $workingDay = in_array((int) $in->format('N'), array_map('intval', $schedule['work_days']), true);
        $grace = $start->modify('+'.(int) $schedule['late_grace_minutes'].' minutes');
        $adjustedIn = $workingDay && $in <= $grace ? min($in, $start) : $in;
        $regular = $workingDay ? self::overlap($adjustedIn, $out, $start, $breakStart) + self::overlap($adjustedIn, $out, $breakEnd, $end) : 0;
        $regular = min((int) $schedule['standard_minutes'], $regular);
        $late = $workingDay && $in > $grace ? (int) ceil(($in->getTimestamp() - $start->getTimestamp()) / 60) : 0;
        // Arriving early does not count as OT. After-shift daytime OT still needs HR approval.
        $dayStart = $at('06:00:00'); $nightStart = $at('22:00:00');
        $overtimeStart = max($in, $start);
        $overtime = $workingDay
            ? self::overlap($in, $out, $end, $nightStart)
            : self::overlap($overtimeStart, $out, $dayStart, $nightStart) - self::overlap($overtimeStart, $out, $breakStart, $breakEnd);
        return ['regular_minutes' => $regular, 'late_minutes' => $late,
            'work_units' => number_format($regular / $schedule['standard_minutes'], 4, '.', ''),
            'overtime_minutes' => max(0, $overtime), 'overtime_hours' => number_format(max(0, $overtime) / 60, 2, '.', ''),
            'overtime_type' => $overtime > 0 ? ($workingDay ? 'weekday' : 'rest_day') : null,
            'overtime_status' => $overtime > 0 ? 'pending' : 'none'];
    }

    public static function standardDays(int $year, int $month, array $workDays): int
    {
        $date = new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
        $count = 0;
        while ((int) $date->format('n') === $month) {
            if (in_array((int) $date->format('N'), array_map('intval', $workDays), true)) { $count++; }
            $date = $date->modify('+1 day');
        }
        return $count;
    }

    private static function overlap(DateTimeImmutable $a, DateTimeImmutable $b, DateTimeImmutable $c, DateTimeImmutable $d): int
    {
        return max(0, (int) floor((min($b, $d)->getTimestamp() - max($a, $c)->getTimestamp()) / 60));
    }
}
