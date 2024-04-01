<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Core\Permission;

/**
 * @var mixed $theme_id
 * @var mixed $info
 * @var mixed $steps_order
 * @var mixed $hide_confirmation_number
 */

if( ! \BookneticApp\Providers\Core\Capabilities::tenantCan( 'receive_appointments' ) )
{
	echo '<div>' . bkntc__('You can\'t receive appointments. Please upgrade your plan to receive appointments.') . '</div>';
	return;
}

$showPoweredByBadge = Helper::isSaaSVersion() && !( Permission::tenantInf() && Capabilities::tenantCan('remove_branding') && Helper::getOption('remove_branding', 'off') == 'on' );
?>

<div id="booknetic_progress" class="booknetic_progress_waiting booknetic_progress_done"><dt></dt><dd></dd></div>

<div class="booknetic_appointment<?php echo Helper::isRTLLanguage( Permission::tenantId() ) ? " rtl": "" ?>" id="booknetic_theme_<?php echo $theme_id;?>" data-info=<?php echo $info ?>>
	<div class="booknetic_appointment_steps <?php echo Capabilities::tenantCan( 'upload_logo_to_booking_panel' ) && Helper::getOption('display_logo_on_booking_panel', 'off') == 'on' ? 'has-logo' : ''; ?>">
		<?php if( Capabilities::tenantCan( 'upload_logo_to_booking_panel' ) && Helper::getOption('display_logo_on_booking_panel', 'off') == 'on' ):?>
		<div class="booknetic_company_logo">
			<img src="<?php echo Helper::profileImage(Helper::getOption('company_image', ''), 'Settings')?>">
		</div>
		<?php endif;?>
		<div class="booknetic_appointment_steps_body nice-scrollbar-primary">
			<?php
			foreach ( $steps_order AS $stepId )
			{
				if( !isset( $steps[ $stepId ] ) )
					continue;

                $step = $steps[ $stepId ];

                ?>
                <div class="<?php echo in_array($stepId ,['cart','confirm_details']) ? '' : 'need_copy '?> booknetic_appointment_step_element<?php echo $step['hidden'] ? ' booknetic_menu_hidden' : ''?>" <?php echo !empty($step['value']) ? ' data-value="'.$step['value'].'"' : ''?> data-step-id="<?php echo $stepId?>" data-loader="<?php echo $step['loader']?>" data-title="<?php echo $step['head_title']?>" <?php echo isset($step['attrs']) && !empty($step['attrs']) ? $step['attrs'] : ''?>>
                    <span class="booknetic_badge"></span>
                    <span class="booknetic_step_title"> <?php echo $step['title']?></span>
                </div>
                <?php
            }
            ?>
        </div>
        <div class="booknetic_appointment_steps_footer">
            <div class="booknetic_appointment_steps_footer_txt1"><?php echo empty($company_phone_number) ? '' : bkntc__('Have any questions?')?></div>
            <div class="booknetic_appointment_steps_footer_txt2"><?php echo $company_phone_number?></div>
        </div>
    </div>
    <div class="booknetic_appointment_container">

        <div class="booknetic_appointment_container_header">
            <div class="booknetic_appointment_container_header_text"></div>
            <?php if (Helper::getOption('show_step_cart', 'on') == 'on'): ?>
            <div class="booknetic_appointment_container_header_cart booknetic_hidden">
                <div class="booknetic-appointment-container-cart-btn <?php echo $showPoweredByBadge ? 'booknetic-appointment-container-cart-btn-r-100' : ''?>">
                    <img src="<?php echo Helper::icon('cart.svg','front-end') ?>" alt="">
                    <span>0</span>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <div class="booknetic_appointment_container_body">

            <div class="booknetic_preloader_card1_box booknetic_hidden">

                <div class="booknetic_preloader_card1">
                    <div class="booknetic_preloader_card1_image"></div>
                    <div class="booknetic_preloader_card1_description"></div>
                </div>

                <div class="booknetic_preloader_card1">
                    <div class="booknetic_preloader_card1_image"></div>
                    <div class="booknetic_preloader_card1_description"></div>
                </div>

                <div class="booknetic_preloader_card1">
                    <div class="booknetic_preloader_card1_image"></div>
                    <div class="booknetic_preloader_card1_description"></div>
                </div>

            </div>

            <div class="booknetic_preloader_card2_box booknetic_hidden">
                <div class="booknetic_preloader_card2">
                    <div class="booknetic_preloader_card2_image"></div>
                    <div class="booknetic_preloader_card2_description"></div>
                </div>

                <div class="booknetic_preloader_card2">
                    <div class="booknetic_preloader_card2_image"></div>
                    <div class="booknetic_preloader_card2_description"></div>
                </div>

                <div class="booknetic_preloader_card2">
                    <div class="booknetic_preloader_card2_image"></div>
                    <div class="booknetic_preloader_card2_description"></div>
                </div>
            </div>

            <div class="booknetic_preloader_card3_box booknetic_hidden">
                <div class="booknetic_preloader_card3"></div>
                <div class="booknetic_preloader_card3"></div>
                <div class="booknetic_preloader_card3"></div>
                <div class="booknetic_preloader_card3"></div>
            </div>

            <div class="booknetic_need_copy" >
                <div data-step-id="location" class="booknetic_hidden"></div>
                <div data-step-id="staff" class="booknetic_hidden"></div>
                <div data-step-id="service" class="booknetic_hidden"></div>
                <div data-step-id="service_extras" class="booknetic_hidden"></div>
                <div data-step-id="date_time" class="booknetic_hidden"></div>
                <div data-step-id="recurring_info" class="booknetic_hidden"></div>
                <div data-step-id="information" class="booknetic_hidden"></div>
            </div>
            <div data-step-id="cart" class="booknetic_hidden"></div>
            <div data-step-id="confirm_details" class="booknetic_hidden"></div>

            <div class="booknetic_appointment_finished_with_error booknetic_hidden">
                <img src="<?php echo Helper::assets('images/payment-error.svg', 'front-end')?>">
                <div><?php echo bkntc__('We aren’t able to process your payment. Please, try again.')?></div>
            </div>

        </div>
        <div class="booknetic_appointment_container_footer">
            <button type="button" class="booknetic_btn_secondary booknetic_prev_step"><?php echo bkntc__('BACK')?></button>
            <div class="booknetic_warning_message"></div>
            <button type="button" class="booknetic_btn_primary booknetic_next_step booknetic_next_step_btn"><?php echo bkntc__('NEXT STEP')?></button>
            <button type="button" class="booknetic_btn_primary booknetic_next_step booknetic_confirm_booking_btn"><?php echo bkntc__('CONFIRM BOOKING')?></button>
            <button type="button" class="booknetic_btn_primary booknetic_hidden booknetic_try_again_btn"><?php echo bkntc__('TRY AGAIN')?></button>
        </div>
    </div>
    <div class="booknetic_appointment_finished">
        <div class="booknetic_appointment_finished_icon"><img src="<?php echo Helper::icon('status-ok.svg', 'front-end')?>"></div>
        <div class="booknetic_appointment_finished_title"><?php echo bkntc__('Thank you for your request!')?></div>
        <div class="booknetic_appointment_finished_subtitle<?php echo $hide_confirmation_number ? ' booknetic_hidden' : '' ?>"><?php echo bkntc__('Your confirmation number:')?></div>
        <div class="booknetic_appointment_finished_code<?php echo $hide_confirmation_number ? ' booknetic_hidden' : '' ?>"></div>
        <div class="booknetic_appointment_finished_actions">
            <button type="button" id="booknetic_add_to_google_calendar_btn" class="booknetic_btn_secondary<?php echo Helper::getOption('hide_add_to_google_calendar_btn', 'off') == 'on' ? ' booknetic_hidden' : ''?>"><img src="<?php echo Helper::icon('calendar.svg', 'front-end')?>"> <?php echo bkntc__('ADD TO GOOGLE CALENDAR')?></button>
            <a id="booknetic_add_to_icalendar_btn" download="<?php echo bkntc__('event') . '.ics' ?>" class="booknetic_btn_secondary<?php echo Helper::getOption('hide_add_to_icalendar_btn', 'off') == 'on' ? ' booknetic_hidden' : ''?>"><img src="<?php echo Helper::icon('calendar.svg', 'front-end')?>"> <?php echo bkntc__('ADD TO iCAL CALENDAR')?></a>
            <button type="button" id="booknetic_start_new_booking_btn" class="booknetic_btn_secondary<?php echo Helper::getOption('hide_start_new_booking_btn', 'off') == 'on' ? ' booknetic_hidden' : ''?>"><img src="<?php echo Helper::icon('plus.svg', 'front-end')?>"> <?php echo bkntc__('START NEW BOOKING')?></button>
            <button type="button" id="booknetic_finish_btn" class="booknetic_btn_secondary" data-redirect-url="<?php echo htmlspecialchars(Helper::getOption('redirect_url_after_booking' ))?>"><img src="<?php echo Helper::icon('check-small.svg', 'front-end')?>"> <?php echo bkntc__('FINISH BOOKING')?></button>
        </div>
    </div>
    <?php if( $showPoweredByBadge ):?>
        <a href="<?php echo site_url('/')?>" target="_blank" class="booknetic_powered_by"><?php echo bkntc__('Powered by %s', [ '<span>' . Helper::getOption('powered_by', 'Booknetic', false ) . '</span>' ], false)?></a>
    <?php endif;?>
</div>

