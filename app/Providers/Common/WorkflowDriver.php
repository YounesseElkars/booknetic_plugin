<?php

namespace BookneticApp\Providers\Common;

class WorkflowDriver
{
	protected $driver;
	protected $name;
	protected $editAction;

	public function setName( $name )
	{
		$this->name = $name;

		return $this;
	}

	public function setEditAction( $route, $action )
	{
		$this->editAction = $route . '.' . $action;

		return $this;
	}

	public function getDriver()
	{
		return $this->driver;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getEditAction()
	{
		return $this->editAction;
	}

    /**
     * Override this method to process event
     * @param $eventData
     * @param $actionSettings
     * @param ShortCodeService $shortCodeService
     * @return void
     */
    public function handle($eventData, $actionSettings, $shortCodeService ) {}

}
