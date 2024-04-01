<?php

namespace BookneticApp\Backend\Workflow;
use BookneticApp\Config;
use BookneticApp\Models\Workflow;
use BookneticApp\Models\WorkflowAction;
use BookneticApp\Providers\Common\WorkflowEventsManager;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Core\Route;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\UI\DataTableUI;

class Controller extends \BookneticApp\Providers\Core\Controller
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

    public function index()
    {
        Capabilities::must('workflow');

        $workflow  = new Workflow();
        $dataTable = new DataTableUI( $workflow );

        $dataTable->addAction('enable', bkntc__('Enable'), function ($IDs) {
            Workflow::where('id', 'in', $IDs)->update(['is_active' => 1]);
        }, DataTableUI::ACTION_FLAG_BULK);

        $dataTable->addAction('disable', bkntc__('Disable'), function ($IDs) {
            Workflow::where('id', 'in', $IDs)->update(['is_active' => 0]);
        }, DataTableUI::ACTION_FLAG_BULK);

        $dataTable->addAction('delete', bkntc__('Delete'), function ($IDs) {
            WorkflowAction::where('workflow_id', $IDs)->delete();
            Workflow::where('id', $IDs)->delete();
        }, DataTableUI::ACTION_FLAG_SINGLE | DataTableUI::ACTION_FLAG_BULK);

        $dataTable->setTitle(bkntc__('Workflows'));
        $dataTable->addNewBtn(bkntc__('CREATE NEW WORKFLOW'));

        $dataTable->searchBy( [ 'id', 'name' ] );

        $dataTable->addColumns(bkntc__('ID'), 'id', [ 'order_by_field' => 'is_active' ]);
        $dataTable->addColumns(bkntc__('NAME'), 'name');
        $dataTable->addColumns(bkntc__('EVENT'), function ( $workflow ){
            return $this->workflowEventsManager->get( $workflow->when )->getTitle();
        });
        $dataTable->addColumns(bkntc__('ACTION(S)'), function( $workflow ) {
            $actions = WorkflowAction::where('workflow_id', $workflow->id)->select('driver, COUNT(*) as count')->groupBy(['driver'])->fetchAll();
            $doThis = "";
            foreach ( $actions as $action )
            {
                $countString = $action->count > 1 ? ' <span class="btn btn-xs btn-light-warning">x'.$action->count.'</span>' : '';
                $doThis .= ! is_null( $this->workflowDriversManager->get( $action->driver ) ) ? $this->workflowDriversManager->get( $action->driver )->getName().$countString."<br>" : "";
            }
           return "<div class='mt-2 mb-2'>" . rtrim( $doThis, "<br>" ) . "</div>";
        }, ['is_html' => true]);

        $eventsFilter = [];
        foreach ($this->workflowEventsManager->getAll() as $key => $event)
        {
            $eventsFilter[$key] = $event->getTitle();
        }
        $dataTable->addFilter(Workflow::getField('when'), 'select', bkntc__('Event Type'), '=', [
            'list' => $eventsFilter
        ], 4);

        $table = $dataTable->renderHTML();

        add_filter('bkntc_localization' , function ($localization){
            $localization['Edit'] = bkntc__('Edit');
            return $localization;
        });

        $this->view( 'index', ['table' => $table] );
    }

    public function edit()
    {
        Capabilities::must('workflow_edit');

        $workflowId = Helper::_get('workflow_id', null, 'int');

        if( $workflowId > 0 )
        {
            $workflowInf = Workflow::where('id', $workflowId)->fetch();

            if( !$workflowInf )
            {
                Helper::redirect( Route::getURL( 'workflow' ) );
                exit();
            }

            $workflowActions = $workflowInf->workflow_actions()->fetchAll();

            $events = $this->workflowEventsManager->getAll();

            add_filter('bkntc_localization' , function ($localization){
                $localization['saved_changes'] = bkntc__('Saved changes');
                $localization['SEND'] = bkntc__('SEND');
                $localization['CLOSE'] = bkntc__('CLOSE');
                return $localization;
            });
            $this->view( 'edit', [
                'id' => $workflowId,
                'actions' => $workflowActions,
                'workflow_info' => $workflowInf,
                'events' => $events,
                'events_manager' => $this->workflowEventsManager
            ] );

        }
    }

}
