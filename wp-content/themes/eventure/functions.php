<?php
////////////////////////
// Localization Support
////////////////////////
load_theme_textdomain( 'themolitor', get_template_directory().'/languages' );
$locale = get_locale();
$locale_file = get_template_directory().'/languages/$locale.php';
if ( is_readable($locale_file) )
    require_once($locale_file);
    
////////////////////////
//CONTENT WIDTH
////////////////////////
if ( ! isset( $content_width ) ) $content_width = 600;

////////////////////////
//BACKGROUND
////////////////////////
add_theme_support( 'custom-background');

////////////////////////
//EXCERPT STUFF
////////////////////////
function new_excerpt_length($length) {
	return 20;
}
add_filter('excerpt_length', 'new_excerpt_length');
function new_excerpt_more($more) {
       global $post;
	return ' ... <a href="'. get_permalink($post->ID) . '">' . 'Continue &rarr;' . '</a>';
}
add_filter('excerpt_more', 'new_excerpt_more');

////////////////////////
//IMAGE ATTACHMENTS TOOLBOX
////////////////////////
function attachment_toolbox($size = thumbnail) {

	if($images = get_children(array(
		'post_parent'    => get_the_ID(),
		'post_type'      => 'attachment',
		'numberposts'    => -1, // show all
		'post_status'    => null,
		'post_mime_type' => 'image',
		'orderby' => 'menu_order'
	))) {
		foreach($images as $image) {
			$attimg   = wp_get_attachment_image($image->ID,$size);
			$atturl   = wp_get_attachment_url($image->ID);
			$attlink  = get_attachment_link($image->ID);
			$postlink = get_permalink($image->post_parent);
			$atttitle = apply_filters('the_title',$image->post_title);
			echo'<li class="wrapperli"><a rel="prettyPhoto[pp_gal]" href="'.$atturl.'">'.$attimg.'</a></li>';
		}
	}
}

////////////////////////
//FEED LINKS
////////////////////////
add_theme_support('automatic-feed-links' );

////////////////////////
//WELCOME TO THE FUTURE
////////////////////////
remove_action('future_post', '_future_post_hook');
add_filter( 'wp_insert_post_data', 'nacin_do_not_set_posts_to_future' );
function nacin_do_not_set_posts_to_future( $data ) {
    if ( $data['post_status'] == 'future' && $data['post_type'] == 'post' )
        $data['post_status'] = 'publish';
    return $data;
}

////////////////////////
//FEATURED IMAGE SUPPORT
////////////////////////
add_theme_support( 'post-thumbnails', array( 'post','page' ) );
set_post_thumbnail_size( 600, 350, true );
add_image_size( 'slider',900 ,350, true );
add_image_size( 'post',900 ,9999, true );
add_image_size( 'small',50 ,50, true );

////////////////////////
//CATEGORY ID FROM NAME FOR PAGE TEMPLATES
////////////////////////
function get_category_id($cat_name){
	$term = get_term_by('name', $cat_name, 'category');
	return $term->term_id;
}

////////////////////////
//ADD MENU SUPPORT
////////////////////////
add_theme_support( 'menus' );
register_nav_menu('main', __('Main Navigation Menu','themolitor'));

