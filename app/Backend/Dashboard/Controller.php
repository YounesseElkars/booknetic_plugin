<?php

namespace BookneticApp\Backend\Dashboard;


use BookneticApp\Config;
use BookneticApp\Models\Appointment;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\DB\DB;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Core\Permission;

class Controller extends \BookneticApp\Providers\Core\Controller
{

	public function index()
	{
		Capabilities::must( 'dashboard' );

		$totalAccordingToStatus = Appointment::where('id' , '>' , 0)
            ->select(['count(status) as count' , 'status'] )
            ->groupBy(['status'])
            ->fetchAll();

        $totalAccordingToStatus = Helper::assocByKey($totalAccordingToStatus,'status');

		$this->view( 'index' , compact('totalAccordingToStatus' ));
	}

}
