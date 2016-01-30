<?php
/**
 * WP FAQ Manager - Front Module
 *
 * Contains our front-end related functionality.
 *
 * @package WordPress FAQ Manager
 */

/**
 * Start our engines.
 */
class WPFAQ_Manager_Front {

	/**
	 * Call our hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'template_redirect',                array( $this, 'faq_redirect'            ),  1       );
		add_action( 'wp_head',                          array( $this, 'seo_head'                ),  5       );
		add_action( 'wp_head',                          array( $this, 'print_css'               ),  999     );
		add_action( 'wp_enqueue_scripts',               array( $this, 'register_scripts'        )           );
		add_action( 'wp_enqueue_scripts',               array( $this, 'register_styles'         )           );
		add_action( 'pre_get_posts',                    array( $this, 'rss_include'             )           );
		add_filter( 'post_class',                       array( $this, 'add_post_class'          )           );
	}

	/**
	 * Set up our optional redirects.
	 *
	 * @return void.
	 */
	public function faq_redirect() {

		// Optional filter to disable this all together.
		if ( false !== $redirect_id = apply_filters( 'wpfaq_enable_redirects', false ) ) {
			return;
		}

		// Make sure our old "none" value isn't returned.
		if ( 'none' === $redirect_id ) {
			return;
		}

		// Get the redirect URL we are going to use.
		$redirect   = get_permalink( $redirect_id );

		// Bail if we don't have an actual URL to use.
		if ( empty( $redirect ) ) {
			return;
		}

        // Confirm we are on a place where we'd do the redirect.
        if ( false !== $check = WPFAQ_Manager_Helper::check_site_location() ) {

        	// Process the redirect itself.
            wp_redirect( esc_url_raw( $redirect ), 301 );

            // And exit.
            exit();
        }
	}

	/**
	 * Make some modifications to the head output for better SEOz
	 *
	 * @return void
	 */
	public function seo_head() {

		// Check if the site is set to public or not.
		$public = get_option( 'blog_public' );

		// Just bail if we aren't public.
		if ( empty( $public ) ) {
			return;
		}

		// Optional filter to disable this all together.
		if ( false === apply_filters( 'wpfaq_enable_seo_tags', true ) ) {
			return;
		}

		// Set a default meta array.
		$meta   = array();

		// Add the individual FAQs.
		if ( is_singular( 'question' ) ) {
			$meta   = apply_filters( 'wpfaq_robots_seo_tags_single', array(), 'single' );
		}

		// Add the FAQ archive pages.
		if ( is_post_type_archive( 'question' ) ) {
			$meta   = apply_filters( 'wpfaq_robots_seo_tags_archive', array(), 'archive' );
		}

		// Add the FAQ taxonomies.
		if ( is_tax(' faq-topic' ) || is_tax( 'faq-tags' ) ) {
			$meta   = apply_filters( 'wpfaq_robots_seo_tags_taxonomy', array(), 'taxonomy' );
		}

		// Set the meta array.
		$meta   = ! empty( $meta ) ? $meta : array();

		// Optional last-chance filter.
		$meta   = apply_filters( 'wpfaq_robots_seo_tags', $meta );

		// Now add all the new meta tags.
		if ( ! empty( $meta ) ) {
			printf( '<meta name="robots" content="%s" />' . "\n", implode( ',', $meta ) );
		}
	}

	/**
	 * Add some basic CSS for print stylesheets.
	 *
	 * @return string  The CSS output.
	 */
	public function print_css() {

		// Optional filter to disable this all together.
		if ( false === apply_filters( 'wpfaq_enable_print_css', true ) ) {
			return;
		}

		// Echo out the CSS.
		echo '<style media="print" type="text/css">';
		echo 'div.faq_answer { display: block!important; }';
		echo 'p.faq_nav { display: none; }';
		echo '</style>';
	}

	/**
	 * Register our scripts to be called when the shortcodes are used.
	 *
	 * @return void
	 */
	public function register_scripts() {

		// Optional filter to disable this all together.
		if ( false === apply_filters( 'wpfaq_enable_front_js', true ) ) {
			return;
		}

		// Set a file suffix structure based on whether or not we want a minified version.
		$jx = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '.js' : '.min.js';

		// Set a version for whether or not we're debugging.
		$vr = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : WPFAQ_VER;
	}

	/**
	 * Register our CSS files to be called when the shortcodes are used.
	 *
	 * @return void
	 */
	public function register_styles() {

		// Optional filter to disable this all together.
		if ( false === apply_filters( 'wpfaq_enable_front_css', true ) ) {
			return;
		}

		// Set a file suffix structure based on whether or not we want a minified version.
		$cx = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '.css' : '.min.css';

		// Set a version for whether or not we're debugging.
		$vr = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : WPFAQ_VER;

		// Register the stylesheet.
		wp_register_style( 'faq-front', plugins_url( '/css/faq.front' . $cx, __FILE__ ), false, $vr, 'all' );
	}

	/**
	 * Include the FAQs in the RSS feed.
	 *
	 * @param  object $query  The original query variable object.
	 *
	 * @return object $query  The potentially modified query variable object.
	 */
	public function rss_include( $query ) {

		// Optional filter to disable this all together.
		if ( false === apply_filters( 'wpfaq_disable_faq_rss', true ) ) {
			return $query;
		}

		// Bail if we are not on the RSS feed.
		if ( ! $query->is_feed ) {
			return $query;
		}

		// Only modify if we are on the RSS feed.
		$query->set( 'post_type', array( 'post', 'question' ) );

		// And return the query.
		return $query;
	}

	/**
	 * Add 'normal' post classes for themes with narrow CSS
	 *
	 * @param array $classes  The existing post classes.
	 *
	 * @return array $class   The updated array of post classes.
	 */
	public function add_post_class( $classes ) {

		// Return the classes we have if we aren't where we should be.
		if ( false === $check = WPFAQ_Manager_Helper::check_site_location() ) {
			return $classes;
		}

		// Check for the 'post' class.
		if ( ! in_array( 'post', $classes ) ) {
			$classes[]  = 'post';
		}

		// Check for the 'type-post' class.
		if ( ! in_array( 'type-post', $classes ) ) {
			$classes[]  = 'type-post';
		}

		// Return my classes.
		return $classes;
	}

	// End our class.
}

// Call our class.
$WPFAQ_Manager_Front = new WPFAQ_Manager_Front();
$WPFAQ_Manager_Front->init();

