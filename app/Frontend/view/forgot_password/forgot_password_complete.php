<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;

?>

<div class="booknetic_forgot_password" data-token="<?php echo htmlspecialchars($parameters['reset_token'])?>">
    <div class="booknetic_step_1">
        <div class="booknetic_header"><?php echo bkntc__('Reset password')?></div>
        <form method="post" class="booknetic_form">
            <div class="booknetic_form_element">
                <label for="booknetic_password1"><?php echo bkntc__('New Password')?></label>
                <input type="password" id="booknetic_password1">
            </div>
            <div class="booknetic_form_element">
                <label for="booknetic_password2"><?php echo bkntc__('Confirm Password')?></label>
                <input type="password" id="booknetic_password2">
            </div>
            <div>
                <button type="button" class="booknetic_btn_primary booknetic_complete_forgot_password_btn"><?php echo bkntc__('RESET PASSWORD')?></button>
            </div>
        </form>
    </div>
    <div class="booknetic_step_2">
        <div class="booknetic_forgot_password_completed">
            <img src="<?php echo Helper::assets('images/signup-success2.svg', 'front-end')?>" alt="">
        </div>
        <div class="booknetic_forgot_password_completed_title"><?php echo bkntc__('Congratulations!')?></div>
        <div class="booknetic_forgot_password_completed_subtitle">
            <?php echo bkntc__('Your password has been reset successfully !')?>
        </div>
        <div class="booknetic_forgot_password_completed_footer">
            <a href="<?php echo get_permalink( Helper::getOption('regular_sing_in_page') )?>" type="button" class="booknetic_btn_primary booknetic_goto_dashboard_btn"><?php echo bkntc__('SIGN IN')?></a>
        </div>
    </div>
</div>
