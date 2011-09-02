<?php
/*
Plugin Name: WordPress FAQ Manager
Plugin URI: http://andrewnorcross.com/tools/faq-manager/
Description: Uses custom post types and taxonomies to manage an FAQ section for your site.
Author: Andrew Norcross
Version: 1.11
Requires at least: 3.0
Author URI: http://andrewnorcross.com
*/
/*  Copyright 2011 Andrew Norcross

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; version 2 of the License (GPL v2) only.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
// flush permalinks on plugin initalization ****thanks to Austin Passay @TheFrosty for the help****

function faq_flush_rewrite_rules() {
	global $pagenow, $wp_rewrite;
	if ('plugins.php' == $pagenow && isset( $_GET['activate'] ) )
		$wp_rewrite->flush_rules();
}
add_action('load-plugins.php', 'faq_flush_rewrite_rules' );

// Shortcode to allow placement of FAQs
// Can either be a complete list or separated by slugs (single only at this time)

//include admin page
include('inc/faq-widgets.php');
if (is_admin () ) {
	include('inc/faq-admin.php');
}

	function faq_shortcode($atts, $displayfaq = NULL) {
		extract(shortcode_atts(array(
			'faq_topic'		=> '',
			'faq_tag'		=> '',
			'faq_id'		=> '',
			'limit'			=> '10',
		), $atts));
		// pagination call. required regardless of whether pagination is active or not
			if( isset( $_GET['faq_page'] ) && $faq_page = absint( $_GET['faq_page'] ) )
				$paged = $faq_page;
			else
				$paged = 1;
			$old_link = trailingslashit(get_permalink());
		// end paginaton
		$faq_topic = preg_replace('~&#x0*([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $faq_topic);
		$faq_tag = preg_replace('~&#x0*([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $faq_tag);
		if (get_option('faq_jquery' ))	{ $jquery_add = ' expa'; } else { $jquery_add = NULL; }
			$wp_query = new WP_Query(array(
				'p'					=> ''.$faq_id.'',
				'faq-topic'			=> ''.$faq_topic.'',
				'faq-tags'			=> ''.$faq_tag.'',
				'post_type'			=>	'question',
				'posts_per_page'	=>	''.$limit.'',
				'orderby'			=>	'menu_order',
				'order'				=>	'ASC',
				'paged'				=>	$paged,
				));
			$displayfaq .= '<div id="faq_block"><div class="faq_list">';
				while ($wp_query->have_posts()) : $wp_query->the_post();
				global $post;
					$faq_content = get_the_content();
					$faq_slug  = basename(get_permalink());
					$faq_htype  = get_option('faq_htype' );
				$displayfaq .= '<div class="single_faq">';
				$displayfaq .= '<'.$faq_htype.' id="'.$faq_slug.'" class="faq_question'.$jquery_add.'">'.get_the_title().'</'.$faq_htype.'>';
				$displayfaq .= '<div class="faq_answer">'.wpautop($faq_content, true).'</div>';
				$displayfaq .= '</div>';
				endwhile;

				if (get_option('faq_paginate' )) {
					// pagination links
					$displayfaq .= '<p class="faq_nav">';
					$displayfaq .= paginate_links(array(
					  'base'	=> $old_link . '%_%',
					  'format'	=> '?faq_page=%#%',
					  'type'	=> 'plain',
					  'total'	=> $wp_query->max_num_pages,
					  'current' => $paged,
					));
					$displayfaq .= '</p>';
				// end pagination links
				}
				wp_reset_query();
			$displayfaq .= '</div></div>';
		return $displayfaq;
		}

add_shortcode('faq','faq_shortcode');

function faqlist_shortcode($atts, $displayfaq = NULL) {
		extract(shortcode_atts(array(
			'faq_topic'	=> '',
			'faq_tag'	=> '',
			'faq_id'	=> '',
			'limit'		=> '10',
		), $atts));
		// pagination call. required regardless of whether pagination is active or not
			if( isset( $_GET['faq_page'] ) && $faq_page = absint( $_GET['faq_page'] ) )
				$paged = $faq_page;
			else
				$paged = 1;
			$old_link = trailingslashit(get_permalink());
		// end paginaton
		$faq_topic = preg_replace('~&#x0*([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $faq_topic);
		$faq_tag = preg_replace('~&#x0*([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $faq_tag);
			$wp_query = new WP_Query(array(
				'p'					=> ''.$faq_id.'',
				'faq-topic'			=> ''.$faq_topic.'',
				'faq-tags'			=> ''.$faq_tag.'',
				'post_type'			=>	'question',
				'posts_per_page'	=>	''.$limit.'',
				'orderby'			=>	'menu_order',
				'order'				=>	'ASC',
				'paged'				=>	$paged,
				));
			$displayfaq .= '<div id="faq_block"><div class="faq_list">';
				while ($wp_query->have_posts()) : $wp_query->the_post();
				global $post;
					$faq_slug  = basename(get_permalink());
					$faq_htype  = get_option('faq_htype' );
				$displayfaq .= '<div class="single_faq">';
				$displayfaq .= '<'.$faq_htype.' id="'.$faq_slug.'" class="faqlist_question"><a href="'.get_permalink().'" title="Permanent link to '.get_the_title().'" >'.get_the_title().'</a></'.$faq_htype.'>';
				$displayfaq .= '</div>';
				endwhile;
				if (get_option('faq_paginate' )) {
					// pagination links
					$displayfaq .= '<p class="faq_nav">';
					$displayfaq .= paginate_links(array(
					  'base'	=> $old_link . '%_%',
					  'format'	=> '?faq_page=%#%',
					  'type'	=> 'plain',
					  'total'	=> $wp_query->max_num_pages,
					  'current' => $paged,
					));
					$displayfaq .= '</p>';
				// end pagination links
				}
			wp_reset_query();
			$displayfaq .= '</div></div>';
		return $displayfaq;
		}

add_shortcode('faqlist','faqlist_shortcode');

// Register Custom Taxonomy and Post Type 

add_action( 'init', '_init_faq_post_type' );

function _init_faq_post_type() {
	$slug = get_option ('faq_arch_slug');
	if ( !empty( $slug ) ) { $faqslug = $slug; } else { $faqslug = 'questions'; };

	$public = get_option ('faq_public');
	if ( !empty( $public ) ) { $faqpublic = true; } else { $faqpublic = false; };

	register_post_type( 'question',
		array(
			'labels' => array(
				'name' => __( 'FAQs' ),
				'singular_name' => __( 'FAQ' ),
				'add_new' => __( 'Add New FAQ' ),
				'add_new_item' => __( 'Add New FAQ' ),
				'edit' => __( 'Edit' ),
				'edit_item' => __( 'Edit FAQ' ),
				'new_item' => __( 'New FAQ' ),
				'view' => __( 'View FAQ' ),
				'view_item' => __( 'View FAQ' ),
				'search_items' => __( 'Search FAQ' ),
				'not_found' => __( 'No FAQs found' ),
				'not_found_in_trash' => __( 'No FAQs found in Trash' ),
			),
			'public' => $faqpublic,
				'show_in_nav_menus' => false,			
				'show_ui' => true,
				'publicly_queryable' => true,
				'exclude_from_search' => false,
			'hierarchical' => false,
			'menu_position' => 20,
			'capability_type' => 'post',
			'menu_icon' => plugins_url( '/inc/img/faq_menu.png', __FILE__ ),
			'query_var' => true,
			'rewrite' => true,
			'has_archive' => $faqslug,
			'supports' => array('title', 'editor', 'author', 'thumbnail', 'comments', 'custom-fields'),
		)
	);
	// register topics (categories) for FAQs
	register_taxonomy(
		'faq-topic',
		array( 'question' ),
		array(
			'public' => true,
			'show_in_nav_menus' => true,
			'show_ui' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'rewrite' => array( 'slug' => 'topics', 'with_front' => true ),
			'hierarchical' => true,
			'query_var' => true,
			'labels' => array(
				'name' => __( 'FAQ Topics' ),
				'singular_name' => __( 'FAQ Topic' ),
				'search_items' => __( 'Search FAQ Topics' ),
				'popular_items' => __( 'Popular FAQ Topics' ),
				'all_items' => __( 'All FAQ Topics' ),
				'parent_item' => __( 'Parent FAQ Topic' ),
				'parent_item_colon' => __( 'Parent FAQ Topic:' ),
				'edit_item' => __( 'Edit FAQ Topics' ),
				'update_item' => __( 'Update FAQ Topics' ),
				'add_new_item' => __( 'Add New FAQ Topics' ),
				'new_item_name' => __( 'New FAQ Topics' ),
			),
		)
	);
	// register tags for FAQs
	register_taxonomy(
		'faq-tags',
		array( 'question' ),
		array(
			'public' => true,
			'show_in_nav_menus' => true,
			'show_ui' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'rewrite' => array( 'slug' => 'faq-tags', 'with_front' => true ),
			'hierarchical' => false,
			'query_var' => true,
			'labels' => array(
				'name' => __( 'FAQ Tags' ),
				'singular_name' => __( 'FAQ Tag' ),
				'search_items' => __( 'Search FAQ Tags' ),
				'popular_items' => __( 'Popular FAQ Tags' ),
				'all_items' => __( 'All FAQ Tags' ),
				'parent_item' => __( 'Parent FAQ Tags' ),
				'parent_item_colon' => __( 'Parent FAQ Tag:' ),
				'edit_item' => __( 'Edit FAQ Tag' ),
				'update_item' => __( 'Update FAQ Tag' ),
				'add_new_item' => __( 'Add New FAQ Tag' ),
				'new_item_name' => __( 'New FAQ Tag' ),
			),
		)
	);
	register_taxonomy_for_object_type('question', 'faq-tags');
	register_taxonomy_for_object_type('question', 'faq-topic');
}

// Admin panel excerpt
    function faq_excerpt_content($limit) {
      $content = explode(' ', get_the_content(), $limit);
      if (count($content)>=$limit) {
        array_pop($content);
        $content = implode(" ",$content).'...';
      } else {
        $content = implode(" ",$content);
      } 
      $content = preg_replace('/\[.+\]/','', $content);
      $content = apply_filters('the_content', $content); 
      $content = str_replace(']]>', ']]&gt;', $content);
      return $content;
    }

function faq_editor_excerpt($limit) {
      $excerpt = explode(' ', get_the_excerpt(), $limit);
      if (count($excerpt)>=$limit) {
        array_pop($excerpt);
        $excerpt = implode(" ",$excerpt).'...';
      } else {
        $excerpt = implode(" ",$excerpt);
      } 
      $excerpt = preg_replace('`\[[^\]]*\]`','',$excerpt);
      return $excerpt;
    }

function faq_excerpt_widget($limit) {
      $excerpt = explode(' ', get_the_excerpt(), $limit);
      if (count($excerpt)>=$limit) {
        array_pop($excerpt);
        $excerpt = implode(" ",$excerpt).'...';
      } else {
        $excerpt = implode(" ",$excerpt);
      } 
      $excerpt = preg_replace('`\[[^\]]*\]`','',$excerpt);
      return $excerpt;
    }

function load_faq_pagination_combo () {
// Optional setting for loading jQuery collapse
	if( !is_admin() && get_option('faq_jquery') == 'true' && get_option('faq_paginate') == 'true') {
		wp_enqueue_script('jpaginate-combo', plugins_url('/inc/js/faq-pagination-combo.js', __FILE__) , array('jquery'), '1.0' );
		}
}
add_action('wp_print_scripts', 'load_faq_pagination_combo');


function load_faq_pagination() {
// Optional setting for loading jQuery collapse
	if( !is_admin() && get_option('faq_paginate') == 'true' && !get_option('faq_jquery') == 'true' ) {
		wp_enqueue_script('jpaginate', plugins_url('/inc/js/faq-pagination.js', __FILE__) , array('jquery'), '1.0' );
		}
}
add_action('wp_print_scripts', 'load_faq_pagination');

function load_faq_collapse() {
// Optional setting for loading jQuery collapse
	if( !is_admin() && get_option('faq_jquery') == 'true' && !get_option('faq_paginate') == 'true') {
		wp_enqueue_script('jcollapse', plugins_url('/inc/js/faq-collapse.js', __FILE__) , array('jquery'), '1.0' );
		}
}
 add_action('wp_print_scripts', 'load_faq_collapse');


function faq_css() {
// Optional setting for loading CSS
	if( !is_admin() && get_option('faq_css') == 'true') {
		wp_enqueue_style('faq_style', plugins_url('/inc/css/faq-default.css', __FILE__) ) ;
	    }
}
add_action( 'wp_print_styles', 'faq_css' );

?>