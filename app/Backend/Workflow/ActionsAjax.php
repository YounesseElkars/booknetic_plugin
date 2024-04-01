<?php

namespace BookneticApp\Backend\Workflow;


use BookneticApp\Models\Workflow;
use BookneticApp\Models\WorkflowAction;
use BookneticApp\Providers\Common\WorkflowEventsManager;
use BookneticApp\Providers\Helpers\Helper;

class ActionsAjax extends \BookneticApp\Providers\Core\Controller
{
    private $workflowDriversManager;

    private $workflowEventsManager;

    /**
     * @param WorkflowEventsManager $workflowEventsManager
     */
    public function __construct($workflowEventsManager)
    {
        $this->workflowEventsManager = $workflowEventsManager;
        $this->workflowDriversManager = $workflowEventsManager->getDriverManager();
    }


    public function set_booking_status_view()
    {
        $id = Helper::_post( 'id', 0, 'int' );

        $workflowActionInfo = WorkflowAction::get( $id );

        if ( ! $workflowActionInfo )
        {
            return $this->response( false );
        }

        $data = json_decode($workflowActionInfo->data, true);

        $availableParams = $this->workflowEventsManager->get(Workflow::get($workflowActionInfo->workflow_id)['when'])
            ->getAvailableParams();

        $idShortcodes = $this->workflowEventsManager->getShortcodeService()->getShortCodesList($availableParams, ['appointment_id']);

        $IDS = [];
        foreach ($idShortcodes as $shortcode)
        {
            $IDS[ '{'. $shortcode['code'] . '}' ] = [
                'name'      => $shortcode['name'],
                'selected'  => false
            ];
        }
        $selectedIds = isset($data['appointment_ids']) ? explode(',',   $data['appointment_ids']) : [];
        foreach ($selectedIds as $ID)
        {
            if (empty($ID)) continue;

            if (array_key_exists($ID, $IDS))
            {
                $IDS[$ID]['selected'] = true;
            } else {
                $IDS[$ID] = [
                    'selected' => true,
                    'name' => $ID
                ];
            }
        }

        return $this->modalView( 'action_set_booking_status', [
            'action_info'           => $workflowActionInfo,
            'appointment_ids'       => $IDS,
            'status'                => isset($data['status']) ? $data['status'] : '',
            'run_workflows'         => isset($data['run_workflows']) ? $data['run_workflows'] : true
        ], [
            'workflow_action_id' => $id,
        ] );
    }

    public function set_booking_status_save()
    {
        $id                 = Helper::_post( 'id', 0, 'int' );
        $is_active          = Helper::_post( 'is_active', 1, 'int' );
        $appointment_ids    = Helper::_post( 'appointment_ids', '', 'string' );
        $status             = Helper::_post( 'status', '', 'string' );
        $run_workflows      = Helper::_post( 'run_workflows', '', 'num' );

        $checkWorkflowActionExist = WorkflowAction::get( $id );

        if ( ! $checkWorkflowActionExist )
        {
            return $this->response( false );
        }

        $newData = [
            'appointment_ids' => $appointment_ids,
            'status' => $status,
            'run_workflows' => $run_workflows == 1 ? true : false
        ];

        WorkflowAction::where( 'id', $id )->update( [
            'data' => json_encode( $newData ),
            'is_active' => $is_active
        ] );

        return $this->response( true );
    }

}
