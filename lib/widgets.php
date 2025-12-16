<?php

/**
 * WP FAQ Manager - Widgets Module
 *
 * Contains our various widgets for front-end use.
 *
 * @package WP FAQ Manager
 */

if (! defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Start our engines.
 */
class WPFAQ_Manager_Widgets
{

	/**
	 * Call our hooks.
	 *
	 * @return void
	 */
	public function init()
	{

		// Optional filter to disable all the widgets.
		if (false === $enable = apply_filters('wpfaq_enable_widgets', true)) {
			return;
		}

		// Call the hook.
		add_action('widgets_init',                     array($this, 'register_widgets'));
	}

	/**
	 * Register all our custom widgets.
	 *
	 * @return void
	 */
	public function register_widgets()
	{

		// Register the search widget (with optional filter to disable).
		if (false === $search = apply_filters('wpfaq_disable_search_widget', false)) {
			register_widget('Search_FAQ_Widget');
		}

		// Register the random FAQ widget (with optional filter to disable).
		if (false === $random = apply_filters('wpfaq_disable_random_widget', false)) {
			register_widget('Random_FAQ_Widget');
		}

		// Register the recent FAQ widget (with optional filter to disable).
		if (false === $recent = apply_filters('wpfaq_disable_recent_widget', false)) {
			register_widget('Recent_FAQ_Widget');
		}

		// Register the FAQ taxonomy list (with optional filter to disable).
		if (false === $taxlst = apply_filters('wpfaq_disable_taxlist_widget', false)) {
			register_widget('Topics_FAQ_Widget');
		}

		// Register the FAQ cloud (with optional filter to disable).
		if (false === $cloud = apply_filters('wpfaq_disable_cloud_widget', false)) {
			register_widget('Cloud_FAQ_Widget');
		}
	}

	// End our class.
}

// Call our class.
$WPFAQ_Manager_Widgets = new WPFAQ_Manager_Widgets();
$WPFAQ_Manager_Widgets->init();


/**
 * Build out the FAQ search widget
 */
class Search_FAQ_Widget extends WP_Widget
{

	/**
	 * The widget construct.
	 */
	public function __construct()
	{

		// Set my widget ops.
		$widget_ops = array(
			'classname'     => 'faq-search-widget widget_search',
			'description'   => __('Puts a search box for just FAQs', 'wp-faq-manager'),
		);

		// Set my parent construct.
		parent::__construct('faq_search', __('FAQ Widget - Search', 'wp-faq-manager'), $widget_ops);
	}

	/**
	 * The display portion of the widget.
	 *
	 * @param  array $args      The variables set up in the sidebar area definition.
	 * @param  array $instance  The saved args in the widget settings.
	 *
	 * @return void
	 */
	public function widget($args, $instance)
	{

		// Check for a title, then wrap the filter around it.
		$title  = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);

		// Output the opening widget markup.
		echo $args['before_widget'];

		// Output the title (if we have one).
		if (! empty($title)) {
			echo wp_kses_post($args['before_title'])
				. esc_html($title)
				. wp_kses_post($args['after_title']);
		}

		// Output the actual search form with the various values.
		echo '<form role="search" method="get" class="search-form" id="faq-search" action="' . esc_url(home_url('/')) . '">';

		echo '<label>';
		echo '<span class="screen-reader-text">' . __('Search FAQs for:', 'wp-faq-manager') . '</span>';
		echo '<input type="search" class="search-field" placeholder="' . __('Search FAQs &hellip;', 'wp-faq-manager') . '" value="' . get_search_query() . '" name="s" />';
		echo '</label>';
		echo '<input type="submit" class="search-submit" value="' . esc_attr_x('Search', 'submit button') . '" />';
		echo '<input type="hidden" name="post_type" value="question" />';

		echo '</form>';

		// Output the closing widget markup.
		echo $args['after_widget'];
	}

	/**
	 * Validate and store the values being passed in the widget settings.
	 *
	 * @param  array $new_instance  The new settings being passed.
	 * @param  array $old_instance  The existing settings.
	 *
	 * @return array instance       The data being stored.
	 */
	public function update($new_instance, $old_instance)
	{

		// Set our instance variable as the existing data.
		$instance = $old_instance;

		// Set our title to be sanitized.
		$instance['title']  = sanitize_text_field($new_instance['title']);

		// Return the instance.
		return $instance;
	}

	/**
	 * The widget settings form.
	 *
	 * @param  array $instance  The stored settings instance.
	 *
	 * @return void
	 */
	public function form($instance)
	{

		// Set the default values (if any).
		$instance   = wp_parse_args((array) $instance, array(
			'title' => __('Search FAQs', 'wp-faq-manager'),
		));

		// Now set the value for each item in the array.
		$title  = $instance['title'];
?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title:', 'wp-faq-manager'); ?></label>-
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
		</p>

	<?php
	}
} // class

