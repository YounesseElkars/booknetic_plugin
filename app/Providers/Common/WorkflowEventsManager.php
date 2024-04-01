<?php


namespace BookneticApp\Providers\Common;


use BookneticApp\Models\Workflow;

class WorkflowEventsManager
{
    /**
     * @var WorkflowEvent[]
     */
    private $workflowEvents = [];

    /**
     * @var bool
     */
    private $isEnabled = true;

    /**
     * @var ShortCodeService
     */
    private $shortcodeService;

    /**
     * @var WorkflowDriversManager
     */
    private $driverManager;

    /**
     * @return ShortCodeService
     */
    public function getShortcodeService()
    {
        return $this->shortcodeService;
    }

    /**
     * @return WorkflowDriversManager
     */
    public function getDriverManager()
    {
        return $this->driverManager;
    }

    /**
     * @param ShortCodeService $shortcodeService
     */
    public function setShortcodeService($shortcodeService)
    {
        $this->shortcodeService = $shortcodeService;
    }

    /**
     * @param WorkflowDriversManager $driverManager
     */
    public function setDriverManager($driverManager)
    {
        $this->driverManager = $driverManager;
    }

    /**
     * Enable/disable all workflow events completely.
     * Returns previous state.
     * @param $enabled
     * @return bool
     */
    public function setEnabled($enabled)
    {
        $previousValue = $this->isEnabled();
        $this->isEnabled = $enabled;
        return $previousValue;
    }

    public function isEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * @param $key
     * @param $instance
     * @return WorkflowEvent
     */
    public function register( $key , $instance )
    {
        $this->workflowEvents[$key] = $instance;
        return $this->workflowEvents[$key];
    }

    /**
     * @param $key
     * @return WorkflowEvent
     */
    public function get( $key )
    {
        if( ! array_key_exists( $key , $this->workflowEvents) )
        {
            $this->workflowEvents[$key] = new WorkflowEvent( $key );
        }
        return $this->workflowEvents[$key];
    }

    /**
     * @return WorkflowEvent[]
     */
    public function getAll()
    {
        return $this->workflowEvents;
    }


    public function trigger( $eventKey, $params, $filterClosure = false, $noTenant = false, $tenant_id = null)
    {
        if ($this->isEnabled() === false)
            return;

        if (!array_key_exists($eventKey, $this->workflowEvents))
            return;

        $workflows = Workflow::where('`when`', $eventKey)
            ->where('is_active', true)
            ->noTenant($noTenant);

        if ($tenant_id !== null)
        {
            $workflows->where('tenant_id', $tenant_id);
        }

        $workflows = $workflows->fetchAll();

        if ( is_callable($filterClosure) )
        {
            $workflows = array_filter($workflows, $filterClosure);
        }

        foreach ($workflows as $workflow)
        {
            /**
             * @var Workflow $workflow
             */

            $actions = $workflow->workflow_actions()->where('is_active', true)->fetchAll();
            foreach ($actions as $action)
            {
                $driver = $this->getDriverManager()->get($action['driver']);
                if ( !empty($driver) )
                {
                    $action->when = $workflow->when;
                    $driver->handle($params, $action, $this->getShortcodeService() );
                }
            }
        }
    }


}