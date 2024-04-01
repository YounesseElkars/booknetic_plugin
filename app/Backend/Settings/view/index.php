<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\Core\Permission;

/**
 * @var mixed $parameters
 */

$openSetting = Helper::_get('setting', '', 'string');
$success = Helper::_get('success', '', 'string');
$msg = Helper::_get('msg', '', 'string');
?>

<link rel="stylesheet" href="<?php echo Helper::assets('css/settings.css', 'Settings')?>">
<link rel="stylesheet" href="<?php echo Helper::assets('css/bootstrap-year-calendar.min.css')?>">
<script type="application/javascript" src="<?php echo Helper::assets('js/bootstrap-year-calendar.min.js')?>"></script>
<script type="application/javascript" src="<?php echo Helper::assets('js/settings.js', 'Settings')?>" id="settingsJS" <?php if( !empty($openSetting) ) echo 'data-goto="'.htmlspecialchars($openSetting).'"'; ?>></script>

<div class="m_header clearfix">
	<div class="m_head_title float-left"><?php echo bkntc__('Settings')?></div>
</div>

<div class="row mr-0">
	<button class="btn btn-primary btn-lg settings-floating-button is-left hidden-important"></button>

	<div class="col-md-12 col-xl-3 col-lg-12 d-none d-xl-block settings-left-menu hidden-important">

		<div>
			<div class="settings-left-menu-title"><?php echo bkntc__('Settings')?> <span class="badge badge-warning row_count">9</span></div>

			<div class="service_categories_list">
				<?php foreach ( $parameters[ 'menu' ] as $menu ): ?>
                    <?php if ( ! $menu->isSubItemsRequired() || ( $menu->isSubItemsRequired() && ! empty( $menu->getSubItems() ) ) ): ?>
                        <div class="settings_menu" data-view="<?php echo $menu->getSlug(); ?>">
                            <div class="sc-bars-cls"><img src="<?php echo $menu->getIcon(); ?>"></div>
                            <div class="sc-title">
                                <div class="sc-title-div"><?php echo $menu->getTitle(); ?></div>
                                <div class="sc-description"><?php echo $menu->getDescription(); ?></div>
                            </div>

                            <?php if ( ! empty( $menu->getSubItems() ) ): ?>
                                <div class="clearfix"></div>

                                <div class="settings_submenus">
                                    <?php foreach ( $menu->getSubItems() as $submenu ): ?>
                                        <div data-view="<?php echo $submenu->getSlug(); ?>"><?php echo $submenu->getTitle(); ?></div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
				<?php endforeach; ?>

			</div>
		</div>

	</div>
	<div class="col-md-12 col-xl-9 col-lg-12 settings-main-container hidden"></div>
	<div class="col-md-12 settings-main-menu">
		<div>
			<div class="row">

				<?php foreach ( $parameters[ 'menu' ] as $menu ): ?>
                    <?php if ( ! $menu->isSubItemsRequired() || ( $menu->isSubItemsRequired() && ! empty( $menu->getSubItems() ) ) ): ?>
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
                            <div class="settings-chart" data-view="<?php echo $menu->getSlug(); ?>">
                                <div class="settings-icon"><img src="<?php echo $menu->getIcon(); ?>"></div>
                                <div class="settings-title"><?php echo $menu->getTitle(); ?></div>
                                <div class="settings-description"><?php echo $menu->getDescription(); ?></div>
                            </div>
                        </div>
                    <?php endif; ?>
				<?php endforeach; ?>

			</div>
		</div>
	</div>
</div>

<?php if( $success === 'false' && !empty( $msg ) ): ?>
	<script>
		booknetic.toast("<?php echo htmlspecialchars($msg)?>", 'unsuccess');
	</script>
<?php endif;?>
