<?php


namespace BookneticApp\Providers\Common\Elementor\Widgets;

use Elementor\Plugin;
use Elementor\Widget_Base;

if (!defined('ABSPATH')) {
    exit;
}


class ForgotPasswordElementor extends Widget_Base
{

    private $atts;
    private $shortCodeContent;
    private $shortcodeKey = 'booknetic-forgot-password';
    public static $allowInSaaS = true;

    public function __construct($data = [], $args = null)
    {
        parent::__construct($data, $args);

    }

    public function get_name()
    {
        return 'booknetic-forgot-password';
    }


    public function get_title()
    {
        return esc_html__('Booknetic Customer Forgot Password', 'booknetic-forgot-password');
    }


    public function get_icon()
    {
        return 'eicon-shortcode';
    }


    public function get_custom_help_url()
    {
        return 'https://www.booknetic.com/documentation/sign-in-to-booknetic';
    }


    public function get_categories()
    {
        return ['general'];
    }

    public function get_keywords()
    {
        return ['bkntc', 'booknetic', 'booking', 'forgot-password', 'forgotpassword'];
    }

    protected function register_controls()
    {
        $this->start_controls_section('content_section');

        $this->end_controls_section();
    }

    private function getShortCodeContent()
    {
        $shortcode = '[' . $this->shortcodeKey . ']';

        $this->shortCodeContent = $shortcode;
    }

    protected function render()
    {

        $this->getShortCodeContent();

        if ( Plugin::$instance->editor->is_edit_mode() )
        {
            ?>
            <script> var bookneticElementor = { 'url' : '<?php echo urlencode( site_url() ) ?>' } </script>
            <script src="<?php echo rtrim(plugin_dir_url( dirname( __DIR__ ) ), '/') .  ucfirst('/Elementor') . '/assets/frontend/js/' . ltrim('booknetic-forgot-password-elementor.js', '/'); ?>"></script>
            <?php
        }

        echo '<div id="bookneticForgotPasswordElementorContainer">'. $this->shortCodeContent .'</div>';

    }


}
