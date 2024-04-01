<button
        data-location="<?php echo (isset($parameters['location'])     && is_numeric($parameters['location']) )   ? htmlspecialchars($parameters['location']) : ''; ?>"
        data-theme="<?php echo (isset($parameters['theme'])           && is_numeric($parameters['theme']) )      ? htmlspecialchars($parameters['theme']) : ''; ?>"
        data-category="<?php echo (isset($parameters['category'])     && is_numeric($parameters['category']) )   ? htmlspecialchars($parameters['category']) : ''; ?>"
        data-staff="<?php echo (isset($parameters['staff'])           && is_numeric($parameters['staff']) )      ? htmlspecialchars($parameters['staff']) : ''; ?>"
        data-service="<?php echo (isset($parameters['service'])       && is_numeric($parameters['service']) )    ? htmlspecialchars($parameters['service']) : ''; ?>"
        class='bnktc_booking_popup_btn <?php echo isset($parameters['class']) ? htmlspecialchars($parameters['class']) : "" ?>'
        <?php echo isset($parameters['style']) ? 'style="'. htmlspecialchars($parameters['style']) .'"' : '' ?>>
    <?php echo isset($parameters['caption']) ? htmlspecialchars($parameters['caption']) : bkntc__( 'Book now' ) ;?>
</button>
<script type="text/javascript">
    var ajaxurl = <?php echo wp_json_encode( admin_url( 'admin-ajax.php', 'relative' ) ); ?>;
</script>
<script type="application/javascript" src="<?php echo \BookneticApp\Providers\Helpers\Helper::assets('js/booknetic-popup-button.js', 'front-end' )?>" ></script>