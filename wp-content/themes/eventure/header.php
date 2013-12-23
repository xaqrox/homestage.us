<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="initial-scale=1.0,width=device-width" />
<meta name="description" content="A community resource and listing of DIY Events in the Washington, DC area including music, film, art, and salon. Why Do-It-Yourself when we can Do-It-Together?" />
<meta property="og:image" content="https://s3.amazonaws.com/Homestage/images/homestage+og+image+large.jpg" />
<title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>
<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="alternate" type="application/atom+xml" title="<?php bloginfo('name'); ?> Atom Feed" href="<?php bloginfo('atom_url'); ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

<?php
//VAR SETUP
$googleApi = get_theme_mod('themolitor_customizer_google_api');
$googleKeyword = get_theme_mod('themolitor_customizer_google_key');
$eventCat = get_option('themolitor_events_category');
$logo = get_theme_mod('themolitor_customizer_logo');
$linkColor = get_theme_mod('themolitor_customizer_link_color');
$sidebarPosition = get_theme_mod('themolitor_customizer_sidebar_pos');
$twoColumn = get_theme_mod('themolitor_customizer_two_columns');
$customCSS = get_theme_mod('themolitor_customizer_css');
$favicon = get_theme_mod('themolitor_customizer_favicon'); 
$welcomeMsg = get_theme_mod('themolitor_customizer_welcome');
?>

<!--using static favicon 
<?php if($favicon) { ?><link rel="icon" href="<?php echo $favicon; ?>" type="image/x-icon" /><?php } ?>  
-->

<?php if($googleApi) { echo $googleApi; } ?>
<link rel="shortcut icon" href="https://s3.amazonaws.com/Homestage/images/logo+for+favicon.png" />
<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/scripts/prettyPhoto.css" type="text/css" media="screen" />
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/font-awesome/css/font-awesome.min.css">

<style>
<?php if($googleKeyword){?>
body,
#countDown,
#wp-calendar {font-family: '<?php echo $googleKeyword;?>', sans-serif;}
<?php } ?>

/*--SIDEBSR STUFF--*/
<?php if($sidebarPosition == "left"){?>
#sidebar,
#postDetails {float: left;}
#listing, 
body.page .page {float: right;}
body.single .post {
    border-left: 1px dashed #CCCCCC;
    float: right;
    padding: 0 0 40px 35px;
}
<?php } else { ?>
#sidebar,
#postDetails {float: right;}
#listing, 
body.page .page {float: left;}
body.single .post {
    border-right: 1px dashed #CCCCCC;
    float: left;
    padding: 0 35px 40px 0;
}
<?php } ?>

/*--TWO COLUMN STUFF--*/
<?php if($twoColumn){?>
#twoColumns {
	text-align: justify;
	-moz-column-count: 2;
	-webkit-column-count: 2;
	column-count: 2;
	-moz-column-gap: 60px;
	-webkit-column-gap: 60px;
	column-gap:60px;
	-moz-column-rule: 1px dashed #ccc;
	-webkit-column-rule: 1px dashed #ccc;
	column-rule: 1px dashed #ccc;
}
@media screen and (max-width:740px) {
	#twoColumns {
		text-align: inherit;
		-moz-column-count: 1;
		-webkit-column-count: 1;
		column-count: 1;
	}
}
<?php } ?>

/*--FONT COLOR STUFF--*/
#wp-calendar #prev a,
#wp-calendar #next a,
li.activeMonth a.dateLink,
#copyright a:hover,
a {color:<?php echo $linkColor;?>;}

/*--BACKGROUND COLOR STUFF--*/
li.box a:hover,
.dateInfo:hover,
#wp-calendar td a:hover,
#commentform input[type="submit"]:hover,
input[type="submit"]:hover,
#postNav .pagenav a:hover,
#theTags a:hover,
#tagLine a:hover,
.sliderInfo a:hover,
.flex-direction-nav li a:hover,
#dropmenu li a:hover {background-color:<?php echo $linkColor;?>;}

