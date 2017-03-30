<?php
/**
 * WP FAQ Manager - Legacy Module
 *
 * Backwards compatibility stuff to handle old settings, etc.
 *
 * @package WordPress FAQ Manager
 */

/**
 * Start our engines.
 */
class WPFAQ_Manager_Legacy {

	/**
	 * Call our hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_filter( 'wpfaq_question_post_slug_single',  array( $this, 'check_single_slug'   ),  5       );
		add_filter( 'wpfaq_question_post_slug_archive', array( $this, 'check_archive_slug'  ),  5       );
		add_filter( 'wpfaq_robots_seo_tags_single',     array( $this, 'index_robot_tags'    ),  5,  2   );
		add_filter( 'wpfaq_robots_seo_tags_archive',    array( $this, 'index_robot_tags'    ),  5,  2   );
		add_filter( 'wpfaq_robots_seo_tags_taxonomy',   array( $this, 'index_robot_tags'    ),  5,  2   );
		add_filter( 'wpfaq_display_htype',              array( $this, 'set_htype_display'   ),  5,  2   );
		add_filter( 'wpfaq_display_content_expand',     array( $this, 'set_expand_setting'  ),  5,  2   );
		add_filter( 'wpfaq_display_expand_speed',       array( $this, 'set_expand_speed'    ),  5,  2   );
		add_filter( 'wpfaq_display_content_filter',     array( $this, 'set_content_filter'  ),  5,  2   );
		add_filter( 'wpfaq_display_content_more_link',  array( $this, 'read_more_link'      ),  5,  2   );
		add_filter( 'wpfaq_display_shortcode_paginate', array( $this, 'paginate_output'     ),  5,  2   );
		add_filter( 'wpfaq_scroll_combo_list',          array( $this, 'combo_scrolling'     ),  5,  2   );
		add_filter( 'wpfaq_display_content_backtotop',  array( $this, 'combo_backtotop'     ),  5,  2   );
		add_filter( 'wpfaq_enable_redirects',           array( $this, 'set_faq_redirects'   ),  5       );
		add_filter( 'wpfaq_enable_front_css',           array( $this, 'load_frontend_css'   ),  5       );
		add_filter( 'wpfaq_disable_faq_rss',            array( $this, 'check_frontend_rss'  ),  5       );
	}

	/**
	 * Check for a single post type slug stored and return it, or the original.
	 *
	 * @param  string $slug  The original slug.
	 *
	 * @return string $slug  The stored slug from the old settings, or the original passed.
	 */
	public function check_single_slug( $slug ) {

		// Get my stored value.
		$stored = WPFAQ_Manager_Helper::get_legacy_option( 'single' );

		// Return the stored value, or the original.
		return ! empty( $stored ) ? $stored : $slug;
	}

	/**
	 * Check for the archive type slug stored and return it, or the original.
	 *
	 * @param  string $slug  The original slug.
	 *
	 * @return string $slug  The stored slug from the old settings, or the original passed.
	 */
	public function check_archive_slug( $slug ) {

		// Get my stored value.
		$stored = WPFAQ_Manager_Helper::get_legacy_option( 'arch' );

		// Return the stored value, or the original.
		return ! empty( $stored ) ? $stored : $slug;
	}

	/**
	 * Check for the meta tags on various items.
	 *
	 * @param  array $tags    The possible tags being passed.
	 * @param  string $type   The part of the site we are on.
	 *
	 * @return array $values  The potential values being stored.
	 */
	public function index_robot_tags( $tags, $type ) {

		// Fetch some defaults that may be stored.
		$noindex    = WPFAQ_Manager_Helper::get_legacy_option( 'noindex' );
		$nofollow   = WPFAQ_Manager_Helper::get_legacy_option( 'nofollow' );
		$noarchive  = WPFAQ_Manager_Helper::get_legacy_option( 'noarchive' );

		// Return the array of values.
		return array(
			'noindex'   => $noindex,
			'nofollow'  => $nofollow,
			'noarchive' => $noarchive,
		);
	}

	/**
	 * Check for an htype setting and filter it.
	 *
	 * @param string $htype      The current htype being passed.
	 * @param string $shortcode  The shortcode it's being called on.
	 *
	 * @return string $htype     The stored htype from the old settings, or the original passed.
	 */
	public function set_htype_display( $htype, $shortcode ) {

		// Check for a stored value.
		$stored = WPFAQ_Manager_Helper::get_legacy_option( 'htype' );

		// Return the stored value, or the original.
		return ! empty( $stored ) ? $stored : $htype;
	}

	/**
	 * Check for an expand flag setting and filter it.
	 *
	 * @param bool   $expand     The current flag being passed.
	 * @param string $shortcode  The shortcode it's being called on.
	 *
	 * @return bool  $expand     The stored value from the old settings, or the original passed.
	 */
	public function set_expand_setting( $expand, $shortcode ) {

		// Check for a stored value.
		$stored = WPFAQ_Manager_Helper::get_legacy_option( 'expand' );

		// Return the stored value, or the original.
		return ! empty( $stored ) ? true : false;
	}