/**
 * Build out the widget to display a random FAQ.
 */
class Random_FAQ_Widget extends WP_Widget
{

	/**
	 * The widget construct.
	 */
	public function __construct()
	{

		// Set my widget ops.
		$widget_ops = array(
			'classname'     => 'faq-random-widget',
			'description'   => __('Lists a single random FAQ on the sidebar', 'wp-faq-manager'),
		);

		// Set my parent construct.
		parent::__construct('faq_random', __('FAQ Widget - Random', 'wp-faq-manager'), $widget_ops);
	}

	/**
	 * The display portion of the widget.
	 *
	 * @param  array $args      The variables set up in the sidebar area definition.
	 * @param  array $instance  The saved args in the widget settings.
	 *
	 * @return void
	 */
	public function widget($args, $instance)
	{

		// Make sure we have a count.
		$count  = empty($instance['count']) ? 1 : absint($instance['count']);

		// If no items are found, bail before any display is set up.
		if (false === $faqs = WPFAQ_Manager_Data::get_random_widget_faqs($count)) {
			return;
		}

		// Check for a title, then wrap the filter around it.
		$title  = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
		$more   = empty($instance['more']) ? __('See the entire answer', 'wp-faq-manager') : $instance['more'];

		// Output the opening widget markup.
		echo $args['before_widget'];

		// Output the title (if we have one).
		if (! empty($title)) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		// Loop the FAQs we have.
		foreach ($faqs as $faq) {

			// Set our text variable.
			$text   = ! empty($instance['chars']) ? wp_trim_words($faq->post_content, absint($instance['chars']), null) : $faq->post_content;

			// Grab our link and title.
			$link   = get_permalink($faq->ID);
			$stitle = $faq->post_title;

			// Set a div around the FAQ.
			echo '<div class="faq-random-single">';

			// Output the title of the individual FAQ.
			if (! empty($faq->post_title)) {
				echo '<h5 class="faq-widget-title">' . esc_html($stitle) . '</h5>';
			}

			// Output the text.
			echo wpautop($text);

			// Output the "read more" portion.
			echo '<p class="faq-single-random-read-more">';
			echo '<a href="' . esc_url($link) . '">' . esc_html($more) . '</a>';
			echo '</p>';

			// Close the div.
			echo '</div>';
		}

		// Output the closing widget markup.
		echo $args['after_widget'];
	}

	/**
	 * Validate and store the values being passed in the widget settings.
	 *
	 * @param  array $new_instance  The new settings being passed.
	 * @param  array $old_instance  The existing settings.
	 *
	 * @return array instance       The data being stored.
	 */
	public function update($new_instance, $old_instance)
	{

		// Set our instance variable as the existing data.
		$instance = $old_instance;

		// Set our values to be sanitized.
		$instance['title']  = sanitize_text_field($new_instance['title']);
		$instance['more']   = sanitize_text_field($new_instance['more']);
		$instance['chars']  = absint($new_instance['chars']);
		$instance['count']  = absint($new_instance['count']);

		// Delete our transient.
		delete_transient('wpfaq_widget_fetch_random');

		// Return the instance.
		return $instance;
	}

