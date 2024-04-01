<?php

use BookneticApp\Providers\Helpers\Helper;

defined( 'ABSPATH' ) or die();

?>

<div class="booknetic_login">
	<div class="booknetic_header"><?php echo bkntc__('Sign In')?></div>
	<form class="booknetic_form">
		<div class="booknetic_form_element">
			<label for="booknetic_email"><?php echo bkntc__('Username or Email Address')?></label>
			<input type="text" id="booknetic_email" name="email">
		</div>
		<div class="booknetic_form_element">
			<label for="booknetic_password"><?php echo bkntc__('Password')?></label>
			<input type="password" id="booknetic_password" name="password">
		</div>
		<div>
            <div class="booknetic_form_element"><a href="<?php echo get_permalink( Helper::getOption('regular_forgot_password_page') )?>" class="booknetic_forgot_password"><img src="<?php echo Helper::icon('question.svg', 'front-end')?>" alt="?"><span><?php echo bkntc__('Forgot password?')?></span></a></div>
			<button type="submit" class="booknetic_btn_primary booknetic_login_btn"><?php echo bkntc__('SIGN IN')?></button>
		</div>
	</form>
	<div class="booknetic_footer">
        <span><?php echo bkntc__('Don\'t have an account?')?></span>
        <a href="<?php echo get_permalink( Helper::getOption('regular_sign_up_page') )?>"><?php echo bkntc__('Sign up')?></a>
	</div>
</div>
