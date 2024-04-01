<?php


namespace BookneticApp\Providers\Common\Elementor\Widgets;

use Elementor\Plugin;
use Elementor\Widget_Base;

if (!defined('ABSPATH')) {
    exit;
}


class ChangeStatusElementor extends Widget_Base
{
    private $atts;
    private $shortCodeContent;
    private $shortcodeKey = 'booknetic-change-status';
    public static $allowInSaaS = true;

    public function __construct($data = [], $args = null)
    {
        parent::__construct($data, $args);

    }



    public function get_name()
    {
        return 'booknetic-change-status';
    }


    public function get_title()
    {
        return esc_html__('Booknetic Change Status', 'booknetic-change-status');
    }


    public function get_icon()
    {
        return 'eicon-shortcode';
    }


    public function get_custom_help_url()
    {
        return 'https://www.booknetic.com/documentation/change-appointment-status-via-the-link';
    }


    public function get_categories()
    {
        return ['general'];
    }

    public function get_keywords()
    {
        return ['bkntc', 'booknetic', 'booking', 'change-status', 'status'];
    }

    protected function register_controls()
    {

        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__('Change Status Settings', 'booknetic-change-status'),
            ]
        );

        $this->add_control(
            'label',
            [
                'label' => esc_html__('Label', 'booknetic-change-status'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
                'placeholder' => esc_html__( 'Do you want to change your appointment status to {status}', 'booknetic-change-status' ),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'successLabel',
            [
                'label' => esc_html__('Success Label', 'booknetic-change-status'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
                'placeholder' => esc_html__( 'Success Button Text', 'booknetic-change-status' ),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'button',
            [
                'label' => esc_html__('Change Button Text', 'booknetic-change-status'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
                'placeholder' => esc_html__( 'Change Button Text', 'booknetic-change-status' ),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'successButton',
            [
                'label' => esc_html__('Change Success Button Text', 'booknetic-change-status'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
                'placeholder' => esc_html__( 'Change Success Button Text', 'booknetic-change-status' ),
                'label_block' => true,
            ]
        );


        $this->end_controls_section();

    }

    private function getShortCodeContent( $settings )
    {

        $this->atts = [
            'label'         => $settings['label'],
            'successLabel'  => $settings['successLabel'],
            'button'        => $settings['button'],
            'successButton' => $settings['successButton']
        ];

        $shortcode = '[' . $this->shortcodeKey;

        foreach ($this->atts as $key => $value)
        {
            if ( ! empty( $value ) )
            {
                $shortcode .= " $key=\"" . preg_replace( "/[\"\”\“]+/", '', $value ) . '"';

            }
        }

        $shortcode .= ']';

        $this->shortCodeContent = $shortcode;
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $this->print_render_attribute_string('booknetic');

        $this->getShortCodeContent( $settings );

        if ( Plugin::$instance->editor->is_edit_mode() )
        {
            ?>
            <script> var bookneticElementor = { 'url' : '<?php echo urlencode( site_url() ) ?>' } </script>
            <script src="<?php echo rtrim(plugin_dir_url( dirname( __DIR__ ) ), '/') . ucfirst('/Elementor') . '/assets/frontend/js/' . ltrim('booknetic-change-status-elementor.js', '/'); ?>"></script>
            <?php
        }

        echo '<div id="bookneticChangeStatusElementorContainer">'. $this->shortCodeContent .'</div>';

    }


}