	/**
	 * The widget settings form.
	 *
	 * @param  array $instance  The stored settings instance.
	 *
	 * @return void
	 */
	public function form($instance)
	{

		// Set the default values (if any).
		$instance   = wp_parse_args((array) $instance, array(
			'title' => '',
			'more'  => __('See the entire answer', 'wp-faq-manager'),
			'chars' => 0,
			'count' => 1,
		));

		// Now set the value for each item in the array.
		$title  = $instance['title'];
		$more   = $instance['more'];
		$chars  = $instance['chars'];
		$count  = $instance['count'];
	?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title:', 'wp-faq-manager'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('more'); ?>"><?php _e('"See More" text:', 'wp-faq-manager'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('more'); ?>" name="<?php echo $this->get_field_name('more'); ?>" type="text" value="<?php echo esc_attr($more); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('chars'); ?>"><?php _e('Character Count:', 'wp-faq-manager'); ?></label>
			<input class="small-text" id="<?php echo $this->get_field_id('chars'); ?>" name="<?php echo $this->get_field_name('chars'); ?>" type="text" value="<?php echo esc_attr($chars); ?>" /><br>
			<span class="description"><?php echo esc_html('Enter the amount of characters to display. Use zero to show all.'); ?></span>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Post Count:', 'wp-faq-manager'); ?></label>
			<input class="small-text" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" type="text" value="<?php echo esc_attr($count); ?>" />
		</p>

	<?php
	}
} // class

/**
 * Build out the widget to display recent FAQs.
 */
class Recent_FAQ_Widget extends WP_Widget
{

	/**
	 * The widget construct.
	 */
	public function __construct()
	{

		// Set my widget ops.
		$widget_ops = array(
			'classname'     => 'faq-recent-widget recent-questions-widget',
			'description'   => __('List recent questions', 'wp-faq-manager'),
		);

		// Set my parent construct.
		parent::__construct('recent_questions', __('FAQ Widget - Recent', 'wp-faq-manager'), $widget_ops);
	}

	/**
	 * The display portion of the widget.
	 *
	 * @param  array $args      The variables set up in the sidebar area definition.
	 * @param  array $instance  The saved args in the widget settings.
	 *
	 * @return void
	 */
	public function widget($args, $instance)
	{

		// Make sure we have a count.
		$count  = empty($instance['count']) ? 1 : absint($instance['count']);

		// If no items are found, bail before any display is set up.
		if (false === $faqs = WPFAQ_Manager_Data::get_recent_widget_faqs($count)) {
			return;
		}

		// Check for a title, then wrap the filter around it.
		$title  = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);

		// Output the opening widget markup.
		echo $args['before_widget'];

		// Output the title (if we have one).
		if (! empty($title)) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		// Set a div around the list
		echo '<div class="faq-recent-list">';
		echo '<ul>';

		// Loop the FAQs we have.
		foreach ($faqs as $faq) {

			// Grab our link and title.
			$link   = get_permalink($faq->ID);
			$stitle = $faq->post_title;

			// Output the actual list item.
			echo '<li>';
			echo '<a href="' . esc_url($link) . '" title=" ' . esc_attr($stitle) . '">' . esc_html($stitle) . '</a>';
			echo '</li>';
		}

		// Close the div.
		echo '</ul>';
		echo '</div>';

		// Output the closing widget markup.
		echo $args['after_widget'];
	}

	/**
	 * Validate and store the values being passed in the widget settings.
	 *
	 * @param  array $new_instance  The new settings being passed.
	 * @param  array $old_instance  The existing settings.
	 *
	 * @return array instance       The data being stored.
	 */
	public function update($new_instance, $old_instance)
	{

		// Set our instance variable as the existing data.
		$instance = $old_instance;

		// Set our values to be sanitized.
		$instance['title']  = sanitize_text_field($new_instance['title']);
		$instance['count']  = absint($new_instance['count']);

		// Delete our transient.
		delete_transient('wpfaq_widget_fetch_recent');

		// Return the instance.
		return $instance;
	}

	/**
	 * The widget settings form.
	 *
	 * @param  array $instance  The stored settings instance.
	 *
	 * @return void
	 */
	public function form($instance)
	{

		// Set the default values (if any).
		$instance   = wp_parse_args((array) $instance, array(
			'title' => '',
			'count' => 5,
		));

		// Now set the value for each item in the array.
		$title  = $instance['title'];
		$count  = $instance['count'];
	?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title:', 'wp-faq-manager'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Post Count:', 'wp-faq-manager'); ?></label>
			<input class="small-text" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" type="text" value="<?php echo esc_attr($count); ?>" />
		</p>

	<?php
	}
} // class

