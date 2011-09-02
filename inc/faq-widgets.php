<?php
class random_FAQ_Widget extends WP_Widget {
	function random_FAQ_Widget() {
		$widget_ops = array( 'classname' => 'faq_random_widget', 'description' => 'Lists a random FAQ on the sidebar' );
		$this->WP_Widget( 'faq_random', 'FAQ Widget - Random', $widget_ops );
	}

	function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );
		echo $before_widget;
		$title = empty($instance['title']) ? 'Frequently Asked Question' : apply_filters('widget_title', $instance['title']);
		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };
		global $post;
			$args = array(
				'post_type' => 'question',
				'numberposts' => 1,
				'orderby' => 'rand',
				);
		$faqs = get_posts( $args );
		foreach( $faqs as $post ) :	setup_postdata($post);
		echo '<h4 class="faq_widget_title">'.get_the_title().'</h4>';
		echo faq_excerpt_content(10);
		echo '<p><a href="'.get_permalink().'">See the entire answer</a></p>';
        endforeach;
		wp_reset_query();
		echo $after_widget;
		?>
        
        <?php }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
	$instance = $old_instance;
	$instance['title'] = strip_tags($new_instance['title']);
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {				
        $instance = wp_parse_args( (array) $instance, array( 'title' => '') );
		$title = strip_tags($instance['title']);
        ?>
        <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget Title:' ); ?><input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo isset( $instance['title'] ) ? $instance['title'] : ''; ?>" /></label></p>
        <?php }

} // class 

// register widget

add_action( 'widgets_init', create_function( '', "register_widget('random_FAQ_Widget');" ) );


// Recent Questions

class recent_FAQ_Widget extends WP_Widget {
	function recent_FAQ_Widget() {
		$widget_ops = array( 'classname' => 'recent_questions_widget', 'description' => 'List recent questions' );
		$this->WP_Widget( 'recent_questions', 'FAQ Widget - Recent', $widget_ops );
	}

	function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );
		echo $before_widget;
		$title = empty($instance['title']) ? 'Recent Questions' : apply_filters('widget_title', $instance['title']);
		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };
		echo '<ul>';
		global $post;
			$args = array(
				'posts_per_page'	=>	$instance['post_num'],
				'post_type' 		=> 'question',
				'post_status'		=> 'publish',
				);
		$faqs = get_posts( $args );
		foreach( $faqs as $post ) :	setup_postdata($post);
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
	$instance['title'] = strip_tags($new_instance['title']);
	$instance['post_num'] = strip_tags($new_instance['post_num']);
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {				
        $instance = wp_parse_args( (array) $instance, array( 
			'title'			=> '',
			'post_num'		=> '5',
			));
		$title = strip_tags($instance['title']);
		$post_num = strip_tags($instance['post_num']);
        ?>
        <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget Title:' ); ?><input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo isset( $instance['title'] ) ? $instance['title'] : ''; ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('post_num'); ?>">Number of Questions: <input class="widefat" id="<?php echo $this->get_field_id('post_num'); ?>" name="<?php echo $this->get_field_name('post_num'); ?>" type="text" value="<?php echo esc_attr($post_num); ?>" /></label></p>
		<?php }

} // class 

// register widget

add_action( 'widgets_init', create_function( '', "register_widget('recent_FAQ_Widget');" ) );


// FAQ Topics List

class topics_FAQ_Widget extends WP_Widget {
	function topics_FAQ_Widget() {
		$widget_ops = array( 'classname' => 'recent_faqtopics_widget', 'description' => 'List FAQ topics (similar to categories)' );
		$this->WP_Widget( 'recent_faqtopics', 'FAQ Widget - Topics', $widget_ops );
	}

	function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );
		echo $before_widget;
		$title = empty($instance['title']) ? 'Recent Topics' : apply_filters('widget_title', $instance['title']);
		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };
		echo '<ul>';
		$orderby = 'name';
		$show_count = 0; // 1 for yes, 0 for no
		$pad_counts = 0; // 1 for yes, 0 for no
		$hierarchical = 1; // 1 for yes, 0 for no
		$taxonomy = 'faq-topic';
		$title = '';
		
		$topic_args = array(
		  'orderby' => $orderby,
		  'show_count' => $show_count,
		  'pad_counts' => $pad_counts,
		  'hierarchical' => $hierarchical,
		  'taxonomy' => $taxonomy,
		  'title_li' => $title
		);
		wp_list_categories($topic_args);
		echo '</ul>';
		wp_reset_query();
		echo $after_widget;
		?>
        
        <?php }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
	$instance = $old_instance;
	$instance['title'] = strip_tags($new_instance['title']);
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {				
        $instance = wp_parse_args( (array) $instance, array( 
			'title'			=> '',
			));
		$title = strip_tags($instance['title']);
        ?>
        <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget Title:' ); ?><input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo isset( $instance['title'] ) ? $instance['title'] : ''; ?>" /></label></p>
		<?php }

} // class 

