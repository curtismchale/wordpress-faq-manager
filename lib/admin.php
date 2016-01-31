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
		add_action( 'admin_menu',                       array( $this, 'admin_pages'             )           );
		add_action( 'wp_ajax_save_faq_sort',            array( $this, 'save_faq_sort'           )           );
		add_action( 'admin_enqueue_scripts',            array( $this, 'admin_scripts'           ),  10      );
		add_action( 'parse_query',                      array( $this, 'default_admin_sort'      )           );
		add_action( 'save_post',                        array( $this, 'clear_transients'        )           );
		add_filter( 'plugin_action_links',              array( $this, 'quick_link'              ),  10, 2   );
		add_filter( 'faq-caps',                         array( $this, 'menu_cap_filter'         ),  10, 2   );
		add_filter( 'enter_title_here',                 array( $this, 'title_text'              )           );
	}

	/**
	 * Call our individual admin pages.
	 *
	 * @return void
	 */
	public function admin_pages() {

		// Load the sorting page.
		add_submenu_page( 'edit.php?post_type=question', __( 'Sort FAQs', 'wordpress-faq-manager' ), __( 'Sort FAQs', 'wordpress-faq-manager' ), apply_filters( 'faq-caps', 'manage_options', 'sort' ), 'sort-page', array( $this, 'sort_page' ) );

		// Load the instructions page.
		add_submenu_page('edit.php?post_type=question', __( 'FAQ Manager Instructions', 'wordpress-faq-manager' ), __( 'Instructions', 'wordpress-faq-manager' ), apply_filters( 'faq-caps', 'manage_options', 'instructions' ), 'instructions', array( $this, 'instructions_page' ) );
	}

	/**
	 * Build out the page to sort FAQs on.
	 *
	 * @return void
	 */
	public function sort_page() {

		// Build out the page.
		echo '<div id="faq-admin-sort" class="wrap faq-admin-page-wrap faq-admin-sort-wrap">';

			// Title it.
			echo '<h1>' . __( 'Sort FAQs', 'wordpress-faq-manager' ) . '<span class="spinner faq-sort-spinner"></span></h1>';

			// SHow the message or the items.
			echo self::sort_display();

		// Close out the page.
		echo '</div>';
	}

	/**
	 * The actual display for the FAQ sorting.
	 *
	 * @return mixed  $build  The layout of the page.
	 */
	public static function sort_display() {

		// Fetch my FAQs to sort and return a message if we have none.
		if ( false === $faqs = WPFAQ_Manager_Data::get_admin_faqs() ) {
			return '<p>' . __( 'You have no FAQs to sort.', 'wordpress-faq-manager' ) . '</p>';
		}

		// Set an empty.
		$build  = '';

		// Set the message about where this works.
		$build .= '<p>' . __( '<strong>Note:</strong> this only affects the FAQs listed using the shortcode functions', 'wordpress-faq-manager' ) . '</p>';

		// Now wrap the list.
		$build .= '<div class="faq-sort-list">';
		$build .= '<ul id="faq-sort-type-list">';

		// Loop the FAQs.
		foreach ( $faqs as $faq ) {
			$build .= '<li id="' . absint( $faq->ID ) . '">' . esc_html( $faq->post_title ) . '</li>';
		}

		// Close the list wrap.
		$build .= '</ul>';
		$build .= '</div>';

		// Include our nonce.
		$build .= wp_nonce_field( 'wpfaq_sort_nonce', 'wpfaq_sort_nonce', false, false );

		// Return the build.
		return $build;
	}

	/**
	 * Save the items being sorted on the FAQ sort page.
	 *
	 * @return void
	 */
	public function save_faq_sort() {

		// Only run on admin.
		if ( ! is_admin() ) {
			die();
		}

		// Make sure we have our nonce.
		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wpfaq_sort_nonce' ) ) {
			die(1);
		}

		// Bail if the FAQ order hasn't been passed.
		if ( empty( $_POST['order'] ) ) {
			die(1);
		}

		// Call the WordPress database class.
		global $wpdb;

		// Create an array of items.
		$items  = explode( ',', $_POST['order'] );

		// Set a counter.
		$count  = 0;

		// Loop the items passed.
		foreach ( $items as $item_id ) {

			$wpdb->update( $wpdb->posts,
				array(
					'menu_order' => absint( $count )
				),
				array(
					'ID' => absint( $item_id )
				)
			);

			// Increment the counter.
			$count++;
		}

		// Delete the transient.
		delete_transient( 'wpfaq_admin_fetch_faqs' );

		// And die.
		die(1);
	}

	/**
	 * Load our CSS and JS on the admin side as needed.
	 *
	 * @param  string $hook  The page hook being called.
	 *
	 * @return void.
	 */
	public function admin_scripts( $hook ) {

		// Set a file suffix structure based on whether or not we want a minified version.
		$cx = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '.css' : '.min.css';
		$jx = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '.js' : '.min.js';

		// Set a version for whether or not we're debugging.
		$vr = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : WPFAQ_VER;

		// Run our quick check on the post type screen and load our CSS on the post editor.
		if ( false !== $check = WPFAQ_Manager_Helper::check_current_screen() ) {
			wp_enqueue_style( 'faq-admin', plugins_url( '/css/faq.admin' . $cx, __FILE__ ), array(), $vr, 'all' );
		}

		// Now our hook check.
		if ( in_array( $hook, array( 'question_page_sort-page' ) ) ) {

			// Load the CSS.
			wp_enqueue_style( 'faq-admin', plugins_url( '/css/faq.admin' . $cx, __FILE__ ), array(), $vr, 'all' );

			// Load the JS.
			wp_enqueue_script( 'faq-admin', plugins_url( '/js/faq.admin' . $jx, __FILE__ ) , array( 'jquery', 'jquery-ui-sortable' ), $vr, true );
			wp_localize_script( 'faq-admin', 'faqAdmin', array(
				'updateText'    => self::admin_messages( 'update-sort' ),
				'errorText'     => self::admin_messages( 'error-sort', 'error' ),
			));
		}
	}

	/**
	 * Get our admin message for sorting and saving.
	 *
	 * @param  string $type   The type of message to display.
	 * @param  string $class  The class to use in the markup.
	 *
	 * @return HTML   $build  The message with appropriate markup.
	 */
	public static function admin_messages( $type = 'update-sort', $class = 'updated' ) {

		// Set a default message block.
		$text   = '';

		// Handle my different types.
		switch ( $type ) {

			case 'update-sort' :

				$text   = __( 'FAQ sort order has been saved.', 'wordpress-faq-manager' );
				break;

			case 'error-sort' :

				$text   = __( 'There was an error saving the sort order. Please try again later.', 'wordpress-faq-manager' );
				break;
		}

		// Set an empty.
		$build  = '';

		// Open the div wrapper
		$build .= '<div id="message" class="wpfaq-message ' . esc_attr( $class ) . ' notice is-dismissible">';

			// Add the text itself.
			$build .= '<p>' . esc_html( $text ) . '</p>';

			// Add the button.
			$build .= '<button class="notice-dismiss" type="button"><span class="screen-reader-text">' . __( 'Dismiss this notice.' ) . '</span></button>';

		// Close the message wrapper.
		$build .= '</div>';

		// Return the message.
		return $build;
	}

	/**
	 * Set the default order for FAQs
	 *
	 * @param  object $query  The existing query object.
	 *
	 * @return object $query  The modified query object.
	 */
	public function default_admin_sort( $query ) {

		// Bail on non-admin.
		if ( ! is_admin() ) {
			return $query;
		}

		// Bail if our screen check doesn't work.
		if ( false === $check = WPFAQ_Manager_Helper::check_current_screen() ) {
			return $query;
		}

		// Bail on trash or draft page.
		if ( ! empty( $_REQUEST['post_status'] ) && in_array( $_REQUEST['post_status'], array( 'trash', 'draft' ) ) ) {
			return $query;
		}

		// Bail on a month-based lookup.
		if ( ! empty( $_REQUEST['m'] ) ) {
			return $query;
		}

		// Our standard setup to sort in accending menu order.
		if ( empty( $_REQUEST['order'] ) && empty( $_REQUEST['orderby'] ) ) {
			$query->query_vars['order']      = 'ASC';
			$query->query_vars['orderby']    = 'menu_order';
		}

		// send back the query
		return $query;
	}

	/**
	 * Clear any transients related to the FAQs when saving one.
	 *
	 * @param  integer $post_id  The post ID of the item being saved.
	 *
	 * @return void
	 */
	public function clear_transients( $post_id ) {

		// Bail out if running an autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Bail out if running an ajax.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		// Bail out if running a cron job.
		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return;
		}

		// Now check the post type.
		if ( 'question' !== get_post_type( $post_id ) ) {
			return;
		}

		// Delete our transients.
		delete_transient( 'wpfaq_widget_fetch_random' );
		delete_transient( 'wpfaq_widget_fetch_recent' );
		delete_transient( 'wpfaq_total_faq_count' );
		delete_transient( 'wpfaq_admin_fetch_faqs' );
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

			// Our instruction links.
			$instruct   = '<a href="' . admin_url( 'edit.php?post_type=question&page=instructions' ) . '">' . __( 'How-To', 'wordpress-faq-manager' ) . '</a>';

			// Add them all into the array.
			array_push( $links, $instruct );
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
	 * Update the "enter title here" text for the FAQs.
	 *
	 * @param  string $title  The current title.
	 *
	 * @return string $title  The updated title.
	 */
	public function title_text( $title ){
		return false !== WPFAQ_Manager_Helper::check_current_screen() ? __( 'Enter question title here', 'wordpress-faq-manager' ) : $title;
	}

	/**
	 * The instructions page.
	 *
	 * @return void
	 */
	public function instructions_page() {
		?>

        <div id="faq-admin-instructions" class="wrap faq-admin-page-wrap faq-admin-instructions-wrap">

        	<h1><?php _e( 'FAQ Manager Instructions', 'wordpress-faq-manager' ); ?></h1>

			<div class="faqinfo-intro-content">

				<p><?php _e( 'The FAQ Manager plugin uses a combination of a custom post type and custom taxonomies.', 'wordpress-faq-manager' ); ?></p>

				<p><?php _e( 'The plugin will automatically create single posts using your existing permalink structure, and the FAQ topics and tags can be added to your menu using the WP Menu Manager.', 'wordpress-faq-manager' ); ?></p>

				<h4 class="faqinfo-callout"><span class="dashicons dashicons-megaphone faqinfo-dashicon"></span><?php _e( 'Questions? Issues? Bugs?', 'wordpress-faq-manager' ); ?> <a href="https://github.com/norcross/wordpress-faq-manager/issues" target="_blank" title="<?php _e( 'WordPress FAQ Manager on GitHub', 'wordpress-faq-manager' ); ?>"><?php _e( 'Please report them on GitHub', 'wordpress-faq-manager' ); ?></a>.</h4>
			</div>

			<div class="faqinfo-instruction-content">

				<h2 class="title"><?php _e( 'Shortcodes', 'wordpress-faq-manager' ); ?></h2>

				<p><?php _e( 'The plugin also has the option of using shortcodes. To use them, follow the syntax accordingly in the HTML tab:', 'wordpress-faq-manager' ); ?></p>

				<ul class="faqinfo-list">

					<li class="faqinfo-strong"><?php _e( 'For the complete list (including title and content):', 'wordpress-faq-manager' ); ?></li>

					<li class="faqinfo-code"><?php _e( 'place <code>[faq]</code> on a post / page', 'wordpress-faq-manager' ); ?></li>

					<li class="faqinfo-strong"><?php _e( 'For the question title, and a link to the FAQ on a separate page:', 'wordpress-faq-manager' ); ?></li>

					<li class="faqinfo-code"><?php _e( 'place <code>[faqlist]</code> on a post / page', 'wordpress-faq-manager' ); ?></li>

					<li class="faqinfo-strong"><?php _e( 'For a list with a group of titles that link to complete content later in page:', 'wordpress-faq-manager' ); ?></li>

					<li class="faqinfo-code"><?php _e( 'place <code>[faqcombo]</code> on a post / page', 'wordpress-faq-manager' ); ?></li>

					<li class="faqinfo-strong"><?php _e( 'For a list of taxonomy titles that link to the related archive page:', 'wordpress-faq-manager' ); ?></li>

					<li class="faqinfo-code"><?php _e( 'place <code>[faqtaxlist type="topics"]</code> or <code>[faqtaxlist type="tags"]</code> on a post / page', 'wordpress-faq-manager' ); ?></li>

					<li class="faqinfo-code"><?php _e( 'Show optional description: <code>[faqtaxlist type="topics" desc="true"]</code>', 'wordpress-faq-manager' ); ?></li>

					<li class="faqinfo-details"><?php _e( '<strong>Please note:</strong> the combo and taxonomy list shortcodes will not recognize the pagination and expand / collapse', 'wordpress-faq-manager' ); ?></li>

				</ul>

			</div>

			<div class="faqinfo-instruction-content">

				<h2 class="title"><?php _e( 'The following options apply to all the <code>shortcode</code> types', 'wordpress-faq-manager' ); ?></h2>

				<p><?php _e( 'The list will show 10 FAQs based on your sorting (if none has been done, it will be in date order).', 'wordpress-faq-manager' ); ?></p>

				<ul class="faqinfo-list">

					<li class="faqinfo-strong"><?php _e( 'To display only 5:', 'wordpress-faq-manager' ); ?></li>

					<li class="faqinfo-code"><?php _e( 'place <code>[faq limit="5"]</code> on a post / page', 'wordpress-faq-manager' ); ?></li>

					<li class="faqinfo-strong"><?php _e( 'To display ALL:', 'wordpress-faq-manager' ); ?></li>

					<li class="faqinfo-code"><?php _e( 'place <code>[faq limit="-1"]</code> on a post / page', 'wordpress-faq-manager' ); ?></li>

				</ul>

				<ul class="faqinfo-list">

					<li class="faqinfo-strong"><?php _e( 'For a single FAQ:', 'wordpress-faq-manager' ); ?></li>

					<li class="faqinfo-code"><?php _e( 'place <code>[faq faq_id="ID"]</code> on a post / page', 'wordpress-faq-manager' ); ?></li>

					<li class="faqinfo-strong"><?php _e( 'List all from a single FAQ topic category:', 'wordpress-faq-manager' ); ?></li>

					<li class="faqinfo-code"><?php _e( 'place <code>[faq faq_topic="topic-slug"]</code> on a post / page', 'wordpress-faq-manager' ); ?></li>

					<li class="faqinfo-strong"><?php _e( 'List all from multiple FAQ topic categories:', 'wordpress-faq-manager' ); ?></li>

					<li class="faqinfo-code"><?php _e( 'place <code>[faq faq_topic="topic-slug-1, topic-slug-2"]</code> on a post / page', 'wordpress-faq-manager' ); ?></li>

					<li class="faqinfo-strong"><?php _e( 'List all from a single FAQ tag:', 'wordpress-faq-manager' ); ?></li>

					<li class="faqinfo-code"><?php _e( 'place <code>[faq faq_tag="tag-slug"]</code> on a post / page', 'wordpress-faq-manager' ); ?></li>

					<li class="faqinfo-strong"><?php _e( 'List all from multiple FAQ tags:', 'wordpress-faq-manager' ); ?></li>

					<li class="faqinfo-code"><?php _e( 'place <code>[faq faq_tag="tag-slug-1, tag-slug-2"]</code> on a post / page', 'wordpress-faq-manager' ); ?></li>

					<li class="faqinfo-strong"><?php _e( 'List all from both FAQ topcis and FAQ tags:', 'wordpress-faq-manager' ); ?></li>

					<li class="faqinfo-code"><?php _e( 'place <code>[faq faq_topic="topic-slug-1" faq_tag="tag-slug-2"]</code> on a post / page', 'wordpress-faq-manager' ); ?></li>
				</ul>

			</div>

		</div>

	<?php }

	// End our class.
}

// Call our class.
$WPFAQ_Manager_Admin = new WPFAQ_Manager_Admin();
$WPFAQ_Manager_Admin->init();

