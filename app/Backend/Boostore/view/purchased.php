<?php

use BookneticApp\Providers\Helpers\Helper;

defined( 'ABSPATH' ) or die();

?>

<script>
    window.opener.location.href = 'admin.php?page=<?php echo Helper::getBackendSlug(); ?>&module=boostore&action=my_purchases';
    window.close();
</script>

<?php echo bkntc__( 'Processing...' ); ?>