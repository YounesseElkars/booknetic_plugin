<?php
/*
 * Plugin Name: Booknetic
 * Description: WordPress Appointment Booking and Scheduling system
 * Version: 3.8.18
 * Author: FS-Code
 * Author URI: https://www.booknetic.com
 * License: Commercial
 * Requires PHP: 7.4
 * Text Domain: booknetic
 */

defined( 'ABSPATH' ) or exit;

require_once __DIR__ . '/vendor/autoload.php';

new \BookneticApp\Providers\Core\Bootstrap();
