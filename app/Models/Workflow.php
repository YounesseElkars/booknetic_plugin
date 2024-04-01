<?php

namespace BookneticApp\Models;

use BookneticApp\Providers\DB\Model;
use BookneticApp\Providers\DB\MultiTenant;

/**
 * @method Model workflow_actions()
 * @method Model workflow_logs()
 */
class Workflow extends Model
{
	use MultiTenant;

	public static $relations = [
		'workflow_actions'  => [ WorkflowAction::class, 'workflow_id', 'id' ],
		'workflow_logs'     => [ WorkflowLog::class, 'workflow_id', 'id' ]
	];

}