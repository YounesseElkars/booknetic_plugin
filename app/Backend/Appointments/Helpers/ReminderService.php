<?php

namespace BookneticApp\Backend\Appointments\Helpers;

use BookneticApp\Config;
use BookneticApp\Models\Appointment;
use BookneticApp\Models\Workflow;
use BookneticApp\Providers\Core\Permission;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\Helpers\Helper;
use BookneticSaaS\Models\Tenant;

class ReminderService
{

	public static function run()
	{
        set_time_limit(0);
        self::triggerWorkflows();

        return true;
	}

    public static function triggerWorkflows()
    {
        $workflowActions = [
            'booking_starts',
            'booking_ends'
        ];

        if( Helper::isSaaSVersion() ) $workflowActions[] = 'tenant_notified';

        $backUpTenantId = Permission::tenantId();
        $workflows = Workflow::noTenant()->where('`when`', $workflowActions)
            ->where('is_active', true)
            ->fetchAll();

        foreach ( $workflows as $workflow )
        {
            Permission::setTenantId($workflow->tenant_id);

            $offset = 0; // in minutes
            $status_filter = [];
            $locations_filter = [];
            $service_filter = [];
            $staff_filter = [];
            $locale = null;
            $for_each_customer = true;

            try {
                if (!empty($workflow->data)) {
                    $data = json_decode($workflow->data, true);
                    $offset = $data['offset_value'] * 60;
                    $offset *= $data['offset_sign'] === 'before' ? 1 : -1;

                    if ($data['offset_type'] === 'hour') $offset *= 60;
                    if ($data['offset_type'] === 'day') $offset *= 60 * 24;

                    $status_filter = isset( $data['statuses'] ) ? $data['statuses'] : [];
                    $locations_filter = $data['locations'];
                    $service_filter = $data['services'];
                    $staff_filter = $data['staffs'];
                    $locale = isset( $data[ 'locale' ] ) ? $data[ 'locale' ] : null;
                    if (isset($data['for_each_customer'])) $for_each_customer = $data['for_each_customer'];
                }
            } catch (\Exception $e) {
                $offset = 0;
            }

            if( $workflow -> when == 'tenant_notified' )
            {
                $tenants = Tenant::where( 'expires_in', '>=', date( 'Y-m-d', Date::epoch( 'now', '-5 minutes' ) + $offset ) )
                                 -> where( 'expires_in', '<=', date( 'Y-m-d', Date::epoch( 'now', '+5 minutes' ) + $offset ) )
                                 -> fetchAll()
                ;

                $workflow_actions = $workflow -> workflow_actions() -> where( 'is_active', true ) -> fetchAll();

                foreach( $tenants as $tenant )
                {
                    $alreadyTriggeredWorkflowIDs = json_decode( Tenant::getData( $tenant -> id, 'triggered_cronjob_workflows', '[]' ), true );

                    if( ! in_array( $workflow -> id, $alreadyTriggeredWorkflowIDs ) )
                    {
                        do_action( 'bkntcsaas_tenant_notified', $tenant -> id );

                        $alreadyTriggeredWorkflowIDs[] = $workflow -> id;

                        Tenant::setData( $tenant -> id, 'triggered_cronjob_workflows', json_encode( $alreadyTriggeredWorkflowIDs ), count( $alreadyTriggeredWorkflowIDs ) );
                    }
                }
            }

            else
            {
                if ( $workflow->when === 'booking_ends' )
                {
                    $nearbyAppointments = Appointment::where('ends_at', '>=', Date::epoch('now', '-5 minutes') + $offset)
                        ->where('ends_at', '<=', Date::epoch('now', '+5 minutes') + $offset);
                }
                else
                {
                    $nearbyAppointments = Appointment::where('starts_at', '>=', Date::epoch('now', '-5 minutes') + $offset)
                        ->where('starts_at', '<=', Date::epoch('now', '+5 minutes') + $offset);
                }
    
                if (is_array($locations_filter) && count($locations_filter) > 0)
                {
                    $nearbyAppointments->where('location_id', $locations_filter);
                }
    
                if (is_array($service_filter) && count($service_filter) > 0)
                {
                    $nearbyAppointments->where('service_id', $service_filter);
                }
    
                if (is_array($staff_filter) && count($staff_filter) > 0)
                {
                    $nearbyAppointments->where('staff_id', $staff_filter);
                }
    
                if ( !! $locale )
                {
                    $nearbyAppointments->where( 'locale', $locale );
                }
    
                if (is_array($status_filter) && count($status_filter) > 0)
                {
                    $nearbyAppointments->where('status', $status_filter);
                }
    
                $nearbyAppointments = $nearbyAppointments->fetchAll();
    
                $workflow_actions = $workflow->workflow_actions()->where('is_active', true)->fetchAll();
    
                foreach ($nearbyAppointments as $nearbyAppointment)
                {
                    $alreadyTriggeredWorkflowIDs = json_decode(Appointment::getData($nearbyAppointment->id, 'triggered_cronjob_workflows', '[]'), true);
                    if (in_array($workflow->id, $alreadyTriggeredWorkflowIDs))
                    {
                        continue;
                    }
    
                    Date::resetTimezone();
    
                    $params = [
                        'appointment_id' => $nearbyAppointment->id,
                        'customer_id' => $nearbyAppointment->customer_id,
                        'staff_id' => $nearbyAppointment->staff_id,
                        'location_id' => $nearbyAppointment->location_id,
                        'service_id' => $nearbyAppointment->service_id
                    ];
    
                    foreach ($workflow_actions as $action)
                    {
                        $driver = Config::getWorkflowDriversManager()->get($action['driver']);
                        if ( !empty($driver) )
                        {
                            $action->when = $workflow->when;
                            $driver->handle($params, $action, Config::getShortCodeService() );
                        }
                    }
    
                    $alreadyTriggeredWorkflowIDs[] = $workflow->id;
                    Appointment::setData($nearbyAppointment->id, 'triggered_cronjob_workflows', json_encode($alreadyTriggeredWorkflowIDs), count($alreadyTriggeredWorkflowIDs) > 1);
                }
            }
        }

        Permission::setTenantId($backUpTenantId);
    }

}
