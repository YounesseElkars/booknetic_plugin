<?php

namespace BookneticApp\Backend\Staff;

use BookneticApp\Models\Appointment;
use BookneticApp\Models\Data;
use BookneticApp\Models\Holiday;
use BookneticApp\Models\ServiceStaff;
use BookneticApp\Models\SpecialDay;
use BookneticApp\Models\Staff;
use BookneticApp\Models\Timesheet;
use BookneticApp\Providers\UI\DataTableUI;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\DB\DB;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Core\Permission;

class Controller extends \BookneticApp\Providers\Core\Controller
{

	public function index()
	{
		Capabilities::must( 'staff' );

		$dataTable = new DataTableUI( new Staff() );

        $dataTable->addAction('enable', bkntc__('Enable'),  function ( $ids ){
            Staff::where('id' , 'in' , $ids )->update([ 'is_active' => 1 ]);
        }, DataTableUI::ACTION_FLAG_BULK);
        $dataTable->addAction('disable', bkntc__('Disable'), function ( $ids ){
            Staff::where('id' , 'in' , $ids )->update([ 'is_active' => 0 ]);
        }, DataTableUI::ACTION_FLAG_BULK);

        $dataTable->addAction('edit', bkntc__('Edit'));
        $dataTable->addAction('delete', bkntc__('Delete'), [static::class , '_delete'], DataTableUI::ACTION_FLAG_SINGLE | DataTableUI::ACTION_FLAG_BULK );
        $dataTable->addAction('share', bkntc__('Share') );

        $dataTable->setTitle(bkntc__('Staff'));

        if( Permission::isAdministrator() || Capabilities::userCan('staff_add') )
		{
			$dataTable->addNewBtn(bkntc__('ADD STAFF'));
		}

		$dataTable->searchBy(["name", 'email', 'phone_number']);

		$dataTable->addColumns(bkntc__('ID'), 'id');
		$dataTable->addColumns(bkntc__('STAFF NAME'), function( $staff )
		{
			return Helper::profileCard( $staff['name'], $staff['profile_image'], '', 'staff' );
		}, ['is_html' => true, 'order_by_field' => "name"]);
		$dataTable->addColumns(bkntc__('EMAIL'), 'email');
		$dataTable->addColumns(bkntc__('PHONE'), 'phone_number');

		$table = $dataTable->renderHTML();

		$edit = Helper::_get('edit', '0', 'int');

        add_filter('bkntc_localization', function ($localization)
        {
            $localization['delete_associated_wordpress_account'] = bkntc__('Delete associated WordPress account');
            $localization['link_copied'] = bkntc__('Link copied!');
            return $localization;
        });

		$this->view( 'index', [
			'table' => $table,
			'edit'	=> $edit
		] );
	}

	public static function _delete( $ids )
	{
        $deleteWpUser = Helper::_post('delete_wp_user', 1, 'int');
        $deleteWpUser = $deleteWpUser == 1 && ( Permission::isAdministrator() || Capabilities::userCan( 'staff_delete_wordpress_account' ) );

		if( !( Permission::isAdministrator() || Capabilities::userCan( 'staff_delete' ) ) )
		{
			Helper::response(false, bkntc__('You do not have sufficient permissions to perform this action'));
		}

		foreach ( $ids AS $id )
		{
			// check if appointment exist
			$checkAppointments = Appointment::where( 'staff_id', $id )->fetch();

			if( $checkAppointments )
			{
				Helper::response(false, bkntc__('This staff has been added some Appointments. Firstly remove them!'));
			}

			$staffInf = Staff::get( $id );
			if( $staffInf->user_id > 0 )
			{
				$userData = get_userdata( $staffInf->user_id );
				if( $userData && in_array( 'booknetic_staff', $userData->roles ) )
				{
					require_once ABSPATH.'wp-admin/includes/user.php';
                    if ( $deleteWpUser )
                    {
                        wp_delete_user( $staffInf->user_id );
                    }
                    else
                    {
                        $userData->remove_role('booknetic_staff');
                    }
				}
			}

			ServiceStaff::where('staff_id' , $id )->delete();
			Holiday::where('staff_id' , $id )->delete();
			SpecialDay::where('staff_id' , $id )->delete();
			Timesheet::where('staff_id' , $id )->delete();
            Data::where('table_name', 'staff')->where('row_id', $id)->delete();
            Staff::where( 'id', $id )->delete();
		}
	}

}
