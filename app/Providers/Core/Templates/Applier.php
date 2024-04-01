<?php

namespace BookneticApp\Providers\Core\Templates;

use BookneticApp\Models\Appearance;
use BookneticApp\Models\Location;
use BookneticApp\Models\Service;
use BookneticApp\Models\ServiceCategory;
use BookneticApp\Models\ServiceStaff;
use BookneticApp\Models\Staff;
use BookneticApp\Models\Timesheet;
use BookneticApp\Models\Workflow;
use BookneticApp\Models\WorkflowAction;
use BookneticApp\Providers\Core\FSCodeAPI;
use BookneticApp\Providers\Core\Permission;
use BookneticApp\Providers\Helpers\Helper;

class Applier
{
    use Data, ApplierCreate;

    /**
     * @var int $reservedTenantId
     */
    private static $reservedTenantId;

    /**
     * @var $fromServer bool
    */
    private $fromServer;

    /**
     * @var array[]
     */
    private $newIds = [
        'locations'       => [],
        'services'        => [],
        'staff'           => [],
        'serviceCategory' => [],
        'workflows'       => []
    ];

    /**
     * @param array $template
     */
    public function __construct( $template )
    {
        $this->data       = json_decode( $template[ 'data' ], true );
        $this->fromServer = $template[ 'from_server' ];
    }

    /**
     * @param array $templates
     * @return void
     */
    public static function applyMultiple( $templates )
    {
        //reset initial data of the user
        self::reset();


        //apply default templates one by one
        foreach ( $templates as $template )
        {
            $applier = new static( $template );

            $applier->apply();
        }
    }

    /**
     * @param int $id
     * @return void
     */
    public static function setTenantId( $id )
    {
        self::$reservedTenantId = Permission::tenantId();

        Permission::setTenantId( $id );
    }

    /**
     * @return void
     */
    public static function unsetTenantId()
    {
        Permission::setTenantId( self::$reservedTenantId );
    }

    /**
     * @return void
     */
    private static function reset()
    {
        ServiceStaff::where( 'staff_id', Staff::select( 'id' ) )->delete();
        Staff::delete();

        Service::delete();
        ServiceCategory::delete();

        Location::delete();

        WorkflowAction::where( 'workflow_id', Workflow::select( 'id' ) )->delete();
        Workflow::delete();

        Timesheet::delete();

        Appearance::delete();
    }

    /**
     * @return void
     */
    public function apply()
    {
        $this->createLocations();

        $this->createServiceCategories();
        $this->createServices();

        $this->createStaff();
        $this->createServiceStaff();

        $this->createWorkflows();
        $this->createWorkflowActions();

        $this->createTimesheets();

        $this->createAppearances();

        $this->createSettings();

        do_action( 'bkntc_template_apply_template', $this );
    }

    /**
     * @param string $key
     * @return boolean
     */
    public function isEnabled( $key )
    {
        $columns = $this->get( 'columns' );

        if ( isset( $columns[ $key ] ) )
            return $columns[ $key ];

        return false;
    }

    /*----------------------------MODIFIERS----------------------------*/

    /**
     * @param array $row
     * @return array
     */
    private function modifyRow( $row )
    {
        $oldId = $row[ 'id' ];

        unset( $row[ 'id' ] );

        return [ $row, $oldId ];
    }

    /**
     * @param array $staff
     * @return array
     */
    private function modifyStaffLoc( $staff )
    {
        $oldLocations = explode( ',', $staff[ 'locations' ] );
        $newLocations = [];

        foreach ( $oldLocations as $oldLocId )
        {
            $newLocations[] = $this->newIds[ 'locations' ][ $oldLocId ];
        }

        $staff[ 'locations' ] = implode( ',', $newLocations );

        return $staff;
    }

    /**
     * @param string $strData
     * @return string
     */
    private function modifyWorkflowData( $strData )
    {
        $data = json_decode( $strData, true );

        foreach ( $data as $k => $datum )
        {
            //the data we are going to modify stored as an array inside the $data
            if ( ! is_array( $datum ) )
                continue;

            //check if it's one of the data fields we are supposed to change
            if ( ! isset( $this->newIds[ $k ] ) )
                continue;

            //ignore the data if it's empty
            if ( empty( $datum ) )
                continue;

            //update datum with the modified value
            $data[ $k ] = $this->modifyWorkflowDataOldIds( $datum );
        }

        return  json_encode( $data );
    }

    /**
     * updates old ids of the given data to the newly created ones
     * @param array $data
     * @return array
     */
    private function modifyWorkflowDataOldIds( $data )
    {
        foreach ( $data as $k => $v )
        {
            $data[ $k ] = $this->newIds[ 'locations' ][ $v ];
        }

        return $data;
    }

    /**
     * @param string $image
     * @param string $module
     * @return string
     */
    public function upload( $image, $module )
    {
        if ( Helper::isSaaSVersion() && ! $this->fromServer )
        {
            return apply_filters( 'bkntc_template_upload_image', $image, $module );
        }

        $rand    = md5( base64_encode(rand( 1, 9999999 ) . microtime(true ) ) );
        $newName = $rand . '.' . pathinfo( $image, PATHINFO_EXTENSION );
        $newPath = Helper::uploadedFile( $newName, $module );

        FSCodeAPI::uploadFileFromName( sprintf( '%s/%s', $module, $image ), $newPath );

        return $newName;
    }
}
