<?php

namespace BookneticApp\Backend\Appointments\Helpers;

use BookneticApp\Models\Location;
use BookneticApp\Models\Service;
use BookneticApp\Models\Staff;
use BookneticApp\Models\ServiceCategory;
use BookneticApp\Models\ServiceStaff;

trait ARDHelper
{
    private $categoryIds = [];

    public function getAvailableLocations()
    {
        $appointmentObj = $this;

        $locations      = Location::where('is_active', 1)->orderBy('id');

        if( $appointmentObj->staffId > 0 )
        {
            $locationsFilter = empty( $appointmentObj->staffInf->locations ) ? [0] : explode( ',', $appointmentObj->staffInf->locations );
            $locations->where('id', $locationsFilter);
        }
        else if( $appointmentObj->serviceId > 0 || $appointmentObj->serviceCategoryId > 0 )
        {
            $locationsFilter    = [];

            if( $appointmentObj->serviceId > 0){
                $serviceIds = [$appointmentObj->serviceId];
            }else{
                $serviceCategories = ServiceCategory::fetchAll();
                $ids = array_unique( array_merge( [ $appointmentObj->serviceCategoryId ], $this->getTree( $appointmentObj->serviceCategoryId , $serviceCategories ) ) );
                $services = Service::where('category_id',$ids )->where('is_active','1')->fetchAll();
                $serviceIds = array_map(function ($service){
                    return $service->id;
                },$services);
            }

            $staffList = ServiceStaff::where( 'service_id', $serviceIds )
                ->leftJoin( Staff::getTableName(), [ 'locations' ] )->where( Staff::getField( 'is_active' ), 1 )
                ->fetchAll();

            foreach ( $staffList AS $staffInf )
            {
                $locationsFilter = array_merge( $locationsFilter, explode(',', $staffInf->staff_locations) );
            }

            $locationsFilter = array_unique( $locationsFilter );
            $locationsFilter = empty( $locationsFilter ) ? [0] : $locationsFilter;

            $locations->where('id', $locationsFilter);
        }

        return $locations;
    }

    private function getTree($id,$items)
    {
        foreach ($items as $item)
        {
            if($item->parent_id == $id )
            {
                $this->categoryIds[] = $item->id;
                $this->getTree($item->id , $items );
            }
        }
        return $this->categoryIds;
    }

}