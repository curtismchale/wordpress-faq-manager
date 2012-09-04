<?php
/*
Plugin Name: WordPress FAQ Manager
Plugin URI: http://andrewnorcross.com/plugins/wordpress-faq-manager/
Description: Uses custom post types and taxonomies to manage an FAQ section for your site.
Author: Andrew Norcross
Version: 1.28
Requires at least: 3.0
Author URI: http://andrewnorcross.com
*/
/*  Copyright 2012 Andrew Norcross

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

if(!defined('FAQ_BASE'))
	define('FAQ_BASE', plugin_basename(__FILE__) );

class WP_FAQ_Manager
{

	/**
	 * This is our constructor
	 *
	 * @return WP_FAQ_Manager
	 */
	public function __construct() {
		add_action					( 'load-plugins.php',				array( $this, 'flush_rewrite'	) );
		add_action					( 'init',							array( $this, '_register_faq'	) );
		add_action					( 'admin_menu',						array( $this, 'admin_pages'		) );
		add_action					( 'admin_init', 					array( $this, 'reg_settings'	) );
		add_action					( 'the_posts', 						array( $this, 'style_loader'	) );
		add_action					( 'the_posts', 						array( $this, 'script_loader'	) );
		add_action					( 'the_posts',						array( $this, 'combo_wrapper'	) );
		add_action					( 'wp_ajax_save_sort',				array( $this, 'save_sort'		) );
		add_action					( 'wp_head', 						array( $this, 'seo_head'		), 5  );
		add_action					( 'wp_head', 						array( $this, 'print_css'		), 999  );
		add_action					( 'admin_enqueue_scripts', 			array( $this, 'admin_scripts'	), 10 );
		add_action					( 'manage_posts_custom_column',		array( $this, 'column_data'		), 10, 2);
		add_filter					( 'manage_edit-question_columns',	array( $this, 'column_setup'	) );
		add_filter					( 'enter_title_here',				array( $this, 'title_text'		) );
		add_filter					( 'pre_get_posts',					array( $this, 'rss_include'		) );
		add_filter					( 'faq-caps',						array( $this, 'menu_filter'		), 10, 2);
		add_filter 					( 'plugin_action_links', 			array( $this, 'quick_link'		), 10, 2 );
		add_shortcode				( 'faq',							array( $this, 'shortcode_main'	) );
		add_shortcode				( 'faqlist',						array( $this, 'shortcode_list'	) );
		add_shortcode				( 'faqcombo',						array( $this, 'shortcode_combo'	) );

	}

	/**
	 * flush rewrite rules on activation
	 *
	 * @return WP_FAQ_Manager
	 */

	public function flush_rewrite() {
		
		global $pagenow, $wp_rewrite;
			if ('plugins.php' == $pagenow && isset( $_GET['activate'] ) )
			$wp_rewrite->flush_rules();
	}


	/**
	 * Declare filters
	 *
	 * @return WP_FAQ_Manager
	 */


	public function menu_filter( $capability, $menu ) {

		// Anybody who can publish posts has access to the sort menu		
		if( $menu === 'sort' )
			return 'manage_options';
  
  		// Anybody who can edit posts has access to the instructions page
  		if( $menu === 'instructions' )
			return 'manage_options';
  		
  		// Anybody who can manage options has access to the settings page
  		// If another function has changed this capability already, we'll respect that by just passing the value we were given
		return $capability;
	}

	/**
	 * Call admin pages
	 *
	 * @return WP_FAQ_Manager
	 */

	public function admin_pages() {
		
		add_submenu_page('edit.php?post_type=question', 'Sort FAQs', 'Sort FAQs', apply_filters( 'faq-caps', 'manage_options', 'sort' ), basename(__FILE__), array( &$this, 'sort_page' ));
		add_submenu_page('edit.php?post_type=question', 'Settings', 'Settings', apply_filters( 'faq-caps', 'manage_options', 'settings' ), 'faq-options', array( &$this, 'settings_page' ));
		add_submenu_page('edit.php?post_type=question', 'Instructions', 'Instructions', apply_filters( 'faq-caps', 'manage_options', 'instructions' ), 'faq-instructions', array( &$this, 'instructions_page' ));
	}


	/**
	 * show settings link on plugins page
	 *
	 * @return WP_FAQ_Manager
	 */

    public function quick_link( $links, $file ) {

		static $this_plugin;
		
		if (!$this_plugin) {
			$this_plugin = FAQ_BASE;
		}
 
    	// check to make sure we are on the correct plugin
    	if ($file == $this_plugin) {
        	
        	$settings_url	= menu_page_url( 'faq-options' );
			$settings_link	= '<a href="'.$settings_url.'">Settings</a>';

        	$instruct_url	= menu_page_url( 'faq-instructions' );
			$instruct_link	= '<a href="'.$instruct_url.'">How-To</a>';
        
        	array_unshift($links, $settings_link, $instruct_link);
    	}
 
		return $links;

	}	

	/**
	 * Custom column setup
	 *
	 * @return WP_FAQ_Manager
	 */

	public function column_setup($columns) {
		$qcolumns['cb']			= '<input type="checkbox" />';
		$qcolumns['title']		= _x('Question', 'column name');
		$qcolumns['answers']	= __('Answer');
		$qcolumns['topics']		= __('FAQ Topic');
		$qcolumns['faq_tags']	= __('FAQ Tags');		
		$qcolumns['date']		= _x('Date', 'column name');
 
		return $qcolumns;
	}

	/**
	 * Custom column data
	 *
	 * @return WP_FAQ_Manager
	 */

	function column_data($column_name, $id) {
		global $post;
		switch ($column_name) {
 		case 'answers':
 			$text = get_the_content($post->ID);
 			echo wp_trim_words( $text, 15, null );
		        break;
 		case 'topics':
 			$terms = get_the_terms( $post->ID, 'faq-topic' );
			if ( $terms && ! is_wp_error( $terms ) ) : 
 			foreach ($terms as $term) {
 				$title = $term->name;
				$link = '<a href="' . get_edit_term_link( $term->term_id, $term->taxonomy ) . '" title="' . $title . '">' . $title . '</a>';
				echo apply_filters( 'edit_term_link', $link, $term->term_id );
			}
			endif;
		        break;
 		case 'faq_tags':
 			$terms = get_the_terms( $post->ID, 'faq-tags' );
 			if ( $terms && ! is_wp_error( $terms ) ) : 
 			foreach ($terms as $term) {
 				$title = $term->name;
				$link = '<a href="' . get_edit_term_link( $term->term_id, $term->taxonomy ) . '" title="' . $title . '">' . $title . '</a>';
				echo apply_filters( 'edit_term_link', $link, $term->term_id );
			}
			endif;
		        break;
		default:
			break;
		} // end switch
	}

	/**
	 * expand FAQs for print
	 *
	 * @return WP_FAQ_Manager
	 */

	public function print_css() { ?>
		
		<style media="print" type="text/css">
			div.faq_answer {display: block!important;}
			p.faq_nav {display: none;}
		</style>
		
	<?php }

	/**
	 * Add optional SEO headings
	 *
	 * @return WP_FAQ_Manager
	 */

	public function seo_head() {

		// just get out if the blog is set to private
		if ( 0 == get_option( 'blog_public' ) )
			return;

		// set some defaults
		$faq_options	= get_option('faq_options');
		
		$noindex		= (isset($faq_options['noindex'])	? 'noindex'   : '' );
		$nofollow		= (isset($faq_options['nofollow'])	? 'nofollow'  : '' );
		$noarchive		= (isset($faq_options['noarchive'])	? 'noarchive' : '' );

		$meta = array(
			'noindex'   => '',
			'nofollow'  => '',
			'noarchive' => '',
		);

		// individual FAQs
		if ( is_singular('question') ) {
			$meta['noindex']   = (isset($faq_options['noindex'])	? 'noindex'   : '' );
			$meta['nofollow']  = (isset($faq_options['nofollow'])	? 'nofollow'  : '' );
			$meta['noarchive'] = (isset($faq_options['noarchive'])	? 'noarchive' : '' );
		}

		// FAQ archive pages
		if ( is_post_type_archive('question') ) {
			$meta['noindex']   = (isset($faq_options['noindex'])	? 'noindex'   : '' );
			$meta['nofollow']  = (isset($faq_options['nofollow'])	? 'nofollow'  : '' );
			$meta['noarchive'] = (isset($faq_options['noarchive'])	? 'noarchive' : '' );
		}

		// FAQ taxonomies
		if ( is_tax('faq-topic') || is_tax('faq-tags') ) {
			$meta['noindex']   = (isset($faq_options['noindex'])	? 'noindex'   : '' );
			$meta['nofollow']  = (isset($faq_options['nofollow'])	? 'nofollow'  : '' );
			$meta['noarchive'] = (isset($faq_options['noarchive'])	? 'noarchive' : '' );
		}


		$meta = array_filter( $meta );
		
		/** Add meta if any exist */
		if ( $meta )
			printf( '<meta name="robots" content="%s" />' . "\n", implode( ',', $meta ) );

	}


	/**
	 * Display main options page structure
	 *
	 * @return WP_FAQ_Manager
	 */
	 
	public function settings_page() { 
		?>
	
		<div class="wrap">
    	<div id="icon-faq-admin" class="icon32"><br /></div>
		<h2>FAQ Manager Settings</h2>
        
	        <div class="faq_options">
            	<div class="faq_form_text">
            	<p>Options relating to the FAQ manager</p>
                <hr />
                </div>
               

                <div class="faq_form_options">
	            <form method="post" action="options.php">
			    <?php
                settings_fields( 'faq_options' );
				$faq_options	= get_option('faq_options');

				$htype		= (isset($faq_options['htype'])		? 'choice'					: 'default' );
				$paginate	= (isset($faq_options['paginate'])	? $faq_options['paginate']	: 'false' );
				$expand		= (isset($faq_options['expand'])	? $faq_options['expand']	: 'false' );
				$scroll		= (isset($faq_options['scroll'])	? $faq_options['scroll']	: 'false' );
				$css		= (isset($faq_options['css'])		? $faq_options['css']		: 'false' );
				$rss		= (isset($faq_options['rss'])		? $faq_options['rss']		: 'false' );
				$noindex	= (isset($faq_options['noindex'])	? $faq_options['noindex']	: 'false' );
				$nofollow	= (isset($faq_options['nofollow'])	? $faq_options['nofollow']	: 'false' );
				$noarchive	= (isset($faq_options['noarchive'])	? $faq_options['noarchive']	: 'false' );
				$archtext	= (isset($faq_options['arch'])		? $faq_options['arch']		: 'questions' );
				?>

				<p>
					<select class="faq_htype <?php echo $htype?>" name="faq_options[htype]" id="faq_htype">
		            <option value="h1" <?php selected( $faq_options['htype'], 'h1' ); ?>>H1</option>
					<option value="h2" <?php selected( $faq_options['htype'], 'h2' ); ?>>H2</option>
					<option value="h3" <?php selected( $faq_options['htype'], 'h3' ); ?>>H3</option>
					<option value="h4" <?php selected( $faq_options['htype'], 'h4' ); ?>>H4</option>
					<option value="h5" <?php selected( $faq_options['htype'], 'h5' ); ?>>H5</option>
					<option value="h6" <?php selected( $faq_options['htype'], 'h6' ); ?>>H6</option>
					</select>
					<label type="select" for="faq_options[htype]"><?php _e('Choose your H type for FAQ title') ?></label>
				</p>               

				<p>
			    	<input type="checkbox" name="faq_options[paginate]" id="faq_paginate" value="true" <?php checked( $paginate, 'true' ); ?> />
    				<label for="faq_options[paginate]" rel="checkbox"><?php _e('Paginate shortcode output') ?></label>
				</p>

				<p>
				    <input type="checkbox" name="faq_options[expand]" id="faq_expand" value="true" <?php checked( $expand, 'true' ); ?> />
				    <label for="faq_options[expand]" rel="checkbox">Include jQuery collapse / expand</label>
				</p>

				<p>
				    <input type="checkbox" name="faq_options[scroll]" id="faq_scroll" value="true" <?php checked( $scroll, 'true' ); ?> />
				    <label for="faq_options[scroll]" rel="checkbox">Include jQuery scrolling for Combo shortcode</label>
				</p>

				<p>
				    <input type="checkbox" name="faq_options[css]" id="faq_css" value="true" <?php checked( $css, 'true' ); ?> />
				    <label for="faq_options[css]" rel="checkbox">Load default CSS</label>
				</p>

				<p>
				    <input type="checkbox" name="faq_options[rss]" id="faq_rss" value="true" <?php checked( $rss, 'true' ); ?> />
				    <label for="faq_options[rss]" rel="checkbox">Include in main RSS feed</label>
				</p>

				<h3>SEO Options</h3>

				<p>
				    <input type="checkbox" name="faq_options[noindex]" id="faq_noindex" value="true" <?php checked( $noindex, 'true' ); ?> />
				    <label for="faq_options[noindex]" rel="checkbox"> Apply <code>noindex</code> header tag to FAQs</label>
				</p>

				<p>
				    <input type="checkbox" name="faq_options[nofollow]" id="faq_nofollow" value="true" <?php checked( $nofollow, 'true' ); ?> />
				    <label for="faq_options[nofollow]" rel="checkbox"> Apply <code>nofollow</code> header tag to FAQs</label>
				</p>

				<p>
				    <input type="checkbox" name="faq_options[noarchive]" id="faq_noarchive" value="true" <?php checked( $noarchive, 'true' ); ?> />
				    <label for="faq_options[noarchive]" rel="checkbox"> Apply <code>noarchive</code> header tag to FAQs</label>
				</p>				

				<p>
					<input name="faq_options[arch]" id="faq_arch" type="text" size="25" value="<?php echo sanitize_title($archtext); ?>" />
					<label for="faq_options[arch]">Desired page slug for archiving (all lower case, no capitals or spaces)</label>
				</p>


    			<!-- submit -->
	    		<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>

				<p class="description"><strong>Note:</strong> You may need to flush your permalinks after changing settings. <a href="<?php echo admin_url( 'options-permalink.php'); ?>">Go to your Permalink Settings here</a></p>

				</form>
                </div>
    
            </div>

        </div>    

	
	<?php }

	/**
	 * Instructions Page
	 *
	 * @return WP_FAQ_Manager
	 */

	public function instructions_page() {
		?>
		
		<div class="wrap">
	    	<div id="icon-faq-admin" class="icon32"><br /></div>
		    <h2>FAQ Instructions</h2>
			<p>A brief overview of the available options / shortcodes</p>
			<hr />
    
			<p>The FAQ Manager plugin uses a combination of custom post types, meta fields, and taxonomies. The plugin will automatically create single posts using your existing permalink structure. And the FAQ categories and tags can be added to your menu using the WP Menu Manager</p>

			<h3>Shortcodes</h3>
			<p>The plugin also has the option of using shortcodes. To use them, follow the syntax accordingly in the HTML tab:</p>

			<ul class="faqinfo">
			<li><strong>For the complete list (including title and content):</strong></li>
			<li>place <code>[faq]</code> on a post / page</li><br />
			<li><strong>For the question title, and a link to the FAQ on a separate page:</strong></li>
			<li>place <code>[faqlist]</code> on a post / page</li><br />
			<li><strong>For a list with a group of titles that link to complete content later in page:</strong></li>
			<li>place <code>[faqcombo]</code> on a post / page</li><br />
			<li><em><strong>Please note:</strong> that the combo shortcode will not recognize the pagination and expand / collapse:</em></li><br />			
			</ul>

			<h3>The following options apply to all the <code>shortcode</code> types</h3>

			<p>The list will show 10 FAQs based on your sorting (if none has been done, it will be in date order).</p>
			<ul class="faqinfo">
			<li><strong>To display only 5:</strong></li>
			<li>place <code>[faq limit="5"]</code> on a post / page</li><br />
			<li><strong>To display ALL:</strong></li>
			<li>place <code>[faq limit="-1"]</code> on a post / page</li><br />
			</ul>

			<ul class="faqinfo">
			<li><strong>For a single FAQ:</strong></li>
			<li>place <code>[faq faq_id="ID"]</code> on a post / page</li><br />
			<li><strong>List all from a single FAQ topic category:</strong></li>
			<li>place <code>[faq faq_topic="topic-slug"]</code> on a post / page</li><br />
			<li><strong>List all from a single FAQ tag:</strong></li>
			<li>place <code>[faq faq_tag="tag-slug"]</code> on a post / page</li><br />
			</ul>

			<p><strong><em>Please note that the shortcode can't handle a query of multiple categories / topics in a single shortcode. However, you can stack them as such:</em></strong></p>
			<p>...content....<p>
			<p class="indent"><code>[faq faq_topic="topic-slug-one"]</code></p>
			<p>...more content....<p>
			<p class="indent"><code>[faq faq_topic="topic-slug-two"]</code></p>
			<p>...even more content....<p>



			<p class="norcross_donate">Like the plugin? Find it useful? Maybe wanna buy me a cup of coffee?</p>
			<form style="text-align: left;" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank"> <input name="cmd" type="hidden" value="_s-xclick" />
			<input name="hosted_button_id" type="hidden" value="11085100" />
			<input alt="PayPal - The safer, easier way to pay online!" name="submit" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" type="image" />
			<img src="https://www.paypal.com/en_US/i/scr/pixel.gif" border="0" alt="" width="1" height="1" />
			</form>

		</div>

	<?php }	

	/**
	 * Sort Page
	 *
	 * @return WP_FAQ_Manager
	 */


	public function sort_page() {
		$questions = new WP_Query('post_type=question&posts_per_page=-1&orderby=menu_order&order=ASC');
	?>
		<div class="wrap">
		<div id="icon-faq-admin" class="icon32"><br /></div>
		<h2>Sort FAQs <img src=" <?php echo admin_url(); ?>/images/loading.gif" id="loading-animation" /></h2>
			<?php if ( $questions->have_posts() ) : ?>
	    	<p><strong>Note:</strong> this only affects the FAQs listed using the shortcode functions</p>
			<ul id="custom-type-list">
				<?php while ( $questions->have_posts() ) : $questions->the_post(); ?>
					<li id="<?php the_id(); ?>"><?php the_title(); ?></li>			
				<?php endwhile; ?>
	    	</ul>
			<?php else: ?>
			<p>You have no FAQs to sort.</p>
			<?php endif; ?>
		</div>
	
	<?php }

	/**
	 * Save sort order
	 *
	 * @return WP_FAQ_Manager
	 */

	public function save_sort() {
		global $wpdb; // WordPress database class
 
		$order = explode(',', $_POST['order']);
		$counter = 0;
 
		foreach ($order as $item_id) {
			$wpdb->update($wpdb->posts, array( 'menu_order' => $counter ), array( 'ID' => $item_id) );
			$counter++;
		}
		die(1);
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
		
		// pagination call. required regardless of whether pagination is active or not
		if( isset( $_GET['faq_page'] ) && $faq_page = absint( $_GET['faq_page'] ) )
			$paged = $faq_page;
		else
			$paged = 1;
		$old_link = trailingslashit(get_permalink());
		// end paginaton

		// clean up text
		$faq_topic	= preg_replace('~&#x0*([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $faq_topic);
		$faq_tag	= preg_replace('~&#x0*([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $faq_tag);

		// FAQ query
		$args = array (
			'p'					=> ''.$faq_id.'',
			'faq-topic'			=> ''.$faq_topic.'',
			'faq-tags'			=> ''.$faq_tag.'',
			'post_type'			=>	'question',
			'posts_per_page'	=>	''.$limit.'',
			'orderby'			=>	'menu_order',
			'order'				=>	'ASC',
			'paged'				=>	$paged,
		);
		
		$wp_query = new WP_Query($args);

		if($wp_query->have_posts()) :
		
		$displayfaq = '<div id="faq_block"><div class="faq_list">';
			
			while ($wp_query->have_posts()) : $wp_query->the_post();
			
			global $post;
			$content	= get_the_content();
			$title		= get_the_title();
			$slug		= basename(get_permalink());

			// get options from settings page
			$faqopts	= get_option('faq_options');
			$expand_a	= (isset($faqopts['expand']) && $faqopts['expand'] == 'true' ? ' expand_faq'  : '' );
			$expand_b	= (isset($faqopts['expand']) && $faqopts['expand'] == 'true' ? ' expand_title'  : '' );
			$htype		= (isset($faqopts['htype']) ? $faqopts['htype']  : 'h3' );


				$displayfaq .= '<div class="single_faq'.$expand_a.'">';
				$displayfaq .= '<'.$htype.' id="'.$slug.'" class="faq_question'.$expand_b.'">'.$title.'</'.$htype.'>';
				$displayfaq .= '<div class="faq_answer">'.wpautop($content, true).'</div>';
				$displayfaq .= '</div>';
			
			endwhile;

				if (isset($faqopts['paginate'])) {
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
		
		// pagination call. required regardless of whether pagination is active or not
		if( isset( $_GET['faq_page'] ) && $faq_page = absint( $_GET['faq_page'] ) )
			$paged = $faq_page;
		else
			$paged = 1;
		$old_link = trailingslashit(get_permalink());
		// end paginaton

		// clean up text
		$faq_topic	= preg_replace('~&#x0*([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $faq_topic);
		$faq_tag	= preg_replace('~&#x0*([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $faq_tag);

		// FAQ query
		$args = array (
			'p'					=> ''.$faq_id.'',
			'faq-topic'			=> ''.$faq_topic.'',
			'faq-tags'			=> ''.$faq_tag.'',
			'post_type'			=>	'question',
			'posts_per_page'	=>	''.$limit.'',
			'orderby'			=>	'menu_order',
			'order'				=>	'ASC',
			'paged'				=>	$paged,
		);
		
		$wp_query = new WP_Query($args);

		if($wp_query->have_posts()) :
		
		$displayfaq = '<div id="faq_block"><div class="faq_list">';
			
			$displayfaq .= '<ul>';
			while ($wp_query->have_posts()) : $wp_query->the_post();
			
			global $post;
			$title		= get_the_title();
			$link		= get_permalink();
			$slug		= basename(get_permalink());

			// get options from settings page
			$faqopts	= get_option('faq_options');
			$htype		= (isset($faqopts['htype']) ? $faqopts['htype']  : 'h3' );

				$displayfaq .= '<li class="faqlist_question"><a href="'.$link.'" title="Permanent link to '.$title.'" >'.$title.'</a></li>';
				
			
			endwhile;
			$displayfaq .= '</ul>';
			
				if (isset($faqopts['paginate'])) {
					// pagination links
					$displayfaq .= '<p class="faq_nav">';
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
		
		$displayfaq = '<div id="faq_block">';
		$displayfaq .= '<div class="faq_list">';
			
			$displayfaq .= '<ul>';
			while ($wp_query->have_posts()) : $wp_query->the_post();
			
			global $post;
			$title		= get_the_title();
			$slug		= basename(get_permalink());

			// get options from settings page
			$faqopts	= get_option('faq_options');
			$htype		= (isset($faqopts['htype']) ? $faqopts['htype']  : 'h3' );

				$displayfaq .= '<li class="faqlist_question"><a href="#'.$slug.'" rel="'.$slug.'">'.$title.'</a></li>';
				
			
			endwhile;
		$displayfaq .= '</ul>';
		$displayfaq .= '</div>';

		$displayfaq .= '<div class="faq_content">';			
			// second part of query
			while ($wp_query->have_posts()) : $wp_query->the_post();
			
			global $post;
			$title		= get_the_title();
			$slug		= basename(get_permalink());

			// get options from settings page
			$faqopts	= get_option('faq_options');
			$htype		= (isset($faqopts['htype']) ? $faqopts['htype']  : 'h3' );

			$content	= get_the_content();
			$title		= get_the_title();
			$slug		= basename(get_permalink());

			// get options from settings page
			$faqopts	= get_option('faq_options');
			$htype		= (isset($faqopts['htype']) ? $faqopts['htype']  : 'h3' );

				$displayfaq .= '<div class="single_faq" rel="'.$slug.'">';
				$displayfaq .= '<'.$htype.' id="'.$slug.'" class="faq_question">'.$title.'</'.$htype.'>';
				$displayfaq .= '<div class="faq_answer">'.wpautop($content, true).'</div>';
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
	 * build out post type and taxonomies
	 *
	 * @return WP_FAQ_Manager
	 */

	public function _register_faq() {

		// get options from settings page
		$faqopts	= get_option('faq_options');
		$arch		= (isset($faqopts['arch']) ? sanitize_title($faqopts['arch']) : 'questions' );

		register_post_type( 'question',
			array(
				'labels'	=> array(
					'name' 					=> __( 'FAQs' ),
					'singular_name' 		=> __( 'FAQ' ),
					'add_new'				=> __( 'Add New FAQ' ),
					'add_new_item'			=> __( 'Add New FAQ' ),
					'edit'					=> __( 'Edit' ),
					'edit_item'				=> __( 'Edit FAQ' ),
					'new_item'				=> __( 'New FAQ' ),
					'view'					=> __( 'View FAQ' ),
					'view_item'				=> __( 'View FAQ' ),
					'search_items'			=> __( 'Search FAQ' ),
					'not_found'				=> __( 'No FAQs found' ),
					'not_found_in_trash'	=> __( 'No FAQs found in Trash' ),
				),
				'public'	=> true,
					'show_in_nav_menus'		=> true,
					'show_ui'				=> true,
					'publicly_queryable'	=> true,
					'exclude_from_search'	=> false,
				'hierarchical'		=> false,
				'menu_position'		=> 20,
				'capability_type'	=> 'post',
				'menu_icon'			=> plugins_url( '/inc/img/faq_menu.png', __FILE__ ),
				'query_var'			=> true,
				'rewrite'			=> true,
				'has_archive'		=> $arch,
				'supports'			=> array('title', 'editor', 'author', 'thumbnail', 'comments', 'custom-fields'),
			)
		);
		// register topics (categories) for FAQs
		register_taxonomy(
			'faq-topic',
			array( 'question' ),
			array(
				'public'				=> true,
				'show_in_nav_menus'		=> true,
				'show_ui'				=> true,
				'publicly_queryable'	=> true,
				'exclude_from_search'	=> false,
				'rewrite'				=> array( 'slug' => 'topics', 'with_front' => true ),
				'hierarchical'			=> true,
				'query_var'				=> true,
				'labels'	=> array(
					'name' 					=> __( 'FAQ Topics' ),
					'singular_name'			=> __( 'FAQ Topic' ),
					'search_items'			=> __( 'Search FAQ Topics' ),
					'popular_items'			=> __( 'Popular FAQ Topics' ),
					'all_items'				=> __( 'All FAQ Topics' ),
					'parent_item'			=> __( 'Parent FAQ Topic' ),
					'parent_item_colon'		=> __( 'Parent FAQ Topic:' ),
					'edit_item'				=> __( 'Edit FAQ Topics' ),
					'update_item'			=> __( 'Update FAQ Topics' ),
					'add_new_item'			=> __( 'Add New FAQ Topics' ),
					'new_item_name'			=> __( 'New FAQ Topics' ),
				),
			)
		);
		// register tags for FAQs
		register_taxonomy(
			'faq-tags',
			array( 'question' ),
			array(
				'public'				=> true,
				'show_in_nav_menus'		=> true,
				'show_ui'				=> true,
				'publicly_queryable'	=> true,
				'exclude_from_search'	=> false,
				'rewrite'				=> array( 'slug' => 'faq-tags', 'with_front' => true ),
				'hierarchical'			=> false,
				'query_var'				=> true,
				'labels'	=> array(
					'name'					=> __( 'FAQ Tags' ),
					'singular_name'			=> __( 'FAQ Tag' ),
					'search_items'			=> __( 'Search FAQ Tags' ),
					'popular_items'			=> __( 'Popular FAQ Tags' ),
					'all_items'				=> __( 'All FAQ Tags' ),
					'parent_item'			=> __( 'Parent FAQ Tags' ),
					'parent_item_colon'		=> __( 'Parent FAQ Tag:' ),
					'edit_item'				=> __( 'Edit FAQ Tag' ),
					'update_item'			=> __( 'Update FAQ Tag' ),
					'add_new_item'			=> __( 'Add New FAQ Tag' ),
					'new_item_name'			=> __( 'New FAQ Tag' ),
				),
			)
		);
		register_taxonomy_for_object_type('question', 'faq-tags');
		register_taxonomy_for_object_type('question', 'faq-topic');
	}

	/**
	 * add to RSS
	 *
	 * @return WP_FAQ_Manager
	 */

	public function rss_include( $query ) {
	
		$faqopts = get_option('faq_options');

		if(!isset($faqopts['rss']) )
			return $query;

		if (!$query->is_feed) 
			return $query;

			$args = array(
				'public'	=> true,
				'_builtin'	=> false
			);
		
			$output		= 'names';
			$operator	= 'and';
			$post_types = get_post_types( $args , $output , $operator );
		
			// remove 'pages' from the RSS
			$post_types = array_merge( $post_types, array('post') ) ;
		
			$query->set( 'post_type' , $post_types );
	
		return $query;
	}


	/**
	 * load front-end CSS
	 *
	 * @return WP_FAQ_Manager
	 */


	public function style_loader($posts) {
		
		$faqopts = get_option('faq_options');

		if(!isset($faqopts['css']) )
			return $posts;		
		
		if ( empty($posts) )
			return $posts;
		
		// false because we have to search through the posts first
		$found = false;
		 
		// search through each post
		foreach ($posts as $post) {
			// check the post content for the short code
			$content	= $post->post_content;
			if ( preg_match('/faq(.*)/', $content) ) // we have found a post with the short code
				$found = true;
				
				// stop the search
				break;
		}
		 
		if ($found == true )
			$this->front_style();
		

		return $posts;
	}

	/**
	 * Check for FAQCombo shortcode and call related JS
	 *
	 * @return WP_FAQ_Manager
	 */

	public function combo_wrapper($posts) {

		$faqopts = get_option('faq_options');

		if(!isset($faqopts['scroll']) )
			return $posts;

		if ( empty($posts) )
			return $posts;
		
		// false because we have to search through the posts first
		$found = false;
		 
		// search through each post
		foreach ($posts as $post) {
			// check the post content for the short code
			$content	= $post->post_content;
			if ( preg_match('/faqcombo(.*)/', $content) ) // we have found a post with the short code
				$found = true;
				
				// stop the search
				break;
		}
		 
		if ($found == true )
			$this->scroll_script();
		

		return $posts;
	}


	/**
	 * load front-end JS
	 *
	 * @return WP_FAQ_Manager
	 */


	public function script_loader($posts) {
		
		$faqopts = get_option('faq_options');

		if(!isset($faqopts['paginate']) && !isset($faqopts['expand']) )
			return $posts;		
		
		if ( empty($posts) )
			return $posts;
		
		// false because we have to search through the posts first
		$found = false;
		 
		// search through each post
		foreach ($posts as $post) {
			// check the post content for the short code
			$content	= $post->post_content;
			if ( preg_match('/faq(.*)/', $content) ) // we have found a post with the short code
				$found = true;
				
				// stop the search
				break;
		}
		 
		if ($found == true )
			$this->front_script();
		

		return $posts;
	}

	/**
	 * Change title entry on post type
	 *
	 * @return WP_FAQ_Manager
	 */


	public function title_text( $title ){
		$screen = get_current_screen();
		if ( 'question' == $screen->post_type ) :
			$title = 'Enter Question Title Here';
		endif;
		
		return $title;
	}



	/**
	 * Register settings
	 *
	 * @return WP_FAQ_Manager
	 */


	public function reg_settings() {
		register_setting( 'faq_options', 'faq_options');		

	}


	/**
	 * Admin scripts and styles
	 *
	 * @return WP_FAQ_Manager
	 */

	public function admin_scripts() {
	
		$screen = get_current_screen();

		if ( 'question' == $screen->post_type ) :
		
			wp_enqueue_style( 'faq-admin', plugins_url('/inc/css/faq-admin.css', __FILE__) );

			wp_enqueue_script('jquery-ui-sortable');
			wp_enqueue_script( 'faq-admin', plugins_url('/inc/js/faq.admin.init.js', __FILE__) , array('jquery'), null, true );
		
		endif;



	}

	/**
	 * load scripts and styles for front end
	 *
	 * @return WP_FAQ_Manager
	 */

	public function front_style() {

		wp_enqueue_style( 'faq-style', plugins_url('/inc/css/faq-style.css', __FILE__) );

	}

	public function front_script() {

		wp_enqueue_script( 'faq-init', plugins_url('/inc/js/faq.init.js', __FILE__) , array('jquery'), null, true );

	}

	public function scroll_script() {

		wp_enqueue_script( 'faq-scroll', plugins_url('/inc/js/faq.scroll.js', __FILE__) , array('jquery'), null, true );

	}

		

/// end class
}


// Instantiate our class

function WP_FAQ_Manager_init() {
	$WP_FAQ_Manager = new WP_FAQ_Manager();
}

if(!function_exists('WP_FAQ_Manager_init')) {
	WP_FAQ_Manager_init();
}

add_action('init', 'WP_FAQ_Manager_init', 1);


	/**
	 * setup widgets
	 *
	 * @return WP_FAQ_Manager
	 */

// FAQ Search
class search_FAQ_Widget extends WP_Widget {
	function search_FAQ_Widget() {
		$widget_ops = array( 'classname' => 'faq_search_widget widget_search', 'description' => 'Puts a search box for just FAQs' );
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


// show randoms
class random_FAQ_Widget extends WP_Widget {
	function random_FAQ_Widget() {
		$widget_ops = array( 'classname' => 'faq_random_widget', 'description' => 'Lists a single random FAQ on the sidebar' );
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
 			
				echo '<h5 class="faq_widget_title">'.$faq->post_title.'</h5>';
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


// Recent Questions

class recent_FAQ_Widget extends WP_Widget {
	function recent_FAQ_Widget() {
		$widget_ops = array( 'classname' => 'recent_questions_widget', 'description' => 'List recent questions' );
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


// FAQ Taxonomy List
class topics_FAQ_Widget extends WP_Widget {
	function topics_FAQ_Widget() {
		$widget_ops = array( 'classname' => 'recent_faqtax_widget', 'description' => 'List FAQ topics or tags' );
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


// FAQ Tag Cloud

class cloud_FAQ_Widget extends WP_Widget {
	function cloud_FAQ_Widget() {
		$widget_ops = array( 'classname' => 'faq_cloud_widget', 'description' => 'A tag cloud of FAQ topics and tags' );
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



// register widget
add_action( 'widgets_init', create_function( '', "register_widget('search_FAQ_Widget');" ) );
add_action( 'widgets_init', create_function( '', "register_widget('random_FAQ_Widget');" ) );
add_action( 'widgets_init', create_function( '', "register_widget('recent_FAQ_Widget');" ) );
add_action( 'widgets_init', create_function( '', "register_widget('topics_FAQ_Widget');" ) );
add_action( 'widgets_init', create_function( '', "register_widget('cloud_FAQ_Widget');" ) );
