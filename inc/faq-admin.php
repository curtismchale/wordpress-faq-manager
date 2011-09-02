<?php
// settings page and AJAX sorting
add_action('admin_menu' , 'faq_enable_pages'); 

function faq_enable_pages() {
	add_submenu_page('edit.php?post_type=question', 'Sort FAQs', 'Sort FAQs', 'edit_posts', basename(__FILE__), 'faq_sort_questions');
	add_submenu_page('edit.php?post_type=question', 'Settings', 'Settings', 'manage_options', 'faq-options', 'faq_settings_page');
	add_submenu_page('edit.php?post_type=question', 'Instructions', 'Instructions', 'manage_options', 'faq-instructions', 'faq_instructions');
}


function faq_questions_print_styles() {

	global $pagenow;
	if (in_array( $pagenow, array('edit.php' ) ) ) {
		wp_enqueue_style('faq_questions_style', plugins_url( '/css/sortable.css', __FILE__ ), array(), '1.01', 'screen' );
	}
}
add_action( 'admin_print_styles', 'faq_questions_print_styles' );

function faq_questions_print_scripts() {

	global $pagenow;
	if (in_array( $pagenow, array('edit.php' ) ) ) {
		wp_enqueue_script('faq_questions_script', plugins_url('/js/custom-type-sort.js', __FILE__) , array('jquery-ui-sortable'), '1.0' );
	}
}
add_action( 'admin_print_scripts', 'faq_questions_print_scripts' );


function faq_sort_questions() {
	$questions = new WP_Query('post_type=question&posts_per_page=-1&orderby=menu_order&order=DESC');
?>
	<div class="wrap">
	<h3>Sort FAQs <img src="<?php bloginfo('url'); ?>/wp-admin/images/loading.gif" id="loading-animation" /></h3>
    <p><strong>Note:</strong> this only affects the FAQs listed using the shortcode functions</p>
	<ul id="custom-type-list">
	<?php while ( $questions->have_posts() ) : $questions->the_post(); ?>
		<li id="<?php the_id(); ?>"><?php the_title(); ?></li>			
	<?php endwhile; ?>
    </ul>
	</div>

<?php }

function save_faq_questions_order() {
	global $wpdb; // WordPress database class
 
	$order = explode(',', $_POST['order']);
	$counter = 0;
 
	foreach ($order as $question_id) {
		$wpdb->update($wpdb->posts, array( 'menu_order' => $counter ), array( 'ID' => $question_id) );
		$counter++;
	}
	die(1);
}
add_action('wp_ajax_faq_questions_sort', 'save_faq_questions_order');

	//call register settings function
add_action( 'admin_init', 'register_faq_settings' );


function register_faq_settings() {
	//register our settings
	register_setting( 'faq-options-group', 'faq_jquery' );
	register_setting( 'faq-options-group', 'faq_css' );
	register_setting( 'faq-options-group', 'faq_rss' );
	register_setting( 'faq-options-group', 'faq_public' );
	register_setting( 'faq-options-group', 'faq_arch_slug' );
	register_setting( 'faq-options-group', 'faq_htype' );
	register_setting( 'faq-options-group', 'faq_paginate' );
}