////////////////////////
//BREADCRUMBS
////////////////////////
function dimox_breadcrumbs() {
  $delimiter = '&nbsp;/&nbsp;';
  $name = __('Home','themolitor');
  $currentBefore = '<span class="current">';
  $currentAfter = '</span>';
  if ( !is_home() && !is_front_page() || is_paged() ) {
    echo '<div id="crumbs">';
    global $post;
    $home = home_url();
    echo '<a href="' . $home . '">' . $name . '</a> ' . $delimiter . ' ';
    if ( is_category() ) {
      global $wp_query;
      $cat_obj = $wp_query->get_queried_object();
      $thisCat = $cat_obj->term_id;
      $thisCat = get_category($thisCat);
      $parentCat = get_category($thisCat->parent);
      if ($thisCat->parent != 0) echo(get_category_parents($parentCat, TRUE, ' ' . $delimiter . ' '));
      echo $currentBefore . '';
      single_cat_title();
      echo '' . $currentAfter;
    } elseif ( is_day() ) {
      echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
      echo '<a href="' . get_month_link(get_the_time('Y'),get_the_time('m')) . '">' . get_the_time('F') . '</a> ' . $delimiter . ' ';
      echo $currentBefore . get_the_time('d') . $currentAfter;
    } elseif ( is_month() ) {
      echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
      echo $currentBefore . get_the_time('F') . $currentAfter;
    } elseif ( is_year() ) {
      echo $currentBefore . get_the_time('Y') . $currentAfter;
    } elseif ( is_single() && !is_attachment() ) {
      $cat = get_the_category(); $cat = $cat[0];
      echo get_category_parents($cat, TRUE, ' ' . $delimiter . ' ');
      echo $currentBefore;
      _e("Current Page",'themolitor');
      echo $currentAfter;
    } elseif ( is_attachment() ) {
      $parent = get_post($post->post_parent);
      $cat = get_the_category($parent->ID); $cat = $cat[0];
      echo get_category_parents($cat, TRUE, ' ' . $delimiter . ' ');
      echo '<a href="' . get_permalink($parent) . '">' . $parent->post_title . '</a> ' . $delimiter . ' ';
      echo $currentBefore;
      the_title();
      echo $currentAfter;
    } elseif ( is_page() && !$post->post_parent ) {
      echo $currentBefore;
      _e("Current Page",'themolitor');
      echo $currentAfter;
    } elseif ( is_page() && $post->post_parent ) {
      $parent_id  = $post->post_parent;
      $breadcrumbs = array();
      while ($parent_id) {
        $page = get_page($parent_id);
        $breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
        $parent_id  = $page->post_parent;
      }
      $breadcrumbs = array_reverse($breadcrumbs);
      foreach ($breadcrumbs as $crumb) echo $crumb . ' ' . $delimiter . ' ';
      echo $currentBefore;
      _e("Current Page",'themolitor');
      echo $currentAfter;
    } elseif ( is_search() ) {
      echo $currentBefore . __('Search Results','themolitor') . $currentAfter;
    } elseif ( is_tag() ) {
      echo $currentBefore . __('Posts tagged &#39;','themolitor');
      single_tag_title();
      echo '&#39;' . $currentAfter;
    } elseif ( is_author() ) {
       global $author;
      $userdata = get_userdata($author);
      echo $currentBefore . __('Articles posted by ','themolitor') . $userdata->display_name . $currentAfter;
    } elseif ( is_404() ) {
      echo $currentBefore . __('Error 404','themolitor') . $currentAfter;
    }
    if ( get_query_var('paged') ) {
      if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo ' (';
      echo __('Page','themolitor') . ' ' . get_query_var('paged');
      if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo ')';
    }
    echo '</div>';
  }
}

////////////////////////
//SIDEBAR GENERATOR (FOR SIDEBAR AND FOOTER)
////////////////////////
if ( function_exists('register_sidebar') )
register_sidebar(array('name'=>__('Live Widgets','themolitor'),
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widgettitle"><span>',
		'after_title' => '</span></h3>',
));

////////////////////////
//CUSOTM POST OPTIONS
////////////////////////
include(TEMPLATEPATH . '/include/post-meta.php');

////////////////////////////
//THEME CUSTOMIZER MENU ITEM
////////////////////////////
function themolitor_customizer_admin() {
    add_theme_page( __('Theme Options','themolitor'),  __('Theme Options','themolitor'), 'edit_theme_options', 'customize.php' ); 
}
add_action ('admin_menu', 'themolitor_customizer_admin');

////////////////////////////
//THEME CUSTOMIZER SETTINGS
////////////////////////////
add_action( 'customize_register', 'themolitor_customizer_register' );

