<?php
	/*
	Plugin Name: Stratus
	Plugin URI: http://stratus.sc
	Description: Stratus is a <a href="http://jquery.com" target="_blank">jQuery</a> powered <a href="http://soundcloud.com" target="_blank">SoundCloud</a> player that lives at the bottom (or top) of your website or blog.
	Version: 1.0.0
	Author: Lee Martin
	Author URI: http://lee.ma/rtin
	License: MIT
	*/
	
	// Copyright (c) 2012 Lee Martin and SoundCloud

	// Permission is hereby granted, free of charge, to any person obtaining a copy
	// of this software and associated documentation files (the "Software"), to deal
	// in the Software without restriction, including without limitation the rights
	// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	// copies of the Software, and to permit persons to whom the Software is
	// furnished to do so, subject to the following conditions:

	// The above copyright notice and this permission notice shall be included in
	// all copies or substantial portions of the Software.

	// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	// THE SOFTWARE.
	
	if ( !defined( 'WP_CONTENT_URL' ) )
		define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
	if ( !defined( 'WP_CONTENT_DIR' ) )
		define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
	if ( !defined( 'WP_PLUGIN_URL' ) )
		define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
	if ( !defined( 'WP_PLUGIN_DIR' ) )
		define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
	if ( !defined( 'WP_LANG_DIR') )
		define( 'WP_LANG_DIR', WP_CONTENT_DIR . '/languages' );
		
	function stratus(){
	  $settings = get_option('stratus_settings');
	  echo ("<script type=\"text/javascript\">jQuery(document).ready(function(){ jQuery('body').stratus(" . ( $settings ? "{" . $settings . "}" : "" ) . "); });</script>");
	}
	
	add_action("wp_head", "stratus");
	
	wp_enqueue_script("stratus", "http://stratus.sc/stratus.js", array("jquery"));
	
	add_action('admin_menu', 'stratus_create_menu');
	
  add_action('admin_init', 'stratus_register_settings');
	
	add_filter('plugin_action_links', 'stratus_filter_plugin_actions', 10, 2);
	
	function stratus_create_menu() {
		add_options_page("Stratus Settings", "Stratus Settings", 8, 'stratus', 'stratus_settings_page');
	}
	
	function stratus_register_settings() {
	  register_setting('stratus-settings-group', 'stratus_settings');
	}
	
	function stratus_settings_page() {
?>
	
<div class="wrap">
  
  <img src="http://stratus.sc/images/logo.png" style="margin: 20px 0 0 0;"/>
  
  <p>Stratus is a jQuery powered SoundCloud player that lives at the bottom (or top) of your website or blog.</p>
  
  <form method="post" action="options.php">
    
    <?php settings_fields('stratus-settings-group'); ?>
    
    <h3>UPDATE SETTINGS</h3>
    
    <p>You can customize Stratus by passing the appropriate options to the <code>stratus</code> function in JSON format.</p>	  
    <p>Refer to the table on <a href="http://stratus.sc/#options" target="_blank" style="color:black">stratus.sc</a> for all configuration options.</p>
      
    <p>
      <code>$.stratus({</code>
      <input type="text" name="stratus_settings" size="50" value="<?php echo get_option('stratus_settings'); ?>" />
      <code>});</code>
    </p>
    
    <input type="submit" value="<?php _e('Save Changes') ?>" style="cursor:pointer;background:#F60;border:none;color:white;padding:6px 10px 4px 10px" />
    
  </form>
  
  <p>For example, the following code would play the Foo Fighters set 'Wasting Light' via a 000000 (black) player:</p>	    
  <p><code>$.stratus({ <span style="color:#F60">color: '000000', links: 'http://soundcloud.com/foofighters/sets/wasting-light'</span> });</code></p>    
  <p>And, the following code will cause the player to auto play a random QOTSA track and hide the download button.</p>
  <p><code>$.stratus({ <span style="color:#F60">auto_play: true, download: false, links: 'http://soundcloud.com/qotsa', random: true</span> });</code></p>
  
  <h3 style="margin-top:30px;">FEEDBACK</h3>
  <p>Got a suggestion? Found a bug? Deployed the player on your site? Let me know <a href="http://twitter.com/leemartin" target="_blank" style="color:black">@leemartin</a></p>
  
</div>
	
<?php

  }

  function stratus_filter_plugin_actions( $links, $file ) {
  	static $stratus_plugin;

  	if ( ! isset( $stratus_plugin ) )
  		$stratus_plugin = plugin_basename( __FILE__ );

  	if ( $file == $stratus_plugin ) {
  		$settings_link = '<a href="' . admin_url( 'options-general.php?page=stratus' ) . '">' . __( 'Settings', 'stratus' ) . '</a>';
  		array_unshift( $links, $settings_link );
  	}

  	return $links;
  }

?>
