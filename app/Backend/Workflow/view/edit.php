<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Config;
use BookneticApp\Providers\Helpers\Helper;

/**
 * @var mixed $parameters
 */

$workflowEventsManager = $parameters['events_manager'];

?>

<script src="<?php echo Helper::assets('js/edit.js', "Workflow")?>"></script>
<link rel="stylesheet" href="<?php echo Helper::assets('css/edit.css', 'Workflow')?>" type="text/css">

<div class="px-3 pb-5">
    <div class="m_header clearfix">
        <div class="m_head_title float-left">
            <a href="admin.php?page=<?php echo Helper::getBackendSlug(); ?>&module=workflow"><?php echo bkntc__( 'Workflows' ); ?></a>
            <i class="mx-2"><img src="<?php echo Helper::icon( 'arrow.svg' ); ?>"></i>
            <span class="name"><?php echo bkntc__( 'Edit workflow' ); ?></span>
        </div>
    </div>

    <div class="fs_separator"></div>

    <div class="d-flex flex-column justify-content-center align-items-center">
        <div class="workflow_card">
            <div class="form--group mb-4">
                <label class="workflow_card--label" for="workflow_name"><?php echo bkntc__('Workflow name')?></label>
                <input class="form-control" id="workflow_name" value="<?php echo $parameters['workflow_info']->name ?>">
            </div>

            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="workflow_card-activate d-flex align-items-center mr-2">
                    <label class="mb-0 mr-3" for="workflow_activated"><?php echo bkntc__('Activated')?></label>
                    <div class="fs_onoffswitch">
                        <input type="checkbox" class="fs_onoffswitch-checkbox" id="workflow_activated" <?php echo $parameters['workflow_info']->is_active ? 'checked' : ''; ?>>
                        <label class="fs_onoffswitch-label" for="workflow_activated"></label>
                    </div>
                </div>

                <button id="workflow_save_btn" type="button" class="btn btn-success float-right"><?php echo bkntc__('SAVE')?></button>
            </div>
        </div>

        <div class="workflow_card">
            <div>
                <h6 class="workflow_card--label" for="workflow_name"><?php echo bkntc__('When')?></h6>
                <div class="workflow_card--group workflow_card--group-info">
                    <span><?php echo $workflowEventsManager->get($parameters['workflow_info']->when)->getTitle() ?></span>
                    <?php if (!empty($workflowEventsManager->get($parameters['workflow_info']->when)->getEditAction())) : ?>
                    <button data-load-modal="<?php echo $workflowEventsManager->get($parameters['workflow_info']->when)->getEditAction() ?>" data-parameter-id="<?php echo $parameters['id'] ?>"><?php echo bkntc__('Edit')?></button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="workflow_card workflow_card--edit">
            <div class="workflow_card--label" for="workflow_name"><?php echo bkntc__('Do this')?></div>
                <div class="workflow_action_list">
                    <?php include 'modal/action_list_view.php'?>
                </div>

            <div class="mt-1">
                <button id="addBtn" class="workflow_card--btn btn btn-primary mx-auto"><?php echo bkntc__('Add')?></button>
            </div>
        </div>
    </div>
</div>

<script>

    var currentWorkflowID = <?php echo $parameters['id']?>;

</script>
