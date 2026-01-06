<?php

/**
 * WP FAQ Manager - Shortcodes Module
 *
 * Contains our shortcodes and related functionality.
 *
 * @package WP FAQ Manager
 */

if (! defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Start our engines.
 */
class WPFAQ_Manager_Shortcodes
{

	/**
	 * Call our hooks.
	 *
	 * @return void
	 */
	public function init()
	{
		add_shortcode('faq',                           array($this, 'shortcode_main'));
		add_shortcode('faqlist',                       array($this, 'shortcode_list'));
		add_shortcode('faqtaxlist',                    array($this, 'shortcode_tax_list'));
		add_shortcode('faqcombo',                      array($this, 'shortcode_combo'));
	}

	/**
	 * Our primary shortcode display.
	 *
	 * @param  array $atts     The shortcode attributes.
	 * @param  mixed $content  The content on the post being displayed.
	 *
	 * @return mixed           The original content with our shortcode data.
	 */
	public function shortcode_main($atts, $content = null)
	{

		// Parse my attributes.
		$atts   = shortcode_atts(array(
			'faq_topic' => '',
			'faq_tag'   => '',
			'faq_id'    => 0,
			'limit'     => 10,
		), $atts, 'faq');

		// Set each possible taxonomy into an array.
		$topics = ! empty($atts['faq_topic']) ? explode(',', esc_attr($atts['faq_topic'])) : array();
		$tags   = ! empty($atts['faq_tag']) ? explode(', ', esc_attr($atts['faq_tag'])) : array();

		// Determine my pagination set.
		$paged = 1;

		if (isset($_GET['faq_page'])) {
			$paged = max(1, absint(wp_unslash($_GET['faq_page'])));
		}
		// Fetch my items.
		if (false === $faqs = WPFAQ_Manager_Data::get_main_shortcode_faqs($atts['faq_id'], $atts['limit'], $topics, $tags, $paged)) {
			return;
		}

		// Set some variables used within.
		$speed  = apply_filters('wpfaq_display_expand_speed', 200, 'main');
		$expand = apply_filters('wpfaq_display_content_expand', true, 'main');
		$filter = apply_filters('wpfaq_display_content_filter', true, 'main');
		$exlink = apply_filters('wpfaq_display_content_more_link', array('show' => 1, 'text' => __('Read More', 'easy-faq-manager')), 'main');
		$pageit = apply_filters('wpfaq_display_shortcode_paginate', true, 'main');

		// Make sure we have a valid H type to use.
		$htype  = WPFAQ_Manager_Helper::check_htype_tag('h3', 'main');

		// Set some classes for markup.
		$dclass = ! empty($expand) ? 'faq-list expand-faq-list' : 'faq-list';
		$bclass = ! empty($expand) ? 'single-faq expand-faq' : 'single-faq';
		$tclass = ! empty($expand) ? 'faq-question expand-title' : 'faq-question';

		// Call our CSS and JS files.
		wp_enqueue_style('faq-front');
		wp_enqueue_script('faq-front');

		// Start my markup.
		$build  = '';

		// The wrapper around.
		$build .= '<div id="faq-block" class="faq-block-wrap" name="faq-block">';
		$build .= '<div class="' . esc_attr($dclass) . '" data-speed="' . absint($speed) . '">';

		// Loop my individual FAQs
		foreach ($faqs as $faq) {

			// Wrap a div around each item.
			$build .= '<div class="' . esc_attr($bclass) . '">';

			// Our title setup.
			$build .= '<' . esc_attr($htype) . ' id="' . esc_attr($faq->post_name) . '" name="' . esc_attr($faq->post_name) . '" class="' . esc_attr($tclass) . '">' . esc_html($faq->post_title) .  '</' . esc_attr($htype) . '>';

			// Our content display.
			$build .= '<div class="faq-answer" rel="' . esc_attr($faq->post_name) . '">';

			// Show the content, with the optional filter.
			$build .= false !== $filter ? apply_filters('the_content', $faq->post_content) : wpautop($faq->post_content);

			// Show the "read more" link.
			if (! empty($exlink)) {

				// Fetch the link and text to display.
				$link   = get_permalink(absint($faq->ID));
				$more   = ! empty($exlink['text']) ? $exlink['text'] : __('Read More', 'easy-faq-manager');

				// The display portion itself.
				$build .= '<p class="faq-link">';
				$build .= '<a href="' . esc_url($link) . '" title="' . esc_attr($faq->post_title) .  '">' . esc_html($more) . '</a>';
				$build .= '</p>';
			}

			// Close the div around the content display.
			$build .= '</div>';

			// Close the div around each item.
			$build .= '</div>';
		}

		// Handle our optional pagination.
		if (! empty($pageit) && empty($atts['faq_id'])) {
			$build .= WPFAQ_Manager_Helper::build_pagination($atts, get_permalink(), $paged, 'main');
		}

		// Close the markup wrappers.
		$build .= '</div>';
		$build .= '</div>';

		// Return my markup.
		return $build;
	}

	/**
	 * Our list version of the shortcode display.
	 *
	 * @param  array $atts     The shortcode attributes.
	 * @param  mixed $content  The content on the post being displayed.
	 *
	 * @return mixed           The original content with our shortcode data.
	 */
	public function shortcode_list($atts, $content = null)
	{

		// Parse my attributes.
		$atts   = shortcode_atts(array(
			'faq_topic' => '',
			'faq_tag'   => '',
			'faq_id'    => 0,
			'limit'     => 10,
		), $atts, 'faqlist');

		// Set each possible taxonomy into an array.
		$topics = ! empty($atts['faq_topic']) ? explode(',', esc_attr($atts['faq_topic'])) : array();
		$tags   = ! empty($atts['faq_tag']) ? explode(',', esc_attr($atts['faq_tag'])) : array();

		// Determine my pagination set.
		$paged  = ! empty($_GET['faq_page']) ? absint($_GET['faq_page']) : 1;

		// Fetch my items.
		if (false === $faqs = WPFAQ_Manager_Data::get_main_shortcode_faqs($atts['faq_id'], $atts['limit'], $topics, $tags, $paged)) {
			return;
		}

		// Call our CSS file.
		wp_enqueue_style('faq-front');
		wp_enqueue_script('faq-front');

		// Set some variables used within.
		$pageit = apply_filters('wpfaq_display_shortcode_paginate', true, 'list');

		// Start my markup.
		$build  = '';

		// The wrapper around.
		$build .= '<div id="faq-block" class="faq-block-wrap" name="faq-block">';
		$build .= '<div class="faq-list">';

		// Set up a list wrapper.
		$build .= '<ul>';

		// Loop my individual FAQs
		foreach ($faqs as $faq) {

			// Get my permalink.
			$link   = get_permalink($faq->ID);

			// Wrap a li around each item.
			$build .= '<li class="faqlist-question">';

			// The actual link.
			$build .= '<a href="' . esc_url($link) . '" title="' . esc_attr($faq->post_title) .  '">' . esc_html($faq->post_title) .  '</a>';

			// Close the li around each item.
			$build .= '</li>';
		}

		// Close up the list wrapper.
		$build .= '</ul>';

		// Handle our optional pagination.
		if (! empty($pageit) && empty($atts['faq_id'])) {
			$build .= WPFAQ_Manager_Helper::build_pagination($atts, get_permalink(), $paged, 'list');
		}

		// Close the markup wrappers.
		$build .= '</div>';
		$build .= '</div>';

		// Return my markup.
		return $build;
	}

	/**
	 * Our list of taxonomies of the shortcode display.
	 *
	 * @param  array $atts     The shortcode attributes.
	 * @param  mixed $content  The content on the post being displayed.
	 *
	 * @return mixed           The original content with our shortcode data.
	 */
	public function shortcode_tax_list($atts, $content = null)
	{

		// Parse my attributes.
		$atts   = shortcode_atts(array(
			'type'      => 'topics',
			'desc'      => '',
			'linked'    => true,
		), $atts, 'faqtaxlist');

		// If no type is set, or it's not a valid one, bail.
		if (empty($atts['type']) || ! in_array(esc_attr($atts['type']), array('topics', 'tags'))) {
			return;
		}

		// Now set the actual type we have registered, along with the description flag.
		$type   = ! empty($atts['type']) && 'topics' === esc_attr($atts['type']) ? 'faq-topic' : 'faq-tag';

		// Fetch my terms.
		if (false === $terms = WPFAQ_Manager_Data::get_tax_shortcode_terms($type)) {
			return;
		}

		// Call our CSS file.
		wp_enqueue_style('faq-front');
		wp_enqueue_script('faq-front');

		// Make sure we have a valid H type to use.
		$htype  = WPFAQ_Manager_Helper::check_htype_tag('h3', 'taxlist');

		// Start my markup.
		$build  = '';

		// The wrapper around.
		$build .= '<div id="faq-block" name="faq-block" class="faq-block-wrap faq-taxonomy faq-taxonomy-' . sanitize_html_class($type) . '">';

		// Loop my individual terms
		foreach ($terms as $term) {

			// Wrap a div around each item.
			$build .= '<div id="' . esc_attr($term->slug) . '" class="faq-item faq-taxlist-item">';

			// Our title setup.
			$build .= '<' . esc_attr($htype) . ' name="' . esc_attr($term->slug) . '">';

			// The title name (linked or otherwise).
			$build .= ! empty($atts['linked']) ? '<a href="' . get_term_link($term, $type) . '">' . esc_html($term->name) . '</a>' : esc_html($term->name);

			// Close the title.
			$build .= '</' . esc_attr($htype) . '>';

			// Optional description.
			if (! empty($atts['desc']) && ! empty($term->description)) {
				$build .= wpautop(esc_attr($term->description));
			}

			// Close the div around each item.
			$build .= '</div>';
		}

		// Close the wrapper
		$build .= '</div>';

		// Return my markup.
		return $build;
	}

	/**
	 * Our list of taxonomies of the shortcode display.
	 *
	 * @param  array $atts     The shortcode attributes.
	 * @param  mixed $content  The content on the post being displayed.
	 *
	 * @return mixed           The original content with our shortcode data.
	 */
	public function shortcode_combo($atts, $content = null)
	{

		// Parse my attributes.
		$atts   = shortcode_atts(array(
			'faq_topic' => '',
			'faq_tag'   => '',
			'faq_id'    => 0,
		), $atts, 'faqcombo');

		// Set each possible taxonomy into an array.
		$topics = ! empty($atts['faq_topic']) ? explode(',', esc_attr($atts['faq_topic'])) : array();
		$tags   = ! empty($atts['faq_tag']) ? explode(',', esc_attr($atts['faq_tag'])) : array();

		// Fetch my items.
		if (false === $faqs = WPFAQ_Manager_Data::get_combo_shortcode_faqs($atts['faq_id'], $topics, $tags)) {
			return;
		}

		// Call our CSS file.
		wp_enqueue_style('faq-front');
		wp_enqueue_script('faq-front');

		// Some display variables.
		$scroll = apply_filters('wpfaq_scroll_combo_list', true, 'combo');
		$filter = apply_filters('wpfaq_display_content_filter', true, 'combo');
		$bktop  = apply_filters('wpfaq_display_content_backtotop', true, 'combo');

		// Make sure we have a valid H type to use.
		$htype  = WPFAQ_Manager_Helper::check_htype_tag('h3', 'combo');

		// Set a class based on the scrolling.
		$sclass = ! empty($scroll) ? 'faq-block-combo-wrap faq-block-combo-wrap-scroll' : 'faq-block-combo-wrap';

		// Start my markup.
		$build  = '';

		// The wrapper around the entire thing.
		$build .= '<div id="faq-block" class="faq-block-wrap ' . esc_attr($sclass) . '" name="faq-block" rel="faq-top">';

		// Wrap the list portion of the combo.
		$build .= '<div class="faq-list">';
		$build .= '<ul>';

		// Loop my individual FAQs
		foreach ($faqs as $faq) {

			// Wrap a li around each item.
			$build .= '<li class="faqlist-question">';

			// The actual link.
			$build .= '<a href="#' . esc_attr($faq->post_name) . '" rel="' . esc_attr($faq->post_name) . '">' . esc_html($faq->post_title) .  '</a>';

			// Close the li around each item.
			$build .= '</li>';
		}

		// Close the wrapper around the list portion.
		$build .= '</ul>';
		$build .= '</div>';

		// Wrap the content portion of the combo.
		$build .= '<div class="faq-content">';

		// Loop my individual FAQs
		foreach ($faqs as $faq) {

			// Wrap a div around each item.
			$build .= '<div class="single-faq" rel="' . esc_attr($faq->post_name) . '">';

			// Our title setup.
			$build .= '<' . esc_attr($htype) . ' id="' . esc_attr($faq->post_name) . '" name="' . esc_attr($faq->post_name) . '" class="faq-question">' . esc_html($faq->post_title) .  '</' . esc_attr($htype) . '>';

			// Handle the content itself.
			$build .= '<div class="faq-answer">';

			// Show the content, with the optional filter.
			$build .= false !== $filter ? apply_filters('the_content', $faq->post_content) : wpautop($faq->post_content);

			// Show the "back to top" if requested.
			if (! empty($bktop)) {
				$build .= '<p class="scroll-back"><a href="#faq-block">' . __('Back To Top', 'easy-faq-manager') . '</a></p>';
			}

			// Close the div around each bit of content.
			$build .= '</div>';

			// Close the div around each item.
			$build .= '</div>';
		}

		// Close the wrap the content portion of the combo.
		$build .= '</div>';

		// Close the entire wrapper.
		$build .= '</div>';

		// Return my markup.
		return $build;
	}

	// End our class.
}

// Call our class.
$WPFAQ_Manager_Shortcodes = new WPFAQ_Manager_Shortcodes();
$WPFAQ_Manager_Shortcodes->init();
