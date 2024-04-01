<?php

namespace BookneticApp\Providers\Core\Templates;

use BookneticApp\Models\Appearance;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Models\Location;
use BookneticApp\Models\Service;
use BookneticApp\Models\ServiceCategory;
use BookneticApp\Models\ServiceStaff;
use BookneticApp\Models\Staff;
use BookneticApp\Models\Timesheet;
use BookneticApp\Models\Workflow;
use BookneticApp\Models\WorkflowAction;

trait ApplierCreate
{
    /**
     * @return void
     */
    private function createLocations()
    {
        if ( ! $this->isEnabled( 'locations' ) ||  ! $this->get( 'locations' ) )
            return;

        foreach ( $this->get( 'locations' ) as $location )
        {
            list( $location, $oldId ) = $this->modifyRow( $location );

            if ( !! $location[ 'image' ] )
            {
                $location[ 'image' ] = $this->upload( $location[ 'image' ], 'Locations' );
            }

            Location::insert( $location );

            $this->newIds[ 'locations' ][ $oldId ] = Location::lastId();
        }
    }

    /**
     * @return void
     */
    private function createServiceCategories()
    {
        if ( ! $this->isEnabled( 'services' ) || ! $this->get( 'serviceCategories' ) )
            return;

        foreach ( $this->get( 'serviceCategories' ) as $category )
        {
            list( $category, $oldId ) = $this->modifyRow( $category );

            if ( $category[ 'parent_id' ] > 0 )
            {
                $category[ 'parent_id' ] = $this->newIds[ 'serviceCategory' ][ $category[ 'parent_id' ] ];
            }

            ServiceCategory::insert( $category );

            $this->newIds[ 'serviceCategory' ][ $oldId ] = ServiceCategory::lastId();
        }
    }

    /**
     * @return void
     */
    private function createServices()
    {
        if ( ! $this->isEnabled( 'services' ) || ! $this->get( 'services' ) )
            return;

        foreach ( $this->get( 'services' ) as $service )
        {
            list( $service, $oldId ) = $this->modifyRow( $service );

            $service[ 'category_id' ] = $this->newIds[ 'serviceCategory' ][ $service[ 'category_id' ] ];

            if ( !! $service[ 'image' ] )
            {
                $service[ 'image' ] = $this->upload( $service[ 'image' ], 'Services' );
            }

            Service::insert( $service );

            $this->newIds[ 'services' ][ $oldId ] = Service::lastId();
        }
    }

    /**
     * @return void
     */
    private function createStaff()
    {
        if ( ! $this->isEnabled( 'staff' ) || ! $this->get( 'staff' ) )
            return;

        foreach ( $this->get( 'staff' ) as $staff )
        {
            list( $staff, $oldId ) = $this->modifyRow( $staff );

            $staff = $this->modifyStaffLoc( $staff );

            if ( !! $staff[ 'profile_image' ] )
            {
                $staff[ 'profile_image' ] = $this->upload( $staff[ 'profile_image' ], 'Staff' );
            }

            Staff::insert( $staff );

            $this->newIds[ 'staff' ][ $oldId ] = Staff::lastId();
        }
    }

    /**
     * @return void
     */
    private function createServiceStaff()
    {
        if (
            ! $this->isEnabled( 'staff' ) ||
            ! $this->isEnabled( 'services' ) ||
            ! $this->get( 'serviceStaff' )
        )
            return;

        foreach ( $this->get( 'serviceStaff' ) as $sStaff )
        {
            unset( $sStaff[ 'id' ] );

            $sStaff[ 'service_id' ] = $this->newIds[ 'services' ][ $sStaff[ 'service_id' ] ];
            $sStaff[ 'staff_id' ]   = $this->newIds[ 'staff' ][ $sStaff[ 'staff_id' ] ];

            ServiceStaff::insert( $sStaff );
        }
    }

    /**
     * @return void
     */
    private function createWorkflows()
    {
        if ( ! $this->isEnabled( 'workflows' ) || ! $this->get( 'workflows' ) )
            return;

        foreach ( $this->get( 'workflows' ) as $workflow )
        {
            if ( !! $workflow[ 'data' ] )
            {
                $workflow[ 'data' ] = $this->modifyWorkflowData( $workflow[ 'data' ] );
            }

            list( $workflow, $oldId ) = $this->modifyRow( $workflow );

            Workflow::insert( $workflow );

            $this->newIds[ 'workflows' ][ $oldId ] = Workflow::lastId();
        }
    }

    /**
     * @return void
     */
    private function createWorkflowActions()
    {
        if ( ! $this->isEnabled( 'workflows' ) || ! $this->get( 'workflowActions' ) )
            return;

        foreach ( $this->get( 'workflowActions' ) as $action )
        {
            $action[ 'workflow_id' ] = $this->newIds[ 'workflows' ][ $action[ 'workflow_id' ] ];

            WorkflowAction::insert( $action );
        }
    }

    /**
     * @return void
     */
    private function createTimesheets()
    {
        if ( ! $this->isEnabled( 'timesheets' ) || ! $this->get( 'timesheets' ) )
            return;

        foreach ( $this->get( 'timesheets' ) as $timesheet )
        {
            // if the template has a default timesheet reset the timesheet set before
            if ( ! $timesheet[ 'service_id' ] && ! $timesheet[ 'staff_id' ] )
            {
                Timesheet::whereIsNull( 'staff_id' )
                    ->whereIsNull( 'service_id' )
                    ->delete();
            }

            if ( !! $timesheet[ 'service_id' ] )
            {
                //if it's a service timesheet but the services is not going to be applied, skip it
                if ( ! $this->isEnabled( 'services' ) )
                    continue;

                $timesheet[ 'service_id' ] = $this->newIds[ 'services' ][ $timesheet[ 'service_id' ] ];
            }

            if ( !! $timesheet[ 'staff_id' ] )
            {
                //if it's a staff timesheet but the staff is not going to be applied, skip it
                if ( $this->isEnabled( 'staff' ) )
                    continue;

                $timesheet[ 'staff_id' ] = $this->newIds[ 'staff' ][ $timesheet[ 'staff_id' ] ];
            }

            Timesheet::insert( $timesheet );
        }
    }

    /**
     * @return void
     */
    private function createAppearances()
    {
        if ( ! $this->isEnabled( 'appearances' ) || ! $this->get( 'appearances' ) )
            return;

        foreach ( $this->get( 'appearances' ) as $appearance )
        {
            if ( $appearance[ 'is_default' ] > 0 )
            {
                Appearance::where( 'is_default', 1 )->update( [ 'is_default' => 0 ] );
            }

            Appearance::insert( $appearance );
        }
    }

    /**
     * @return void
     */
    private function createSettings()
    {
        if ( ! $this->isEnabled( 'settings' ) || ! $this->get( 'settings' ) )
            return;

        foreach ( $this->get( 'settings' ) as $k => $v )
        {
            Helper::setOption( $k, $v );
        }
    }
}