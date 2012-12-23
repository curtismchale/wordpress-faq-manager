<?php
/*  
Keeping a separate file for the widgets for orgaization purposes
*/


	/**
	 * FAQ Search
	 *
	 * @return WP_FAQ_Manager
	 */

class search_FAQ_Widget extends WP_Widget {
	function search_FAQ_Widget() {
		$widget_ops = array( 'classname' => 'faq-search-widget widget_search', 'description' => 'Puts a search box for just FAQs' );
		$this->WP_Widget( 'faq_search', 'FAQ Widget - Search', $widget_ops );
	}

	function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );
		echo $before_widget;
		$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };		

		echo '<form class="searchform" role="search" method="get" id="faq-search" action="' . home_url( '/' ) . '" >';
		echo '<input type="text" value="' . get_search_query() . '" name="s" id="s" class="s" />';
		echo '<input type="submit" class="searchsubmit" value="'. esc_attr__('Search') .'" />';
		echo '<input type="hidden" name="post_type" value="question" />';
		echo '</form>';
		
		echo $after_widget;
		?>
      
        <?php }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {             
    $instance = $old_instance;
    $instance['title']  = strip_tags($new_instance['title']);
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {              
        $instance = wp_parse_args( (array) $instance, array( 
            'title' => 'Search FAQs',
            ));
        $title  = strip_tags($instance['title']);
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Widget Title:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
        </p>

	<?php }


} // class 

	/**
	 * Random FAQ widget
	 *
	 * @return WP_FAQ_Manager
	 */


class random_FAQ_Widget extends WP_Widget {
	function random_FAQ_Widget() {
		$widget_ops = array( 'classname' => 'faq-random-widget', 'description' => 'Lists a single random FAQ on the sidebar' );
		$this->WP_Widget( 'faq_random', 'FAQ Widget - Random', $widget_ops );
	}

	function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );
		echo $before_widget;
		$title		= empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
		$count		= empty($instance['count']) ? 1 : $instance['count'];
		$seemore	= empty($instance['seemore']) ? 'See the entire answer' : $instance['seemore'];

		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };
			$args = array(
				'post_type'		=> 'question',
				'numberposts'	=> $count,
				'orderby'		=> 'rand',
				);
			$faqs = get_posts( $args );
			
			foreach( $faqs as $faq ) :
				$text = wpautop( $faq->post_content );
 			
				echo '<h5 class="faq-widget-title">'.$faq->post_title.'</h5>';
				echo wp_trim_words( $text, 15, null );
				echo '<p><a href="'.get_permalink($faq->ID).'">'.$seemore.'</a></p>';
        
        	endforeach;
		wp_reset_query();
		echo $after_widget;
		?>
      
        <?php }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {             
    $instance = $old_instance;
    $instance['title']		= strip_tags($new_instance['title']);
    $instance['seemore']	= strip_tags($new_instance['seemore']);
    $instance['count']		= strip_tags($new_instance['count']);
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {              
        $instance = wp_parse_args( (array) $instance, array( 
            'title'		=> 'Frequently Asked Question',
            'seemore'	=> 'See the entire answer',
            'count'		=> '1',
            ));
        $title		= strip_tags($instance['title']);
        $seemore	= strip_tags($instance['seemore']);
        $count		= strip_tags($instance['count']);
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Widget Title:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('seemore'); ?>">"See More" text:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('seemore'); ?>" name="<?php echo $this->get_field_name('seemore'); ?>" type="text" value="<?php echo esc_attr($seemore); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('count'); ?>">Post Count:</label>
            <input class="small-text" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" type="text" value="<?php echo esc_attr($count); ?>" />
        </p>
	<?php }


} // class 

	/**
	 * Recent FAQ 
	 *
	 * @return WP_FAQ_Manager
	 */


class recent_FAQ_Widget extends WP_Widget {
	function recent_FAQ_Widget() {
		$widget_ops = array( 'classname' => 'recent-questions-widget', 'description' => 'List recent questions' );
		$this->WP_Widget( 'recent_questions', 'FAQ Widget - Recent', $widget_ops );
	}

	function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );
		echo $before_widget;
		$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };
			$args = array(
				'posts_per_page'	=>	$instance['count'],
				'post_type' 		=> 'question',
				'post_status'		=> 'publish',
			);

			$faqs = get_posts( $args );
		echo '<ul>';
			foreach( $faqs as $post ) :	setup_postdata($post);
				global $post;
				echo '<li><a href="'.get_permalink($post->ID).'" title="'.get_the_title($post->ID).'">'.get_the_title($post->ID).'</a></li>';

        	endforeach;
		echo '</ul>';
		wp_reset_query();
		echo $after_widget;
		?>

        <?php }

    /** @see WP_Widget::update */

    function update($new_instance, $old_instance) {				
	$instance = $old_instance;
	$instance['title']	= strip_tags($new_instance['title']);
	$instance['count']	= strip_tags($new_instance['count']);
        return $instance;
    }



    /** @see WP_Widget::form */
    function form($instance) {				
        $instance = wp_parse_args( (array) $instance, array( 
			'title'		=> 'Recent Questions',
			'count'		=> '5',
		));
		$title	= strip_tags($instance['title']);
		$count	= strip_tags($instance['count']);

        ?>

        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Widget Title:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('count'); ?>">Post Count:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" type="text" value="<?php echo esc_attr($count); ?>" />
        </p>
		<?php }



} // class 

	/**
	 * FAQ taxonomy list
	 *
	 * @return WP_FAQ_Manager
	 */


class topics_FAQ_Widget extends WP_Widget {
	function topics_FAQ_Widget() {
		$widget_ops = array( 'classname' => 'recent-faqtax-widget', 'description' => 'List FAQ topics or tags' );
		$this->WP_Widget( 'recent_faqtax', 'FAQ Widget - Taxonomies', $widget_ops );
	}

