<?php

namespace BookneticApp\Providers\Core;

use BookneticApp\Providers\Core\BookneticQuietSkin;

class PluginInstaller
{

	private $download_link;
    private $file_path;

	public function __construct( $download_link, $file_path )
	{
		$this->download_link = $download_link;
        $this->file_path = $file_path;
	}

	public function install()
	{
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/misc.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		$upgrader = new \Plugin_Upgrader( new BookneticQuietSkin( ) );

		if ( ! file_exists( WP_PLUGIN_DIR . $this->file_path ) )
		{
			$upgrader->install( $this->download_link );
		}

		if ( file_exists( WP_PLUGIN_DIR . $this->file_path ) )
		{
			activate_plugin( WP_PLUGIN_DIR . $this->file_path );
		}

		return true;
	}

}

