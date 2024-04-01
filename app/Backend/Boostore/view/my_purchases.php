<?php

use BookneticApp\Providers\Helpers\Math;
use BookneticApp\Providers\Helpers\Helper;

defined( 'ABSPATH' ) or die();

/**
 * @var $parameters
 */

?>

<div class="m_header clearfix">
    <div class="m_head_title float-left">
        <div class="m_head_title float-left">
            <a href="admin.php?page=<?php echo Helper::getBackendSlug(); ?>&module=boostore"><?php echo bkntc__( 'Add-ons' ); ?></a>
            <i class="mx-2"><img src="<?php echo Helper::icon( 'arrow.svg' ); ?>"></i>
            <span class="name"><?php echo bkntc__( 'My purchases' ); ?></span>
        </div>
    </div>
    <?php if( $parameters['version'] == 2 ): ?>
        <div class="m_head_actions float-right">
            <a class="btn btn-lg btn-warning" href="admin.php?page=<?php echo Helper::getBackendSlug(); ?>&module=cart"> <i class="fa fa-shopping-cart mr-2" aria-hidden="true"></i> <?php echo bkntc__( 'CART' ); ?> <span class="badge badge-info" id="bkntc_cart_items_counter"><?php echo $parameters[ 'cart_items_count' ]; ?></span> </a>
        </div>
    <?php endif; ?>
</div>

<div class="fs_separator"></div>

<div class="m_content pt-0" id="fs_data_table_div">
    <div class="fs_data_table_wrapper">
        <table class="fs_data_table elegant_table">
            <thead>
            <tr>
                <th></th>
                <th><?php echo bkntc__( 'Add-on' ); ?></th>
                <th><?php echo bkntc__( 'Status' ); ?></th>
                <th><?php echo bkntc__( 'Amount' ); ?></th>
                <th><?php echo bkntc__( 'Purchased on' ); ?></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php if ( empty( $parameters[ 'items' ] ) ): ?>
                <tr>
                    <td colspan="100%" class="pl-4 text-secondary"><?php echo bkntc__( 'No entries!' ); ?></td>
                </tr>
            <?php else: ?>
                <?php foreach ( $parameters[ 'items' ] as $purchase ): ?>
                    <tr>
                        <td></td>
                        <td>
                            <?php echo htmlspecialchars( $purchase[ 'addon' ] ); ?>&emsp;
                            <a href="admin.php?page=<?php echo Helper::getBackendSlug(); ?>&module=boostore&action=details&slug=<?php echo $purchase[ 'slug' ]; ?>">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </td>
                        <td>
                            <div class="btn btn-xs btn-light-<?php echo $purchase[ 'status' ][ 'status' ] === 'owned' ? 'success' : 'warning'; ?> <?php echo ! empty( $purchase[ 'status' ][ 'message' ] ) ? 'do_tooltip' : ''; ?>" <?php echo ! empty( $purchase[ 'status' ][ 'message' ] ) ? 'data-content="' . htmlspecialchars( $purchase[ 'status' ][ 'message' ] ) . '"' : ''; ?>><?php echo htmlspecialchars( $purchase[ 'status' ][ 'status_text' ] ); ?></div>
                        </td>
                        <td>$<?php echo Math::floor( $purchase[ 'amount' ], 2 ); ?></td>
                        <td><?php echo htmlspecialchars( $purchase[ 'purchased_on' ] ); ?></td>
                        <td class="text-right">
                            <?php if ( $purchase[ 'is_installed' ] ): ?>
                                <button class="btn btn-outline-danger btn-uninstall" data-addon="<?php echo htmlspecialchars( $purchase[ 'slug' ] ); ?>">
                                    <?php echo bkntc__( 'UNINSTALL' ); ?>
                                </button>
                            <?php elseif ( $purchase[ 'status' ][ 'status' ] === 'owned' ): ?>
                                <button class="btn btn-success btn-install" data-addon="<?php echo htmlspecialchars( $purchase[ 'slug' ] ); ?>">
                                    <?php echo bkntc__( 'INSTALL' ); ?>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="<?php echo Helper::assets( 'js/shared.js', 'Boostore' ); ?>"></script>
<script src="<?php echo Helper::assets( 'js/my_purchases.js', 'Boostore' ); ?>"></script>

<?php if ( $parameters[ 'is_migration' ] ): ?>
    <div id="migrationModal" class="modal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-4">
                <div class="progress mb-4" style="height: 8px;">
                    <div id="migrationProgress" class="progress-bar"></div>
                </div>

                <div class="mb-2">
                    <?php echo bkntc__( 'We are migrating your data.' ); ?><br>
                    <?php echo bkntc__( 'Please wait until the migration process is done.' ); ?><br>
                </div>

                <div class="text-danger">
                    <?php echo bkntc__( 'Do not leave the page.' ); ?>
                </div>
            </div>

        </div>
    </div>

    <script src="<?php echo Helper::assets( 'js/migration.js', 'Boostore' ); ?>"></script>
<?php endif; ?>
