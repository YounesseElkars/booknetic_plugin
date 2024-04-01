<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;

/**
 * @var mixed $parameters
 */

?>

<div class="booknetic_signup" data-token="<?php echo htmlspecialchars($parameters['activation_token'])?>">
    <div class="booknetic_step_3">
        <div class="booknetic_signup_completed">
            <img src="<?php echo Helper::assets('images/signup-success2.svg', 'front-end')?>" alt="">
        </div>
        <div class="booknetic_signup_completed_title"><?php echo bkntc__('Congratulations!')?></div>
        <div class="booknetic_signup_completed_subtitle">
            <?php echo bkntc__('You have successfully signed up!')?>
            <?php echo bkntc__( 'You will be redirected in ' ) . ' <span id="bkntc_timer">5</span>'?>

            <script>
                let timer = 5;
                setTimeout( () => { window.location.href = decodeURIComponent( "<?php echo $parameters[ 'redirect_to' ]; ?>" ) }, 5000 );
                setInterval( () => { timer -= 1; document.querySelector("#bkntc_timer").innerHTML = timer }, 1000 )
            </script>
        </div>
        <div class="booknetic_signup_completed_footer">
        </div>
    </div>
</div>