function faq_settings_page() {

    //must check that the user has the required capability 
    if (!current_user_can('manage_options'))
    {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }
	?>
<div class="wrap">
    <h2>FAQ Manager Settings</h2>
	<p>Options relating to the FAQ manager</p>
<hr />
<form method="post" action="options.php">
    <?php settings_fields( 'faq-options-group' ); ?>

<p>
			<select class="faq_htype" name="faq_htype" id="faq_htype">
            <option value="h1"<?php if (get_option('faq_htype' ) == 'h1') { echo ' selected="selected"'; } ?>>H1&nbsp;&nbsp;&nbsp;&nbsp;</option>
			<option value="h2"<?php if (get_option('faq_htype' ) == 'h2') { echo ' selected="selected"'; } ?>>H2&nbsp;&nbsp;&nbsp;&nbsp;</option>
			<option value="h3"<?php if (get_option('faq_htype' ) == 'h3') { echo ' selected="selected"'; } ?>>H3&nbsp;&nbsp;&nbsp;&nbsp;</option>
			<option value="h4"<?php if (get_option('faq_htype' ) == 'h4') { echo ' selected="selected"'; } ?>>H4&nbsp;&nbsp;&nbsp;&nbsp;</option>
			<option value="h5"<?php if (get_option('faq_htype' ) == 'h5') { echo ' selected="selected"'; } ?>>H5&nbsp;&nbsp;&nbsp;&nbsp;</option>
			<option value="h6"<?php if (get_option('faq_htype' ) == 'h6') { echo ' selected="selected"'; } ?>>H6&nbsp;&nbsp;&nbsp;&nbsp;</option>
			</select>
<label type="select" for="faq_htype"><?php _e('Choose your H type for FAQ title') ?></label>
</p> 
    
<p>
	<?php if(get_option('faq_paginate')){ $checked = 'checked="checked"'; }else{ $checked = "";} ?>
    <input type="checkbox" name="faq_paginate" id="faq_paginate" value="true" <?php echo $checked; ?> />
    <label for="faq_paginate">Paginate shortcode output</label>
</p>

<p>
	<?php if(get_option('faq_jquery')){ $checked = 'checked="checked"'; }else{ $checked = "";} ?>
    <input type="checkbox" name="faq_jquery" id="faq_jquery" value="true" <?php echo $checked; ?> />
    <label for="faq_jquery">Include jQuery collapse / expand</label>
</p>

<p>
	<?php if(get_option('faq_css')){ $checked = 'checked="checked"'; }else{ $checked = "";} ?>
    <input type="checkbox" name="faq_css" id="faq_css" value="true" <?php echo $checked; ?> />
    <label for="faq_css">Load default CSS</label>
</p>

<p>
	<?php if(get_option('faq_rss')){ $checked = 'checked="checked"'; }else{ $checked = "";} ?>
    <input type="checkbox" name="faq_rss" id="faq_rss" value="true" <?php echo $checked; ?> />
    <label for="faq_rss">Include in main RSS feed</label>
</p>

<p>	<?php if(get_option('faq_public')){ $checked = 'checked="checked"'; }else{ $checked = "";} ?>
    <input type="checkbox" name="faq_public" id="faq_public" value="true" <?php echo $checked; ?> />
    <label for="faq_public">Make individual FAQ entries public</label>
</p>

<p><label for="faq_arch_slug">Desired page slug for archiving (all lower case, no capitals or spaces)</label></p>
<p><input name="faq_arch_slug" id="faq_arch_slug" type="text" size="40" value="<?php echo get_option('faq_arch_slug'); ?>" />&nbsp;<em><small>You may need to flush your permalinks after changing this. Go to Settings &raquo; Permalinks &raquo; and click "save"</small></em></p>

    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>

</form>
</div>

<?php }

// display "saved settings" message

if( isset($_GET['settings-updated']) ) { ?>
    <div id="message" class="updated">
        <p><strong><?php _e('Settings saved.') ?></strong></p>
    </div>
<?php }

// adds default CSS to theme

// Add columns to FAQ list page

add_filter('manage_edit-question_columns', 'add_new_question_columns');

	function add_new_question_columns($question_columns) {
		$new_columns['cb'] = '<input type="checkbox" />';
		$new_columns['title'] = _x('Question', 'column name');
		$new_columns['answers'] = __('Answer');
		$new_columns['faq_cat'] = __('FAQ Topic');
		$new_columns['faq_tags'] = __('FAQ Tags');		
		$new_columns['date'] = _x('Date', 'column name');
 
		return $new_columns;
	}

add_action('manage_posts_custom_column', 'manage_question_columns', 10, 2);
 
	function manage_question_columns($column_name, $id) {
		global $post;
		switch ($column_name) {
 		case 'answers':
			echo faq_editor_excerpt(15);
		        break;
 		case 'faq_cat':
			echo get_the_term_list( $post->ID, 'faq-topic', '', ', ', '');
		        break;
 		case 'faq_tags':
			echo get_the_term_list( $post->ID, 'faq-tags', '', ', ', '');
		        break;
		default:
			break;
		} // end switch
	}


