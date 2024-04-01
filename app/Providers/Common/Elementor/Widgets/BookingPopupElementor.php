<?php


namespace BookneticApp\Providers\Common\Elementor\Widgets;

use BookneticApp\Models\Appearance;
use BookneticApp\Models\Location;
use BookneticApp\Models\Service;
use BookneticApp\Models\ServiceCategory;
use BookneticApp\Models\Staff;
use Elementor\Plugin;
use Elementor\Widget_Base;

if (!defined('ABSPATH')) {
    exit;
}


class BookingPopupElementor extends Widget_Base
{

    private $props;
    private $atts;
    private $shortCodeContent;
    private $shortcodeKey = 'booknetic-booking-button';
    public static $allowInSaaS = true;


    public function __construct($data = [], $args = null)
    {
        parent::__construct($data, $args);

        $bookneticData = [
            'appearances' => Appearance::select(['id', 'name'])->fetchAll(),
            'staff' => Staff::select(['id', 'name'])->fetchAll(),
            'services' => Service::select(['id', 'name'])->fetchAll(),
            'service_categories' => ServiceCategory::select(['id', 'name'])->fetchAll(),
            'locations' => Location::select(['id', 'name'])->fetchAll()
        ];

        foreach ($bookneticData as $key => $data) {

            $this->props[$key] = [];

            foreach ($data as $item)
            {
                $this->props[$key][$item->id] = $item->name;
            }

            $this->props[$key][0] = '- - - - - - - - - -';
        }

    }



    public function get_name()
    {
        return 'booknetic-popup';
    }


    public function get_title()
    {
        return esc_html__('Booknetic Booking Popup', 'booknetic-popup');
    }


    public function get_icon()
    {
        return 'eicon-shortcode';
    }


    public function get_custom_help_url()
    {
        return 'https://www.booknetic.com/documentation/booking-panel-in-popup-modal';
    }


    public function get_categories()
    {
        return ['general'];
    }

    public function get_keywords()
    {
        return ['bkntc', 'booknetic', 'booking', 'popup', 'booking-popup'];
    }

    protected function register_controls()
    {

        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__('Booknetic Popup Settings', 'booknetic-popup'),
            ]
        );

        $this->add_control(
            'appearance',
            [
                'label' => esc_html__('Appearances', 'booknetic-popup'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $this->props['appearances'],
            ]
        );

        $this->add_control(
            'staff_filter',
            [
                'label' => esc_html__('Staff filter', 'booknetic-popup'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $this->props['staff'],
            ]
        );

        $this->add_control(
            'service_filter',
            [
                'label' => esc_html__('Service filter', 'booknetic-popup'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $this->props['services'],
            ]
        );

        $this->add_control(
            'category_filter',
            [
                'label' => esc_html__('Category filter', 'booknetic-popup'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $this->props['service_categories'],
            ]
        );

        $this->add_control(
            'location_filter',
            [
                'label' => esc_html__('Location filter', 'booknetic-popup'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $this->props['locations'],
            ]
        );

        $this->add_control(
            'caption',
            [
                'label' => esc_html__('Caption', 'booknetic-change-status'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
                'placeholder' => esc_html__( 'Text', 'booknetic-change-status' ),
                'label_block' => false,
            ]
        );

        $this->add_control(
            'class',
            [
                'label' => esc_html__('Class', 'booknetic-change-status'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
                'placeholder' => esc_html__( 'Text', 'booknetic-change-status' ),
                'label_block' => false,
            ]
        );

        $this->add_control(
            'style',
            [
                'label' => esc_html__('Style', 'booknetic-change-status'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => '',
                'placeholder' => esc_html__( 'Text', 'booknetic-change-status' ),
            ]
        );

        $this->end_controls_section();

    }

    private function getShortCodeContent( $settings )
    {

        $this->atts = [
            'theme' => $settings['appearance'],
            'staff' => $settings['staff_filter'],
            'service' => $settings['service_filter'],
            'category' => $settings['category_filter'],
            'location' => $settings['location_filter'],
            'caption' => $settings['caption'],
            'class' => $settings['class'],
            'style' => $settings['style'],
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

        $this->getShortCodeContent( $settings );

        if ( Plugin::$instance->editor->is_edit_mode() )
        {
            ?>
            <script> var bookneticElementor = { 'url' : '<?php echo urlencode( site_url() ) ?>' } </script>
            <script src="<?php echo rtrim(plugin_dir_url( dirname( __DIR__ ) ), '/') . ucfirst('/Elementor') . '/assets/frontend/js/' . ltrim('booknetic-popup-elementor.js', '/'); ?>"></script>
            <?php
        }

        echo '<div id="bookneticPopupElementorContainer">'. $this->shortCodeContent .'</div>';

    }


}