function themolitor_customizer_register($wp_customize) {

	//CREATE TEXTAREA OPTION
	class Example_Customize_Textarea_Control extends WP_Customize_Control {
    	public $type = 'textarea';
 
    	public function render_content() { ?>
        	<label>
        	<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
        	<textarea rows="5" style="width:100%;" <?php $this->link(); ?>><?php echo esc_textarea( $this->value() ); ?></textarea>
        	</label>
        <?php }
	}
	
	//CREATE CATEGORY DROP DOWN OPTION
	$options_categories = array();  
	$options_categories_obj = get_categories();
	$options_categories[''] = 'Select a Category';
	foreach ($options_categories_obj as $category) {
		$options_categories[$category->cat_ID] = $category->cat_name;
	}
	
	//-------------------------------
	//GENERAL SECTION
	//-------------------------------
	
	//ADD GENERAL SECTION
	$wp_customize->add_section( 'themolitor_customizer_general_section', array(
		'title' => __( 'General', 'themolitor' ),
		'priority' => 1
	));
	
	//LOGO
	$wp_customize->add_setting( 'themolitor_customizer_logo');
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'themolitor_customizer_logo', array(
    	'label'    => __('Logo', 'themolitor'),
    	'section'  => 'themolitor_customizer_general_section',
    	'settings' => 'themolitor_customizer_logo',
    	'priority' => 1
	)));
	
	//WELCOME MESSAGE
    $wp_customize->add_setting( 'themolitor_customizer_welcome',array(
    	'default' => 'Welcome to the future. <a href="http://themolitor.com/shop">eventure</a> is a premier <a href="http://wordpress.org" >WordPress</a> theme built specifically for <a href="http://themes.themolitor.com/eventure/category/events/">events</a>'
    ));
	$wp_customize->add_control('themolitor_customizer_welcome', array(
   		'label'   => __( 'Welcome Message', 'themolitor'),
    	'section' => 'themolitor_customizer_general_section',
    	'settings'   => 'themolitor_customizer_welcome',
    	'type' => 'text',
    	'priority' => 2
	));
	
	//SIDEBAR POSITION
	$wp_customize->add_setting('themolitor_customizer_sidebar_pos', array(
	    'capability'     => 'edit_theme_options',
	    'default'        => 'left'
	));
	$wp_customize->add_control( 'themolitor_customizer_sidebar_pos', array(
 	   	'label'   => __('Sidebar Position','themolitor'),
		'section' => 'themolitor_customizer_general_section',
   	 	'type'    => 'select',
   	 	'choices' => array('left' => 'left','right' => 'Right'),
   	 	'settings' => 'themolitor_customizer_sidebar_pos',
   	 	'priority' => 3
	));

	//DISPLAY SLIDER
	$wp_customize->add_setting( 'themolitor_customizer_slider_onoff', array(
    	'default' => 1
	));
	$wp_customize->add_control( 'themolitor_customizer_slider_onoff', array(
    	'label' => 'Display Slider on Home Page',
    	'type' => 'checkbox',
    	'section' => 'themolitor_customizer_general_section',
    	'settings' => 'themolitor_customizer_slider_onoff',
    	'priority' => 4
	));
	
	//DISPLAY CALENDAR
	$wp_customize->add_setting( 'themolitor_customizer_calendar_onoff', array(
    	'default' => 1
	));
	$wp_customize->add_control( 'themolitor_customizer_calendar_onoff', array(
    	'label' => 'Display Calendar on Home Page',
    	'type' => 'checkbox',
    	'section' => 'themolitor_customizer_general_section',
    	'settings' => 'themolitor_customizer_calendar_onoff',
    	'priority' => 5
	));
	
	//DISPLAY TWO COLUMNS
	$wp_customize->add_setting( 'themolitor_customizer_two_columns', array(
    	'default' => 1
	));
	$wp_customize->add_control( 'themolitor_customizer_two_columns', array(
    	'label' => 'Display Two Columns on Full Width Pages',
    	'type' => 'checkbox',
    	'section' => 'themolitor_customizer_general_section',
    	'settings' => 'themolitor_customizer_two_columns',
    	'priority' => 6
	));
	
	//EVENTS CATEGORY
	$wp_customize->add_setting('themolitor_events_category', array(
	    'capability'     => 'edit_theme_options',
	    'type'           => 'option'
	));
	$wp_customize->add_control( 'themolitor_events_category', array(
 	   'settings' => 'themolitor_events_category',
 	   'label'   => __('Events Category','themolitor'),
   	 	'section' => 'themolitor_customizer_general_section',
   	 	'type'    => 'select',
   	 	'choices' => $options_categories,
   	 	'priority' => 7
	));
	
	//DISPLAY THE END
	$wp_customize->add_setting( 'themolitor_the_end', array(
    	'default' => 1
	));
	$wp_customize->add_control( 'themolitor_the_end', array(
    	'label' => 'Display "The End" Box in Calendar',
    	'type' => 'checkbox',
    	'section' => 'themolitor_customizer_general_section',
    	'settings' => 'themolitor_the_end',
    	'priority' => 8
	));
		
	//FAVICON URL
    $wp_customize->add_setting( 'themolitor_customizer_favicon');
	$wp_customize->add_control('themolitor_customizer_favicon', array(
   		'label'   => __( 'Favicon URL (optional)', 'themolitor'),
    	'section' => 'themolitor_customizer_general_section',
    	'settings'   => 'themolitor_customizer_favicon',
    	'type' => 'text',
    	'priority' => 9
	));
				
	//-------------------------------
	//COLORS SECTION
	//-------------------------------
	
	//LINK COLOR
	$wp_customize->add_setting( 'themolitor_customizer_link_color', array(
		'default' => '#0080e8'
	));
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'themolitor_customizer_link_color', array(
		'label'   => __( 'Link Color', 'themolitor'),
		'section' => 'colors',
		'settings'   => 'themolitor_customizer_link_color'
	)));		
		
	//-------------------------------
	//SLIDER SECTION
	//-------------------------------

	//ADD FOOTER SECTION
	$wp_customize->add_section( 'themolitor_customizer_slider_section', array(
		'title' => __( 'Slider Settings', 'themolitor' ),
		'priority' => 198
	));
	
	//SLIDER CATEGORY
	$wp_customize->add_setting('themolitor_slider_category', array(
	    'capability'     => 'edit_theme_options',
	    'type'           => 'option'
	));
	$wp_customize->add_control( 'themolitor_slider_category', array(
 	   'settings' => 'themolitor_slider_category',
 	   'label'   => __('Slider Category','themolitor'),
   	 	'section' => 'themolitor_customizer_slider_section',
   	 	'type'    => 'select',
   	 	'choices' => $options_categories,
   	 	'priority' => 1
	));
	
	//NUMBER OF ITEMS
    $wp_customize->add_setting( 'themolitor_customizer_slider_number',array(
    	'default' => '10'
    ));
	$wp_customize->add_control('themolitor_customizer_slider_number', array(
   		'label'   => __( 'Number of slides', 'themolitor'),
    	'section' => 'themolitor_customizer_slider_section',
    	'settings'   => 'themolitor_customizer_slider_number',
    	'type' => 'text',
    	'priority' => 2
	));

		
	//-------------------------------
	//FOOTER SECTION
	//-------------------------------

	//ADD FOOTER SECTION
	$wp_customize->add_section( 'themolitor_customizer_footer_section', array(
		'title' => __( 'Footer', 'themolitor' ),
		'priority' => 199
	));
	
	//FOOTER TEXT
    $wp_customize->add_setting( 'themolitor_customizer_footer');
	$wp_customize->add_control('themolitor_customizer_footer', array(
   		'label'   => __( 'Footer Text', 'themolitor'),
    	'section' => 'themolitor_customizer_footer_section',
    	'settings'   => 'themolitor_customizer_footer',
    	'type' => 'text',
    	'priority' => 1
	));
			
	//DISPLAY RSS BUTTON
	$wp_customize->add_setting( 'themolitor_customizer_rss_onoff', array(
    	'default' => 1
	));
	$wp_customize->add_control( 'themolitor_customizer_rss_onoff', array(
    	'label' => 'Display RSS Button',
    	'type' => 'checkbox',
    	'section' => 'themolitor_customizer_footer_section',
    	'settings' => 'themolitor_customizer_rss_onoff',
    	'priority' => 5
	));
	
	//TWITTER
    $wp_customize->add_setting( 'themolitor_customizer_twitter');
	$wp_customize->add_control('themolitor_customizer_twitter', array(
   		'label'   => __( 'Twitter URL', 'themolitor'),
    	'section' => 'themolitor_customizer_footer_section',
    	'settings'   => 'themolitor_customizer_twitter',
    	'type' => 'text',
    	'priority' => 6
	));
	
	//FACEBOOK
    $wp_customize->add_setting( 'themolitor_customizer_facebook');
	$wp_customize->add_control('themolitor_customizer_facebook', array(
   		'label'   => __( 'Facebook URL', 'themolitor'),
    	'section' => 'themolitor_customizer_footer_section',
    	'settings'   => 'themolitor_customizer_facebook',
    	'type' => 'text',
    	'priority' => 7
	));
	
	//FLIKr
    $wp_customize->add_setting( 'themolitor_customizer_flikr');
	$wp_customize->add_control('themolitor_customizer_flikr', array(
   		'label'   => __( 'Flikr URL', 'themolitor'),
    	'section' => 'themolitor_customizer_footer_section',
    	'settings'   => 'themolitor_customizer_flikr',
    	'type' => 'text',
    	'priority' => 8
	));
	
	//MYSPACE
    $wp_customize->add_setting( 'themolitor_customizer_myspace');
	$wp_customize->add_control('themolitor_customizer_myspace', array(
   		'label'   => __( 'MySpace URL', 'themolitor'),
    	'section' => 'themolitor_customizer_footer_section',
    	'settings'   => 'themolitor_customizer_myspace',
    	'type' => 'text',
    	'priority' => 9
	));
	
	//GOOGLE PLUS
    $wp_customize->add_setting( 'themolitor_customizer_gplus');
	$wp_customize->add_control('themolitor_customizer_gplus', array(
   		'label'   => __( 'Google Plus URL', 'themolitor'),
    	'section' => 'themolitor_customizer_footer_section',
    	'settings'   => 'themolitor_customizer_gplus',
    	'type' => 'text',
    	'priority' => 10
	));
	
	//YOUTUBE
    $wp_customize->add_setting( 'themolitor_customizer_youtube');
	$wp_customize->add_control('themolitor_customizer_youtube', array(
   		'label'   => __( 'YouTube URL', 'themolitor'),
    	'section' => 'themolitor_customizer_footer_section',
    	'settings'   => 'themolitor_customizer_youtube',
    	'type' => 'text',
    	'priority' => 11
	));
	
	//MEETUP
    $wp_customize->add_setting( 'themolitor_customizer_meetup');
	$wp_customize->add_control('themolitor_customizer_meetup', array(
   		'label'   => __( 'Meetup URL', 'themolitor'),
    	'section' => 'themolitor_customizer_footer_section',
    	'settings'   => 'themolitor_customizer_meetup',
    	'type' => 'text',
    	'priority' => 12
	));
	
	//VIMEO
    $wp_customize->add_setting( 'themolitor_customizer_vimeo');
	$wp_customize->add_control('themolitor_customizer_vimeo', array(
   		'label'   => __( 'Vimeo URL', 'themolitor'),
    	'section' => 'themolitor_customizer_footer_section',
    	'settings'   => 'themolitor_customizer_vimeo',
    	'type' => 'text',
    	'priority' => 13
	));
	
	//-------------------------------
	//GOOGLE FONT SECTION
	//-------------------------------

	//ADD GOOGLE FONT SECTION
	$wp_customize->add_section( 'themolitor_customizer_googlefont_section', array(
		'title' => __( 'Google Custom Font', 'themolitor' ),
		'priority' => 200
	));
	
	//GOOGLE API
    $wp_customize->add_setting( 'themolitor_customizer_google_api', array(
    	'default' => '<link href="http://fonts.googleapis.com/css?family=Orbitron:400,700" rel="stylesheet"" type="text/css">'
	));
	$wp_customize->add_control('themolitor_customizer_google_api', array(
   		'label'   => __( 'Google Font API Link', 'themolitor'),
    	'section' => 'themolitor_customizer_googlefont_section',
    	'settings'   => 'themolitor_customizer_google_api',
    	'type' => 'text',
    	'priority' => 1
	));
	
	//GOOGLE KEYWORD
    $wp_customize->add_setting( 'themolitor_customizer_google_key', array(
    	'default' => 'orbitron'
	));
	$wp_customize->add_control('themolitor_customizer_google_key', array(
   		'label'   => __( 'Google Font Keyword', 'themolitor'),
    	'section' => 'themolitor_customizer_googlefont_section',
    	'settings'   => 'themolitor_customizer_google_key',
    	'type' => 'text',
    	'priority' => 2
	));
		
	//-------------------------------
	//CUSTOM CSS SECTION
	//-------------------------------
	
	//ADD CSS SECTION
	$wp_customize->add_section( 'themolitor_customizer_custom_css', array(
		'title' => __( 'CSS', 'themolitor' ),
		'priority' => 201
	));
			
	//CUSTOM CSS
    $wp_customize->add_setting( 'themolitor_customizer_css');
	$wp_customize->add_control( new Example_Customize_Textarea_Control( $wp_customize, 'themolitor_customizer_css', array(
   		'label'   => __( 'Custom CSS', 'themolitor'),
    	'section' => 'themolitor_customizer_custom_css',
    	'settings'   => 'themolitor_customizer_css'
	)));
	
	//-------------------------------
	//POST FORM SECTION
	//-------------------------------
	
	//ADD POST FORM SECTION
	$wp_customize->add_section( 'themolitor_customizer_form_section', array(
		'title' => __( 'Front-end Submission Form', 'themolitor' ),
		'priority' => 201
	));
	
	//SEND ADMIN EMAIL NOTICE FOR NEW SUBMISSION
	$wp_customize->add_setting( 'themolitor_send_email', array(
    	'default' => 1
	));
	$wp_customize->add_control( 'themolitor_send_email', array(
    	'label' => 'Send email notice',
    	'type' => 'checkbox',
    	'section' => 'themolitor_customizer_form_section',
    	'settings' => 'themolitor_send_email',
    	'priority' => 1
	));
	
	//EMAIL TO USE
	$adminEmail = get_option('admin_email');
	$wp_customize->add_setting( 'themolitor_alt_email',array(
		'default' => $adminEmail
	));
	$wp_customize->add_control( 'themolitor_alt_email', array(
    	'label' => 'Email notice goes to:',
    	'type' => 'text',
    	'section' => 'themolitor_customizer_form_section',
    	'settings' => 'themolitor_alt_email',
    	'priority' => 2
	));
	
	//REMOVE STUFF
	$wp_customize->remove_section('title_tagline');
	$wp_customize->remove_section('static_front_page');
}
?>