<?php

namespace BookneticApp\Providers\Core;

use BookneticApp\Backend\Boostore\Helpers\BoostoreHelper;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\Helpers\Helper;
use Exception;
use stdClass;

class PluginUpdater
{
    private $expiration = 43200; // seconds
    private $plugin_slug;
    private $update_url;
    private $plugin_base;
    private $purchase_code;
    private $is_forced_update;
    private $transient; // temporary cache for multiple calls
    private $updates = null;


    public function __construct ( $plugin, $updateURL, $purchase_code )
    {
        $this->plugin_slug   = $plugin;
        $this->update_url    = $updateURL;
        $this->plugin_base   = $plugin . '/init.php';
        $this->purchase_code = $purchase_code;

        $this->check_if_forced_for_update();

        add_filter( 'plugins_api', [ $this, 'plugin_info' ], 20, 3 );
        add_filter( 'site_transient_update_plugins', [ $this, 'push_update' ] );
        add_filter( 'site_transient_update_plugins', [ $this, 'push_update_addons' ] );
        add_action( 'upgrader_process_complete', [ $this, 'after_update' ], 10, 2 );
        add_filter( 'plugin_row_meta', [ $this, 'check_for_update' ], 10, 2 );
        add_filter( 'upgrader_pre_download', [ $this, 'block_expired_updates' ], 10, 3 );

        add_action( 'in_plugin_update_message-' . $this->plugin_slug . '/init.php', [
            $this,
            'plugin_notice'
        ], 10, 2 );

        $this->plugin_update_message();
    }

    public function plugin_info ( $res, $action, $args )
    {
        if ( $action !== 'plugin_information' )
        {
            return false;
        }

        if ( $args->slug !== $this->plugin_slug )
        {
            return false;
        }

        $remote = $this->get_transient();

        if ( $remote && isset( $remote->version ) )
        {
            $res = new stdClass();

            $res->name         = $remote->name;
            $res->slug         = $this->plugin_slug;
            $res->tested       = $remote->tested;
            $res->version      = $remote->version;
            $res->last_updated = $remote->last_updated;
	        $res->requires_php = isset( $remote->requires_php ) ? $remote->requires_php : '7.4';

            $res->author         = '<a href="https://www.fs-code.com">FS Code</a>';
            $res->author_profile = 'https://www.fs-code.com';

            $res->sections = [
                'description' => $remote->sections->description,
                'changelog'   => $remote->sections->changelog
            ];

            return $res;
        }

        return false;
    }

    public function push_update ( $transient )
    {
        if ( empty( $transient->checked ) )
        {
            return $transient;
        }

        $remote = $this->get_transient();

        if ( $remote && isset( $remote->version ) && version_compare( Helper::getVersion(), $remote->version, '<' ) )
        {
            $res = new stdClass();

            $res->slug          = $this->plugin_slug;
            $res->plugin        = $this->plugin_base;
            $res->new_version   = $remote->version;
            $res->tested        = $remote->tested;
            $res->package       = isset( $remote->download_url ) ? $remote->download_url : '';
	        $res->requires_php  = isset( $remote->requires_php ) ? $remote->requires_php : '7.4';
            $res->update_notice = isset( $remote->update_notice ) ? $remote->update_notice : '';
            $res->compatibility = new stdClass();

            $transient->response[ $res->plugin ] = $res;
        }

        return $transient;
    }


    public function push_update_addons( $transient )
    {
        if ( empty( $transient->checked ) )
        {
            return $transient;
        }

        $addons = [];
        foreach (get_plugins() as $key => $plugin)
        {
            $key = dirname($key);
            $addons[$key] = $plugin['Version'];
        }

        if ($this->updates === null)
        {
            $cache = Helper::getOption('addons_updates_cache', '', false);
            $cache = json_decode($cache, true);
            if (isset($cache['time']) && (Date::epoch() - $cache['time']) < $this->expiration)
            {
                $this->updates = $cache['updates'];
            }
            else
            {
                $this->updates = BoostoreHelper::get( 'check_update', [
                    'domain' => site_url(),
                    'addons' => $addons
                ] );

                Helper::setOption('addons_updates_cache', json_encode(['time' => Date::epoch(), 'updates' => $this->updates]), false);
            }

        }


        foreach ($addons as $addonSlug => $addonCurrentVersion)
        {
            if (array_key_exists($addonSlug, $this->updates) && ! empty( BoostoreHelper::getAddonSlug( $addonSlug ) ) )
                $update = $this->updates[$addonSlug];
            else continue;

            if (isset($update['deactivate']) )
            {
                deactivate_plugins( BoostoreHelper::getAddonSlug( $addonSlug ) );

                continue;
            }

            if ( !isset( $update['version'] ) || version_compare($update['version'], $addonCurrentVersion, '<='))
            {
                continue;
            }

            $res = new stdClass();

            $res->slug          = $update['slug'];
            $res->plugin        = BoostoreHelper::getAddonSlug( $update['slug'] );
            $res->new_version   = $update['version'];
            $res->tested        = $update['tested'];
            $res->package       = $update['download_url'];
            $res->update_notice = '';
            $res->compatibility = new stdClass();

            $transient->response[ $res->plugin ] = $res;
        }

        return $transient;
    }

    public function after_update ( $upgrader_object, $options )
    {
        if ( $options[ 'action' ] === 'update' && $options[ 'type' ] === 'plugin' )
        {
            delete_transient( 'fscode_upgrade_' . $this->plugin_slug );
        }
    }

