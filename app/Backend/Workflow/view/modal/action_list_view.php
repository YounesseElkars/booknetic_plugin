<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Config;
use BookneticApp\Providers\Helpers\Helper;

/**
 * @var mixed $parameters
 */

$workflowDriversManager = $parameters['events_manager']->getDriverManager();

?>
<?php foreach ($parameters['actions'] as $action) :
    if( empty( $workflowDriversManager->get( $action->driver ) ) )
    {
        continue;
    }
    ?>
    <div class="mb-3">
        <div class="d-flex align-items-center">
            <div class="workflow_card--group workflow_card--group-info <?php echo (empty($action->data) ? 'error' : '') ?> mr-2 rtl-mr-2">
                <span <?php echo $action->is_active ? '' : 'style="opacity: 0.3"' ?> ><?php echo $workflowDriversManager->get($action->driver)->getName(); ?></span>
                <button data-load-modal="<?php echo $workflowDriversManager->get( $action['driver'] )->getEditAction() ?>" data-parameter-id="<?php echo $action->id ?>" data-option-width="750px"><?php echo bkntc__(empty($action->data) ? 'Setup' : 'Edit')?></button>
            </div>
            <button class="workflow_card--group-delete delete_action" data-id="<?php echo $action->id; ?>"><img src="<?php echo Helper::assets('icons/delete.svg')?>" alt="delete"></button>
        </div>
        <?php if ( empty( $action->data ) ) : ?>
            <small><?php echo bkntc__('Configuration needed')?></small>
        <?php endif; ?>
    </div>
<?php endforeach; ?>