<?php

namespace BookneticApp\Backend\Dashboard\Helpers;


use BookneticApp\Models\Appointment;
use BookneticApp\Providers\DB\DB;
use BookneticApp\Providers\Helpers\Date;

class UIHelper
{
    public static function renderGraph( $startDate , $endDate )
    {
        $appointments = Appointment::select([Appointment::getField('starts_at') , 'sum('.Appointment::getField('weight').') as count'] ,true)
                                   ->groupBy(DB::field('FROM_UNIXTIME('.Appointment::getField('starts_at').', "%d-%m-%Y")'))
                                   ->fetchAll();

        $days = [] ;
        $maxCount = 0 ;
        foreach ($appointments as $appointment)
        {
            $date = Date::dateSQL($appointment->starts_at);
            $days[$date] = ['count'=>$appointment->count];
            if ( $appointment->count > $maxCount )
            {
                $maxCount = $appointment->count;
            }
        }

        $parameters = [
            'days'=>$days,
            'start_day' => $startDate,
            'end_day'   => $endDate,
            'max_count' =>$maxCount,
        ];


        require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'modal' . DIRECTORY_SEPARATOR . "svg_tpl.php";
    }
}