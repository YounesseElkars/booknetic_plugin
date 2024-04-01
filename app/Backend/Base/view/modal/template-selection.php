<?php

/**
 * @var array $parameters
 * @var array $templates
*/

use BookneticApp\Providers\Helpers\Helper;

$templates = $parameters[ 'templates' ];

?>

<link rel="stylesheet" href="<?php echo Helper::assets( 'css/template-selection.css' )?>">

<div class="modal-header">
    <?php echo bkntc__( 'Choose A Template' ) ?>
</div>
<div class="modal-body">
    <section class="d-flex justify-content-center">
        <div class="container-fluid">
            <div class="row template-card-wrapper">
                <div id="skipSelection" class="card_col template-card col-12 col-sm-6 col-lg-4 col-xl-3 cursor-pointer">
                    <div class="addons_card box h-100 d-flex flex-column text-dark justify-content-center align-items-center border">
                        <div class="mb-4">
                            <i class="fas fa-plus" style="font-size: 48px"></i>
                        </div>
                        <div><?php echo bkntc__( 'Start From Scratch' ) ?></div>
                    </div>
                </div>

                <?php foreach ( $templates as $template ):?>
                    <div class="card_col template-card col-12 col-sm-6 col-lg-4 col-xl-3">
                            <div class="addons_card box h-100 d-flex flex-column text-dark">
                                <img src="<?php echo $template[ 'full_image_url' ]; ?>" alt="">
                                <h5><?php echo $template[ 'name' ] ?></h5>
                                <p><?php echo  $template[ 'description' ] ?></p>
                                <button type="button" class="btn btn-primary mt-auto applyTemplate" data-id="<?php echo $template[ 'id' ] ?>">
                                    <?php echo bkntc__( 'APPLY' ) ?>
                                </button>
                            </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>

<script type="application/javascript" src="<?php echo Helper::assets( 'js/template-selection.js' )?>" id="templateSelectionJS"></script>
