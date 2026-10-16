<?php
namespace App\Services;

use InvalidArgumentException;

class PayrollCalculator
{
    // All money uses decimal integer arithmetic. No binary floating point in payments.
    public static function calculate(string $base, string $coefficient, string $allowance, string $standard, string $actual,
        string $overtime, string $deduction, ?int $regularMinutes = null, int $standardMinutes = 480): array
    {
        $base = self::scaled($base, 2); $coefficient = self::scaled($coefficient, 3);
        $allowance = self::scaled($allowance, 2); $standard = self::scaled($standard, 2);
        $actual = self::scaled($actual, 4); $overtime = self::scaled($overtime, 2); $deduction = self::scaled($deduction, 2);
        if ($standard <= 0 || $standard > 3100 || $actual > $standard * 100 || $coefficient <= 0 || $coefficient > 999999) {
            throw new InvalidArgumentException('Công chuẩn, công thực tế hoặc hệ số không hợp lệ.');
        }
        if ($regularMinutes !== null) {
            if ($standardMinutes <= 0 || $regularMinutes < 0 || $regularMinutes * 100 > $standard * $standardMinutes) {
                throw new InvalidArgumentException('Tổng phút công không hợp lệ.');
            }
            $wage = self::roundedProduct($base, [$coefficient, $regularMinutes, 100], 1000 * $standard * $standardMinutes);
        } else {
            $wage = self::roundedProduct($base, [$coefficient, $actual], 1000 * $standard * 100);
        }
        $gross = $wage + $allowance + $overtime; $net = $gross - $deduction;
        if ($gross > 999999999999999 || $net < 0) { throw new InvalidArgumentException('Tổng lương quá lớn hoặc khấu trừ vượt tổng lương.'); }
        return ['gross_salary' => self::money($gross), 'net_salary' => self::money($net)];
    }

    public static function overtime(string $hourlyRate, string $multiplier, int $minutes, string $type): string
    {
        $minimum = self::minimumMultiplier($type);
        $factor = self::scaled($multiplier, 3);
        if ($factor < self::scaled($minimum, 3) || $factor > 999999 || $minutes < 0 || $minutes > 960) {
            throw new InvalidArgumentException('Hệ số OT thấp hơn mức tối thiểu hoặc phút OT không hợp lệ.');
        }
        $rate = self::scaled($hourlyRate, 2);
        if ($rate <= 0) { throw new InvalidArgumentException('Lương giờ OT phải lớn hơn 0.'); }
        return self::money(self::roundedProduct($rate, [$factor, $minutes], 1000 * 60));
    }

    public static function minimumMultiplier(string $type): string
    {
        // Daytime only: Article 98 Labour Code 2019 / Article 55 Decree 145/2020.
        return match ($type) {
            'weekday' => '1.500', 'rest_day' => '2.000', 'holiday' => '3.000',
            default => throw new InvalidArgumentException('Chưa xác nhận loại ngày OT.'),
        };
    }

    public static function suggestedHourlyRate(string $base, string $coefficient, string $allowance, int $days, int $minutes): string
    {
        if ($days <= 0 || $minutes <= 0) { throw new InvalidArgumentException('Thiếu thời gian làm việc chuẩn.'); }
        $monthly = self::roundedProduct(self::scaled($base, 2), [self::scaled($coefficient, 3)], 1000) + self::scaled($allowance, 2);
        // A proposal for HR verification: allowance classification is not represented by the schema.
        return self::money(self::roundedProduct($monthly, [60], $days * $minutes));
    }

    public static function sumMoney(array $amounts): string
    {
        $sum = 0;
        foreach ($amounts as $amount) {
            $sum += self::scaled((string) $amount, 2);
            if ($sum > 999999999999999) { throw new InvalidArgumentException('Tổng tiền vượt giới hạn DECIMAL(15,2).'); }
        }
        return self::money($sum);
    }

    public static function daysFromMinutes(int $minutes, int $standard): string
    {
        if ($minutes < 0 || $standard <= 0) { throw new InvalidArgumentException('Phút công không hợp lệ.'); }
        $units = self::roundedProduct($minutes, [10000], $standard);
        return intdiv($units, 10000).'.'.str_pad((string)($units % 10000), 4, '0', STR_PAD_LEFT);
    }

    private static function roundedProduct(int $value, array $factors, int $divisor): int
    {
        $numerator = (string) $value;
        foreach ($factors as $factor) { $numerator = self::multiply($numerator, $factor); }
        $quotient = ''; $remainder = 0;
        foreach (str_split($numerator) as $digit) {
            $remainder = $remainder * 10 + (int) $digit;
            $quotient .= (string) intdiv($remainder, $divisor);
            $remainder %= $divisor;
        }
        $quotient = ltrim($quotient, '0') ?: '0';
        if (strlen($quotient) > 15) { throw new InvalidArgumentException('Giá trị vượt giới hạn DECIMAL(15,2).'); }
        $result = (int)$quotient + ($remainder * 2 >= $divisor ? 1 : 0);
        if ($result > 999999999999999) { throw new InvalidArgumentException('Giá trị vượt giới hạn DECIMAL(15,2).'); }
        return $result;
    }
    private static function scaled(string $value, int $scale): int
    {
        if (!preg_match('/^\d+(?:\.\d{1,'.$scale.'})?$/D', $value)) { throw new InvalidArgumentException('Số thập phân không hợp lệ.'); }
        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');
        $digits = ltrim($whole.str_pad($fraction, $scale, '0'), '0') ?: '0';
        if (strlen($digits) > 15) { throw new InvalidArgumentException('Giá trị vượt giới hạn.'); }
        return (int) $digits;
    }
    private static function multiply(string $value, int $factor): string
    {
        $out = ''; $carry = 0;
        foreach (array_reverse(str_split($value)) as $digit) {
            $product = (int)$digit * $factor + $carry;
            $out = ($product % 10).$out; $carry = intdiv($product, 10);
        }
        return ltrim(($carry ? (string)$carry : '').$out, '0') ?: '0';
    }
    private static function money(int $cents): string
    {
        return intdiv($cents, 100).'.'.str_pad((string)($cents % 100), 2, '0', STR_PAD_LEFT);
    }
}
