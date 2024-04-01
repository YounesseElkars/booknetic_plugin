<?php

namespace BookneticApp\Providers\Common;

use BookneticApp\Models\Workflow;

class WorkflowEvent
{

    private $key;
    private $title;

    private $editAction;
    private $availableParams;

    public function __construct( $key )
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    public function setTitle( $title )
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setEditAction($route, $action)
    {
        $this->editAction = $route . '.' . $action;

        return $this;
    }

    public function getEditAction()
    {
        return $this->editAction;
    }

    public function setAvailableParams($params)
    {
        $this->availableParams = $params;

        return $this;
    }

    public function getAvailableParams()
    {
        return $this->availableParams;
    }

}
