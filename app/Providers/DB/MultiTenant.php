<?php

namespace BookneticApp\Providers\DB;

use BookneticApp\Providers\Core\Permission;
use BookneticApp\Providers\Helpers\Helper;

trait MultiTenant
{

	public static function booted()
	{
		self::addGlobalScope( 'tenant', function ( QueryBuilder $builder, $queryType )
		{
            if (!Helper::isSaaSVersion()) return;

			if( $queryType == 'insert' )
			{
				$builder->tenant_id = Permission::tenantId();
			}
			else
			{
				/**
				 * wrap wheres in brackets: example: "WHERE type=5 OR type=6 AND tenant_id=10" => "WHERE (type=5 OR type=6) AND tenant_id=10"
				 */
				if( ! empty( $builder->getWhereArr() ) )
				{
					$currentWhereArr = $builder->getWhereArr();

					$newWrappedWhere = new QueryBuilder( $builder->getModel() );

					$newWrappedWhere->where( fn( $query ) => $query->setWhereArr( $currentWhereArr ) );

					$builder->setWhereArr( $newWrappedWhere->getWhereArr() );
				}

                if (!empty(Permission::tenantId()) && Permission::tenantId() != -1) {
                    $builder->where(self::getField('tenant_id'), Permission::tenantId());
                } else {
                    $builder->where(self::getField('tenant_id'), 'IS', null);
                }
			}
		});
	}

	public static function scopeNoTenant( QueryBuilder $builder, $noTenant = true )
	{
        if ($noTenant === false)
        {
            return $builder;
        }

		return $builder->withoutGlobalScope('tenant');
	}

}
