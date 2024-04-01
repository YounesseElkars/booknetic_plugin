<?php

namespace BookneticApp\Backend\Workflow;


use BookneticApp\Config;
use BookneticApp\Models\Location;
use BookneticApp\Models\Service;
use BookneticApp\Models\Staff;
use BookneticApp\Models\Workflow;
use BookneticApp\Models\WorkflowAction;
use BookneticApp\Providers\Common\WorkflowEventsManager;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Helpers\Helper;

class Ajax extends \BookneticApp\Providers\Core\Controller
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

    public function add_new()
    {
        Capabilities::must('workflow_add');
        $drivers = $this->workflowDriversManager->getList();
        $events = $this->workflowEventsManager->getAll();

        return $this->modalView('add_new', [
            'drivers' => $drivers,
            'events' => $events
        ]);
    }

    public function add_new_action()
    {
        $drivers = $this->workflowDriversManager->getList();
        return $this->modalView('add_new_action', [
            'drivers' => $drivers
        ]);

    }

    public function create_workflow()
    {
        $workflowName				= Helper::_post('workflow_name', '', 'string');
        $workflowEvent				= Helper::_post('when', '', 'string');
        $workflowAction				= Helper::_post('do_this', '', 'string');
        $workflowIsActive			= Helper::_post('is_active', 0, 'int');


        if( $this->workflowDriversManager->get( $workflowAction ) && array_key_exists( $workflowEvent, $this->workflowEventsManager->getAll() ) )
        {
            $sqlDataWorkflow = [
                'name'	=>	$workflowName,
                'when'  =>	$workflowEvent,
                'is_active' => $workflowIsActive
            ];

           Workflow::insert( $sqlDataWorkflow );

           $workflowId = Workflow::lastId();

            $sqlDataWorkflowAction = [
                'workflow_id'	=>	$workflowId,
                'driver'  =>	$workflowAction,
                'is_active' => 1
            ];

           WorkflowAction::insert(  $sqlDataWorkflowAction );

           return $this->response(true, [
               'workflow_id' => $workflowId
           ] );
        }
        else
        {
            return $this->response(false );
        }

    }

    public function create_new_action()
    {
        $actionDriver	= Helper::_post('action_driver', '', 'string');
        $workflowId		= Helper::_post('workflow_id', '', 'int');


        if( $this->workflowDriversManager->get( $actionDriver ) && $workflowId > 0 )
        {
            $sqlData = [
                'driver'	   =>	$actionDriver,
                'workflow_id'  =>	$workflowId,
                'is_active'    =>   1
            ];

            WorkflowAction::insert(  $sqlData );

            $insertedActionId = WorkflowAction::lastId();

            $driverEditAction = $this->workflowDriversManager->get( $actionDriver )->getEditAction();

            return $this->response(true, [
                'action_id' => $insertedActionId,
                'edit_action' => $driverEditAction
            ] );
        }
        else
        {
            return $this->response(false );
        }

    }

    public function get_action_list_view()
    {
        $workflowId = Helper::_post('workflow_id', 0, 'int');

        if( $workflowId > 0 )
        {
            $workflowActions = WorkflowAction::where('workflow_id', $workflowId)->fetchAll();

            return $this->modalView( 'action_list_view', [
                'actions' => $workflowActions,
                'events_manager' => $this->workflowEventsManager
            ] );

        }
    }

    public function delete_action()
    {
        $id		= Helper::_post('id', '', 'int');

        if(  $id > 0 )
        {
            WorkflowAction::where('id', $id )->delete();
            return $this->response(true );
        }
        else
        {
            return $this->response(false );
        }

    }

    public function save_workflow()
    {
        $workflowId     = Helper::_post('id', -1, 'num');
        $name           = Helper::_post('name', '', 'str');
        $is_active      = Helper::_post('is_active', 1, 'num');

        Workflow::where('id', $workflowId)->update(['name' => $name, 'is_active' => $is_active]);

        return $this->response(true);
    }

}
