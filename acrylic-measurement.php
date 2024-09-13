<?php 
/*
 * Plugin Name:       Acrylic Measurement
 * Plugin URI:        
 * Description:       It's a plugin for measuring based on category and measuring units.
 * Version:           1.2.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Sanzida Tasnim
 * Author URI:        https://github.com/SanzidaTasnim
 * Text Domain:       acrylic-measurement
*/

namespace Sanzida\AcrylicMeasurement;

if( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Plugin main class
 */ 
final class AcrylicMeasurement {
    static $instance = false;

    /**
     * class constructor
     */
    private function __construct() {
        $this->include();
        $this->define();
        $this->hooks();
    }

    /**
     * Include all needed files
     */
    public function include() {
        require_once( dirname( __FILE__ ) . '/vendor/autoload.php' );
        require_once( dirname( __FILE__ ) . '/inc/functions.php' );
    }

    /**
     * define all constant
     */
    private function define() {
        define( 'ACRYLIC_MEASUREMENT', __FILE__ );
        define( 'ACRYLIC_MEASUREMENT_DIR', dirname( ACRYLIC_MEASUREMENT ) );
        define( 'ACRYLIC_MEASUREMENT_ASSET', plugins_url( 'assets', ACRYLIC_MEASUREMENT ) );
    }

    /**
     * All hooks
     */
    private function hooks() {
        new App\Admin();
        new App\Front();
        new App\Shortcode();
    }

    /**
     * Singleton Instance
    */
    static function get_esent_plugin() {
        
        if( ! self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}

/**
 * Cick off the plugins 
 */
AcrylicMeasurement::get_esent_plugin();