/*--CUSTOM CSS STUFF--*/
<?php echo $customCSS;?>
</style>

<?php 
wp_enqueue_script('jquery'); wp_enqueue_script('jquery-ui-datepicker');
wp_head(); 
if ( is_singular() ) wp_enqueue_script( "comment-reply" );
?>

<!--[if lt IE 8]>
<script src="http://ie7-js.googlecode.com/svn/version/2.0(beta3)/IE8.js" type="text/javascript"></script>
<![endif]-->

<!--Google Analytics Async Universal-->
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-46639824-1', 'homestage.us');
  ga('send', 'pageview');

</script>
<!--end GA snippet-->

</head>

<body <?php body_class();?>>

<div id = "content">

<div id="wrapper">

<!--DOTTED ACCENT-->
<!--
<div class="dotted"></div>
-->

<div id="header">
	<!--MENU-->
	<?php if (has_nav_menu( 'main' ) ) { wp_nav_menu(array('theme_location' => 'main', 'container_id' => 'navigation', 'menu_id' => 'dropmenu')); }?>
	<?php 
	$menu_name = 'main';
    if ( ( $locations = get_nav_menu_locations() ) && isset( $locations[ $menu_name ] ) ) {
		$menu = wp_get_nav_menu_object( $locations[ $menu_name ] );
		$menu_items = wp_get_nav_menu_items($menu->term_id);
		$menu_list = '<select id="selectMenu"><option value="" selected="selected">'.__('Menu','themolitor').'</option>';
		foreach ( (array) $menu_items as $key => $menu_item ) {
	    	$title = $menu_item->title;
	   		$url = $menu_item->url;
	   		$menu_list .= '<option value="' . $url . '">' . $title . '</option>';
		}
		$menu_list .= '</select>';
		echo $menu_list;
    }
    ?>	

    <div id="titlebar">
		<!--DC FLAG-->
		<!--remove  
		<img id="flagleft" src="https://s3.amazonaws.com/Homestage/images/dc_black_flag.jpg" alt="dc flag">
		-->

		<!--LOGO-->
		<!--remove  
		<?php if($logo){?>	<a id="logo" href="<?php echo home_url(); ?>"><img src="<?php echo $logo;?>" alt="<?php bloginfo('name'); ?>" /></a><?php } ?>
		-->

		<a id="logo" href="<?php echo home_url(); ?>"><img src ="https://s3.amazonaws.com/Homestage/2013/12/Homestage_solo_med_neg.png" alt="Homestage Concerts in your house" /></a>
		
		<img src="https://s3.amazonaws.com/Homestage/2013/12/homestage_title.png" id="titleimg" alt="homestage: DC DIY houseshows and events" />

		<h1 id="hstitle">Homestage DC</h1>	
		

		<!--TAGLINE--> 
		<!--remove
		<?php if($welcomeMsg){ ?> <div id="tagLine"><?php echo $welcomeMsg;?></div> <?php } ?> 
		-->

		<a id="logoright" href="<?php echo home_url(); ?>"><img src ="https://s3.amazonaws.com/Homestage/2013/12/Homestage_solo_med_neg.png" alt="Homestage Concerts in your house" /></a>



		<!--DC FLAG-->
		<!--remove
		<img id="flagright" src="https://s3.amazonaws.com/Homestage/images/dc_black_flag.jpg" alt="dc flag">
		-->

	</div><!--end titlebar-->

	<div id="callTo">
		<h2 id="tagtext"><span id="thetag">The Source for DC DIY </span><a href="https://docs.google.com/forms/d/119qD3yZOrrcJ_EA1eqIBx9AUe-2Gbyxz8h4rOu-d3j0/viewform" target="_blank">GET INVOLVED</a></h2>
	</div>

</div><!--end header-->

<div id="content">
