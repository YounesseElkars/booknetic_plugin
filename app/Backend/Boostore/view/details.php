<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Math;
use BookneticApp\Providers\Helpers\Helper;

?>

<link rel="stylesheet" href="<?php echo Helper::assets( 'css/shared.css', 'Boostore' ) ?>" type="text/css">
<link rel="stylesheet" href="<?php echo Helper::assets( 'css/details.css', 'Boostore' ) ?>" type="text/css">

<?php if ( ! empty( $parameters[ 'addon' ] ) ) {
    $addon = $parameters[ 'addon' ]; ?>
    <div class="boostore">
        <!-- Page header -->
        <div class="m_header clearfix">
            <div class="m_head_title float-left">
                <a href="admin.php?page=<?php echo Helper::getBackendSlug(); ?>&module=boostore"><?php echo bkntc__( 'Add-ons' ); ?></a>
                <i class="mx-2"><img src="<?php echo Helper::icon( 'arrow.svg' ); ?>"></i>
                <span class="name"><?php echo $addon[ 'name' ]; ?></span>
            </div>
        </div>

        <div class="fs_separator"></div>

        <!-- Addon info -->
        <section class="row details_info">
            <div class="col-lg-7 col_content order-lg-1 order-2 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <h1 class="mb-2"><?php echo htmlspecialchars( $addon[ 'name' ] ); ?></h1>

                        <span class="info_category">
                            <?php echo htmlspecialchars( $addon[ 'category' ][ 'name' ] ); ?>
                        </span>

                        <div class="boostore_rating mt-4">
                            <?php for ( $i = 1; $i <= 5; $i++ ): ?>
                                <i class="fa fa-star mr-1 <?php echo $addon[ 'rating' ] >= $i ? 'filled' : '' ?>"></i>
                            <?php endfor ?>

                            <span class="ml-1"><?php echo number_format( $addon[ 'rating' ], 1 ) ?></span>
                        </div>
                    </div>
                    <?php if ( $addon[ 'released' ] ): ?>
                        <div class="info_price boostore_price d-flex align-items-start mt-1">
                            <?php if ( $addon[ 'purchase_status' ] === 'owned' ): ?>
                            <?php elseif ( $addon[ 'price' ][ 'current' ] === 0 ): ?>
                                <span class="free"><?php echo bkntc__( 'Free' ) ?></span>
                            <?php else: ?>
                                <?php if ( $addon[ 'price' ][ 'current' ] < $addon[ 'price' ][ 'old' ] ): ?>
                                    <span class="discount"><?php echo '$' . Math::floor( $addon[ 'price' ][ 'old' ], 1 ) ?></span>
                                <?php endif ?>

                                <span><?php echo '$' . Math::floor( $addon[ 'price' ][ 'current' ], 1 ) ?></span>
                            <?php endif ?>
                        </div>
                    <?php endif ?>
                </div>
                <div class="">
                    <?php if ( ! empty( $addon[ 'is_installed' ] ) ): ?>
                        <button class="btn btn-outline-danger btn-lg mt-4 btn-uninstall" data-addon="<?php echo htmlspecialchars( $addon[ 'slug' ] ); ?>">
                            <?php echo bkntc__( 'UNINSTALL' ); ?>
                        </button>
                    <?php elseif ( $addon[ 'purchase_status' ] === 'owned' ): ?>
                        <button class="btn btn-success btn-lg mt-4 btn-install" data-addon="<?php echo htmlspecialchars( $addon[ 'slug' ] ); ?>">
                            <?php echo bkntc__( 'INSTALL' ); ?>
                        </button>
                    <?php elseif ( ! $addon[ 'released' ] ): ?>
                        <button class="btn btn-light-warning btn-lg mr-2 mb-2">
                            <?php echo bkntc__( 'SOON' ); ?>
                        </button>
                    <?php elseif ( ! empty( $addon[ 'error_message' ] ) ): ?>
                        <div class="text-danger">
                            <i class="fa fa-exclamation-triangle"></i>
                            <?php echo htmlspecialchars( $addon[ 'error_message' ] ); ?>
                        </div>
                    <?php elseif ( $addon[ 'in_cart' ] === true && $parameters[ 'version' ] == 2 ): ?>
                        <a class="btn btn-lg btn-warning view_cart_btn mb-2 mr-2" href="admin.php?page=<?php echo Helper::getBackendSlug(); ?>&module=cart"> <i class="fa fa-shopping-cart mr-2" aria-hidden="true"></i> <?php echo bkntc__( 'VIEW CART' ); ?> </a>
                    <?php elseif ( $addon[ 'purchase_status' ] === 'unowned' && $parameters[ 'version' ] == 2 ): ?>
                        <button class="btn btn-primary btn-lg mr-2 mb-2 btn-add-to-cart" data-addon="<?php echo htmlspecialchars( $addon[ 'slug' ] ); ?>">
                            <?php echo bkntc__( 'ADD TO CART' ); ?>
                        </button>
                    <?php elseif ( $addon[ 'purchase_status' ] === 'unowned' ): ?>
                        <button class="btn btn-primary btn-lg mt-4 btn-purchase" data-addon="<?php echo htmlspecialchars( $addon[ 'slug' ] ); ?>">
                            <?php echo bkntc__( 'BUY' ); ?>
                        </button>
                    <?php elseif ( $addon[ 'purchase_status' ] === 'pending' ): ?>
                        <button class="btn btn-light-warning btn-lg mt-4">
                            <?php echo bkntc__( 'PENDING...' ); ?>
                        </button>
                    <?php endif; ?>
                </div>

            </div>

            <div class="col-lg-5 d-flex align-items-center col_img order-lg-2 order-1">
                <img src="<?php echo $addon[ 'cover' ] ?>" alt="<?php echo $addon[ 'cover' ] ?>">
            </div>
        </section>

        <section class="details_content">
            <div>
                <!-- Tabs -->
                <ul class="nav nav-tabs nav-light">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#tab_details"><?php echo bkntc__( 'Details' ); ?></a>
                    </li>
                </ul>

                <div class="tab-content mt-5">

                    <!-- Details -->
                    <section id="tab_details" class="tab-pane active">
                        <div class="row">
                            <!-- Content -->
                            <div class="col-lg-8 col_content order-lg-1 order-2">
                                <?php echo $addon[ 'description' ]; ?>
                            </div>

                            <!-- Info -->
                            <div class="col-lg-4 col_info order-lg-2 order-1 mb-lg-0 mb-5">
                                <div>
                                    <?php if ( ! empty( $addon[ 'latest_version' ][ 'version_string' ] ) ): ?>
                                        <div class="info_item">
                                            <b><?php echo bkntc__( 'Latest version' ); ?>:</b>
                                            <span><?php echo htmlspecialchars( $addon[ 'latest_version' ][ 'version_string' ] ); ?></span>
                                        </div>

                                        <?php if ( ! empty( $addon[ 'latest_compatible_version' ][ 'version' ] ) && $addon[ 'latest_version' ][ 'version' ] > $addon[ 'latest_compatible_version' ][ 'version' ] ): ?>
                                            <div class="info_not-compatible text-danger">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <?php echo bkntc__( 'Latest version %s requires minimum Booknetic %s.', [ $addon[ 'latest_version' ][ 'version_string' ], $addon[ 'latest_version' ][ 'required_booknetic_version_string' ] ] ); ?>
                                            </div>

                                            <div class="info_item">
                                                <b><?php echo bkntc__( 'Compatible version' ); ?>:</b>
                                                <span><?php echo htmlspecialchars( $addon[ 'latest_compatible_version' ][ 'version_string' ] ); ?></span>
                                            </div>
                                        <?php else: ?>
                                            <div class="info_not-compatible text-success">
                                                <i class="fas fa-check-circle"></i>
                                                <?php echo bkntc__( 'Latest version is compatible with your Booknetic version.' ); ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>



                                    <?php foreach ( $addon[ 'info' ] as $k => $v ): ?>
                                        <div class="info_item"><b><?php echo htmlspecialchars( $k ); ?>:</b>
                                            <span><?php echo htmlspecialchars( $v ); ?></span></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </section>

                </div>
            </div>
        </section>
    </div>
<?php } ?>

<script src="<?php echo Helper::assets( 'js/shared.js', 'Boostore' ); ?>"></script>
<script src="<?php echo Helper::assets( 'js/details.js', 'Boostore' ); ?>"></script>
