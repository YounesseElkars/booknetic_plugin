<?php


class ChangeStatus extends ET_Builder_Module {

	public $slug       = 'booknetic_change_status';
	public $vb_support = 'on';
    private $data;

	protected $module_credits = array(
		'module_uri' => '',
		'author'     => '',
		'author_uri' => '',
	);

	public function init() {
		$this->name = bkntc__( 'Booknetic Change Status');

        $this->data['divi_booknetic_options'] = [ 'url' => urlencode( site_url() ) ];
    }

	public function get_fields() {
		return array(
            'label' => array(
                'label'           => bkntc__( 'Label' ),
                'type'            => 'text',
                'toggle_slug'     => 'main_content',
            ),
            'successLabel' => array(
                'label'           => bkntc__( 'Success Label' ),
                'type'            => 'text',
                'toggle_slug'     => 'main_content',
            ),
            'button' => array(
                'label'           => bkntc__( 'Change Button Text' ),
                'type'            => 'text',
                'toggle_slug'     => 'main_content',
            ),
            'successButton' => array(
                'label'           => bkntc__( 'Change Success Button Text' ),
                'type'            => 'text',
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

        $shortcode = "booknetic-change-status";
        $allowedAttrs = [ 'label' , 'successLabel' , 'button' , 'successButton' ];

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

new ChangeStatus;
