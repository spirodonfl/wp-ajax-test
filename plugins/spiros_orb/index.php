<?php
/*
Plugin Name: Spiros Orbisius Plugin
Description: To demonstrate that I know WP a bit
Version: 1.0.0
Author: Spiro Floropoulos
Author URI: https://spirofloropoulos.com
Text Domain: spiro-orb
License: MIT
*/

define( 'ORB_MAIN_FILE', __FILE__ );

require_once dirname( __FILE__ ) . '/src/Orb.php';

if ( is_admin() ) {
    Orb::instance();
}