// register widget

add_action( 'widgets_init', create_function( '', "register_widget('topics_FAQ_Widget');" ) );

// FAQ categories widget

class tags_FAQ_Widget extends WP_Widget {
	function tags_FAQ_Widget() {
		$widget_ops = array( 'classname' => 'recent_faqtags_widget', 'description' => 'List FAQ tags (similar to categories)' );
		$this->WP_Widget( 'recent_faqtags', 'FAQ Widget - Tags', $widget_ops );
	}

	function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );
		echo $before_widget;
		$title = empty($instance['title']) ? 'Recent Topics' : apply_filters('widget_title', $instance['title']);
		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };
		echo '<ul>';
		$orderby = 'name';
		$show_count = 0; // 1 for yes, 0 for no
		$pad_counts = 0; // 1 for yes, 0 for no
		$hierarchical = 0; // 1 for yes, 0 for no
		$taxonomy = 'faq-tags';
		$title = '';
		
		$tag_args = array(
		  'orderby' => $orderby,
		  'show_count' => $show_count,
		  'pad_counts' => $pad_counts,
		  'hierarchical' => $hierarchical,
		  'taxonomy' => $taxonomy,
		  'title_li' => $title
		);
		wp_list_categories($tag_args);
		echo '</ul>';
		wp_reset_query();
		echo $after_widget;
		?>
        
        <?php }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
	$instance = $old_instance;
	$instance['title'] = strip_tags($new_instance['title']);
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {				
        $instance = wp_parse_args( (array) $instance, array( 
			'title'			=> '',
			));
		$title = strip_tags($instance['title']);
        ?>
        <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget Title:' ); ?><input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo isset( $instance['title'] ) ? $instance['title'] : ''; ?>" /></label></p>
		<?php }

} // class 

// register widget

add_action( 'widgets_init', create_function( '', "register_widget('tags_FAQ_Widget');" ) );



// FAQ Tag Cloud

class cloud_FAQ_Widget extends WP_Widget {
	function cloud_FAQ_Widget() {
		$widget_ops = array( 'classname' => 'faq_cloud_widget', 'description' => 'A tag cloud of FAQ topics and tags' );
		$this->WP_Widget( 'faq_cloud', 'FAQ Widget - Cloud', $widget_ops );
	}

	function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );
		$ok_topic = isset($instance['topic_include']) ? $instance['topic_include'] : true;
		$ok_tag = isset($instance['tag_include']) ? $instance['tag_include'] : true;
		echo $before_widget;
		$title = empty($instance['title']) ? 'Recent Topics' : apply_filters('widget_title', $instance['title']);
		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };
		echo '<div class="faqcloud">';
			if ($ok_topic) :
				$cloud_args = array('taxonomy' => 'faq-topic' );
			endif;
			if($ok_tag) :
				$cloud_args = array('taxonomy' => 'faq-tags' );
			endif; 
			if($ok_topic && $ok_tag) :
				$cloud_args = array('taxonomy' => array ('faq-tags', 'faq-topic' ));
			endif; 
        wp_tag_cloud( $cloud_args ); 
		echo '</div>';
		echo $after_widget;
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
	$instance = $old_instance;
	$instance['title'] = strip_tags($new_instance['title']);
	$instance['topic_include'] = !empty($new_instance['topic_include']) ? 1 : 0;
	$instance['tag_include'] = !empty($new_instance['tag_include']) ? 1 : 0;
        return $instance;
    }	
	
    /** @see WP_Widget::form */
    function form($instance) {				
        $instance = wp_parse_args( (array) $instance, array( 
			'title'			=> '',
			'topic_include'	=> 0,
			'tag_include'	=> 1,
			));
		foreach ( $instance as $field => $val ) {
			if ( isset($new_instance[$field]) )
				$instance[$field] = 1;
		}
		$title = strip_tags($instance['title']);
        ?>
        <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget Title:' ); ?><input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo isset( $instance['title'] ) ? $instance['title'] : ''; ?>" /></label></p>
       	<p><input class="checkbox" type="checkbox" <?php checked($instance['topic_include'], true) ?> id="<?php echo $this->get_field_id('topic_include'); ?>" name="<?php echo $this->get_field_name('topic_include'); ?>" />
        <label for="<?php echo $this->get_field_id('topic_include'); ?>"><?php _e('Include FAQ Topics'); ?></label></p>
       	<p><input class="checkbox" type="checkbox" <?php checked($instance['tag_include'], true) ?> id="<?php echo $this->get_field_id('tag_include'); ?>" name="<?php echo $this->get_field_name('tag_include'); ?>" />
		<label for="<?php echo $this->get_field_id('tag_include'); ?>"><?php _e('Include FAQ Tags'); ?></label></p>
		<?php }

} // class 

add_action( 'widgets_init', create_function( '', "register_widget('cloud_FAQ_Widget');" ) );

?>