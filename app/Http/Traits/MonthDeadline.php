<?php
namespace App\Http\Traits;

use App\Models\Setting;

trait MonthDeadline {

    public $days_deadline = 2;

    public function __construct() {
        $setting = Setting::find(1);
        $this->days_deadline = $setting->mcp_deadline;
    }

    // get the deadline of the month
    public function getMonthDeadline($year, $month) {
        // check month format
        $month = $month < 10 ? '0'.(int)$month : $month;
        // get the end of the month
        $month_end_date = date('Y-m-t', strtotime($year.'-'.$month.'-01'));
        // set subtract days string
        $no_of_day_string = $this->days_deadline > 1 ? $this->days_deadline.' days' : $this->days_deadline.' day';
        // subtract days deadline from end of month date
        $date = date_create($month_end_date);
        date_sub($date, date_interval_create_from_date_string($no_of_day_string));

        // 0 => sunday
        // 6 => saturday
        // check if Saturday or Sunday
        while(date('w', strtotime(date_format($date, 'Y-m-d'))) == 0 || date('w', strtotime(date_format($date, 'Y-m-d'))) == 6) {
            // if yes subtract 1 day
            date_sub($date, date_interval_create_from_date_string('1 day'));
        }

        return date_format($date, 'Y-m-d');
    }

    public function getDateDeadline($date) {
        // set subtract days string
        $no_of_day_string = $this->days_deadline > 1 ? $this->days_deadline.' days' : $this->days_deadline.' day';
        // subtract days deadline from end of month date
        $date = date_create($date);
        date_sub($date, date_interval_create_from_date_string($no_of_day_string));

        // 0 => sunday
        // 6 => saturday
        // check if Saturday or Sunday
        while(date('w', strtotime(date_format($date, 'Y-m-d'))) == 0 || date('w', strtotime(date_format($date, 'Y-m-d'))) == 6) {
            // if yes subtract 1 day
            date_sub($date, date_interval_create_from_date_string('1 day'));
        }

        return date_format($date, 'Y-m-d');
    }

    public function getDeadlineCount($deadline) {
        // get current date
        $current_datetime = time();
        // convert date
        $deadline_datetime = strtotime($deadline);
        $datediff = $deadline_datetime - $current_datetime;
        // seconds in a day
        $day = 60 * 60 * 24;
        
        return round($datediff / $day);
    }
}