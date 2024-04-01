<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Core\Permission;
use BookneticApp\Providers\Helpers\Helper;

/**
 * @var mixed $parameters
 */

echo $parameters['table'];
?>


<link rel="stylesheet" href="<?php echo Helper::assets('css/bootstrap-year-calendar.min.css')?>">
<script type="application/javascript" src="<?php echo Helper::assets('js/bootstrap-year-calendar.min.js')?>"></script>
<script>
    booknetic.can_delete_associated_account = <?php echo ( Permission::isAdministrator() || Capabilities::userCan( 'staff_delete_wordpress_account' ) ) ? 1 : 0 ?>;
</script>
<script type="application/javascript" src="<?php echo Helper::assets('js/staff.js', 'Staff')?>" id="staff-js12394610" data-edit="<?php echo $parameters['edit']?>"></script>
