<?php

use BookneticApp\Providers\Helpers\Helper;

defined( "ABSPATH" ) or die();

require_once ABSPATH . 'wp-admin/includes/translation-install.php';
/**
 * @var array $parameters
 */

$translations = wp_get_available_translations();

$translations['en_US'] = [
        'language'  => 'en_US',
        'english_name' => 'English (US)',
        'native_name' => 'English (US)',
];
?>

<link rel="stylesheet" href="<?php echo Helper::assets( 'css/translations.css' ) ?>">
<script src="<?php echo Helper::assets( 'js/translation.js' ) ?>"></script>

<div class="fs-modal-title">
    <div class="title-icon badge-lg badge-purple"><i class="fa fa-plus"></i></div>
    <div class="title-text"><?php echo bkntc__( 'Edit translation' ) ?></div>
    <div class="close-btn" data-dismiss="modal"><i class="fa fa-times"></i></div>
</div>

<div class="fs-modal-body">
    <div class="fs-modal-inner fs-multilang-modal-inner">

        <div class="form-row bkntc_multilang_row">
            <div class="col-md-4 col-4">
                <input class="form-control" value="<?php echo bkntc__('Default') ?>" disabled>
            </div>
            <div class="col-md-7 col-6">
                <?php if( $parameters[ 'node' ] === 'textarea' ):?>
                    <textarea disabled id="bkntc_default_value" class="form-control bkntc_multilang_value"></textarea>
                <?php else:?>
                    <input disabled id="bkntc_default_value" class="form-control bkntc_multilang_value">
                <?php endif;?>
            </div>
            <button type="button" class="btn btn-primary" id="copyTranslatingValueBtn">
                <i class="fas fa-copy"></i>
            </button>
        </div>

        <form id="bkntcEditMultilangForm" data-id="<?php echo $parameters[ 'id' ] ?>" data-column="<?php echo $parameters[ 'column' ] ?>" data-table="<?php echo $parameters[ 'table' ]; ?>">

            <?php foreach ( $parameters[ "translations" ] as $item ):?>
                <div class="form-row bkntc_multilang_row" data-id="<?php echo isset( $item[ 'id' ] ) ? $item[ 'id' ] : null ?>">
                    <div class="col-md-4 col-4">
                        <select class="bkntc_multilang_row_locale form-control">
                            <?php foreach ( $translations as $translation ): ?>
                                <option <?php if ( $translation[ 'language' ] === $item[ 'locale' ] ) echo 'selected';?> value="<?php echo $translation['language'] ?>"><?php echo $translation[ 'native_name' ] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-7 col-6">
                        <?php if( $parameters[ 'node' ] === 'textarea' ):?>
                            <textarea class="form-control bkntc_multilang_value"><?php echo $item[ 'value' ] ?></textarea>
                        <?php else:?>
                            <input class="form-control bkntc_multilang_value" value="<?php echo $item[ 'value' ] ?>">
                        <?php endif;?>
                    </div>
                    <button type="button" class="bkntc_delete_multilang_btn btn btn-danger">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            <?php endforeach;?>

            <button type="button" id="bkntcAddNewTranslationBtn" class="btn btn-primary btn-text " >
                <i class="fas fa-plus mr-2"></i>
                <?php echo bkntc__('Add new translation'); ?>
            </button>
        </form>
    </div>
</div>

<div class="fs-modal-footer">
    <button class="btn btn-default btn-lg" data-dismiss="modal"><?php echo bkntc__('CLOSE'); ?></button>
    <button id="bkntcSaveTranslationsBtn" class="btn btn-primary btn-lg"><?php echo bkntc__('SAVE CHANGES'); ?></button>
</div>

<div class="hidden" id="bkntc_translations_template">
    <div class="form-row bkntc_multilang_row">
        <div class="col-md-4 col-4">
            <select class="form-control">
                <?php foreach ( $translations as $translation ): ?>
                    <option value="<?php echo $translation['language'] ?>"><?php echo $translation[ 'native_name' ] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-7 col-6">
            <?php if( $parameters[ 'node' ] === 'textarea' ):?>
                <textarea class="form-control bkntc_multilang_value"></textarea>
            <?php else:?>
                <input class="form-control bkntc_multilang_value">
            <?php endif;?>
        </div>
        <button type="button" class="bkntc_delete_multilang_btn btn btn-danger">
            <i class="fas fa-trash-alt"></i>
        </button>
    </div>
</div>


