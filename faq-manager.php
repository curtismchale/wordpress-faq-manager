<?php
/*
Plugin Name: WordPress FAQ Manager
Plugin URI: http://andrewnorcross.com/plugins/faq-manager/
Description: Uses custom post types and taxonomies to manage an FAQ section for your site.
Author: Andrew Norcross
Version: 1.331
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

if(!defined('FAQ_VER'))
	define('FAQ_VER', '1.331');

//call widgets file
include('faq-widgets.php');

//call shortcodes file
include('faq-shortcodes.php');

class WP_FAQ_Manager
{

	/**
	 * This is our constructor
	 *
	 * @return WP_FAQ_Manager
	 */
	public function __construct() {
		add_action					( 'plugins_loaded', 				array( $this, 'textdomain'		) 			);
		add_action					( 'init',							array( $this, '_register_faq'	) 			);
		add_action					( 'admin_init', 					array( $this, 'reg_settings'	) 			);
		add_action					( 'admin_menu',						array( $this, 'admin_pages'		) 			);
		add_action					( 'admin_footer',					array( $this, 'flush_rewrite'	) 			);
		add_action					( 'the_posts', 						array( $this, 'style_loader'	) 			);
		add_action					( 'the_posts', 						array( $this, 'script_loader'	) 			);
		add_action					( 'the_posts',						array( $this, 'combo_wrapper'	) 			);
		add_action					( 'wp_ajax_save_sort',				array( $this, 'save_sort'		) 			);
		add_action					( 'template_redirect',				array( $this, 'faq_redirect'	), 1		);
		add_action					( 'wp_head', 						array( $this, 'seo_head'		), 5		);
		add_action					( 'wp_head', 						array( $this, 'print_css'		), 999		);
		add_action					( 'admin_enqueue_scripts', 			array( $this, 'admin_scripts'	), 10		);
		add_action					( 'widgets_init',					array( $this, 'register_widgets')			);
		add_filter					( 'enter_title_here',				array( $this, 'title_text'		) 			);
		add_filter					( 'pre_get_posts',					array( $this, 'rss_include'		) 			);
		add_filter					( 'faq-caps',						array( $this, 'menu_filter'		), 10, 2	);
		add_filter 					( 'plugin_action_links', 			array( $this, 'quick_link'		), 10, 2	);
		add_filter					( 'post_class', 					array( $this, 'faq_post_class'	) 			);
		add_shortcode				( 'faq',							array( $this, 'shortcode_main'	) 			);
		add_shortcode				( 'faqlist',						array( $this, 'shortcode_list'	) 			);
		add_shortcode				( 'faqcombo',						array( $this, 'shortcode_combo'	) 			);
		add_shortcode				( 'faqtaxlist',						array( $this, 'shortcode_taxls'	) 			);

	}

	public function register_widgets() {
		if( class_exists( 'search_FAQ_Widget' ) ) {
			register_widget('search_FAQ_Widget');
		}

		if( class_exists( 'random_FAQ_Widget' ) ) {
			register_widget('random_FAQ_Widget');
		}
		if( class_exists( 'recent_FAQ_Widget' ) ) {
			register_widget('recent_FAQ_Widget');
		}
		if( class_exists( 'topics_FAQ_Widget' ) ) {
			register_widget('topics_FAQ_Widget');
		}
		if( class_exists( 'cloud_FAQ_Widget' ) ) {
			register_widget('cloud_FAQ_Widget' );
		}
	}

	/**
	 * load textdomain for
	 *
	 * @return WP_FAQ_Manager
	 */


	public function textdomain() {

		load_plugin_textdomain( 'wpfaq', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
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

		add_submenu_page('edit.php?post_type=question', __('Sort FAQs', 'wpfaq'), __('Sort FAQs', 'wpfaq'), apply_filters( 'faq-caps', 'manage_options', 'sort' ), basename(__FILE__), array( &$this, 'sort_page' ));
		add_submenu_page('edit.php?post_type=question', __('Settings', 'wpfaq'), __('Settings', 'wpfaq'), apply_filters( 'faq-caps', 'manage_options', 'settings' ), 'faq-options', array( &$this, 'settings_page' ));
		add_submenu_page('edit.php?post_type=question', __('Instructions', 'wpfaq'), __('Instructions', 'wpfaq'), apply_filters( 'faq-caps', 'manage_options', 'instructions' ), 'faq-instructions', array( &$this, 'instructions_page' ));
	}


	/**
	 * flush rewrite rules on activation or settings change
	 *
	 * @return WP_FAQ_Manager
	 */

	public function flush_rewrite() {

		global $wp_rewrite;
		$screen = get_current_screen();

		if ( 'plugins' == $screen->base && isset( $_GET['activate'] ) )
			$wp_rewrite->flush_rules();

		if ( 'question_page_faq-options' == $screen->base && isset( $_GET['settings-updated'] ) )
			$wp_rewrite->flush_rules();

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

			$settings_link	= '<a href="'.menu_page_url( 'faq-options', 0 ).'">'.__('Settings', 'wpfaq').'</a>';
			$instruct_link	= '<a href="'.menu_page_url( 'faq-instructions', 0 ).'">'.__('How-To', 'wpfaq').'</a>';

        	array_unshift($links, $settings_link, $instruct_link);
    	}

		return $links;

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
	 * redirect to FAQ page based on user setting
	 *
	 * @return WP_FAQ_Manager
	 */

	public function faq_redirect() {

		// grab some settings
		$faq_options	= get_option('faq_options');
		$redirect		= (isset($faq_options['redirect'])		? true   						: false );
		$redirectid		= (isset($faq_options['redirectid'])	? $faq_options['redirectid']	: false );

		// bail if they never set it
		if ( $redirect === false )
			return;

		// bail if they set it to "none"
		if ( $redirectid == 'none' )
			return;

        // redirect to page selected by user
        if (is_singular('question') || is_post_type_archive('question') || is_tax('faq-topic') || is_tax('faq-tags')) :

            $faq_page = get_permalink($redirectid);

            wp_redirect( esc_url_raw( $faq_page ), 301 );
            exit();

        endif;

	}

	/**
	 * Add 'normal' post classes for themes with narrow CSS
	 *
	 * @return WP_FAQ_Manager
	 */

	public function faq_post_class($classes) {

		if (is_singular('question') || is_post_type_archive('question') || is_tax('faq-topic') || is_tax('faq-tags')) :
			$classes[] = 'post';
			$classes[] = 'type-post';
		endif;

		return $classes;

	}

	/**
	 * Helper for getting pages for redirect
	 *
	 * @return WP_FAQ_Manager
	 */

	private function redirect_pages() {

		$args = array(
			'sort_order'	=> 'ASC',
			'sort_column'	=> 'post_title',
			'hierarchical'	=> 1,
			'post_type'		=> 'page',
			'post_status'	=> 'publish'
		);

		$pages = get_pages($args);

		return $pages;

	}

	/**
	 * Display main options page structure
	 *
	 * @return WP_FAQ_Manager
	 */

	public function settings_page() {
		if (!current_user_can('manage_options') )
			return;
		?>

        <div class="wrap">
        	<div id="icon-faq-admin" class="icon32"><br /></div>
        	<h2><?php _e('FAQ Manager Settings', 'wpfaq') ?></h2>

			<?php
			if ( isset( $_GET['settings-updated'] ) )
    			echo '<div id="message" class="updated below-h2"><p>'. __('FAQ Manager settings updated successfully.', 'wpfaq').'</p></div>';
			?>


			<div id="poststuff" class="metabox-holder has-right-sidebar">

			<?php
			echo $this->settings_side();
			echo $this->settings_open();
			?>

	            <form method="post" action="options.php">
			    <?php
                settings_fields( 'faq_options' );
				$faq_options	= get_option('faq_options');

				$htype		= (isset($faq_options['htype'])			? 'choice'					: 'default'		);
				$paginate	= (isset($faq_options['paginate'])		? $faq_options['paginate']	: 'false'		);
				$expand		= (isset($faq_options['expand'])		? $faq_options['expand']	: 'false'		);
				$exspeed	= (isset($faq_options['exspeed'])		? $faq_options['exspeed']	: '200'			);
				$exlink		= (isset($faq_options['exlink'])		? $faq_options['exlink']	: 'false'		);
				$extext		= (isset($faq_options['extext'])		? $faq_options['extext']	: 'Read More'	);
				$scroll		= (isset($faq_options['scroll'])		? $faq_options['scroll']	: 'false'		);
				$backtop	= (isset($faq_options['backtop'])		? $faq_options['backtop']	: 'false'		);
				$css		= (isset($faq_options['css'])			? $faq_options['css']		: 'false'		);
				$rss		= (isset($faq_options['rss'])			? $faq_options['rss']		: 'false'		);
				$nofilter	= (isset($faq_options['nofilter'])		? $faq_options['nofilter']	: 'false'		);
				$noindex	= (isset($faq_options['noindex'])		? $faq_options['noindex']	: 'false'		);
				$nofollow	= (isset($faq_options['nofollow'])		? $faq_options['nofollow']	: 'false'		);
				$noarchive	= (isset($faq_options['noarchive'])		? $faq_options['noarchive']	: 'false'		);
				$archtext	= (isset($faq_options['arch'])			? $faq_options['arch']		: 'questions'	);
				$singletext	= (isset($faq_options['single'])		? $faq_options['single']	: 'question'	);
				$redirect	= (isset($faq_options['redirect'])		? $faq_options['redirect']	: 'false'		);
				$redirectid	= (isset($faq_options['redirectid'])	? $faq_options['redirectid']: 'none'		);

				// Set a message to show next to options that are set but deprecated
				$deprecated_option_message = __( "<br />- <strong>Deprecated</strong>: You only see this option because you previously changed it from it's default state. If you revert it and save, you won't see it again.", 'wp-faq-manager' );
				?>

				<h2 class="inst-title"><?php _e( 'Display Options', 'wpfaq' ) ?></h2>

				<?php if( 'h1' !== $faq_options['htype'] && !empty( $faq_options['htype'] ) ) { ?>
				<p>
					<select class="faq_htype <?php echo $htype; ?>" name="faq_options[htype]" id="faq_htype">
		            <option value="h1" <?php selected( $faq_options['htype'], 'h1' ); ?>>H1</option>
					<option value="h2" <?php selected( $faq_options['htype'], 'h2' ); ?>>H2</option>
					<option value="h3" <?php selected( $faq_options['htype'], 'h3' ); ?>>H3</option>
					<option value="h4" <?php selected( $faq_options['htype'], 'h4' ); ?>>H4</option>
					<option value="h5" <?php selected( $faq_options['htype'], 'h5' ); ?>>H5</option>
					<option value="h6" <?php selected( $faq_options['htype'], 'h6' ); ?>>H6</option>
					</select>
					<label type="select" for="faq_options[htype]"><?php _e( 'Choose your H type for FAQ title', 'wpfaq' ); ?></label>
					<?php echo $deprecated_option_message; ?>
				</p>
				<?php } ?>

				<p>
			    	<input type="checkbox" name="faq_options[paginate]" id="faq_paginate" value="true" <?php checked( $paginate, 'true' ); ?> />
    				<label for="faq_options[paginate]" rel="checkbox"><?php _e( 'Paginate shortcode output', 'wpfaq' ); ?></label>
				</p>

				<p>
				    <input type="checkbox" name="faq_options[expand]" id="faq_expand" value="true" <?php checked( $expand, 'true' ); ?> />
				    <label for="faq_options[expand]" rel="checkbox"><?php _e('Include jQuery collapse / expand', 'wpfaq'); ?></label>
				</p>

				<div class="secondary-option" style="display:none;">

				<p class="speedshow">
					<input type="text" name="faq_options[exspeed]" id="faq_exspeed" size="20" class="small-text" value="<?php echo sanitize_title($exspeed); ?>" />
					<label for="faq_options[exspeed]"><?php _e('Expand / collapse speed <em><small>(in milliseconds, i.e. 200 or 1000)</small></em>', 'wpfaq'); ?></label>
				</p>

				<p class="expandlink">
				    <input type="checkbox" name="faq_options[exlink]" id="faq_exlink" value="true" <?php checked( $exlink, 'true' ); ?> />
				    <label for="faq_options[exlink]" rel="checkbox"><?php _e('Include permalink beneath expanded text.', 'wpfaq'); ?></label>
				</p>

				<p class="extext" style="display:none;">
					<input type="text" name="faq_options[extext]" id="faq_extext" size="20" value="<?php echo esc_attr($extext); ?>" />
					<label for="faq_options[extext]"><?php _e('Permalink "read more" text', 'wpfaq'); ?></label>
				</p>

				</div>

				<p class="scroll">
				    <input type="checkbox" name="faq_options[scroll]" id="faq_scroll" value="true" <?php checked( $scroll, 'true' ); ?> />
				    <label for="faq_options[scroll]" rel="checkbox"><?php _e('Include jQuery scrolling for Combo shortcode', 'wpfaq'); ?></label>
				</p>

				<p class="scrolltop secondary-option" style="display:none;">
				    <input type="checkbox" name="faq_options[backtop]" id="faq_backtop" value="true" <?php checked( $backtop, 'true' ); ?> />
				    <label for="faq_options[backtop]" rel="checkbox"><?php _e('Include a "back to top" link below each FAQ', 'wpfaq'); ?></label>
				</p>

				<p>
				    <input type="checkbox" name="faq_options[css]" id="faq_css" value="true" <?php checked( $css, 'true' ); ?> />
				    <label for="faq_options[css]" rel="checkbox"><?php _e('Load default CSS', 'wpfaq'); ?></label>
				</p>

				<h2 class="inst-title"><?php _e('Content Options') ?></h2>

				<p>
				    <input type="checkbox" name="faq_options[nofilter]" id="faq_nofilter" value="true" <?php checked( $nofilter, 'true' ); ?> />
				    <label for="faq_options[nofilter]" rel="checkbox"><?php _e('Disable content filter on shortcode output <em><small>(Use when certain plugins add sharing buttons, etc)</small></em>', 'wpfaq'); ?></label>
				</p>

				<?php if( 'true' === $rss ) { ?>
				<p>
				    <input type="checkbox" name="faq_options[rss]" id="faq_rss" value="true" <?php checked( $rss, 'true' ); ?> />
				    <label for="faq_options[rss]" rel="checkbox"><?php _e('Include FAQs in main RSS feed <em><small>(Use with caution, as this will remove all non-posts from the native RSS feed)</small></em>', 'wpfaq'); ?></label>
				    <?php echo $deprecated_option_message; ?>
				</p>
				<?php } ?>

				<p class="redirect">
				    <input type="checkbox" name="faq_options[redirect]" id="faq_redirect" value="true" <?php checked( $redirect, 'true' ); ?> />
				    <label for="faq_options[redirect]" rel="checkbox"> <?php _e('Redirect all FAQ archive and single posts to a single FAQ page', 'wpfaq'); ?></label>
				</p>

				<p class="redirectid" style="display:none;">
					<select class="faq_redirectid" name="faq_options[redirectid]" id="faq_redirectid">

		            <option value="none" <?php selected( $redirectid, 'none' ); ?>>(Select)  </option>
		            <?php
		            $pages = $this->redirect_pages();
					foreach ( $pages as $page ) {
						$page_id = $page->ID;

						$option = '<option value="' . $page_id . '" '.selected( $faq_options['redirectid'], $page_id ).'>';
						$option .= $page->post_title;
						$option .= '</option>';

						echo $option;

  					}
		            ?>
					</select>

					<label type="select" for="faq_options[redirectid]"><?php _e('Select the page to redirect', 'wpfaq'); ?></label>
				</p>

				<h2 class="inst-title"><?php _e('SEO Options') ?></h2>

				<?php if( 'true' === $noindex ) { ?>
				<p>
				    <input type="checkbox" name="faq_options[noindex]" id="faq_noindex" value="true" <?php checked( $noindex, 'true' ); ?> />
				    <label for="faq_options[noindex]" rel="checkbox"> <?php _e('Apply <code>noindex</code> header tag to FAQs', 'wpfaq'); ?></label>
				    <?php echo $deprecated_option_message; ?>
				</p>
				<?php } ?>

				<?php if( 'true' === $nofollow ) { ?>
				<p>
				    <input type="checkbox" name="faq_options[nofollow]" id="faq_nofollow" value="true" <?php checked( $nofollow, 'true' ); ?> />
				    <label for="faq_options[nofollow]" rel="checkbox"> <?php _e('Apply <code>nofollow</code> header tag to FAQs', 'wpfaq'); ?></label>
				    <?php echo $deprecated_option_message; ?>
				</p>
				<?php } ?>

				<?php if( 'true' === $noarchive ) { ?>
				<p>
				    <input type="checkbox" name="faq_options[noarchive]" id="faq_noarchive" value="true" <?php checked( $noarchive, 'true' ); ?> />
				    <label for="faq_options[noarchive]" rel="checkbox"> <?php _e('Apply <code>noarchive</code> header tag to FAQs', 'wpfaq'); ?></label>
				    <?php echo $deprecated_option_message; ?>
				</p>
				<?php } ?>

				<p>
					<input type="text" name="faq_options[single]" id="faq_single" size="20" value="<?php echo sanitize_title($singletext); ?>" />
					<label for="faq_options[single]"><?php _e('Desired slug for single FAQs <em><small>(all lower case, no capitals or spaces)</small></em>', 'wpfaq'); ?></label>
				</p>

				<p>
					<input type="text" name="faq_options[arch]" id="faq_arch" size="20" value="<?php echo sanitize_title($archtext); ?>" />
					<label for="faq_options[arch]"><?php _e('Desired slug for FAQ archive page <em><small>(all lower case, no capitals or spaces)</small></em>', 'wpfaq'); ?></label>
				</p>


    			<!-- submit -->
	    		<p id="faq-submit" class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>

				<p id="faq-desc" class="description"><?php _e('<strong>Note:</strong> You may need to flush your permalinks after changing settings.', 'wpfaq'); ?> <a href="<?php echo admin_url( 'options-permalink.php'); ?>"><?php _e('Go to your Permalink Settings here', 'wpfaq'); ?></a></p>

				</form>

	<?php echo $this->settings_close(); ?>

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
        	<h2><?php _e('FAQ Instructions', 'wpfaq'); ?></h2>
			<div id="poststuff" class="metabox-holder has-right-sidebar">

			<?php
			echo $this->settings_side();
			echo $this->settings_open();
			?>
			<p><?php _e('The FAQ Manager plugin uses a combination of custom post types, meta fields, and taxonomies. The plugin will automatically create single posts using your existing permalink structure. And the FAQ categories and tags can be added to your menu using the WP Menu Manager', 'wpfaq'); ?></p>

			<h2 class="inst-title"><?php _e('Shortcodes', 'wpfaq'); ?></h2>
			<p><?php _e('The plugin also has the option of using shortcodes. To use them, follow the syntax accordingly in the HTML tab:', 'wpfaq'); ?></p>
			<ul class="faqinfo">
			<li><strong><?php _e('For the complete list (including title and content):', 'wpfaq'); ?></strong></li>
			<li><?php _e('place <code>[faq]</code> on a post / page', 'wpfaq'); ?></li><br />
			<li><strong><?php _e('For the question title, and a link to the FAQ on a separate page:', 'wpfaq'); ?></strong></li>
			<li><?php _e('place <code>[faqlist]</code> on a post / page', 'wpfaq'); ?></li><br />
			<li><strong><?php _e('For a list with a group of titles that link to complete content later in page:', 'wpfaq'); ?></strong></li>
			<li><?php _e('place <code>[faqcombo]</code> on a post / page', 'wpfaq'); ?></li><br />
			<li><strong><?php _e('For a list of taxonomy titles that link to the related archive page:', 'wpfaq'); ?></strong></li>
			<li><?php _e('place <code>[faqtaxlist type="topics"]</code> or <code>[faqtaxlist type="tags"]</code> on a post / page', 'wpfaq'); ?></li>
			<li><?php _e('Show optional description: <code>[faqtaxlist type="topics" desc="true"]</code>', 'wpfaq'); ?></li><br />
			<li><?php _e('<em><strong>Please note:</strong> the combo and taxonomy list shortcodes will not recognize the pagination and expand / collapse</em>', 'wpfaq'); ?></li><br />
			</ul>

			<h2 class="inst-title"><?php _e('The following options apply to all the <code>shortcode</code> types', 'wpfaq'); ?></h2>

			<p><?php _e('The list will show 10 FAQs based on your sorting (if none has been done, it will be in date order).', 'wpfaq'); ?></p>

			<ul class="faqinfo">
			<li><strong><?php _e('To display only 5:', 'wpfaq'); ?></strong></li>
			<li><?php _e('place <code>[faq limit="5"]</code> on a post / page', 'wpfaq'); ?></li><br />
			<li><strong><?php _e('To display ALL:', 'wpfaq'); ?></strong></li>
			<li><?php _e('place <code>[faq limit="-1"]</code> on a post / page', 'wpfaq'); ?></li><br />
			</ul>

			<ul class="faqinfo">
			<li><strong><?php _e('For a single FAQ:', 'wpfaq'); ?></strong></li>
			<li><?php _e('place <code>[faq faq_id="ID"]</code> on a post / page', 'wpfaq'); ?></li><br />
			<li><strong><?php _e('List all from a single FAQ topic category:', 'wpfaq'); ?></strong></li>
			<li><?php _e('place <code>[faq faq_topic="topic-slug"]</code> on a post / page', 'wpfaq'); ?></li><br />
			<li><strong><?php _e('List all from a single FAQ tag:', 'wpfaq'); ?></strong></li>
			<li><?php _e('place <code>[faq faq_tag="tag-slug"]</code> on a post / page', 'wpfaq'); ?></li><br />
			</ul>

			<p><strong><em><?php _e('Please note that the shortcode cannot handle a query of multiple categories / topics in a single shortcode. However, you can stack them as such:', 'wpfaq'); ?></em></strong></p>
			<p><?php _e('...content....', 'wpfaq'); ?></p>
			<p class="indent"><code><?php _e('[faq faq_topic="topic-slug-one"]', 'wpfaq'); ?></code></p>
			<p><?php _e('...more content....', 'wpfaq'); ?></p>
			<p class="indent"><code><?php _e('[faq faq_topic="topic-slug-two"]', 'wpfaq'); ?></code></p>
			<p><?php _e('...even more content....', 'wpfaq'); ?></p>

			<h2 class="inst-title"><?php _e( 'Available Filters', 'wpfaq' ); ?></h2>
			<p><?php _e( 'There are a few different filters that you can use to manipulate the output from your shortcodes. Currently, they are:' ); ?></p>

			<ul>
				<li>
					<h3>wp_faq_title_html( $html_title, $title_data )</h3>
					<ul class="faqinfo">
						<li>
							<strong><?php _e( '$html_title', 'wpfaq' ); ?></strong>
							<ul class="faqinfo">
								<li>(string) The unaltered HTML title</li>
							</ul>
						</li>
						<li>
							<strong><?php _e( '$title_data', 'wpfaq' ); ?></strong>
							<ul class="faqinfo">
								<li>
									(array) All of the data that is used to generate the HTML title. This provides you with everything you need to recreate it, or pieces of it.
									<ul class="faqinfo">
										<li><strong>context</strong> - Which shortcode is calling this (main, combo-link, combo-answer, list)</li>
										<li><strong>title</strong> - The raw title</li>
										<li><strong>slug</strong> - The raw slug</li>
										<li><strong>class</strong> - The classes that were going to be applied</li>
									</ul>
								</li>
							</ul>
						</li>
						<li>
							<h4><?php _e( 'Example Implementation:', 'wpfaq' ); ?></h4>
							<ul class="faqinfo">
								<li><pre style="overflow: scroll"><code><?php echo htmlspecialchars("/**
 * Example use case of wp_faq_title_html
 */
add_filter( 'wp_faq_title_html', 'custom_faq_title', 10, 2);
function custom_faq_title( \$html, \$title_data ) {
	// customize title for the [faq] shortcode
	if( 'main' === \$title_data['context'] ) {
		return '<small id=\"' . \$title_data['slug'] . '\" class=\"' . \$title_data['class'] . ' custom-class\">' . \$title_data['title'] . '</small>';
	} else {
		return \$html;
	}
}"); ?></code></pre></li>
							</ul>
						</li>
					</ul>
				</li>
			</ul>

			<ul>
				<li>
					<h3>wp_faq_read_more_html( $html_read_more, $data )</h3>
					<ul class="faqinfo">
						<li>
							<strong><?php _e( '$html_read_more', 'wpfaq' ); ?></strong>
							<ul class="faqinfo">
								<li>(string) The unaltered HTML read more link</li>
							</ul>
						</li>
						<li>
							<strong><?php _e( '$read_more_data', 'wpfaq' ); ?></strong>
							<ul class="faqinfo">
								<li>
									(array) All of the data that is used to generate the HTML read more link. This provides you with everything you need to recreate it, or pieces of it.
									<ul class="faqinfo">
										<li><strong>link</strong> - The <code>href</code> of the link</li>
										<li><strong>title</strong> - The raw title</li>
										<li><strong>text</strong> - The 'Read More' custom text you set in the Settings page</li>
										<li><strong>class</strong> - The classes that were going to be applied</li>
									</ul>
								</li>
							</ul>
						</li>
						<li>
							<h4><?php _e( 'Example Implementation:' ); ?></h4>
							<ul class="faqinfo">
								<li><pre style="overflow: scroll"><code><?php echo htmlspecialchars("/**
 * Example use case of wp_faq_read_more_html
 */
add_filter( 'wp_faq_read_more_html', 'custom_faq_read_more', 10, 2 );
function custom_faq_read_more( \$html, \$read_more_data ) {
	// customize read more
	return '<div class=\"read-more\"><a href=\"' . \$read_more_data['link'] . '\" title=\"' . \$read_more_data['title'] . '\" class=\"' . \$read_more_data['class'] . '\">' . \$read_more_data['text'] . '</a></div>';
}"); ?></code></pre></li>
							</ul>
						</li>
					</ul>
				</li>
			</ul>

			<h2 class="inst-title"><?php _e( 'Available Hooks', 'wpfaq' ); ?></h2>
			<p><?php _e( 'There are a few different hooks that you can use to add to the output from your shortcodes. Currently, they are:' ); ?></p>

			<ul>
				<li>
					<h3>load_wp_faqs( $context )</h3>
					<ul class="faqinfo">
						<li>
							<strong><?php _e( '$context', 'wpfaq' ); ?></strong>
							<ul class="faqinfo">
								<li>(string) Which shortcode loaded FAQs in (main || combo)</li>
							</ul>
						</li>
						<li>
							<h4><?php _e( 'Example Implementation:', 'wpfaq' ); ?></h4>
							<ul class="faqinfo">
								<li><pre style="overflow: scroll"><code><?php echo htmlspecialchars("/**
 * Example use of load_wp_faqs hook
 */
add_action( 'load_wp_faqs', 'custom_faq_loaded', 10, 1 );
function custom_faq_loaded( \$context ) {
	var_dump( \$context );
}"); ?></code></pre></li>
							</ul>
						</li>
					</ul>
				</li>
			</ul>

	<?php echo $this->settings_close(); ?>

	</div>
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
		<div id="faq-admin-sort" class="wrap">
		<div id="icon-faq-admin" class="icon32"><br /></div>
		<h2><?php _e('Sort FAQs', 'wpfaq'); ?> <img src=" <?php echo admin_url(); ?>/images/loading.gif" id="loading-animation" /></h2>
			<?php if ( $questions->have_posts() ) : ?>
	    	<p><?php _e('<strong>Note:</strong> this only affects the FAQs listed using the shortcode functions', 'wpfaq'); ?></p>
			<ul id="custom-type-list">
				<?php while ( $questions->have_posts() ) : $questions->the_post(); ?>
					<li id="<?php the_id(); ?>"><?php the_title(); ?></li>
				<?php endwhile; ?>
	    	</ul>
			<?php else: ?>
			<p><?php _e('You have no FAQs to sort.', 'wpfaq'); ?></p>
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
	 * build out post type and taxonomies
	 *
	 * @return WP_FAQ_Manager
	 */

	public function _register_faq() {

		// get options from settings page
		$faqopts	= get_option('faq_options');
		$single		= (isset($faqopts['single'])	? sanitize_title($faqopts['single'])	: 'question'	);
		$arch		= (isset($faqopts['arch'])		? sanitize_title($faqopts['arch'])		: 'questions'	);

		$args = array(
			'labels'                  => array(
				'name'                => __( 'FAQs', 'wpfaq' ),
				'singular_name'       => __( 'FAQ', 'wpfaq' ),
				'add_new'             => __( 'Add New FAQ', 'wpfaq' ),
				'add_new_item'        => __( 'Add New FAQ', 'wpfaq' ),
				'edit'                => __( 'Edit', 'wpfaq' ),
				'edit_item'           => __( 'Edit FAQ', 'wpfaq' ),
				'new_item'            => __( 'New FAQ', 'wpfaq' ),
				'view'                => __( 'View FAQ', 'wpfaq' ),
				'view_item'           => __( 'View FAQ', 'wpfaq' ),
				'search_items'        => __( 'Search FAQ', 'wpfaq' ),
				'not_found'           => __( 'No FAQs found', 'wpfaq' ),
				'not_found_in_trash'  => __( 'No FAQs found in Trash', 'wpfaq' ),
			),
			'public'                  => true,
				'show_in_nav_menus'   => true,
				'show_ui'             => true,
				'publicly_queryable'  => true,
				'exclude_from_search' => false,
			'hierarchical'            => false,
			'menu_position'           => 20,
			'capability_type'         => 'post',
			'menu_icon'               => plugins_url( '/inc/img/faq_menu.png', __FILE__ ),
			'query_var'               => true,
			'rewrite'                 => array( 'slug' => $single, 'with_front' => false ),
			'has_archive'             => $arch,
			'supports'                => array('title', 'editor', 'author', 'thumbnail', 'comments', 'custom-fields'),
		);

		register_post_type( 'question', apply_filters( 'wpfaq_question_post_args', $args ) );

		// register topics (categories) for FAQs
		$args = array(
			'public'                => true,
			'show_in_nav_menus'     => true,
			'show_ui'               => true,
			'publicly_queryable'    => true,
			'show_admin_column'     => true,
			'exclude_from_search'   => false,
			'rewrite'               => array( 'slug' => 'topics', 'with_front' => true ),
			'hierarchical'          => true,
			'query_var'             => true,
			'labels'                => array(
				'name'              => __( 'FAQ Topics', 'wpfaq' ),
				'singular_name'     => __( 'FAQ Topic', 'wpfaq' ),
				'search_items'      => __( 'Search FAQ Topics', 'wpfaq' ),
				'popular_items'     => __( 'Popular FAQ Topics', 'wpfaq' ),
				'all_items'         => __( 'All FAQ Topics', 'wpfaq' ),
				'parent_item'       => __( 'Parent FAQ Topic', 'wpfaq' ),
				'parent_item_colon' => __( 'Parent FAQ Topic:', 'wpfaq' ),
				'edit_item'         => __( 'Edit FAQ Topics', 'wpfaq' ),
				'update_item'       => __( 'Update FAQ Topics', 'wpfaq' ),
				'add_new_item'      => __( 'Add New FAQ Topics', 'wpfaq' ),
				'new_item_name'     => __( 'New FAQ Topics', 'wpfaq' ),
			),
		);

		register_taxonomy(
			'faq-topic',
			array( 'question' ),
			apply_filters( 'wpfaq_topic_taxonomy_args', $args )
		);

		// register tags for FAQs
		$args = array(
			'public'                => true,
			'show_in_nav_menus'     => true,
			'show_ui'               => true,
			'publicly_queryable'    => true,
			'show_admin_column'     => true,
			'exclude_from_search'   => false,
			'rewrite'               => array( 'slug' => 'faq-tags', 'with_front' => true ),
			'hierarchical'          => false,
			'query_var'             => true,
			'labels'                => array(
				'name'              => __( 'FAQ Tags', 'wpfaq' ),
				'singular_name'     => __( 'FAQ Tag', 'wpfaq' ),
				'search_items'      => __( 'Search FAQ Tags', 'wpfaq' ),
				'popular_items'     => __( 'Popular FAQ Tags', 'wpfaq' ),
				'all_items'         => __( 'All FAQ Tags', 'wpfaq' ),
				'parent_item'       => __( 'Parent FAQ Tags', 'wpfaq' ),
				'parent_item_colon' => __( 'Parent FAQ Tag:', 'wpfaq' ),
				'edit_item'         => __( 'Edit FAQ Tag', 'wpfaq' ),
				'update_item'       => __( 'Update FAQ Tag', 'wpfaq' ),
				'add_new_item'      => __( 'Add New FAQ Tag', 'wpfaq' ),
				'new_item_name'     => __( 'New FAQ Tag', 'wpfaq' ),
			),
		);

		register_taxonomy(
			'faq-tags',
			array( 'question' ),
			apply_filters( 'wpfaq_tags_taxonomy_args', $args )
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
/* /// removed until I can determine how to check for any other customizations to the RSS
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
*/
			$query->set( 'post_type' , array( 'post', 'question' ) );

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
			$title = __('Enter Question Title Here', 'wpfaq');;
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

	public function admin_scripts($hook) {

		$screen = get_current_screen();

		if ( is_object($screen) && 'question' == $screen->post_type ) :

			wp_enqueue_style( 'faq-admin', plugins_url('/inc/css/faq-admin.css', __FILE__), array(), FAQ_VER, 'all' );

		endif;

		if ( $hook == 'question_page_faq-manager' ||
			 $hook == 'question_page_faq-options' ||
			 $hook == 'question_page_faq-instructions'
			 ) :

			wp_enqueue_style( 'faq-admin', plugins_url('/inc/css/faq-admin.css', __FILE__), array(), FAQ_VER, 'all' );

			wp_enqueue_script('jquery-ui-sortable');
			wp_enqueue_script( 'faq-admin', plugins_url('/inc/js/faq.admin.init.js', __FILE__) , array('jquery'), FAQ_VER, true );

		endif;



	}

	/**
	 * load scripts and styles for front end
	 *
	 * @return WP_FAQ_Manager
	 */

	public function front_style() {

		wp_enqueue_style( 'faq-style', plugins_url('/inc/css/faq-style.css', __FILE__), array(), FAQ_VER, 'all' );

	}

	public function front_script() {

		wp_enqueue_script( 'faq-init', plugins_url('/inc/js/faq.init.js', __FILE__) , array('jquery'), FAQ_VER, true );

	}

	public function scroll_script() {

		wp_enqueue_script( 'faq-scroll', plugins_url('/inc/js/faq.scroll.js', __FILE__) , array('jquery'), FAQ_VER, true );

	}

    /**
     * Some extra stuff for the settings page
     *
     * this is just to keep the area cleaner
     *
     * @return WP_FAQ_Manager
     */

    public function settings_side() { ?>

		<div id="side-info-column" class="inner-sidebar">
			<div class="meta-box-sortables">
				<div id="faq-admin-about" class="postbox">
					<h3 class="hndle" id="about-sidebar"><?php _e('About the Plugin', 'wpfaq'); ?></h3>
					<div class="inside">
						<p><?php _e('Talk to') ?> <a href="http://twitter.com/norcross" target="_blank">@norcross</a> <?php _e('on twitter or visit the', 'wpfaq'); ?> <a href="http://wordpress.org/support/plugin/wordpress-faq-manager/" target="_blank"><?php _e('plugin support form') ?></a> <?php _e('for bugs or feature requests.', 'wpfaq'); ?></p>
						<p><?php _e('<strong>Enjoy the plugin?</strong>', 'wpfaq'); ?><br />
						<a href="http://twitter.com/?status=I'm using @norcross's WordPress FAQ Manager plugin - check it out! http://l.norc.co/wpfaq/" target="_blank"><?php _e('Tweet about it', 'wpfaq'); ?></a> <?php _e('and consider donating.', 'wpfaq'); ?></p>
						<p><?php _e('<strong>Donate:</strong> A lot of hard work goes into building plugins - support your open source developers. Include your twitter username and I\'ll send you a shout out for your generosity. Thank you!', 'wpfaq'); ?><br />
						<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
						<input type="hidden" name="cmd" value="_s-xclick">
						<input type="hidden" name="hosted_button_id" value="11085100">
						<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
						<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
						</form></p>
					</div>
				</div>
			</div>

			<div class="meta-box-sortables">
				<div id="faq-admin-more" class="postbox">
					<h3 class="hndle" id="about-sidebar"><?php _e('Links', 'wpfaq'); ?></h3>
					<div class="inside">
						<ul>
						<li><a href="http://wordpress.org/extend/plugins/wordpress-faq-manager/" target="_blank"><?php _e('Plugin on WP.org', 'wpfaq'); ?></a></li>
						<li><a href="https://github.com/norcross/WordPress-FAQ-Manager" target="_blank"><?php _e('Plugin on GitHub', 'wpfaq'); ?></a></li>
						<li><a href="http://wordpress.org/support/plugin/wordpress-faq-manager" target="_blank"><?php _e('Support Forum', 'wpfaq'); ?></a><li>
            			<li><a href="<?php echo menu_page_url( 'faq-instructions', 0 ); ?>"><?php _e('Instructions page', 'wpfaq'); ?></a></li>
            			</ul>
					</div>
				</div>
			</div>
		</div> <!-- // #side-info-column .inner-sidebar -->

    <?php }

	public function settings_open() { ?>

		<div id="post-body" class="has-sidebar">
			<div id="post-body-content" class="has-sidebar-content">
				<div id="normal-sortables" class="meta-box-sortables">
					<div id="about" class="postbox">
						<div class="inside">

    <?php }

	public function settings_close() { ?>

						<br class="clear" />
						</div>
					</div>
				</div>
			</div>
		</div>

    <?php }

/// end class
}


// Instantiate our class
$WP_FAQ_Manager = new WP_FAQ_Manager();


