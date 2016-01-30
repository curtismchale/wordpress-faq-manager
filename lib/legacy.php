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
		add_action( 'admin_init',                       array( $this, 'legacy_setting'      )           );
		add_filter( 'wpfaq_question_post_slug_single',  array( $this, 'check_single_slug'   )           );
		add_filter( 'wpfaq_question_post_slug_archive', array( $this, 'check_archive_slug'  )           );
		add_filter( 'wpfaq_robots_seo_tags_single',     array( $this, 'index_robot_tags'    ),  10, 2   );
		add_filter( 'wpfaq_robots_seo_tags_archive',    array( $this, 'index_robot_tags'    ),  10, 2   );
		add_filter( 'wpfaq_robots_seo_tags_taxonomy',   array( $this, 'index_robot_tags'    ),  10, 2   );
	}

	/**
	 * Convert our existing setting into the legacy key.
	 *
	 * @return void
	 */
	public function legacy_setting() {

		// First check for the legacy option.
		$legacy = get_option( 'faq_legacy_options' );

		// Bail if it is set.
		if ( ! empty( $legacy ) ) {
			return;
		}

		// Get our current options.
		$data   = get_option( 'faq_options' );

		// Save the existing data into the legacy key.
		update_option( 'faq_legacy_options', $data );
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

	// End our class.
}

// Call our class.
$WPFAQ_Manager_Legacy = new WPFAQ_Manager_Legacy();
$WPFAQ_Manager_Legacy->init();

