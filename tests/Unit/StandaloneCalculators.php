<?php
// Dependency-free checks: does not load Laravel, vendor, config, .env, DB or SMTP.
require_once __DIR__.'/../../app/Services/WorkTimeCalculator.php';
require_once __DIR__.'/../../app/Services/PayrollCalculator.php';

use App\Services\{PayrollCalculator, WorkTimeCalculator};

$checks = 0;
$equal = function ($expected, $actual, string $label) use (&$checks) {
    $checks++;
    if ($expected !== $actual) {
        throw new RuntimeException($label.': expected '.var_export($expected, true).', actual '.var_export($actual, true));
    }
};
$reject = function (callable $action, string $label) use (&$checks) {
    $checks++;
    try { $action(); } catch (InvalidArgumentException) { return; }
    throw new RuntimeException('Expected rejection: '.$label);
};
$schedule = ['start_time'=>'08:00:00','end_time'=>'17:00:00','break_start'=>'12:00:00','break_end'=>'13:00:00',
    'standard_minutes'=>480,'late_grace_minutes'=>5,'work_days'=>[1,2,3,4,5]];
foreach ([
    ['07:55','17:00',480,'1.0000',0,0],
    ['08:00','17:00',480,'1.0000',0,0],
    ['08:05','17:00',480,'1.0000',0,0],
    ['08:15','17:00',465,'0.9688',15,0],
    ['08:00','12:00',240,'0.5000',0,0],
    ['08:00','18:00',480,'1.0000',0,60],
    ['08:00','19:00',480,'1.0000',0,120],
    ['12:00','13:00',0,'0.0000',240,0],
] as [$in,$out,$minutes,$units,$late,$ot]) {
    $result = WorkTimeCalculator::calculate($schedule, '2026-10-16', '2026-10-16 '.$in, '2026-10-16 '.$out);
    $equal($minutes,$result['regular_minutes'],$in.' minutes');
    $equal($units,$result['work_units'],$in.' units');
    $equal($late,$result['late_minutes'],$in.' late');
    // Early arrival is only a pending OT proposal; HR must approve it.
    if ($in !== '07:55') { $equal($ot,$result['overtime_minutes'],$in.' overtime'); }
}
$weekend = WorkTimeCalculator::calculate($schedule, '2026-10-17', '2026-10-17 08:00', '2026-10-17 17:00');
$equal(0,$weekend['regular_minutes'],'weekend regular');
$equal(480,$weekend['overtime_minutes'],'weekend OT after lunch');
$equal('rest_day',$weekend['overtime_type'],'weekend type');
$equal('pending',$weekend['overtime_status'],'OT not automatically approved');
$equal(22,WorkTimeCalculator::standardDays(2026,10,[1,2,3,4,5]),'October weekdays');
$equal(20,WorkTimeCalculator::standardDays(2026,2,[1,2,3,4,5]),'February weekdays');
$equal(21,WorkTimeCalculator::standardDays(2024,2,[1,2,3,4,5]),'leap February');
$reject(fn()=>WorkTimeCalculator::calculate($schedule,'2026-10-16','2026-10-16 17:00','2026-10-16 08:00'),'reverse timestamps');
$reject(fn()=>WorkTimeCalculator::calculate($schedule,'2026-10-16','2026-10-16 08:00','2026-10-17 01:00'),'overnight');

$equal(['gross_salary'=>'23000000.00','net_salary'=>'22500000.00'],
    PayrollCalculator::calculate('22000000.00','1.000','1000000.00','22.00','22.0000','0.00','500000.00',10560,480),'full month');
$equal('968750.00',PayrollCalculator::calculate('22000000','1','0','22','0.9688','0','0',465,480)['net_salary'],'sum minutes avoids daily rounding');
$equal('300000.00',PayrollCalculator::overtime('100000','1.5',120,'weekday'),'weekday OT');
$equal('400000.00',PayrollCalculator::overtime('100000','2',120,'rest_day'),'rest day OT');
$equal('600000.00',PayrollCalculator::overtime('100000','3',120,'holiday'),'holiday OT');
$equal('0.03',PayrollCalculator::overtime('0.01','1.5',120,'weekday'),'OT cents');
$equal('130681.82',PayrollCalculator::suggestedHourlyRate('22000000','1','1000000',22,480),'suggested hourly basis');
$equal('1.9375',PayrollCalculator::daysFromMinutes(930,480),'sum before rounding');
$equal('0.30',PayrollCalculator::sumMoney(['0.10','0.20']),'decimal addition');
$equal('9999999999999.99',PayrollCalculator::calculate('9999999999999.99','1','0','22','22','0','0',10560,480)['net_salary'],'large exact cents');
$reject(fn()=>PayrollCalculator::overtime('100000','1.499',120,'weekday'),'weekday floor');
$reject(fn()=>PayrollCalculator::overtime('100000','1.5',120,'rest_day'),'rest-day floor');
$reject(fn()=>PayrollCalculator::overtime('100000','2',120,'holiday'),'holiday floor');
$reject(fn()=>PayrollCalculator::calculate('100','1','0','22','22','0','101'),'negative net');
$reject(fn()=>PayrollCalculator::calculate('100','1','0','22','23','0','0'),'above standard');
$reject(fn()=>PayrollCalculator::calculate('9999999999999.99','999.999','0','22','22','0','0'),'overflow');
$reject(fn()=>PayrollCalculator::calculate('100','1','0','0','0','0','0'),'zero denominator');
echo "PASS: ".$checks." calculator checks\n";
