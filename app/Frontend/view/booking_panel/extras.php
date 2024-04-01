<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;

$printedCategories = [];
$i = 0;

if( empty( $parameters['extras'] ) )
{
	echo '<div class="booknetic_empty_box"><img src="' . Helper::assets('images/empty-extras.svg', 'front-end') . '"><span>' . bkntc__('Extras not found in this service. You can select other service or click the <span class="booknetic_text_primary">"Next step"</span> button.' , [] , false) . '</span></div>';
}
else
{

    echo '<div class="bkntc_service_extras_list">';

    do_action('bkntc_service_extras_step_footer',
        array_map( function ( $extra ){
            return $extra->toArray();
        } , $parameters['extras']));

    $accordionEnabled = Helper::getOption('collapse_service_extras', 'off');

    foreach ( $parameters['extras'] AS $eq => $extraInf )
    {
        if ( $accordionEnabled === 'on' && array_key_exists( $extraInf[ 'category_id' ], $parameters['extra_categories'] ) && ! in_array( $extraInf[ 'category_id' ], $printedCategories ) )
        {
            echo '<div class="booknetic_category_accordion active" data-accordion="on" style="border-bottom: 1px solid #ddd; margin-bottom: 16px; padding-bottom: 6px;">';
            echo '<div class="booknetic_service_extra_title booknetic_fade" data-parent="1">' . $parameters['extra_categories'][ $extraInf['category_id']]['name'] . ' <span data-parent="1"></span></div>';
            $printedCategories[] = $extraInf[ 'category_id' ];
        }
        else if ( $accordionEnabled === 'off' && $i == 0 )
        {
            $i++;
            echo '<div class="booknetic_service_extra_title booknetic_fade">' . $parameters['service_name'] . '</div>';
        }

		?>
        <div class="booknetic_service_extra_card <?php echo $extraInf['max_quantity'] == 1 ? ' booknetic_extra_on_off_mode' : ''?> <?php echo (int)$extraInf["min_quantity"] > 0 ? 'booknetic_service_extra_card_selected' : '' ?>" data-id="<?php echo (int)$extraInf['id']?>" style="<?= array_key_exists( $extraInf[ 'category_id' ], $parameters['extra_categories'] ) && $accordionEnabled === 'on' ? 'display:none;' : '' ?>" >
            <div class="booknetic_service_extra_card_header booknetic_fade">
                <div class="booknetic_service_extra_card_image">
                    <img src="<?php echo Helper::profileImage($extraInf['image'], 'Services')?>">
                </div>
                <div class="booknetic_service_extra_card_title_quantity">
                    <div class="booknetic_service_extra_card_title">
                        <span><?php echo htmlspecialchars($extraInf['name'])?></span>
                        <span><?php echo $extraInf['duration'] && $extraInf['hide_duration'] != 1 ? Helper::secFormat($extraInf['duration']*60) : ''?></span>
                    </div>
                    <div class="booknetic_service_extra_quantity<?php echo $extraInf['max_quantity'] == 1 ? ' booknetic_hidden' : ''?>">
                        <?php if ( $extraInf['max_quantity'] !== $extraInf['min_quantity'] ) :?>
                            <div class="booknetic_service_extra_quantity_dec">-</div>
                        <?php endif;?>

                        <input type="text" class="booknetic_service_extra_quantity_input" value="<?php echo (int)$extraInf['min_quantity']?>" data-min-quantity="<?php echo (int)$extraInf['min_quantity']?>" data-max-quantity="<?php echo (int)$extraInf['max_quantity']?>" <?php echo $extraInf['max_quantity'] === $extraInf['min_quantity'] ? ' disabled' : '' ?>>

                        <?php if ( $extraInf['max_quantity'] !== $extraInf['min_quantity'] ) :?>
                            <div class="booknetic_service_extra_quantity_inc">+</div>
                        <?php endif;?>
                    </div>
                </div>
                <div class="booknetic_service_extra_card_price">
                    <?php echo $extraInf['hide_price'] != 1 ? Helper::price( $extraInf['price'] ) : ''?>
                </div>
            </div>
            <div class="booknetic_service_card_description">
                <span class="booknetic_service_card_description_fulltext"><?php echo nl2br( $extraInf[ "notes" ] )?></span>
                <span class="booknetic_service_card_description_wrapped"><?php echo nl2br($extraInf['wrapped_note']) ?></span>
                <?php if( $extraInf['should_wrap'] ) {?>
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
        if ( array_key_exists( $extraInf->id, $parameters[ 'category_last_extras' ] ) )
        {
            echo '</div>';
        }
    }

    echo '</div>';
}