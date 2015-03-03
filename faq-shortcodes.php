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
	public function shortcode_query( $shortcode_args ) {
		// set up $paged
		if( isset( $_GET['faq_page'] ) && $faq_page = absint( $_GET['faq_page'] ) ) {
			$paged = $faq_page;
		} else {
			$paged = 1;
		}

		// clean up text
		$faq_topic	= preg_replace('~&#x0*([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $shortcode_args['topic']);
		$faq_tag = preg_replace('~&#x0*([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $shortcode_args['tag']);

		// FAQ query
		$args = array (
			'p'				  => '' . $shortcode_args['id'] . '',
			'faq-topic'	=> '' . $faq_topic . '',
			'faq-tags'	=> '' . $faq_tag . '',
			'post_type'	=> 'question',
			'orderby'		=> 'menu_order',
			'order'			=> 'ASC',
		);

		// handle optional limit
		if( isset( $shortcode_args['limit'] ) ) {
			$args['posts_per_page'] =	'' . $shortcode_args['limit'] . '';
		}

		// handle optional pagination
		if( isset( $shortcode_args['pagination'] ) ) {
			$args['paged'] = $paged;
		}

		return new WP_Query($args);
	}

	/**
	 * Format the shortcode title
	 * $title_data array(
	 *   'context' => 'main',
	 *   'title'   => $title,
	 *   'slug'    => $slug,
	 *   'htype'   => $htype,
	 *   'class'   => 'faq-question' . $expand_b
	 *  )
	 */
	function format_shortcode_title( $title_data ) {
		$html_title = '<' . $title_data['htype'] . ' id="' . $$title_data['slug'] . '" class="' . $title_data['class'] . '">' . $title_data['title'] . '</' . $title_data['htype'] . '>';

		return apply_filters( 'wp_faq_title_html', $html_title, $title_data );
	}

	/**
	 * Format the read more link
	 * $read_more_data array(
	 *	'link'  => $link,
	 *	'title' => $title,
	 *	'text'  => $extext,
	 *	'class' => 'faq-link'
	 * )
	 */
	function format_read_more( $read_more_data ) {
		$read_more_html = '<p class="' . $read_more_data['class'] . '"><a href="' . $read_more_data['link'] . '" title="' . $read_more_data['title'] . '">' . $read_more_data['text'] . '</a></p>';

		return apply_filters( 'wp_faq_read_more_html', $read_more_html, $read_more_data );
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

		$wp_query = $this->shortcode_query( array(
			'topic'      => $faq_topic,
			'tag'        => $faq_tag,
			'id'         => $faq_id,
			'limit'      => $limit,
			'pagination' => true
		) );

		if($wp_query->have_posts()) :
			// get options from settings page
			$faqopts	= get_option('faq_options');
			$exspeed	= (isset($faqopts['exspeed'])								                	? $faqopts['exspeed']	: '200'	);
			$exlink		= (isset($faqopts['exlink'])							                		? true					      : false	);
			$nofilter	= (isset($faqopts['nofilter'])								                ? true					      : false	);
			$extext		= (isset($faqopts['extext']) && $faqopts['extext'] !== ''		  ? $faqopts['extext']	: 'Read More'	);
			$expand_a	= (isset($faqopts['expand']) && $faqopts['expand'] == 'true'	? ' expand-faq'			  : ''	);
			$expand_b	= (isset($faqopts['expand']) && $faqopts['expand'] == 'true'	? ' expand-title'		  : ''	);
			$htype		= (isset($faqopts['htype'])										                ? $faqopts['htype']		: 'h3'	);

			$displayfaq = '<div id="faq-block"><div class="faq-list" data-speed="'.$exspeed.'">';

			while ($wp_query->have_posts()) : $wp_query->the_post();
				global $post;
				$content = get_the_content();
				$title	 = get_the_title();
				$slug		 = basename(get_permalink());
				$link		 = get_permalink();

				$displayfaq .= '<div class="single-faq'.$expand_a.'">';
				$displayfaq .= $this->format_shortcode_title( array(
					'context' => 'main',
					'title'   => $title,
					'slug'    => $slug,
					'htype'   => $htype,
					'class'   => 'faq-question' . $expand_b
				) );
				$displayfaq .= '<div class="faq-answer" rel="'.$slug.'">';
				$displayfaq .= $nofilter == true ? $content : apply_filters('the_content', $content);
				if ($exlink == true)
					$displayfaq .= $this->format_read_more( array(
						'link'  => $link,
						'title' => $title,
						'text'  => $extext,
						'class' => 'faq-link'
					) );

				$displayfaq .= '</div>';
				$displayfaq .= '</div>';
			endwhile;

			if (isset($faqopts['paginate'])) {
				$old_link = trailingslashit(get_permalink());

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

		$wp_query = $this->shortcode_query( array(
			'topic'      => $faq_topic,
			'tag'        => $faq_tag,
			'id'         => $faq_id,
			'limit'      => $limit,
			'pagination' => true
		) );

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
					$old_link = trailingslashit(get_permalink());

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

		$wp_query = $this->shortcode_query( array(
			'topic' => $faq_topic,
			'tag'   => $faq_tag,
			'id'    => $faq_id,
			'limit' => -1
		) );

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
				$displayfaq .= $this->format_shortcode_title( array(
					'context' => 'combo',
					'title'   => $title,
					'slug'    => $slug,
					'htype'   => $htype,
					'class'   => 'faq-question'
				) );
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
