<?php

use BookneticApp\Models\Appearance;
use BookneticApp\Models\Location;
use BookneticApp\Models\Service;
use BookneticApp\Models\ServiceCategory;
use BookneticApp\Models\Staff;

class BookingPopup extends ET_Builder_Module {

	public $slug       = 'booknetic_booking_popup';
	public $vb_support = 'on';
    private $data;

	protected $module_credits = array(
		'module_uri' => '',
		'author'     => '',
		'author_uri' => '',
	);

	public function init() {
		$this->name = bkntc__( 'Booking panel in popup');

        $bookneticData = [
            'themes'        => Appearance::select(['id', 'name'])->fetchAll(),
            'staff'         => Staff::select(['id', 'name'])->fetchAll(),
            'services'      => Service::select(['id', 'name'])->fetchAll(),
            'category'      => ServiceCategory::select(['id', 'name'])->fetchAll(),
            'locations'     => Location::select(['id', 'name'])->fetchAll()
        ];

        foreach ($bookneticData as $key => $data) {
            $this->data[$key] = [];
            foreach ($data as $item) {
                $this->data[$key][$item->id] = $item->name;
            }
            $this->data[$key][0] = '- - - - - - - - - -';
        }

        $this->data['divi_booknetic_options'] = [ 'url' => urlencode( site_url() ) ];
    }

	public function get_fields() {
		return array(
            'caption' => array(
                'label'           => bkntc__( 'Caption' ),
                'type'            => 'text',
                'toggle_slug'     => 'main_content',
            ),
            'theme' => array(
                'label'           => bkntc__( 'Appearances' ),
                'type'            => 'select',
                'options'=> $this->data['themes'],
                'toggle_slug'     => 'main_content',
            ),'staff' => array(
                'label'           => bkntc__( 'Staff' ),
                'type'            => 'select',
                'options'=> $this->data['staff'],
                'toggle_slug'     => 'main_content',
            ),
            'category' => array(
                'label'           => bkntc__( 'Service Categories' ),
                'type'            => 'select',
                'options'=> $this->data['category'],
                'toggle_slug'     => 'main_content',
            ),
            'service' => array(
                'label'           => bkntc__( 'Services' ),
                'type'            => 'select',
                'options'=> $this->data['services'],
                'toggle_slug'     => 'main_content',
            ),
            'location' => array(
                'label'           => bkntc__( 'Locations' ),
                'type'            => 'select',
                'options'=> $this->data['locations'],
                'toggle_slug'     => 'main_content',
            ),
            'class' => array(
                'label'           => bkntc__( 'Class' ),
                'type'            => 'text',
                'toggle_slug'     => 'main_content',
            ),
            'style' => array(
                'label'           => bkntc__( 'Style' ),
                'type'            => 'textarea',
                'toggle_slug'     => 'main_content',
            ),
            'bookneticDivi' => [
                'label'           => 'Booknetic Divi Options',
                'type'            => 'hidden',
                'options'         => $this->data['divi_booknetic_options'],
                'toggle_slug'     => 'main_content',
                'default'         => json_encode( $this->data['divi_booknetic_options'] ),
            ],

        );
	}

	public function render( $attrs, $content = null, $render_slug ) {

        $shortcode = "booknetic-booking-button";
        $allowedAttrs = ['staff','category','location','service','theme','caption','class','style'];

        foreach ($allowedAttrs as $attr ) {
            if( !array_key_exists($attr,$this->props) )
                continue;

            if( ! empty( $this->props[$attr] ) )
            {
                $shortcode .= ' ' .$attr . '="' . $this->props[$attr] . '"';
            }
        }
        return do_shortcode( "[$shortcode]" );
	}
}

new BookingPopup;
