<?php
/**
 * WP FAQ Manager - Post Types Module
 *
 * Contains custom post types, taxonomies, and related functions
 *
 * @package WordPress FAQ Manager
 */

/**
 * Start our engines.
 */
class WPFAQ_Manager_Types {

	/**
	 * Call our hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init',                             array( $this, '_register_post_type'     )           );
		add_action( 'init',                             array( $this, '_register_tax_topics'    )           );
		add_action( 'init',                             array( $this, '_register_tax_tags'      )           );
	}

	/**
	 * Register our FAQ custom post type.
	 *
	 * @return void
	 */
	public function _register_post_type() {

		// Set my labels first.
		$labels = array(
			'name'                => __( 'FAQs', 'wordpress-faq-manager' ),
			'singular_name'       => __( 'FAQ', 'wordpress-faq-manager' ),
			'add_new'             => __( 'Add New FAQ', 'wordpress-faq-manager' ),
			'add_new_item'        => __( 'Add New FAQ', 'wordpress-faq-manager' ),
			'edit'                => __( 'Edit', 'wordpress-faq-manager' ),
			'edit_item'           => __( 'Edit FAQ', 'wordpress-faq-manager' ),
			'new_item'            => __( 'New FAQ', 'wordpress-faq-manager' ),
			'view'                => __( 'View FAQ', 'wordpress-faq-manager' ),
			'view_item'           => __( 'View FAQ', 'wordpress-faq-manager' ),
			'search_items'        => __( 'Search FAQ', 'wordpress-faq-manager' ),
			'not_found'           => __( 'No FAQs found', 'wordpress-faq-manager' ),
			'not_found_in_trash'  => __( 'No FAQs found in Trash', 'wordpress-faq-manager' ),
		);

		// Set the labels with their filter.
		$labels = apply_filters( 'wpfaq_question_post_labels', $labels );

		// Set a capability type and slugs.
		$cap    = apply_filters( 'wpfaq_question_post_cap_type', 'post' );
		$single = apply_filters( 'wpfaq_question_post_slug_single', 'question' );
		$arch   = apply_filters( 'wpfaq_question_post_slug_archive', 'questions' );

		// Set an array of what we support.
		$sppts  = apply_filters( 'wpfaq_question_post_supports', array( 'title', 'editor', 'author', 'thumbnail', 'comments', 'custom-fields' ) );

		// Now set the args.
		$args   = array(
			'labels'                => $labels,
			'public'                => true,
			'show_in_nav_menus'     => true,
			'show_ui'               => true,
			'publicly_queryable'    => true,
			'exclude_from_search'   => false,
			'hierarchical'          => false,
			'menu_position'         => null,
			'capability_type'       => sanitize_title_with_dashes(( $cap ),
			'menu_icon'             => 'dashicons-editor-help',
			'query_var'             => true,
			'rewrite'               => array( 'slug' => sanitize_title_with_dashes( $single ), 'with_front' => false ),
			'has_archive'           => sanitize_title_with_dashes( $arch ),
			'supports'              => $sppts,
		);

		// Our last-chance filter for everything.
		$args   = apply_filters( 'wpfaq_question_post_args', $args );

		// If someone cleared the args, just bail.
		if ( empty( $args ) ) {
			return;
		}

		// Register the post type.
		register_post_type( 'question', $args );
	}

	/**
	 * Register our FAQ topics custom taxonomies
	 *
	 * @return void
	 */
	public function _register_tax_topics() {

		// Set my labels first.
		$labels = array(
			'name'              => __( 'FAQ Topics', 'wordpress-faq-manager' ),
			'singular_name'     => __( 'FAQ Topic', 'wordpress-faq-manager' ),
			'search_items'      => __( 'Search FAQ Topics', 'wordpress-faq-manager' ),
			'popular_items'     => __( 'Popular FAQ Topics', 'wordpress-faq-manager' ),
			'all_items'         => __( 'All FAQ Topics', 'wordpress-faq-manager' ),
			'parent_item'       => __( 'Parent FAQ Topic', 'wordpress-faq-manager' ),
			'parent_item_colon' => __( 'Parent FAQ Topic:', 'wordpress-faq-manager' ),
			'edit_item'         => __( 'Edit FAQ Topics', 'wordpress-faq-manager' ),
			'update_item'       => __( 'Update FAQ Topics', 'wordpress-faq-manager' ),
			'add_new_item'      => __( 'Add New FAQ Topic', 'wordpress-faq-manager' ),
			'new_item_name'     => __( 'New FAQ Topics', 'wordpress-faq-manager' ),
		);

		// Set the labels with their filter.
		$labels = apply_filters( 'wpfaq_topic_taxonomy_labels', $labels );

		// Set the slug for my taxonomy
		$slug	= apply_filters( 'wpfaq_topic_taxonomy_slug', 'topics' );

		// Set my args array.
		$args   = array(
			'labels'                => $labels,
			'public'                => true,
			'show_in_nav_menus'     => true,
			'show_ui'               => true,
			'publicly_queryable'    => true,
			'show_admin_column'     => true,
			'exclude_from_search'   => false,
			'rewrite'               => array( 'slug' => sanitize_title_with_dashes( $slug ), 'with_front' => true ),
			'hierarchical'          => true,
			'query_var'             => true,
		);

		// Our last-chance filter for everything.
		$args   = apply_filters( 'wpfaq_topic_taxonomy_args', $args );

		// If someone cleared the args, just bail.
		if ( empty( $args ) ) {
			return;
		}

		// And register the taxonomy.
		register_taxonomy( 'faq-topic', array( 'question' ), $args );

		// And register the object type.
		register_taxonomy_for_object_type( 'question', 'faq-topic' );
	}

	/**
	 * Register our FAQ tags custom taxonomies
	 *
	 * @return void
	 */
	public function _register_tax_tags() {

		// Set my labels first.
		$labels = array(
			'name'                       => _x( 'FAQ Tags', 'wordpress-faq-manager' ),
			'singular_name'              => _x( 'FAQ Tag', 'wordpress-faq-manager' ),
			'search_items'               => __( 'Search FAQ Tags', 'wordpress-faq-manager' ),
			'popular_items'              => __( 'Popular FAQ Tags', 'wordpress-faq-manager' ),
			'all_items'                  => __( 'All FAQ Tags', 'wordpress-faq-manager' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit FAQ Tag', 'wordpress-faq-manager' ),
			'update_item'                => __( 'Update FAQ Tag', 'wordpress-faq-manager' ),
			'add_new_item'               => __( 'Add New FAQ Tag', 'wordpress-faq-manager' ),
			'new_item_name'              => __( 'New FAQ Tag Name', 'wordpress-faq-manager' ),
			'separate_items_with_commas' => __( 'Separate FAQ tags with commas', 'wordpress-faq-manager' ),
			'add_or_remove_items'        => __( 'Add or remove FAQ tags', 'wordpress-faq-manager' ),
			'choose_from_most_used'      => __( 'Choose from the most used FAQ tags', 'wordpress-faq-manager' ),
			'not_found'                  => __( 'No FAQ tags found.', 'wordpress-faq-manager' ),
			'menu_name'                  => __( 'FAQ Tags', 'wordpress-faq-manager' ),
		);

		// Set the labels with their filter.
		$labels = apply_filters( 'wpfaq_tags_taxonomy_labels', $labels );

		// Set the slug for my taxonomy
		$slug	= apply_filters( 'wpfaq_tags_taxonomy_slug', 'faq-tags' );

		// Set my args array.
		$args   = array(
			'labels'                => $labels,
			'public'                => true,
			'show_in_nav_menus'     => true,
			'show_ui'               => true,
			'publicly_queryable'    => true,
			'show_admin_column'     => true,
			'exclude_from_search'   => false,
			'rewrite'               => array( 'slug' => sanitize_title_with_dashes( $slug ), 'with_front' => true ),
			'hierarchical'          => false,
			'query_var'             => true,
		);

		// Our last-chance filter for everything.
		$args   = apply_filters( 'wpfaq_tags_taxonomy_args', $args );

		// If someone cleared the args, just bail.
		if ( empty( $args ) ) {
			return;
		}

		// And register the taxonomy.
		register_taxonomy( 'faq-tags', array( 'question' ), $args );

		// And register the object type.
		register_taxonomy_for_object_type( 'question', 'faq-tags' );
	}

	// End our class.
}

// Call our class.
$WPFAQ_Manager_Types = new WPFAQ_Manager_Types();
$WPFAQ_Manager_Types->init();

