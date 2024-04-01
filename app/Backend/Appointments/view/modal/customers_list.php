<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Date;

/**
 * @var mixed $parameters
 */

?>

<div class="clist-area hidden">
	<?php
	foreach( $parameters['customers'] AS $customer )
	{
        $badge = '';
        if ( !empty($customer['billing_full_name']) )
        {
            $billingFullName = $customer['billing_full_name'];
            $billingPhone = $customer['billing_phone'];
            $badge .= '<div class="dropdown">';
            $badge .=   '<button type="button" class="btn btn-xs btn-dark-default ml-1" data-toggle="dropdown"> <i class="far fa-user-circle"></i> </button>';
            $badge .=   '<div class="dropdown-menu billing_names-popover">';
            $badge .=       '<h6>' . bkntc__('Billing info') . '</h6>';
            $badge .=       '<div class="billing_names-popover--cards">';
            $badge .=           "<div><h6>$billingFullName</h6><span>$billingPhone</span></div>";
            $badge .=       '</div>';
            $badge .=   '</div>';
            $badge .= '</div>';
        }

		echo '<div class="list_left_right_box">';
			echo '<div class="list_left_box">';
			echo Helper::profileCard( $customer['customer_name'], $customer['profile_image'], $customer['email'], 'Customers' );
			echo '</div>';
            echo $badge;
			echo '<div class="list_right_box">';
			echo '<span class="list_right_box_date">' . Date::datee($customer['created_at']) .  '</span>';
			echo '<div class="list_right_box_user"><i class="fa fa-user"></i><span>' . $customer['weight'] .  '</span></div>';
			echo '<div class="appointment-status-' . htmlspecialchars( $customer['status'] ) .'"></div>';
            echo '<span class="list_right_box_date" style="margin-left: 10px">#' . $customer['id'] .  '</span>';

        echo '</div>';
		echo '</div>';
	}
	?>
</div>

<script>
	$(".clist-area").fadeIn(400);
</script>
