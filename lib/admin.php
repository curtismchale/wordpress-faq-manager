<?php
/**
 * WP FAQ Manager - Admin Module
 *
 * Contains our admin side related functionality.
 *
 * @package WordPress FAQ Manager
 */

/**
 * Start our engines.
 */
class WPFAQ_Manager_Admin {

	/**
	 * Call our hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'admin_enqueue_scripts',            array( $this, 'admin_scripts'           ),  10      );
		add_filter( 'plugin_action_links',              array( $this, 'quick_link'              ),  10, 2   );
		add_filter( 'faq-caps',                         array( $this, 'menu_cap_filter'         ),  10, 2   );
		add_filter( 'enter_title_here',                 array( $this, 'title_text'              )           );
	}

	/**
	 * Load our CSS and JS on the admin side as needed.
	 *
	 * @param  string $hook  The page hook being called.
	 *
	 * @return void.
	 */
	public function admin_scripts( $hook ) {

		// Run our quick check on the post type screen.
		$type   = WPFAQ_Manager_Helper::check_current_screen();

		// Load our CSS on the post editor.
		if ( ! empty( $type ) && 'question' === $type ) {
			wp_enqueue_style( 'faq-admin', plugins_url( '/css/faq.admin.css', __FILE__ ), array(), WPFAQ_VER, 'all' );
		}

		// Now our hook check.
		if ( in_array( $hook, array( 'question_page_faq-manager', 'question_page_faq-options', 'question_page_faq-instructions' ) ) ) {

			// Load the CSS.
			wp_enqueue_style( 'faq-admin', plugins_url( '/css/faq.admin.css', __FILE__ ), array(), WPFAQ_VER, 'all' );

			// Load the JS.
		//	wp_enqueue_script('jquery-ui-sortable');
		//	wp_enqueue_script( 'faq-admin', plugins_url( '/js/faq.admin.js', __FILE__) , array( 'jquery' ), WPFAQ_VER, true );
		}
	}

	/**
	 * Add our "settings" and "instructions" links to the plugins page.
	 *
	 * @param  array  $links  The existing array of links.
	 * @param  string $file   The file we are actually loading from.
	 *
	 * @return array  $links  The updated array of links.
	 */
	public function quick_link( $links, $file ) {

		// Check to make sure we are on the correct plugin.
		if ( $file === WPFAQ_BASE ) {

			// Our settings link.
			$settings   = '<a href="' . menu_page_url( 'faq-options', 0 ) . '">' . __( 'Settings', 'wordpress-faq-manager' ) . '</a>';

			// Our instruction links.
			$instruct   = '<a href="' . menu_page_url( 'faq-instructions', 0 ) . '">' . __( 'How-To', 'wordpress-faq-manager' ) . '</a>';

			// Add them all into the array.
			array_push( $links, $settings, $instruct );
    	}

    	// Return the full array of links.
		return $links;
	}

	/**
	 * Filter the menu items based on user capabilites.
	 *
	 * @param  string $capability  The capability being passed.
	 * @param  string $menu        The menu item being viewed.
	 *
	 * @return string $capability  The updated capability being passed.
	 */
	public function menu_cap_filter( $capability, $menu ) {

		// Anybody who can publish posts has access to the sort menu.
		if( 'sort' === $menu ) {
			return 'manage_options';
		}

  		// Anybody who can edit posts has access to the instructions page
  		if( 'instructions' === $menu ) {
			return 'manage_options';
  		}

  		// Anybody who can manage options has access to the settings page
  		// If another function has changed this capability already, we'll respect that by just passing the value we were given
		return $capability;
	}

	/**
	 * Update the "enter title here" text for the FAQs
	 *
	 * @param  string $title  The current title.
	 *
	 * @return string $title  The updated title.
	 */
	public function title_text( $title ){

		// Bail if not on admin or our function doesnt exist.
		if ( false === $type = WPFAQ_Manager_Helper::check_current_screen() ) {
			return $title;
		}

		// Return our custom title, or the original.
		return 'question' === $type ? __( 'Enter question title here', 'wordpress-faq-manager' ) : $title;
	}

	// End our class.
}

// Call our class.
$WPFAQ_Manager_Admin = new WPFAQ_Manager_Admin();
$WPFAQ_Manager_Admin->init();

