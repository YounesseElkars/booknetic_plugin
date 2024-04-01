<?php

use BookneticApp\Models\Appearance;
use BookneticApp\Models\Location;
use BookneticApp\Models\Service;
use BookneticApp\Models\ServiceCategory;
use BookneticApp\Models\Staff;

class SignIn extends ET_Builder_Module {

	public $slug       = 'booknetic_signin';
	public $vb_support = 'on';
    private $data;

	protected $module_credits = array(
		'module_uri' => '',
		'author'     => '',
		'author_uri' => '',
	);

	public function init() {
		$this->name = bkntc__( 'Booknetic Customer Sign In');

        $this->data['divi_booknetic_options'] = [ 'url' => urlencode( site_url() ) ];
    }

	public function get_fields() {
		return array(
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
        return do_shortcode( "[booknetic-signin]" );
	}
}

new SignIn;
