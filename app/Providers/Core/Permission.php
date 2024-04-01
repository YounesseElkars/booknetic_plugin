<?php

namespace BookneticApp\Providers\Core;

use BookneticApp\Models\Staff;
use BookneticApp\Providers\DB\DB;
use BookneticApp\Providers\Helpers\Helper;
use BookneticSaaS\Models\Tenant;

class Permission
{

	private static $current_user_info;
	private static $assigned_staff_list;
	private static $is_back_end = false;
	private static $tenantTables = [
        'appearance',
        'appointments',
        'customers',
        'holidays',
        'locations',
        'service_categories',
        'services',
        'special_days',
        'staff',
        'timesheet',
        'tenant_billing',
        'workflows',
        'workflow_logs',
    ];

	/**
	 * @var int
	 */
	private static $tenantId = -1;

	/**
	 * @var Tenant
	 */
	private static $tenantInf;


	public static function setAsBackEnd()
	{
		self::$is_back_end = true;
	}

	public static function isBackEnd()
	{
		// doit bu isBackendi bir arashdirmaq lazimdi seliqeli shekilde...
		if( self::$is_back_end )
			return true;

		if( !( Helper::is_ajax() && !Helper::is_update_process() ) && is_admin() && Permission::canUseBooknetic() )
		{
			return true;
		}

		return false;
	}

	public static function canUseBooknetic()
	{
		if( ! self::userInfo()->exists() )
			return false;

		if( Helper::isSaaSVersion() && self::isSuperAdministrator() )
			return false;

		if( self::isAdministrator() )
			return true;

		if( !empty( self::myStaffList() ) )
			return true;

		return false;
	}

	public static function userInfo()
	{
		if( is_null( self::$current_user_info ) )
		{
			self::$current_user_info = wp_get_current_user();
		}

		return self::$current_user_info;
	}

	public static function userId()
	{
		return get_current_user_id();
	}

	public static function userEmail()
	{
		$current_user = wp_get_current_user();
		return $current_user->user_email;
	}

	public static function isAdministrator()
	{
		if( in_array( 'administrator', self::userInfo()->roles ) )
			return true;

		if( in_array( 'booknetic_saas_tenant', self::userInfo()->roles ) )
			return true;

		if( !Helper::isSaaSVersion() && self::isDemoVersion() )
			return true;

		return false;
	}

	public static function isSuperAdministrator()
	{
		if( self::isDemoVersion() && !in_array( 'booknetic_saas_tenant', self::userInfo()->roles ) )
			return true;

		if( in_array( 'administrator', self::userInfo()->roles ) )
			return true;

		return false;
	}

	public static function myStaffList()
	{
		if( is_null( self::$assigned_staff_list ) )
		{
			self::$assigned_staff_list = Staff::noTenant()->withoutGlobalScope('user_id')->where('user_id', self::userId())->fetchAll();
		}

		return self::$assigned_staff_list;
	}

	public static function myStaffId()
	{
		$staffList = self::myStaffList();

		$ids = [];
		foreach ( $staffList AS $staff )
		{
			$ids[] = (int)$staff['id'];
		}

		return $ids;
	}

	public static function queryFilter( $table, $column = 'id', $joiner = 'AND', $tenant_column = '`tenant_id`' )
	{
		$query = '';

		$joiner = ' ' . trim($joiner) . ' ';

		if( Helper::isSaaSVersion() && in_array( $table, self::$tenantTables ) )
		{
			$query .= DB::tenantFilter( '', $tenant_column );
		}

		if( !self::$is_back_end )
			return empty( $query ) ? $query : $joiner . $query;

		if( self::isAdministrator() )
			return empty( $query ) ? $query : $joiner . $query;

		if( $table == 'staff' )
		{
			$query .= ( ($query == '') ? '' : 'AND' ) . ' ' . $column . ' IN (\'' . implode("', '", self::myStaffId()) . '\') ';
		}
		else if( $table == 'appointments' )
		{
			$query .= ( ($query == '') ? '' : 'AND' ) . ' ' . $column . ' IN (\'' . implode("', '", self::myStaffId()) . '\') ';
		}

		return empty( $query ) ? $query : $joiner . $query;
	}

	public static function tenantId()
	{
		if( self::$tenantId === -1 )
		{
			if( Helper::isSaaSVersion() )
			{
				$currentDomain          = \BookneticSaaS\Providers\Helpers\Helper::getCurrentDomain();
				$tenantIdFromRequest    = Helper::_any('tenant_id', '', 'int');

				if( !self::isBackEnd() && !wp_doing_ajax() && !empty( $currentDomain ) )
				{
					$checkTenantExist = Tenant::where('domain', $currentDomain)->fetch();

					if( $checkTenantExist )
					{
						self::$tenantId = $checkTenantExist->id;

                        return self::$tenantId;
					}
				}

				if( self::$tenantId === -1 && !self::isBackEnd() && $tenantIdFromRequest > 0 )
				{
					$checkTenantExist = Tenant::where('id', $tenantIdFromRequest)->fetch();

					if( $checkTenantExist )
					{
						self::$tenantId = $checkTenantExist->id;
					}
				}
				else if( self::userId() > 0 )
				{
					if( in_array( 'booknetic_saas_tenant', self::userInfo()->roles ) )
					{
						$tenantInf = Tenant::where( 'user_id', self::userId() )->fetch();
						self::$tenantId = $tenantInf ? $tenantInf->id : null;
					}
					else
					{
						$staffInf = Staff::noTenant()->withoutGlobalScope('user_id')->where('user_id', self::userId())->fetch();
						self::$tenantId = $staffInf ? $staffInf->tenant_id : null;
					}
				}
			}
			else
			{
				self::$tenantId = null;
			}
		}

		return self::$tenantId > 0 ? self::$tenantId : null;
	}

	/**
	 * @return Tenant
	 */
	public static function tenantInf()
	{
        /*
         * Eger tenantInf->id ile tenantId property-ni checklemesek tenantId deyishende o zaman foreach icinde setTenantId ve tenantInf istifade olunarsa, tenantInf her zaman kohne datalari return edecek
         * cunki tenantidnin deyishib deyismediyine baxmirdi bu shert. Ikinci sherti elave etdim ki lazim olanda gedib yeniden ceksin datalari
         */
		if( is_null( self::$tenantInf ) || self::$tenantInf->id !== self::tenantId() )
		{
			self::$tenantInf = Tenant::get( self::tenantId() );
		}

		return self::$tenantInf;
	}

	public static function setTenantId( $tenantId )
	{
		self::$tenantId = $tenantId;
	}

	public static function isDemoVersion()
	{
		return defined('FS_CODE_DEMO_VERSION');
	}

    public static function getTenantBookingPageURL()
    {
        return site_url() . '/' . htmlspecialchars(self::tenantInf()->domain);
    }
}