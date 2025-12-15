<?php
/**
 * WP FAQ Manager - Post Types Module
 *
 * Contains custom post types, taxonomies, and related functions
 *
 * @package WP FAQ Manager
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

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
			'name'                => __( 'FAQs', 'wp-faq-manager' ),
			'singular_name'       => __( 'FAQ', 'wp-faq-manager' ),
			'add_new'             => __( 'Add New FAQ', 'wp-faq-manager' ),
			'add_new_item'        => __( 'Add New FAQ', 'wp-faq-manager' ),
			'edit'                => __( 'Edit', 'wp-faq-manager' ),
			'edit_item'           => __( 'Edit FAQ', 'wp-faq-manager' ),
			'new_item'            => __( 'New FAQ', 'wp-faq-manager' ),
			'view'                => __( 'View FAQ', 'wp-faq-manager' ),
			'view_item'           => __( 'View FAQ', 'wp-faq-manager' ),
			'search_items'        => __( 'Search FAQ', 'wp-faq-manager' ),
			'not_found'           => __( 'No FAQs found', 'wp-faq-manager' ),
			'not_found_in_trash'  => __( 'No FAQs found in Trash', 'wp-faq-manager' ),
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
			'show_in_rest'          => true,
			'rest_base'             => 'questions',
			'hierarchical'          => false,
			'menu_position'         => null,
			'capability_type'       => sanitize_title_with_dashes( $cap ),
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
			'name'              => __( 'FAQ Topics', 'wp-faq-manager' ),
			'singular_name'     => __( 'FAQ Topic', 'wp-faq-manager' ),
			'search_items'      => __( 'Search FAQ Topics', 'wp-faq-manager' ),
			'popular_items'     => __( 'Popular FAQ Topics', 'wp-faq-manager' ),
			'all_items'         => __( 'All FAQ Topics', 'wp-faq-manager' ),
			'parent_item'       => __( 'Parent FAQ Topic', 'wp-faq-manager' ),
			'parent_item_colon' => __( 'Parent FAQ Topic:', 'wp-faq-manager' ),
			'edit_item'         => __( 'Edit FAQ Topics', 'wp-faq-manager' ),
			'update_item'       => __( 'Update FAQ Topics', 'wp-faq-manager' ),
			'add_new_item'      => __( 'Add New FAQ Topic', 'wp-faq-manager' ),
			'new_item_name'     => __( 'New FAQ Topics', 'wp-faq-manager' ),
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
			'show_in_rest'          => true,
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
			'name'                       => _x( 'FAQ Tags', 'wp-faq-manager' ),
			'singular_name'              => _x( 'FAQ Tag', 'wp-faq-manager' ),
			'search_items'               => __( 'Search FAQ Tags', 'wp-faq-manager' ),
			'popular_items'              => __( 'Popular FAQ Tags', 'wp-faq-manager' ),
			'all_items'                  => __( 'All FAQ Tags', 'wp-faq-manager' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit FAQ Tag', 'wp-faq-manager' ),
			'update_item'                => __( 'Update FAQ Tag', 'wp-faq-manager' ),
			'add_new_item'               => __( 'Add New FAQ Tag', 'wp-faq-manager' ),
			'new_item_name'              => __( 'New FAQ Tag Name', 'wp-faq-manager' ),
			'separate_items_with_commas' => __( 'Separate FAQ tags with commas', 'wp-faq-manager' ),
			'add_or_remove_items'        => __( 'Add or remove FAQ tags', 'wp-faq-manager' ),
			'choose_from_most_used'      => __( 'Choose from the most used FAQ tags', 'wp-faq-manager' ),
			'not_found'                  => __( 'No FAQ tags found.', 'wp-faq-manager' ),
			'menu_name'                  => __( 'FAQ Tags', 'wp-faq-manager' ),
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
			'show_in_rest'          => true,
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

