<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;

/**
 * @var mixed $parameters
 */

echo $parameters['table'];
?>
<script type="text/javascript" src="<?php echo Helper::assets('js/workflow.js', 'Workflow')?>"></script>
