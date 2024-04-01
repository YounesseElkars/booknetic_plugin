<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Date;

/**
 * @var mixed $parameters
 */

echo $parameters['table'];
?>

<script type="application/javascript" src="<?php echo Helper::assets('js/payments.js', 'Payments')?>"></script>