    public function check_for_update ( $links, $file )
    {
        if ( strpos( $file, $this->plugin_base ) !== false )
        {
            $new_links = [
                'check_for_update' => '<a href="plugins.php?fscode_check_for_update=1&plugin=' . urlencode( $this->plugin_slug ) . '&_wpnonce=' . wp_create_nonce( 'fscode_check_for_update_' . $this->plugin_slug ) . '">Check for update</a>'
            ];

            $links = array_merge( $links, $new_links );
        }

        return $links;
    }

    public function block_expired_updates ( $reply, $package, $extra_data )
    {
        if ( $reply !== false )
        {
            return $reply;
        }

        if ( property_exists($extra_data->skin, 'plugin_info') && $extra_data->skin->plugin_info[ 'TextDomain' ] !== $this->plugin_slug )
        {
            return false;
        }

        $remote = $this->get_transient();

        if ( ! $remote || empty( $remote->update_notice ) )
        {
            return false;
        }

        $update_notice = '<div class="fsp-plugin-blocked-notice">' . $remote->update_notice . '</div>';

        return new \WP_Error( $this->plugin_slug . '_subscription_expired', $update_notice );
    }

    public function plugin_notice ( $plugin_data, $extra_data )
    {
        if ( empty( $extra_data->package ) && ! empty( $extra_data->update_notice ) )
        {
            echo '<style>
                            #'.$this->plugin_slug.'-update .update-message em
                            {
                                display: none;
                            }

                            .fsp-plugin-update-notice {
                                font-size: 13px;
                                line-height: 1.5em;
                                margin: .5em 0;
                                color: #32373c;
                                border-top: 2px solid #ffb900;
                                padding-top: .5em;
                                font-weight: 500;
                            }

                            .fsp-plugin-update-notice + p {
                                display: none;
                            }
                            
                            .fsp-plugin-blocked-notice {
                                font-weight: bold;
                                margin-top: -24px;
                                background: inherit;
                                position: relative;
                            }
                        </style>';
            echo '<div class="fsp-plugin-update-notice">' . $extra_data->update_notice . '</div>';
        }
    }

    /*
     * First check if temporary cache is available, if it is, use it
     * Second check long-live cache, and if it is in the expiration timeframe, use it
     * If neither cache is available, then request to remote server and cache it
     */
    private function get_transient ()
    {
        if ( isset( $this->transient ) )
        {
            return $this->transient;
        }

        try
        {
            $transient_cache = json_decode( Helper::getOption( 'transient_cache_' . $this->plugin_slug, false, false ), false );

            if( empty( $transient_cache ) )
            {
                throw new Exception();
            }

            $transient       = $transient_cache->transient;
            $time            = $transient_cache->time;
        }
        catch ( Exception $e )
        {
            $transient = false;
        }

        if ( ! $transient || time() - $time > $this->expiration )
        {
	        $params = [
		        'act'             => 'check_update',
		        'domain'          => network_site_url(),
		        'purchase_code'   => $this->purchase_code,
		        'version'         => Helper::getVersion(),
		        'is_manual_check' => strval( $this->is_forced_update ),
		        'php_version'     => phpversion()
	        ];

	        $remote = wp_remote_get( $this->update_url . '?' . http_build_query( $params ) );

            if ( ! is_wp_error( $remote ) && isset( $remote[ 'response' ][ 'code' ] ) && $remote[ 'response' ][ 'code' ] == 200 && ! empty( $remote[ 'body' ] ) )
            {
                $transient = json_decode( $remote[ 'body' ] );

                // long-live cache
                Helper::setOption( 'transient_cache_' . $this->plugin_slug, json_encode( [
                    'time'      => time(),
                    'transient' => $transient
                ] ), false );
            }
            else
            {
                Helper::setOption( 'transient_cache_' . $this->plugin_slug, json_encode( [
                    'time'      => time(),
                    'transient' => 1
                ] ), false );
            }
        }

        $this->transient = $transient;

        return $transient;
    }

	/*
	 * Set the expiration limit to 1-minute if update check is forced
	 * There should be at least 1-minute difference between two requests
	 */
    private function check_if_forced_for_update ()
    {
        $check_update = Helper::_get( 'fscode_check_for_update', '', 'string' );
        $plugin       = Helper::_get( 'plugin', '', 'string' );
        $_wpnonce     = Helper::_get( '_wpnonce', '', 'string' );

        if ( $check_update === '1' && $plugin === $this->plugin_slug && wp_verify_nonce( $_wpnonce, 'fscode_check_for_update_' . $this->plugin_slug ) )
        {
            $this->is_forced_update = true;
            $this->expiration = 0;
        }
    }

    private function plugin_update_message()
    {
        $showNotice = false;
        $transient = $this->get_transient();

        if( isset($transient->version) && Helper::getVersion() != $transient->version)
        {
            $showNotice = true;
        }
        else
        {
            $transientCache = json_decode(Helper::getOption('addons_updates_cache',''),true);
            if( ! empty($transientCache['updates']) )
            {
                foreach (Bootstrap::$addons as $addon)
                {
                    $slug = $addon->getAddonSlug();
                    $currentVersion = $addon->getVersion();
                    if( isset( $transientCache[ 'updates' ][ $slug ][ 'version' ] ) && $transientCache[ 'updates' ][ $slug ][ 'version' ] != $currentVersion )
                    {
                        $showNotice = true;
                        break;
                    }
                }
            }
        }
        if( $showNotice )
        {
            add_action('admin_notices',function (){
                echo '<div class="notice"><p>' . bkntc__( 'There is a new update for Booknetic! Please always keep the plugin and add-ons up-to-date in order for the plugin to work properly.' ) . '</p></div>';
            });
        }
    }
}