	function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );
		echo $before_widget;
		$title	= empty($instance['title'])	? ''			: apply_filters('widget_title', $instance['title']);
		$tax	= empty($instance['tax'])	? 'faq-topic'	: $instance['tax'];

		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };
		echo '<ul>';
			// set query variables
			$orderby		= 'name';
			$show_count		= 0; // 1 for yes, 0 for no
			$pad_counts		= 0; // 1 for yes, 0 for no
			$hierarchical	= 1; // 1 for yes, 0 for no
			$taxonomy		= $tax;
			$title			= '';
			$style			= 'list';
	
			$topic_args = array(
				'orderby'		=> $orderby,
				'show_count'	=> $show_count,
				'pad_counts'	=> $pad_counts,
				'hierarchical'	=> $hierarchical,
				'taxonomy'		=> $taxonomy,
				'title_li'		=> $title,
				'style'			=> $style
			);

			wp_list_categories($topic_args);
		echo '</ul>';
		echo $after_widget;
		?>

        

        <?php }


    /** @see WP_Widget::form */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
			if ( in_array( $new_instance['tax'], array( 'faq-topic', 'faq-tags' ) ) ) {
				$instance['tax'] = $new_instance['tax'];
			} else {
				$instance['tax'] = 'faq-topic';
			}
		return $instance;
	}

    /** @see WP_Widget::form */
	function form( $instance ) {

		//Defaults
		$instance = wp_parse_args( (array) $instance, array(
			'tax'	=> 'faq-topic',
			'title' => '',
		) );
		$title = esc_attr( $instance['title'] );
	?>

        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Widget Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
        </p>

		<p>
			<label for="<?php echo $this->get_field_id('tax'); ?>"><?php _e( 'Taxonomy:' ); ?></label>
			<select name="<?php echo $this->get_field_name('tax'); ?>" id="<?php echo $this->get_field_id('tax'); ?>" class="widefat">
				<option value="faq-topic"<?php selected( $instance['tax'], 'faq-topic' ); ?>><?php _e('FAQ Topics'); ?></option>
				<option value="faq-tags"<?php selected( $instance['tax'], 'faq-tags' ); ?>><?php _e('FAQ Tags'); ?></option>
			</select>
		</p>

		<?php }



} // class 



	/**
	 * FAQ Tag Cloud
	 *
	 * @return WP_FAQ_Manager
	 */

class cloud_FAQ_Widget extends WP_Widget {
	function cloud_FAQ_Widget() {
		$widget_ops = array( 'classname' => 'faq-cloud-widget', 'description' => 'A tag cloud of FAQ topics and tags' );
		$this->WP_Widget( 'faq_cloud', 'FAQ Widget - Cloud', $widget_ops );
	}

	function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );
		
		echo $before_widget;
		
		$ok_topic	= isset($instance['to_include']) ? $instance['to_include'] : false;
		$ok_tag		= isset($instance['ta_include']) ? $instance['ta_include'] : false;
		$title		= empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);

		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };

		echo '<div class="faqcloud">';
			if ($ok_topic)
				$cloud_args = array('taxonomy' => 'faq-topic' );

			if($ok_tag)
				$cloud_args = array('taxonomy' => 'faq-tags' );

			if($ok_topic && $ok_tag)
				$cloud_args = array('taxonomy' => array ('faq-tags', 'faq-topic' ));

        echo wp_tag_cloud( $cloud_args ); 
		
		echo '</div>';
		echo $after_widget;
    }



    /** @see WP_Widget::update */

    function update($new_instance, $old_instance) {				
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['to_include'] = !empty($new_instance['to_include']) ? 1 : 0;
		$instance['ta_include'] = !empty($new_instance['ta_include']) ? 1 : 0;
	        return $instance;
    }	


    /** @see WP_Widget::form */
    function form($instance) {				
        $instance = wp_parse_args( (array) $instance, array( 
			'title'			=> 'Recent Topics',
			'to_include'	=> 0,
			'ta_include'	=> 1,
		));

		$title = strip_tags($instance['title']);
		
		foreach ( $instance as $field => $val ) :
			if ( isset($new_instance[$field]) )
				$instance[$field] = 1;
		
		endforeach;
        ?>

        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Widget Title:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
        </p>

       	<p>
       		<input class="checkbox" type="checkbox" <?php checked($instance['to_include'], true) ?> id="<?php echo $this->get_field_id('to_include'); ?>" name="<?php echo $this->get_field_name('to_include'); ?>" />
	        <label for="<?php echo $this->get_field_id('to_include'); ?>"><?php _e('Include FAQ Topics'); ?></label>
	    </p>

       	<p>
       		<input class="checkbox" type="checkbox" <?php checked($instance['ta_include'], true) ?> id="<?php echo $this->get_field_id('ta_include'); ?>" name="<?php echo $this->get_field_name('ta_include'); ?>" />
			<label for="<?php echo $this->get_field_id('ta_include'); ?>"><?php _e('Include FAQ Tags'); ?></label>
		</p>

		<?php }



} // class 

	/**
	 * Register all widgets
	 *
	 * @return WP_FAQ_Manager
	 */


add_action( 'widgets_init', create_function( '', "register_widget('search_FAQ_Widget');" ) );
add_action( 'widgets_init', create_function( '', "register_widget('random_FAQ_Widget');" ) );
add_action( 'widgets_init', create_function( '', "register_widget('recent_FAQ_Widget');" ) );
add_action( 'widgets_init', create_function( '', "register_widget('topics_FAQ_Widget');" ) );
add_action( 'widgets_init', create_function( '', "register_widget('cloud_FAQ_Widget');" ) );