/**
 * Build out the widget to display a taxonomy list.
 */
class Topics_FAQ_Widget extends WP_Widget
{

	/**
	 * The widget construct.
	 */
	public function __construct()
	{

		// Set my widget ops.
		$widget_ops = array(
			'classname'     => 'recent-faqtax-widget',
			'description'   => __('List FAQ topics or tags', 'wp-faq-manager'),
		);

		// Set my parent construct.
		parent::__construct('recent_faqtax', __('FAQ Widget - Taxonomies', 'wp-faq-manager'), $widget_ops);
	}

	/**
	 * The display portion of the widget.
	 *
	 * @param  array $args      The variables set up in the sidebar area definition.
	 * @param  array $instance  The saved args in the widget settings.
	 *
	 * @return void
	 */
	public function widget($args, $instance)
	{

		// Make sure we have a taxonomy.
		$tax    = empty($instance['tax']) ? 'faq-topic' : esc_attr($instance['tax']);

		// Check for a title, then wrap the filter around it.
		$title  = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);

		// Output the opening widget markup.
		echo $args['before_widget'];

		// Output the title (if we have one).
		if (! empty($title)) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		// Set a div around the list.
		echo '<div class="faq-taxonomy-list">';
		echo '<ul>';

		// List the taxonomies.
		$txargs = array(
			'orderby'       => 'name',
			'show_count'    => 0,
			'hide_empty'    => 0,
			'pad_counts'    => 0,
			'hierarchical'  => 1,
			'taxonomy'      => $tax,
			'title_li'      => '',
			'style'         => 'list'
		);

		// Filter the possible args and output the list.
		wp_list_categories(apply_filters('wpfaq_tax_list_widget_args', $txargs));

		// Close the div.
		echo '</ul>';
		echo '</div>';

		// Output the closing widget markup.
		echo $args['after_widget'];
	}

	/**
	 * Validate and store the values being passed in the widget settings.
	 *
	 * @param  array $new_instance  The new settings being passed.
	 * @param  array $old_instance  The existing settings.
	 *
	 * @return array instance       The data being stored.
	 */
	public function update($new_instance, $old_instance)
	{

		// Set our instance variable as the existing data.
		$instance = $old_instance;

		// Set our values to be sanitized.
		$instance['title']  = sanitize_text_field($new_instance['title']);
		$instance['tax']    = sanitize_text_field($new_instance['tax']);

		// Return the instance.
		return $instance;
	}

	/**
	 * The widget settings form.
	 *
	 * @param  array $instance  The stored settings instance.
	 *
	 * @return void
	 */
	public function form($instance)
	{

		// Set the default values (if any).
		$instance   = wp_parse_args((array) $instance, array(
			'title' => '',
			'tax'   => 'faq-topic',
		));

		// Now set the value for each item in the array.
		$title  = $instance['title'];
		$tax    = $instance['tax'];
	?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title:', 'wp-faq-manager'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Taxonomy:', 'wp-faq-managar'); ?></label>
			<select name="<?php echo $this->get_field_name('tax'); ?>" id="<?php echo $this->get_field_id('tax'); ?>" class="widefat">
				<option value="faq-topic" <?php selected($tax, 'faq-topic', true); ?>><?php _e('FAQ Topics', 'wp-faq-manager'); ?></option>
				<option value="faq-tags" <?php selected($tax, 'faq-tags', true); ?>><?php _e('FAQ Tags', 'wp-faq-manager'); ?></option>
			</select>
		</p>

	<?php
	}
} // class

/**
 * Build out the widget to display a taxonomy cloud.
 */
class Cloud_FAQ_Widget extends WP_Widget
{

