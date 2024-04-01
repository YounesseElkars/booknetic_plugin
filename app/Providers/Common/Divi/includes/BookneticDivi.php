<?php

namespace BookneticApp\Providers\Common\Divi\includes;

use DiviExtension;

class BookneticDivi extends DiviExtension {

	/**
	 * BookneticDivi constructor.
	 *
	 * @param string $name
	 * @param array  $args
	 */
	public function __construct( $name = 'booknetic', $args = array() ) {
		$this->plugin_dir     = plugin_dir_path( __FILE__ );
		$this->plugin_dir_url = plugin_dir_url( $this->plugin_dir );
		parent::__construct( $name, $args );
	}
}
