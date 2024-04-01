<?php


namespace BookneticApp\Providers\Common;


class WorkflowDriversManager
{
    /**
     * @var WorkflowDriver[]
     */
    private $drivers = [];

    public function register( $driverInstance )
    {
        $driver = $driverInstance->getDriver();

        $this->drivers[ $driver ] = $driverInstance;
    }

    /**
     * @param string $driver
     * @return WorkflowDriver|null
     */
    public function get( $driver )
    {
        return array_key_exists( $driver, $this->drivers ) ? $this->drivers[ $driver ] : null;
    }

    /**
     * @return WorkflowDriver[]
     */
    public function getList()
    {
        return $this->drivers;
    }


}