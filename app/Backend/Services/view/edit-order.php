<?php
use BookneticApp\Providers\Helpers\Helper;

/**
 * @var $parameters
 */

function bkntcCategoryHtmlRender( $categories, $nestLevel = 0 ) {
    foreach ( $categories AS $key => $category ) {
        echo '<div data-id="' . $category['id'] . '" class="bkntc_order_item ' . ($nestLevel == 0 ? "bkntc_order_item_parent" : null) . '">
                <div>
                    <span class="bkntc_order_item_sort_helper">
                        <i class="fas fa-bars"></i>
                    </span>
                    ' . $category["name"] . '
                </div>';

        if (isset($category['child'])) {
            echo "<div class='bkntc_order_items_wrapper'>";
            bkntcCategoryHtmlRender($category['child'], $nestLevel + 1);
            echo "</div>";
        }
        echo "</div>";
    }
}

?>

<div class="m_header clearfix">
    <div class="m_head_title float-left">
        <a href="?page=<?php echo Helper::getBackendSlug(); ?>&module=services"><?php echo bkntc__( 'Services' ) ?></a>
        <i class="mx-2"><img src="<?php echo Helper::icon( 'arrow.svg' ); ?>"></i>
        <span class="name"><?php echo bkntc__( 'Edit order' ) ?></span>
    </div>
    <div class="m_head_actions float-right">
        <button class="btn btn-primary btn-lg" id="resetOrderBtn"> <i class="fas fa-undo mr-2" aria-hidden="true"></i> <?php echo bkntc__( 'RESET ORDER' ) ?> </button>
        <button class="btn btn-success btn-lg" id="saveChangesBtn"><i class="fa fa-check pr-2" aria-hidden="true"></i> <?php echo bkntc__( 'SAVE CHANGES' ) ?> </button>
    </div>
</div>
<div class="m_content pt-0" id="fs_data_table_div">

    <hr/>
    <div class="row">
        <div class="col-md-4 mb-4">
            <div id="booknetic_categories" class="bkntc_order_items_wrapper">
                <?php bkntcCategoryHtmlRender( $parameters[ "categories" ] ); ?>
            </div>
        </div>
        <div class="col-md-8 mb-4">
            <div class="bkntc_order_items_wrapper nice-scrollbar-primary" id="bkntc_services_order_list">

            </div>
        </div>
    </div>

</div>
<link rel="stylesheet" type="text/css" href="<?php echo Helper::assets('css/edit-order.css', 'Services') ?>">
<script type="text/javascript" src="<?php echo Helper::assets('js/services-order.js', 'Services')?>"></script>
