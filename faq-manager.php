<?php
/**
 * Plugin Name: WP FAQ Manager
 * Plugin URI: https://sfndesign.ca/wordpress-faq-manager/
 * Description: Uses custom post types and taxonomies to manage an FAQ section for your site.
 * Author: Curtis McHale
 * Author URI: https://sfndesign.ca/
 * Version: 2.0.2
 * Text Domain: wp-faq-manager
 * Requires WP: 4.0
 * Requires PHP: 7.0
 * Domain Path: languages
 * License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * GitHub Plugin URI: https://github.com/curtismchale/wordpress-faq-manager
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Define our base file.
if( ! defined( 'WPFAQ_BASE' ) ) {
	define( 'WPFAQ_BASE', plugin_basename( __FILE__ ) );
}

// Define our base directory.
if ( ! defined( 'WPFAQ_DIR' ) ) {
	define( 'WPFAQ_DIR', plugin_dir_path( __FILE__ ) );
}

// Define our version.
if( ! defined( 'WPFAQ_VER' ) ) {
	define( 'WPFAQ_VER', '2.0.0' );
}


/**
 * Call our class.
 */
class WPFAQ_Manager_Base
{
	/**
	 * Static property to hold our singleton instance.
	 * @var $instance
	 */
	static $instance = false;

	/**
	 * This is our constructor. There are many like it, but this one is mine.
	 */
	private function __construct() {
		add_action( 'plugins_loaded',               array( $this, 'textdomain'          )           );
		add_action( 'plugins_loaded',               array( $this, 'load_files'          )           );
		register_activation_hook    ( __FILE__,     array( $this, 'flush_rewrite_rules' )           );
		register_deactivation_hook  ( __FILE__,     array( $this, 'flush_rewrite_rules' )           );
	}

	/**
	 * If an instance exists, this returns it.  If not, it creates one and
	 * retuns it.
	 *
	 * @return $instance
	 */
	public static function getInstance() {

		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Load our textdomain for localization.
	 *
	 * @return void
	 */
	public function textdomain() {
		load_plugin_textdomain( 'wp-faq-manager', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Load our actual files in the places they belong.
	 *
	 * @return void
	 */
	public function load_files() {

		// Load our helper file.
		require_once( WPFAQ_DIR . 'lib/helper.php' );

		// Load our custom post type and taxonomies.
		require_once( WPFAQ_DIR . 'lib/types.php' );

		// Load our widgets file.
		require_once( WPFAQ_DIR . 'lib/widgets.php' );

		// Load our data file.
		require_once( WPFAQ_DIR . 'lib/data.php' );

		// Load our legacy file for backwards compatibility.
		require_once( WPFAQ_DIR . 'lib/legacy.php' );

		// Load our front-end display functions.
		if ( ! is_admin() ) {
			require_once( WPFAQ_DIR . 'lib/front.php' );
			require_once( WPFAQ_DIR . 'lib/shortcodes.php' );
		}

		// Load our admin-related functions.
		if ( is_admin() ) {
			require_once( WPFAQ_DIR . 'lib/admin.php' );
		}
	}

	/**
	 * Flush the rewrite rules on activation / deactivation.
	 *
	 * @return void
	 */
	public function flush_rewrite_rules() {
		flush_rewrite_rules();
	}

	// End our class.
}

// Instantiate our class.
$WPFAQ_Manager_Base = WPFAQ_Manager_Base::getInstance();
