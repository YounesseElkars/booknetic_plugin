<?php

namespace BookneticApp\Providers\Helpers;

use BookneticApp\Models\WorkflowLog;
use BookneticApp\Providers\Core\Permission;

class WorkflowHelper
{
    public static function getUsage( string $driverName ): int
    {
        $startDatetime = Date::dateTimeSQL( WorkflowLog::getData( Permission::tenantId(), 'subscription_datetime' ) );

        return WorkflowLog::where( 'driver', $driverName )
            ->where( 'date_time', '>=', $startDatetime )
            ->count();
    }
}