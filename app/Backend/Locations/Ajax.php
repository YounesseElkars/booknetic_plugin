<?php

namespace BookneticApp\Backend\Locations;

use BookneticApp\Models\Location;
use BookneticApp\Providers\Core\Backend;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\DB\DB;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Core\Permission;
use BookneticApp\Providers\UI\TabUI;

class Ajax extends \BookneticApp\Providers\Core\Controller
{

	public function add_new()
	{
		$lid = Helper::_post('id', '0', 'integer');

		if( $lid > 0 )
		{
			Capabilities::must( 'locations_edit' );

			$locationInfo = Location::get( $lid );
		}
		else
		{
			Capabilities::must( 'locations_add' );
			$allowedLimit = Capabilities::getLimit( 'locations_allowed_max_number' );

			if( $allowedLimit > -1 && Location::count() >= $allowedLimit )
			{
				$view = Helper::renderView('Base.view.modal.permission_denied', [
					'text' => bkntc__('You can\'t add more than %d Location. Please upgrade your plan to add more Location.', [ $allowedLimit ] )
				]);

				return $this->response( true, [ 'html' => $view ] );
			}

			$locationInfo = [
				'id'                =>  null,
				'name'              =>  null,
				'image'             =>  null,
				'address'           =>  null,
				'phone_number'      =>  null,
				'notes'             =>  null,
				'latitude'          =>  null,
				'longitude'         =>  null,
				'is_active'         =>  null
			];
		}

        TabUI::get( 'locations_add_new' )
            ->item( 'details' )
            ->setTitle( bkntc__( 'Location Details' ) )
            ->addView(__DIR__ . '/view/tab/add_new_location_details.php')
            ->setPriority( 1 );

		return $this->modalView('add_new', ['location' => $locationInfo]);
	}

	public function save_location()
	{
		$id				=	Helper::_post('id', '0', 'integer');

		if( $id > 0 )
		{
			Capabilities::must( 'locations_edit' );
		}
		else
		{
			Capabilities::must( 'locations_add' );
		}

		$location_name	=	Helper::_post('location_name', '', 'string');
		$address		=	Helper::_post('address', '', 'string');
		$phone			=	Helper::_post('phone', '', 'string');
		$note			=	Helper::_post('note', '', 'string');
		$latitude		=	Helper::_post('latitude', '', 'string');
		$longitude		=	Helper::_post('longitude', '', 'string');

		if( $id <= 0 )
		{
			$allowedLimit = Capabilities::getLimit( 'locations_allowed_max_number' );

			if( $allowedLimit > -1 && Location::count() >= $allowedLimit )
			{
				return $this->response( false, bkntc__('You can\'t add more than %d Location. Please upgrade your plan to add more Location.', [ $allowedLimit ] ) );
			}
		}

		if( empty($location_name) )
		{
			return $this->response(false, bkntc__('Please fill in all required fields correctly!'));
		}

		$image = '';

		if( isset($_FILES['image']) && is_string($_FILES['image']['tmp_name']) )
		{
			$path_info = pathinfo($_FILES["image"]["name"]);
			$extension = strtolower( $path_info['extension'] );

			if( !in_array( $extension, ['jpg', 'jpeg', 'png'] ) )
			{
				return $this->response(false, bkntc__('Only JPG and PNG images allowed!'));
			}

			$image = md5( base64_encode(rand(1,9999999) . microtime(true)) ) . '.' . $extension;
			$file_name = Helper::uploadedFile( $image, 'Locations' );

			move_uploaded_file( $_FILES['image']['tmp_name'], $file_name );
		}

		$sqlData = [
			'name'			=>	$location_name,
			'address'		=>	$address,
			'phone_number'	=>	$phone,
			'notes'			=>	$note,
			'image'			=>	$image,
			'latitude'		=>	$latitude,
			'longitude'		=>	$longitude
		];

		if( $id > 0 )
		{
			if( empty( $image ) )
			{
				unset( $sqlData['image'] );
			}
			else
			{
				$getOldInf = Location::get( $id );

				if( !empty( $getOldInf['image'] ) )
				{
					$filePath = Helper::uploadedFile( $getOldInf['image'], 'Locations' );

					if( is_file( $filePath ) && is_writable( $filePath ) )
					{
						unlink( $filePath );
					}
				}
			}

			Location::where( 'id', $id )->update( $sqlData );
		}
		else
		{
			$sqlData['is_active'] = 1;
			Location::insert( $sqlData );
            $id = Location::lastId();
		}

        Location::handleTranslation( $id );

		return $this->response(true, [
            'location_id' => $id
        ] );
	}

	public function hide_location()
	{
		Capabilities::must( 'locations_edit' );

		$location_id	= Helper::_post('location_id', '', 'int');

		if( !( $location_id > 0 ) )
		{
			return $this->response(false);
		}

		$location = Location::get( $location_id );

		if( !$location )
		{
			return $this->response( false );
		}

		$new_status = $location['is_active'] == 1 ? 0 : 1;

		Location::where('id', $location_id)->update(['is_active' => $new_status]);

		return $this->response( true );
	}

}
