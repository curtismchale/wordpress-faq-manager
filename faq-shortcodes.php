<?php
/**
 * FAQ Shortcodes
 */
class FAQ_Shortcodes {
	public function __construct() {
		add_shortcode ( 'faq', array( $this, 'shortcode_main'	) );
		add_shortcode ( 'faqlist', array( $this, 'shortcode_list'	) );
		add_shortcode	( 'faqcombo', array( $this, 'shortcode_combo'	) );
		add_shortcode	( 'faqtaxlist', array( $this, 'shortcode_taxls'	) );
	}

	/**
	 * Abstraction of the query that all shortcodes run
	 *
	 * @return WP_Query
	 */
	public function shortcode_query( $topic, $tag, $id, $limit = NULL ) {
		// set up $paged
		if( isset( $_GET['faq_page'] ) && $faq_page = absint( $_GET['faq_page'] ) ) {
			$paged = $faq_page;
		} else {
			$paged = 1;
			$old_link = trailingslashit(get_permalink());
		}

		// clean up text
		$faq_topic	= preg_replace('~&#x0*([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $topic);
		$faq_tag = preg_replace('~&#x0*([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $tag);

		// FAQ query
		$args = array (
			'p'				       => '' . $id . '',
			'faq-topic'			 => '' . $faq_topic . '',
			'faq-tags'			 => '' . $faq_tag . '',
			'post_type'			 =>	'question',
			'orderby'			   =>	'menu_order',
			'order'				   =>	'ASC',
			'paged'				   =>	$paged,
		);

		// handle optional limit
		if( null !== $limit ) {
			$args['posts_per_page'] =	'' . $limit . '';
		}

		return new WP_Query($args);
	}

	/**
	 * load primary shortcode
	 *
	 * @return WP_FAQ_Manager
	 */
	public function shortcode_main($atts, $content = NULL) {
		extract(shortcode_atts(array(
			'faq_topic'		=> '',
			'faq_tag'		=> '',
			'faq_id'		=> '',
			'limit'			=> '10',
		), $atts));

		$wp_query = $this->shortcode_query( $faq_topic, $faq_tag, $faq_id, $limit );

		if($wp_query->have_posts()) :
			// get options from settings page
			$faqopts	= get_option('faq_options');
			$exspeed	= (isset($faqopts['exspeed'])									? $faqopts['exspeed']	: '200'	);
			$exlink		= (isset($faqopts['exlink'])									? true					: false	);
			$nofilter	= (isset($faqopts['nofilter'])									? true					: false	);
			$extext		= (isset($faqopts['extext']) && $faqopts['extext'] !== ''		? $faqopts['extext']	: 'Read More'	);
			$expand_a	= (isset($faqopts['expand']) && $faqopts['expand'] == 'true'	? ' expand-faq'			: ''	);
			$expand_b	= (isset($faqopts['expand']) && $faqopts['expand'] == 'true'	? ' expand-title'		: ''	);
			$htype		= (isset($faqopts['htype'])										? $faqopts['htype']		: 'h3'	);

			$displayfaq = '<div id="faq-block"><div class="faq-list" data-speed="'.$exspeed.'">';

			while ($wp_query->have_posts()) : $wp_query->the_post();
				global $post;
				$content	= get_the_content();
				$title		= get_the_title();
				$slug		= basename(get_permalink());
				$link		= get_permalink();

				$displayfaq .= '<div class="single-faq'.$expand_a.'">';
				$displayfaq .= '<'.$htype.' id="'.$slug.'" class="faq-question'.$expand_b.'">'.$title.'</'.$htype.'>';
				$displayfaq .= '<div class="faq-answer" rel="'.$slug.'">';
				$displayfaq .= $nofilter == true ? $content : apply_filters('the_content', $content);
				if ($exlink == true)
					$displayfaq .= '<p class="faq-link"><a href="'.$link.'" title="'.$title.'">'.$extext.'</a></p>';

				$displayfaq .= '</div>';
				$displayfaq .= '</div>';
			endwhile;

			if (isset($faqopts['paginate'])) {
				// pagination links
				$displayfaq .= '<p class="faq-nav">';
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

		endif;

		// now send it all back
		return $displayfaq;
	}

	/**
	 * load list version shortcode
	 *
	 * @return WP_FAQ_Manager
	 */
	public function shortcode_list($atts, $content = NULL) {
		extract(shortcode_atts(array(
			'faq_topic'		=> '',
			'faq_tag'		=> '',
			'faq_id'		=> '',
			'limit'			=> '10',
		), $atts));

		$wp_query = $this->shortcode_query( $faq_topic, $faq_tag, $faq_id, $limit );

		if($wp_query->have_posts()) :

		$displayfaq = '<div id="faq-block"><div class="faq-list">';

			$displayfaq .= '<ul>';
			while ($wp_query->have_posts()) : $wp_query->the_post();

			global $post;
			$title		= get_the_title();
			$link		= get_permalink();
			$slug		= basename(get_permalink());

			// get options from settings page
			$faqopts	= get_option('faq_options');
			$htype		= (isset($faqopts['htype']) ? $faqopts['htype']  : 'h3' );

				$displayfaq .= '<li class="faqlist-question"><a href="'.$link.'" title="Permanent link to '.$title.'" >'.$title.'</a></li>';


			endwhile;
			$displayfaq .= '</ul>';

				if (isset($faqopts['paginate'])) {
					// pagination links
					$displayfaq .= '<p class="faq-nav">';
					$displayfaq .= paginate_links(array(
						'base'		=> $old_link . '%_%',
						'format'	=> '?faq_page=%#%',
						'type'		=> 'plain',
						'total'		=> $wp_query->max_num_pages,
						'current'	=> $paged,
						'prev_text'	=> __('&laquo;'),
						'next_text'	=> __('&raquo;'),
					));
					$displayfaq .= '</p>';
				// end pagination links
				}
				wp_reset_query();
		$displayfaq .= '</div></div>';
		endif;

		// now send it all back
		return $displayfaq;
	}

	/**
	 * load combo version shortcode
	 *
	 * @return WP_FAQ_Manager
	 */
	public function shortcode_combo($atts, $content = NULL) {
		extract(shortcode_atts(array(
			'faq_topic'		=> '',
			'faq_tag'		=> '',
			'faq_id'		=> '',
		), $atts));

		// no pagination

		// clean up text
		$faq_topic	= preg_replace('~&#x0*([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $faq_topic);
		$faq_tag	= preg_replace('~&#x0*([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $faq_tag);

		// FAQ query
		$args = array (
			'p'					=> ''.$faq_id.'',
			'faq-topic'			=> ''.$faq_topic.'',
			'faq-tags'			=> ''.$faq_tag.'',
			'post_type'			=>	'question',
			'posts_per_page'	=>	-1,
			'orderby'			=>	'menu_order',
			'order'				=>	'ASC',
		);

		$wp_query = new WP_Query($args);

		if($wp_query->have_posts()) :

			$displayfaq = '<div id="faq-block" rel="faq-top">';
			$displayfaq .= '<div class="faq-list">';

			$displayfaq .= '<ul>';

			while ($wp_query->have_posts()) : $wp_query->the_post();
				global $post;
				$title		= get_the_title();
				$slug		= basename(get_permalink());

				// get options from settings page
				$faqopts	= get_option('faq_options');
				$htype		= (isset($faqopts['htype']) ? $faqopts['htype']  : 'h3' );
				$displayfaq .= '<li class="faqlist-question"><a href="#'.$slug.'" rel="'.$slug.'">'.$title.'</a></li>';
			endwhile;

			$displayfaq .= '</ul>';
			$displayfaq .= '</div>';
			$displayfaq .= '<div class="faq-content">';

			// second part of query
			while ($wp_query->have_posts()) : $wp_query->the_post();
				global $post;

				// get FAQ content
				$content	= get_the_content();
				$title		= get_the_title();
				$slug		= basename(get_permalink());

				// get options from settings page
				$faqopts	= get_option('faq_options');
				$htype		= (isset($faqopts['htype'])		? $faqopts['htype']  : 'h3' );
				$nofilter	= (isset($faqopts['nofilter'])	? true : false	);
				$backtop	= (isset($faqopts['backtop'])	? true : false	);

				$displayfaq .= '<div class="single-faq" rel="'.$slug.'">';
				$displayfaq .= '<'.$htype.' id="'.$slug.'" class="faq-question">'.$title.'</'.$htype.'>';
				$displayfaq .= '<div class="faq-answer">';
				$displayfaq .= $nofilter == true ? '<p>'.$content.'</p>' : apply_filters('the_content', $content);
				$displayfaq .= '<p class="scroll-back"><a href="#faq-block">Back To Top</a></p>';
				$displayfaq .= '</div>';
				$displayfaq .= '</div>';
			endwhile;

			$displayfaq .= '</div>';
			wp_reset_query();

			$displayfaq .= '</div>';
		endif;

		// now send it all back
		return $displayfaq;
	}

	/**
	 * load taxonomy list shortcode
	 *
	 * @return WP_FAQ_Manager
	 */
	public function shortcode_taxls($atts, $content = NULL) {
		extract(shortcode_atts(array(
			'type'		=> 'topics',
			'desc'		=> '',
		), $atts));

		// check for type and description variable
		$type_check	= (isset($type) && $type == 'tags' ) ? 'faq-tags' : 'faq-topic';
		$disp_desc	= (isset($desc) && $desc == 'true' ) ? true : false;

		// get list of terms
		$taxitems	= get_terms( $type_check );
		$countitems	= count($taxitems);

 		// only show if we have something
 		if ( $countitems == 0 )
 			return;

		// get options from settings page
		$faqopts	= get_option('faq_options');
		$htype		= (isset($faqopts['htype']) ? $faqopts['htype']  : 'h3' );

		// begin build
		$displayfaq = '<div id="faq-block" class="faq-taxonomy">';

		// now loop through the topics
		foreach ( $taxitems as $item ) :
			$displayfaq .= '<div class="faq-item">';
			$displayfaq .= '<'.$htype.'><a href="'.get_term_link($item->slug, $type_check).'">'.$item->name.'</a></'.$htype.'>';

			// optional description
			if ($disp_desc == true && !empty($item->description) )
				$displayfaq .= '<p>'.$item->description.'</p>';

			$displayfaq .= '</div>';
		endforeach;

		$displayfaq .= '</div>';

		// now send it all back
		return $displayfaq;
	}
}

$FAQ_Shortcodes = new FAQ_Shortcodes;
