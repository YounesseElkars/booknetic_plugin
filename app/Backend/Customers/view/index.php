<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Core\Permission;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Date;

/**
 * @var mixed $parameters
 */

echo $parameters['table'];
?>
<script>
    booknetic.can_delete_associated_account = <?php echo ( Permission::isAdministrator() || Capabilities::userCan( 'customers_delete_wordpress_account' ) ) ? 1 : 0 ?>
</script>
<script type="application/javascript" src="<?php echo Helper::assets('js/customers.js', 'Customers')?>"></script>