	/**
	 * Check for an expand speed setting and filter it.
	 *
	 * @param integer  $speed      The current speed being passed.
	 * @param string   $shortcode  The shortcode it's being called on.
	 *
	 * @return integer $speed      The stored speed from the old settings, or the original passed.
	 */
	public function set_expand_speed( $speed, $shortcode ) {

		// Check for a stored value.
		$stored = WPFAQ_Manager_Helper::get_legacy_option( 'exspeed' );

		// Return the stored value, or the original.
		return ! empty( $stored ) ? $stored : $speed;
	}

	/**
	 * Check for an content filter disable flag and filter it.
	 *
	 * @param bool   $expand     The current flag being passed.
	 * @param string $shortcode  The shortcode it's being called on.
	 *
	 * @return bool  $expand     The stored value from the old settings, or the original passed.
	 */
	public function set_content_filter( $expand, $shortcode ) {

		// Check for a stored value.
		$stored = WPFAQ_Manager_Helper::get_legacy_option( 'nofilter' );

		// Return the stored value, or the original.
		return ! empty( $stored ) ? false : true;
	}

	/**
	 * Check for the 'read more' settings and filter them.
	 *
	 * @param array  $more       The current values being passed.
	 * @param string $shortcode  The shortcode it's being called on.
	 *
	 * @return array $more       The stored value from the old settings, or the original passed.
	 */
	public function read_more_link( $more, $shortcode ) {

		// Check for the initial flag to be enabled.
		if ( false === $check = WPFAQ_Manager_Helper::get_legacy_option( 'exlink' ) ) {
			return false;
		}

		// Check for a stored text value.
		$text   = WPFAQ_Manager_Helper::get_legacy_option( 'extext' );

		// Add the fallback for text.
		$text   = ! empty( $text ) ? $text : __( 'Read More', 'wordpress-faq-manager' );

		// Return the stored value, or the original.
		return array( 'show' => 1, 'text' => $text ) ;
	}

	/**
	 * Check for the pagination filter flag and filter it.
	 *
	 * @param bool   $paginate   The current flag being passed.
	 * @param string $shortcode  The shortcode it's being called on.
	 *
	 * @return bool  $paginate   The stored value from the old settings, or the original passed.
	 */
	public function paginate_output( $paginate, $shortcode ) {

		// Check for a stored value.
		$stored = WPFAQ_Manager_Helper::get_legacy_option( 'paginate' );

		// Return the stored value, or the original.
		return ! empty( $stored ) ? true : false;
	}

	/**
	 * Check for the scrolling flag for the combo shortcode.
	 *
	 * @param bool   $scroll     The current flag being passed.
	 * @param string $shortcode  The shortcode it's being called on.
	 *
	 * @return bool  $scroll     The stored value from the old settings, or the original passed.
	 */
	public function combo_scrolling( $scroll, $shortcode ) {

		// Check for a stored value.
		$stored = WPFAQ_Manager_Helper::get_legacy_option( 'scroll' );

		// Return the stored value, or the original.
		return ! empty( $stored ) ? true : false;
	}

	/**
	 * Check for the back to top for the combo shortcode and filter it.
	 *
	 * @param bool   $backtotop  The current flag being passed.
	 * @param string $shortcode  The shortcode it's being called on.
	 *
	 * @return bool  $backtotop  The stored value from the old settings, or the original passed.
	 */
	public function combo_backtotop( $backtotop, $shortcode ) {

		// Check for a stored value.
		$stored = WPFAQ_Manager_Helper::get_legacy_option( 'backtop' );

		// Return the stored value, or the original.
		return ! empty( $stored ) ? true : false;
	}

	/**
	 * Check for a redirect ID and filter it.
	 *
	 * @param integer $redirect  The current flag being passed.
	 *
	 * @return mixed  $redirect  The ID stored, or 'none', or false.
	 */
	public function set_faq_redirects( $redirect ) {

		// First check if the main flag is there at all.
		if ( false === $check = WPFAQ_Manager_Helper::get_legacy_option( 'redirect' ) ) {
			return 'none';
		}

		// Check for a stored value.
		$stored = WPFAQ_Manager_Helper::get_legacy_option( 'redirectid' );

		// Return the stored value, or the original.
		return ! empty( $stored ) ? $stored : 'none';
	}

	/**
	 * Check for the flag for not loading CSS values.
	 *
	 * @param bool   $loadcss  The current flag being passed.
	 *
	 * @return bool  $loadcss  The stored value from the old settings, or the original passed.
	 */
	public function load_frontend_css( $loadcss ) {

		// Check for a stored value.
		$stored = WPFAQ_Manager_Helper::get_legacy_option( 'css' );

		// Return the stored value, or the original.
		return ! empty( $stored ) ? $stored : $loadcss;
	}

	/**
	 * Check for the flag for not loading into RSS values.
	 *
	 * @param bool   $loadcss  The current flag being passed.
	 *
	 * @return bool  $loadcss  The stored value from the old settings, or the original passed.
	 */
	public function check_frontend_rss( $loadrss ) {

		// Check for a stored value.
		$stored = WPFAQ_Manager_Helper::get_legacy_option( 'rss' );

		// Return the stored value, or the original.
		return ! empty( $stored ) ? false : $loadrss;
	}

	// End our class.
}

// Call our class.
$WPFAQ_Manager_Legacy = new WPFAQ_Manager_Legacy();
$WPFAQ_Manager_Legacy->init();
