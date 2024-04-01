<?php

namespace BookneticApp\Backend\Locations;

use BookneticApp\Models\Appointment;
use BookneticApp\Models\Location;
use BookneticApp\Models\Staff;
use BookneticApp\Providers\UI\DataTableUI;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\DB\DB;
use BookneticApp\Providers\Helpers\Helper;

class Controller extends \BookneticApp\Providers\Core\Controller
{

	public function index()
	{
		Capabilities::must( 'locations' );

		$dataTable = new DataTableUI( new Location() );

        $dataTable->addAction('enable', bkntc__('Enable'),  function ( $ids ){
            Location::where('id' , 'in' , $ids )->update([ 'is_active' => 1 ]);
        }, DataTableUI::ACTION_FLAG_BULK);
        $dataTable->addAction('disable', bkntc__('Disable'), function ( $ids ){
            Location::where('id' , 'in' , $ids )->update([ 'is_active' => 0 ]);
        }, DataTableUI::ACTION_FLAG_BULK);

        $dataTable->addAction('edit', bkntc__('Edit'));

        $dataTable->addAction('delete', bkntc__('Delete'), [static::class, '_delete'], DataTableUI::ACTION_FLAG_SINGLE | DataTableUI::ACTION_FLAG_BULK);
        $dataTable->addAction('share', bkntc__('Share') );

		$dataTable->setTitle(bkntc__('Locations'));
		$dataTable->addNewBtn(bkntc__('ADD LOCATION'));
		$dataTable->activateExportBtn();

		$dataTable->searchBy(["name", 'address', 'phone_number', 'notes']);

		$dataTable->addColumns(bkntc__('ID'), 'id');

		$dataTable->addColumns(bkntc__('NAME'), function( $location )
		{
			return Helper::profileCard( $location['name'], $location['image'], '', 'Locations' );
		}, ['is_html' => true, 'order_by_field' => "name"]);

		$dataTable->addColumns(bkntc__('PHONE'), 'phone_number');
		$dataTable->addColumns(bkntc__('ADDRESS'), 'address');

		$table = $dataTable->renderHTML();

        add_filter('bkntc_localization', function ($localization)
        {
            $localization['link_copied'] = bkntc__('Link copied!');
            return $localization;
        });

		$this->view( 'index', ['table' => $table] );
	}

	public static function _delete( $ids )
	{
		Capabilities::must( 'locations_delete' );

		foreach ( $ids AS $id )
		{
			$checkAppointments = Appointment::where('location_id', $id)->fetch();
            $checkStaff = Staff::where('locations', $id)->fetch();

			if( $checkAppointments )
			{
				Helper::response(false, bkntc__('This location has some appointments scheduled. Please remove them first!'));
			}

            if( $checkStaff )
            {
                Helper::response(false, bkntc__('There are some staff members currently using this location. Please remove them first!'));
            }

			DB::DB()->query( DB::DB()->prepare("UPDATE `".DB::table('staff')."` SET locations=TRIM(BOTH ',' FROM REPLACE(CONCAT(',',`locations`,','),%s,',')) WHERE FIND_IN_SET(%d, `locations`)", [",{$id},", $id]) );

            Location::where('id', $id)->delete();
        }


	}

}
