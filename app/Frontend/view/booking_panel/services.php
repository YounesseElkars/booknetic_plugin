<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Date;

/**
 * @var mixed $parameters
 */

if( count( $parameters['services'] ) == 0 )
{
	echo '<div class="booknetic_empty_box"><img class="booknetic_card_service_image" src="' . Helper::assets('images/empty-service.svg', 'front-end') . '"><span>' . bkntc__('Service not found. Please go back and select a different option.') . '</div>';
}
else
{
    echo '<div class="bkntc_service_list">';

$lastCategoryPrinted = null;
$services = apply_filters('bkntc_booking_panel_render_services_info' , $parameters['services']);

$isAccordionEnabled = Helper::getOption('hide_accordion_default', 'off', [ 'off', 'on' ]);
$isFirstCategoryService = 1;
$servicesLeftToPrint = $services;

    do_action('bkntc_service_step_footer', $parameters['services']);


foreach ( $services AS $eq => $serviceInf )
{
	if( $lastCategoryPrinted != $serviceInf['category_id'] )
	{
        if ( $isFirstCategoryService == 1 && $isAccordionEnabled == 'on' ){
            echo '<div class="booknetic_category_accordion active" data-accordion="on">';
        }

		echo '<div data-parent="'.$isFirstCategoryService .'" class="booknetic_service_category  booknetic_fade">' . htmlspecialchars($serviceInf['category_name']) . '<span data-parent="'. $isFirstCategoryService .'"></span></div>';
		$lastCategoryPrinted = $serviceInf['category_id'];
        $isFirstCategoryService = 0;
	}
	?>
        <div class="booknetic_service_card demo booknetic_fade" data-id="<?php echo $serviceInf[ 'id' ]; ?>" data-is-recurring="<?php echo (int) $serviceInf[ 'is_recurring' ]; ?>" data-has-extras="<?php echo $serviceInf[ 'extras_count' ] > 0 ? 'true':'false'; ?>">
        <div class="booknetic_service_card_header">
            <div class="booknetic_service_card_image">
                <img class="booknetic_card_service_image" src="<?php echo Helper::profileImage( $serviceInf[ 'image' ], 'Services' ); ?>">
            </div>

            <div class="booknetic_service_card_title">
                <span class="booknetic_service_title_span"><?php echo $serviceInf[ 'name' ]; ?></span>
                <div class="booknetic_service_duration_wrapper">
                    <span class="booknetic_service_duration_span <?php echo $serviceInf[ 'hide_duration' ] == 1 ? 'booknetic_hidden' : ''; ?>">
                        <?php echo Helper::secFormat( $serviceInf[ 'duration' ] * 60 ); ?>
                    </span>
                </div>
            </div>

            <div class="booknetic_service_card_price <?php echo $serviceInf[ 'hide_price' ] == 1 ? 'booknetic_hidden' : ''; ?>" data-price="<?php echo htmlspecialchars($serviceInf[ 'real_price' ] == -1 ? $serviceInf[ 'price' ] : $serviceInf[ 'real_price' ])?>">
                <?php echo Helper::price( $serviceInf[ 'real_price' ] == -1 ? $serviceInf[ 'price' ] : $serviceInf[ 'real_price' ] ); ?>
            </div>
        </div>

        <div class="booknetic_service_card_description">
            <span class="booknetic_service_card_description_fulltext"><?php echo nl2br( $serviceInf[ "notes" ] )?></span>
			<span class="booknetic_service_card_description_wrapped"><?php echo nl2br($serviceInf['wrapped_note']); ?></span>
            <?php if( $serviceInf['should_wrap'] ) {?>
                <span class="booknetic_view_more_service_notes_button">
                    <?php echo bkntc__("Show more") ?>
                </span>
                <span class="booknetic_view_less_service_notes_button">
                    <?php echo bkntc__("Show less") ?>
                </span>
            <?php } ?>
        </div>
    </div>


	<?php


    array_shift($servicesLeftToPrint);

    foreach ($servicesLeftToPrint AS $key => $checkForCategory ) {

        if ( $isAccordionEnabled != 'on' ) break;

        if ( $checkForCategory['category_id'] == $lastCategoryPrinted ) break;


        if ( $checkForCategory != end($servicesLeftToPrint) ) continue;

        echo '</div>';
        $isFirstCategoryService = 1;
    }

}


echo '</div>';
}