// add admin CSS for FAQ post table
function custom_faq_type_css() {
	// this is my half-assed attempt at internationalization
	$x = ( 'rtl' == get_bloginfo( 'text_direction' ) ) ? 'left' : 'right';

	echo "
	<style type='text/css'>
	.widefat th#id {width:35px;}
	.widefat th#questions {width:200px;}	
	.widefat th#answers {width:400px;}
	ul.faqinfo {margin-left:15px;}
	p.indent {text-indent:10px;}
	p.norcross_donate {margin-top:50px;}
	</style>
	";
}

add_action('admin_head', 'custom_faq_type_css');

// change post title box text

function change_faq_title_text( $title ){
	$screen = get_current_screen();
	if ( 'question' == $screen->post_type ) {
		$title = 'Enter Question Title Here';
	}
	return $title;
}
add_filter( 'enter_title_here', 'change_faq_title_text' );





// This will add *all* custom post types to the RSS feed naturally
function faq_include_rss( $query ) {
	if(get_option('faq_rss') == 'true') {
	if ($query->is_feed) {
		$args = array(
				'public' => true,
				'_builtin' => false
				);
		$output = 'names';
		$operator = 'and';
		$post_types = get_post_types( $args , $output , $operator );
		// remove 'pages' from the RSS
		$post_types = array_merge( $post_types, array('post') ) ;
		$query->set( 'post_type' , $post_types );
		}
	}
	return $query;
}
add_filter( 'pre_get_posts' , 'faq_include_rss' );

//display contextual help for FAQs (will be filled in)
add_action( 'contextual_help', 'add_faqhelp_text', 10, 3 );

function add_faqhelp_text($contextual_help, $screen_id, $screen) { 
  //$contextual_help .= var_dump($screen); // use this to help determine $screen->id
  if ('question' == $screen->id ) {
    $contextual_help =
      '<p>' . __('Things to remember when adding or editing an FAQ:') . '</p>' .
      '<ul>' .
      '<li>' . __('Include both the question and the answer.') . '</li>' .
      '<li>' . __('Use the included taxonomies (FAQ topics and tags) to organize your FAQs and display in your menu.') . '</li>' .
      '</ul>' .
      '<p><strong>' . __('For more information:') . '</strong></p>' .
      '<p>' . __('<a href="http://codex.wordpress.org/Posts_Edit_SubPanel" target="_blank">Edit Posts Documentation</a>') . '</p>' .
      '<p>' . __('<a href="http://wordpress.org/support/" target="_blank">Support Forums</a>') . '</p>' ;
	  
  } elseif ( 'edit-question' == $screen->id ) {
    $contextual_help = 
      '<p>' . __('Things to remember when adding or editing an FAQ:') . '</p>' .
      '<ul>' .
      '<li>' . __('Include both the question and the answer.') . '</li>' .
      '<li>' . __('Use the included taxonomies (FAQ topics and tags) to organize your FAQs and display in your menu.') . '</li>' .
	  '<li>' . __('RTFM here <a href="'.get_bloginfo('wpurl').'/wp-admin/edit.php?post_type=question&page=faq-instructions">Instructions Page</a>') . '</li>' .
	  '<li>' . __('<a href="http://wordpress.org/tags/wordpress-faq-manager" target="_blank">FAQ Manager Support Forum at WordPress.org</a>') . '</li>' ;
	  '</ul>' ;
  }
  return $contextual_help;
}

//add shortened excerpt for widget


	
function faq_instructions() {

    //must check that the user has the required capability 
    if (!current_user_can('manage_options'))
    {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }
	?>
<div class="wrap">
    <h2>FAQInstructions</h2>
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
</ul>
<h3>The following options apply to both the <code>[faq]</code> and <code>[faqlist]</code> shortcodes</h3>

<p>The list will show 10 FAQs based on your sorting (if none has been done, it will be in date order).</p>
<ul class="faqinfo">
<li>To display only 5:</li><br />
<li>place <code>[faq limit="5"]</code> on a post / page</li>
<li>To display ALL:</li><br />
<li>place <code>[faq limit="-1"]</code> on a post / page</li>
</ul>

<ul class="faqinfo">
<li><strong>For a single FAQ:</strong></li>
<li>place <code>[faq faq_id="ID"]</code> on a post / page</li><br />
<li><strong>List all from a single FAQ topic category:</strong></li>
<li>place <code>[faq faq_topic="topic-slug"]</code> on a post / page</li><br />
<li><strong>List all from a single FAQ tag:</strong></li><br />
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
	
?>