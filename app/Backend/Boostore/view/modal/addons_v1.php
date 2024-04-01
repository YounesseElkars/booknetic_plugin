<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;

?>

<?php if ( ! empty( $parameters[ 'data' ][ 'items' ] ) ): ?>
    <!-- Search result -->
    <div class="addons_search_result <?php echo ! $parameters[ 'is_search' ] ? 'd-none' : ''; ?>">
        <?php echo bkntc__( '%s results', [ '<span class="search_result">' . $parameters[ 'data' ][ 'count' ] . '</span>' ], false ) ?>
    </div>

    <div class="row addons_card_wrapper">
        <?php foreach ( $parameters[ 'data' ][ 'items' ] as $addon ): ?>
            <div class="card_col col-xl-3 col-lg-4 col-md-6">
                <div class="addons_card">
                    <img src="<?php echo $addon[ 'cover' ]; ?>" alt="<?php echo $addon[ 'name' ]; ?>">
                    <div class="addons_card_content">
                        <span class="card_category"><?php echo $addon[ 'category' ][ 'name' ]; ?></span>

                        <div>
                            <div class="card_stats">
                                <div class="d-flex align-items-center flex-wrap mr-2">
                            <span class="downloads">
                                <i class="far fa-arrow-alt-circle-down"></i>
                                <span><?php echo $addon[ 'downloads' ]; ?></span>
                            </span>
                                </div>
                                <div class="boostore_rating">
                                    <?php for ( $i = 1; $i <= 5; $i++ ): ?>
                                        <i class="fa fa-star <?php echo $addon[ 'rating' ] >= $i ? 'filled' : ''; ?>"></i>
                                    <?php endfor; ?>
                                    <span class="ml-1"><?php echo round( $addon[ 'rating' ], 1 ); ?></span>
                                </div>
                            </div>
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <h4 class="card_name"><?php echo $addon[ 'name' ]; ?></h4>

                                <?php if ( $addon[ 'released' ] ): ?>
                                    <div class="card_price boostore_price d-flex">
                                        <?php if ( $addon[ 'purchase_status' ] === 'owned' ): ?>
                                        <?php elseif ( $addon[ 'price' ][ 'current' ] === 0 ): ?>
                                            <span class="free"><?php echo bkntc__( 'Free' ); ?></span>
                                        <?php else: ?>
                                            <?php if ( $addon[ 'price' ][ 'current' ] < $addon[ 'price' ][ 'old' ] ): ?>
                                                <span class="discount">$<?php echo round( $addon[ 'price' ][ 'old' ], 1 ); ?></span>
                                            <?php endif; ?>
                                            <span>$<?php echo round( $addon[ 'price' ][ 'current' ], 1 ); ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="card_btns">
                            <?php if ( ! empty( $addon[ 'is_installed' ] ) ): ?>
                                <button class="btn btn-outline-danger btn-lg mr-2 mb-2 btn-uninstall" data-addon="<?php echo htmlspecialchars( $addon[ 'slug' ] ); ?>">
                                    <?php echo bkntc__( 'UNINSTALL' ); ?>
                                </button>
                            <?php elseif ( $addon[ 'purchase_status' ] === 'owned' ): ?>
                                <button class="btn btn-success btn-lg mr-2 mb-2 btn-install" data-addon="<?php echo htmlspecialchars( $addon[ 'slug' ] ); ?>">
                                    <?php echo bkntc__( 'INSTALL' ); ?>
                                </button>
                            <?php elseif ( ! $addon[ 'released' ] ): ?>
                                <button class="btn btn-light-warning btn-lg mr-2 mb-2">
                                    <?php echo bkntc__( 'SOON' ); ?>
                                </button>
                            <?php elseif ( ! empty( $addon[ 'error_message' ] ) ): ?>
                                <button class="btn btn-outline-danger btn-lg mr-2 mb-2 do_tooltip" data-placement="bottom" data-content="<?php echo htmlspecialchars( $addon[ 'error_message' ] ); ?>">
                                    <i class="fa fa-exclamation-triangle pr-2"></i>
                                    <?php echo bkntc__( 'CAN\'T INSTALL' ); ?>
                                </button>
                            <?php elseif ( $addon[ 'purchase_status' ] === 'unowned' ): ?>
                                <button class="btn btn-primary btn-lg mr-2 mb-2 btn-purchase" data-addon="<?php echo htmlspecialchars( $addon[ 'slug' ] ); ?>">
                                    <?php echo bkntc__( 'BUY' ); ?>
                                </button>
                            <?php elseif ( $addon[ 'purchase_status' ] === 'pending' ): ?>
                                <button class="btn btn-light-warning btn-lg mr-2 mb-2">
                                    <?php echo bkntc__( 'PENDING...' ); ?>
                                </button>
                            <?php endif; ?>

                            <a class="btn btn-primary btn-lg btn-info mb-2" href="admin.php?page=<?php echo Helper::getBackendSlug(); ?>&module=boostore&action=details&slug=<?php echo $addon[ 'slug' ]; ?>">
                                <?php echo bkntc__( 'More details' ); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <div class="pagination row mt-4">
        <div class="col-md-12 d-flex flex-sm-row flex-column align-items-center justify-content-between">
            <div class="d-flex align-items-center mb-sm-0 mb-3">
                <span class="text-secondary mr-2 font-size-14">
                    <?php echo bkntc__( 'Showing %s of %s total', [ '<span class="pagination_current">' . $parameters[ 'data' ][ 'cur_page' ]  . '</span>', '<span class="pagination_total">' . $parameters[ 'data' ][ 'pages' ] . '</span>' ], false ) ?>
                </span>

                <div class="pagination_content">
                    <?php
                    $current = $parameters[ 'data' ][ 'cur_page' ];
                    $total   = $parameters[ 'data' ][ 'pages' ];

                    if ( $total <= 7 )
                    {
                        $startPage = 2;
                        $endPage   = $total - 1;
                    }
                    else
                    {
                        $startPage = $total - 1;
                        $endPage   = $startPage + 4;

                        if ( $startPage < 2 )
                        {
                            $endPage   += 2 - $startPage;
                            $startPage = 2;
                        }

                        if ( $endPage > $total - 1 )
                        {
                            $startPage -= 1 - ( $total - $endPage );
                            $endPage   = $total - 1;
                        }
                    }
                    ?>

                    <span class="page_class badge <?php echo $current === 1 ? ' active_page badge-default' : ''; ?>">1</span><?php echo $startPage > 2 ? ' ... ' : ''; ?>

                    <?php for ( $i = $startPage; $i <= $endPage; $i++ ): ?>
                        <span class="page_class badge<?php echo $i === $current ? ' active_page badge-default' : ''; ?>"><?php echo $i; ?></span>
                    <?php endfor; ?>

                    <?php if ( $total >= 2 ): ?>
                        <?php echo $total - 1 > $endPage ? ' ... ' : ''; ?><span
                        class="page_class badge<?php echo $total === $current ? ' active_page badge-default' : ''; ?>"><?php echo $total; ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <a href="<?php echo htmlspecialchars( 'https://www.booknetic.com/documentation/' ); ?>" class="need_help_btn"
               target="_blank"><i class="far fa-question-circle"></i> <?php echo bkntc__( 'Need Help?' ); ?></a>
        </div>
    </div>
<?php else: ?>
    <div class="text-muted"><?php echo bkntc__( 'Add-ons not found!' ); ?></div>
<?php endif; ?>