	/**
	 * The widget construct.
	 */
	public function __construct()
	{

		// Set my widget ops.
		$widget_ops = array(
			'classname'     => 'faq-cloud-widget',
			'description'   => __('A tag cloud of FAQ topics and tags', 'wp-faq-manager'),
		);

		// Set my parent construct.
		parent::__construct('faq_cloud', __('FAQ Widget - Cloud', 'wp-faq-manager'), $widget_ops);
	}

	/**
	 * The display portion of the widget.
	 *
	 * @param  array $args      The variables set up in the sidebar area definition.
	 * @param  array $instance  The saved args in the widget settings.
	 *
	 * @return void
	 */
	public function widget($args, $instance)
	{

		// Fetch our two potential taxonomies.
		$topics = ! empty($instance['to_include']) ? 'faq-topic' : '';
		$tags   = ! empty($instance['ta_include']) ? 'faq-tags' : '';

		// Bail if we have neither checked.
		if (empty($topics) && empty($tags)) {
			return;
		}

		// Set my cloud args.
		$clargs = array(
			'smallest'  => 12,
			'largest'   => 32,
			'unit'      => 'px',
			'echo'      => 0
		);

		// Filter our args before merging in the taxonomy.
		$clargs = apply_filters('wpfaq_tax_cloud_widget_args', $clargs);

		// Now add in the taxonomy items.
		$clargs = wp_parse_args(array('taxonomy' => array($topics, $tags)), $clargs);

		// Check for a title, then wrap the filter around it.
		$title  = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);

		// Output the opening widget markup.
		echo $args['before_widget'];

		// Output the title (if we have one).
		if (! empty($title)) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		// Set a div around the list.
		echo '<div class="faq-taxonomy-cloud faqcloud">';

		// Echo out the tag cloud.
		echo wp_tag_cloud($clargs);

		// Close the div.
		echo '</div>';

		// Output the closing widget markup.
		echo $args['after_widget'];
	}

	/**
	 * Validate and store the values being passed in the widget settings.
	 *
	 * @param  array $new_instance  The new settings being passed.
	 * @param  array $old_instance  The existing settings.
	 *
	 * @return array instance       The data being stored.
	 */
	public function update($new_instance, $old_instance)
	{

		// Set our instance variable as the existing data.
		$instance = $old_instance;

		// Set our values to be sanitized.
		$instance['title']      = sanitize_text_field($new_instance['title']);
		$instance['to_include'] = sanitize_text_field($new_instance['to_include']);
		$instance['ta_include'] = sanitize_text_field($new_instance['ta_include']);

		// Return the instance.
		return $instance;
	}

	/**
	 * The widget settings form.
	 *
	 * @param  array $instance  The stored settings instance.
	 *
	 * @return void
	 */
	public function form($instance)
	{

		// Set the default values (if any).
		$instance   = wp_parse_args((array) $instance, array(
			'title'         => '',
			'to_include'    => '',
			'ta_include'    => 'on'
		));

		// Now set the value for each item in the array.
		$title  = $instance['title'];
		$to_inc = ! empty($instance['to_include']) ? 'on' : '';
		$ta_inc = ! empty($instance['ta_include']) ? 'on' : '';
	?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title:', 'wp-faq-manager'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
		</p>

		<p>
			<input class="checkbox" type="checkbox" <?php checked($to_inc, 'on', true) ?> id="<?php echo $this->get_field_id('to_include'); ?>" value="on" name="<?php echo $this->get_field_name('to_include'); ?>" />
			<label for="<?php echo $this->get_field_id('to_include'); ?>"><?php _e('Include FAQ Topics', 'wp-faq-manager'); ?></label>
		</p>

		<p>
			<input class="checkbox" type="checkbox" <?php checked($ta_inc, 'on', true) ?> id="<?php echo $this->get_field_id('ta_include'); ?>" value="on" name="<?php echo $this->get_field_name('ta_include'); ?>" />
			<label for="<?php echo $this->get_field_id('ta_include'); ?>"><?php _e('Include FAQ Tags', 'wp-faq-manager'); ?></label>
		</p>

<?php
	}
} // class
