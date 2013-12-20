<?php
/*
License: GPLv3
License URI: http://surniaulula.com/wp-content/plugins/nextgen-facebook/license/gpl.txt
Copyright 2012-2013 - Jean-Sebastien Morisset - http://surniaulula.com/
*/

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbAdminAdvanced' ) && class_exists( 'NgfbAdmin' ) ) {

	class NgfbAdminAdvanced extends NgfbAdmin {

		// executed by NgfbAdminAdvancedPro() as well
		public function __construct( &$plugin, $id, $name ) {
			$this->p =& $plugin;
			$this->p->debug->mark();
			$this->menu_id = $id;
			$this->menu_name = $name;
		}

		protected function add_meta_boxes() {
			// add_meta_box( $id, $title, $callback, $post_type, $context, $priority, $callback_args );
			add_meta_box( $this->pagehook.'_plugin', 'Plugin Settings', array( &$this, 'show_metabox_plugin' ), $this->pagehook, 'normal' );
			add_meta_box( $this->pagehook.'_contact', 'Profile Contact Methods', array( &$this, 'show_metabox_contact' ), $this->pagehook, 'normal' );
			add_meta_box( $this->pagehook.'_taglist', 'Meta Tag List', array( &$this, 'show_metabox_taglist' ), $this->pagehook, 'normal' );
		}

		public function show_metabox_plugin() {
			$show_tabs = array( 
				'activation' => 'Activate and Update',
				'content' => 'Content and Filters',
				'cache' => 'File and Object Cache',
				'rewrite' => 'URL Rewrite',
				'apikeys' => 'API Keys',
			);

			// for now, the apikeys tab contains only url shortening api keys
			// only show if the social sharing button features are enabled
			if ( ! $this->p->is_avail['ssb'] )
				unset( $show_tabs['apikeys'] );

			$tab_rows = array();
			foreach ( $show_tabs as $key => $title )
				$tab_rows[$key] = $this->get_rows( $key );
			$this->p->util->do_tabs( 'plugin', $show_tabs, $tab_rows );
		}

		public function show_metabox_contact() {
			echo '<table class="sucom-setting" style="padding-bottom:0"><tr><td>
			<p>The following options allow you to customize the contact field names and labels shown on the <a href="'.get_admin_url( null, 'profile.php' ).'">user profile page</a>.
			'.$this->p->cf['full'].' uses the Facebook, Google+ and Twitter contact field values for Open Graph and Twitter Card meta tags (along with the Twitter social sharing button).
			<strong>You should not modify the <em>Contact Field Name</em> unless you have a very good reason to do so.</strong>
			The <em>Profile Contact Label</em> on the other hand, is for display purposes only, and its text can be changed as you wish.
			Although the following contact methods may be shown on user profile pages, your theme is responsible for displaying their values in the appropriate template locations
			(see <a href="http://codex.wordpress.org/Function_Reference/get_the_author_meta" target="_blank">get_the_author_meta()</a> for examples).</p>
			</td></tr></table>';
			$show_tabs = array( 
				'custom' => 'Custom Contacts',
				'builtin' => 'Built-In Contacts',
			);
			$tab_rows = array();
			foreach ( $show_tabs as $key => $title )
				$tab_rows[$key] = $this->get_rows( $key );
			$this->p->util->do_tabs( 'cm', $show_tabs, $tab_rows );
		}

		public function show_metabox_taglist() {
			echo '<table class="sucom-setting" style="padding-bottom:0;"><tr><td>';
			echo '<p>'.$this->p->cf['full'].' will add the following Facebook and Open Graph meta tags to your webpages. 
			If your theme or another plugin already generates one or more of these meta tags, you may uncheck them here to 
			prevent duplicates from being added (for example, the "description" meta tag is unchecked by default if any 
			known SEO plugin was detected).</p>
			</td></tr></table>';

			echo '<table class="sucom-setting" style="padding-bottom:0;">';
			foreach ( $this->get_more_taglist() as $num => $row ) 
				echo '<tr>', $row, '</tr>';
			unset( $num, $row );
			echo '</table>';

			echo '<table class="sucom-setting"><tr>';
			echo $this->p->util->th( 'Include Empty og:* Meta Tags', null, null, 
			'Include meta property tags of type og:* without any content (default is unchecked).' );
			echo '<td'.( $this->p->check->is_aop() ? '>'.$this->form->get_checkbox( 'og_empty_tags' ) :
			' class="blank checkbox">'.$this->form->get_fake_checkbox( 'og_empty_tags' ) ).'</td>';
			echo '<td width="100%"></td></tr></table>';

		}

		protected function get_more_taglist() {
			$og_cols = 4;
			$cells = array();
			$rows = array();
			foreach ( $this->p->opt->get_defaults() as $opt => $val ) {
				if ( preg_match( '/^inc_(.*)$/', $opt, $match ) ) {
					$cells[] = '<td class="taglist blank checkbox">'.
					$this->form->get_fake_checkbox( $opt ).'</td>'.
					'<th class="taglist">'.$match[1].'</th>'."\n";
				}
			}
			unset( $opt, $val );
			$per_col = ceil( count( $cells ) / $og_cols );
			foreach ( $cells as $num => $cell ) {
				if ( empty( $rows[ $num % $per_col ] ) )
					$rows[ $num % $per_col ] = '';	// initialize the array
				$rows[ $num % $per_col ] .= $cell;	// create the html for each row
			}
			unset( $num, $cell );
			return array_merge( array( '<td colspan="'.($og_cols * 2).'" align="center">'.$this->p->msg->get( 'pro_feature' ).'</td>' ), $rows );
		}

		protected function get_rows( $id ) {
			$ret = array();
			switch ( $id ) {

				case 'custom' :
					if ( ! $this->p->check->is_aop() )
						$ret[] = '<td colspan="4" align="center">'.$this->p->msg->get( 'pro_feature' ).'</td>';

					$ret[] = '<td></td>'.
					$this->p->util->th( 'Show', 'left checkbox' ).
					$this->p->util->th( 'Contact Field Name', 'left medium', null,
					'<strong>You should not modify the contact field names unless you have a specific reason to do so.</strong>
					As an example, to match the contact field name of a theme or other plugin, you might change \'gplus\' to \'googleplus\'.
					If you change the Facebook or Google+ field names, please make sure to update the Open Graph 
					<em>Author Profile URL</em> and Google <em>Author Link URL</em> options in the '.
					$this->p->util->get_admin_url( 'general', 'General Settings' ).' as well.' ).
					$this->p->util->th( 'Profile Contact Label', 'left wide' );

					$sorted_opt_pre = $this->p->cf['opt']['pre'];
					ksort( $sorted_opt_pre );
					foreach ( $sorted_opt_pre as $id => $pre ) {
						$cm_opt = 'plugin_cm_'.$pre.'_';

						// check for the lib website classname for a nice 'display name'
						$name = empty( $this->p->cf['lib']['website'][$id] ) ? 
							ucfirst( $id ) : $this->p->cf['lib']['website'][$id];
						$name = $name == 'GooglePlus' ? 'Google+' : $name;

						// not all social websites have a contact method field
						if ( array_key_exists( $cm_opt.'enabled', $this->p->options ) ) {
							if ( $this->p->check->is_aop() ) {
								$ret[] = $this->p->util->th( $name ).
								'<td class="checkbox">'.$this->form->get_checkbox( $cm_opt.'enabled' ).'</td>'.
								'<td>'.$this->form->get_input( $cm_opt.'name' ).'</td>'.
								'<td>'.$this->form->get_input( $cm_opt.'label' ).'</td>';
							} else {
								$ret[] = $this->p->util->th( $name ).
								'<td class="blank checkbox">'.$this->form->get_fake_checkbox( $cm_opt.'enabled' ).'</td>'.
								'<td class="blank">'.$this->form->get_hidden( $cm_opt.'name' ).
								$this->p->options[$cm_opt.'name'].'</td>'.
								'<td class="blank">'.$this->form->get_hidden( $cm_opt.'label' ).
								$this->p->options[$cm_opt.'label'].'</td>';
							}
						}
					
					}
					break;

				case 'builtin' :
					if ( ! $this->p->check->is_aop() )
						$ret[] = '<td colspan="4" align="center">'.$this->p->msg->get( 'pro_feature' ).'</td>';

					$ret[] = '<td></td>'.
					$this->p->util->th( 'Show', 'left checkbox' ).
					$this->p->util->th( 'Contact Field Name', 'left medium', null, 
					'The built-in WordPress contact field names cannot be changed.' ).
					$this->p->util->th( 'Profile Contact Label', 'left wide' );

					$sorted_wp_contact = $this->p->cf['wp']['cm'];
					ksort( $sorted_wp_contact );
					foreach ( $sorted_wp_contact as $id => $name ) {
						$cm_opt = 'wp_cm_'.$id.'_';
						if ( array_key_exists( $cm_opt.'enabled', $this->p->options ) ) {
							if ( $this->p->check->is_aop() ) {
								$ret[] = $this->p->util->th( $name ).
								'<td class="checkbox">'.$this->form->get_checkbox( $cm_opt.'enabled' ).'</td>'.
								'<td>'.$this->form->get_fake_input( $cm_opt.'name' ).'</td>'.
								'<td>'.$this->form->get_input( $cm_opt.'label' ).'</td>';
							} else {
								$ret[] = $this->p->util->th( $name ).
								'<td class="blank checkbox">'.$this->form->get_hidden( $cm_opt.'enabled' ).
									$this->form->get_fake_checkbox( $cm_opt.'enabled' ).'</td>'.
								'<td>'.$this->form->get_fake_input( $cm_opt.'name' ).'</td>'.
								'<td class="blank">'.$this->form->get_hidden( $cm_opt.'label' ).
									$this->p->options[$cm_opt.'label'].'</td>';
							}
						}
					}
					break;

				case 'activation':

					$ret = array_merge( $ret, $this->get_pre_activation() );

					$ret[] = $this->p->util->th( 'Preserve Settings on Uninstall', 'highlight', null, 
					'Check this option if you would like to preserve all '.$this->p->cf['full'].
					' settings when you <em>uninstall</em> the plugin (default is unchecked).' ).
					'<td>'.$this->form->get_checkbox( 'plugin_preserve' ).'</td>';

					$ret[] = $this->p->util->th( 'Reset Settings on Activate', null, null, 
					'Check this option if you would like to reset the '.$this->p->cf['full'].
					' settings to their default values when you <em>deactivate</em>, and then 
					<em>re-activate</em> the plugin (default is unchecked). This option will
					be disabled if the \'Preserve Settings on Uninstall\' option is checked.' ).
					'<td>'.$this->form->get_checkbox( 'plugin_reset' ).'</td>';

					$ret[] = $this->p->util->th( 'Add Hidden Debug Info', null, null, 
					'Include hidden debug information with the Open Graph meta tags (default is unchecked).' ).
					'<td>'.$this->form->get_checkbox( 'plugin_debug' ).'</td>';

					break;

				case 'content':
					$ret[] = $this->p->util->th( 'Apply Content Filters', null, null, 
					'Apply the standard WordPress \'the_content\' filter to render the content text (default is checked).
					This renders all shortcodes, and allows '.$this->p->cf['full'].' to detect images and 
					embedded videos that may be provided by these.' ).
					'<td>'.$this->form->get_checkbox( 'plugin_filter_content' ).'</td>';

					$ret[] = $this->p->util->th( 'Apply Excerpt Filters', null, null, 
					'Apply the standard WordPress \'get_the_excerpt\' filter to render the excerpt text (default is unchecked).
					Check this option if you use shortcodes in your excerpt, for example.' ).
					'<td>'.$this->form->get_checkbox( 'plugin_filter_excerpt' ).'</td>';

					if ( $this->p->is_avail['ssb'] )
						$ret[] = $this->p->util->th( 'Enable Shortcode(s)', 'highlight', null, 
						'Enable the '.$this->p->cf['full'].' content shortcode(s) (default is unchecked).' ).
						'<td>'.$this->form->get_checkbox( 'plugin_shortcode_ngfb' ).'</td>';

					$ret[] =  $this->p->util->th( 'Ignore Small Images', null, null, 
					$this->p->cf['full'].' will attempt to include images from the img html tags it finds in the content.
					The img html tags must have a width and height attribute, and their size must be equal or larger than the 
					<em>Image Dimensions</em> you\'ve entered on the General Settings page. 
					Uncheck this option to include smaller images from the content, Media Library, etc.
					<strong>Unchecking this option is not advised</strong> - 
					images that are much too small for some social websites may be included in your meta tags.' ).
					'<td>'.$this->form->get_checkbox( 'plugin_ignore_small_img' ).'</td>';

					/*
					$ret[] =  $this->p->util->th( 'Get Images of Unknown Size', null, null, 
					$this->p->cf['full'].' will attempt to include images from img html tags it finds in the content.
					If the image dimensions cannot be determined <strong>and the <em>Ignore Small Images</em> option is checked</strong>, 
					the plugin can retrieve those images to a cache folder, allowing it to inspect and determine the image dimensions. 
					<strong>Enabling this feature will create a copy of all images in the content without width and height attributes. 
					Use cautiously.</strong>' ).
					'<td>'.$this->form->get_checkbox( 'plugin_get_img_size' ).'</td>';
					*/

					$ret = array_merge( $ret, $this->get_more_content() );
					break;

				case 'cache':
					$ret[] = $this->p->util->th( 'Object Cache Expiry', null, null, 
					$this->p->cf['full'].' saves filtered / rendered content to a non-persistant cache 
					(aka <a href="http://codex.wordpress.org/Class_Reference/WP_Object_Cache" target="_blank">WP Object Cache</a>), 
					and Open Graph, Rich Pin, Twitter Card meta tags, and social buttons to a persistant (aka 
					<a href="http://codex.wordpress.org/Transients_API" target="_blank">Transient</a>) cache. 
					The default is '.$this->p->opt->defaults['plugin_object_cache_exp'].' seconds, and the minimum value is 
					1 second (such a low value is not recommended).' ).
					'<td nowrap>'.$this->form->get_input( 'plugin_object_cache_exp', 'short' ).' Seconds</td>';

					$ret = array_merge( $ret, $this->get_more_cache() );
					break;

				case 'apikeys':
					$ret = array_merge( $ret, $this->get_more_apikeys() );
					break;

				case 'rewrite':
					$ret = array_merge( $ret, $this->get_more_rewrite() );
					break;
			}
			return $ret;
		}

		protected function get_pre_activation() {
			$ret = array();
			$pro_msg = '';
			$input = '';
			if ( is_multisite() && ! empty( $this->p->site_options['plugin_tid:use'] ) && $this->p->site_options['plugin_tid:use'] == 'force' ) {
				$pro_msg = 'The Authentication ID value has been locked in the Network Admin settings.';
				$input = $this->form->get_input( 'plugin_tid', 'mono' );
			} elseif ( $this->p->is_avail['aop'] ) {
				$pro_msg = 'After purchasing a Pro version license, an email will be sent to you with a unique Authentication ID 
				and installation instructions. Enter the Authentication ID here to activate the Pro version features.';
				$input = $this->form->get_input( 'plugin_tid', 'mono' );
			} else {
				$pro_msg = 'After purchasing the Pro version, an email will be sent to you with a unique Authentication ID 
				and installation instructions. Enter this Authentication ID here, and after saving the changes, an update 
				for '.$this->p->cf['full'].' will appear on the <a href="'.get_admin_url( null, 'update-core.php' ).'">WordPress 
				Updates</a> page. Update the \''.$this->p->cf['full'].'\' plugin to download and activate the Pro version.';
				$input = $this->form->get_input( 'plugin_tid', 'mono' );
			}

			$ret[] = $this->p->util->th( 'Pro Version Authentication ID', 'highlight', null, $pro_msg ).'<td>'.$input.'</td>';

			return $ret;
		}

		protected function get_more_content() {
			$add_to_checkboxes = '';
			foreach ( $this->p->util->get_post_types( 'plugin' ) as $post_type )
				$add_to_checkboxes .= '<p>'.$this->form->get_fake_checkbox( 'plugin_add_to_'.$post_type->name ).' '.
					$post_type->label.' '.( empty( $post_type->description ) ? '' : '('.$post_type->description.')' ).'</p>';

			return array(
				'<td colspan="2" align="center">'.$this->p->msg->get( 'pro_feature' ).'</td>',

				$this->p->util->th( 'Check for Wistia Videos', null, null, 
				'Check the content and Custom Settings for Wistia video URLs, 
				and retrieve the preferred oEmbed sharing URL, video dimensions, 
				along with the video preview image.' ).
				'<td class="blank">'.$this->form->get_fake_checkbox( 'plugin_wistia_api' ).'</td>',

				$this->p->util->th( 'Show Custom Settings on', null, null, 
				'The Custom Settings metabox, which allows you to enter custom Open Graph values (among other options), 
				is available on the Posts, Pages, Media, and Product admin pages by default. 
				If your theme (or another plugin) supports additional custom post types, and you would like to 
				include the Custom Settings metabox on their admin pages, check the appropriate option(s) here.' ).
				'<td class="blank">'.$add_to_checkboxes.'</td>',
			);
		}

		protected function get_more_cache() {
			return array(
				'<td colspan="2" align="center">'.$this->p->msg->get( 'pro_feature' ).'</td>',

				$this->p->util->th( 'File Cache Expiry', 'highlight', null, 
				$this->p->cf['full'].' can save social sharing JavaScript and images to a cache folder, 
				providing URLs to these cached files instead of the originals. 
				A value of 0 Hours (the default) disables the file caching feature. 
				If your hosting infrastructure performs reasonably well, this option can improve page load times significantly.
				All social sharing images and javascripts will be cached, except for the Facebook JavaScript SDK, 
				which does not work correctly when cached.' ).
				'<td class="blank">'.$this->form->get_hidden( 'plugin_file_cache_hrs' ). 
				$this->p->options['plugin_file_cache_hrs'].' Hours</td>',

				$this->p->util->th( 'Verify SSL Certificates', null, null, 
				'Enable verification of peer SSL certificates when fetching content to be cached using HTTPS. 
				The PHP \'curl\' function will use the '.NGFB_CURL_CAINFO.' certificate file by default. 
				You may want define the NGFB_CURL_CAINFO constant in your wp-config.php file to use an 
				alternate certificate file (see the constants.txt file in the plugin folder for additional information).' ).
				'<td class="blank">'.$this->form->get_fake_checkbox( 'plugin_verify_certs' ).'</td>',
			);
		}

		protected function get_more_apikeys() {
			return array(
				'<td colspan="2" align="center">'.$this->p->msg->get( 'pro_feature' ).'</td>',

				$this->p->util->th( 'Bit.ly Username', null, null, 
				'The Bit.ly username for the following API key. If you don\'t already have one, see 
				<a href="https://bitly.com/a/your_api_key" target="_blank">Your Bit.ly API Key</a>.' ).
				'<td class="blank mono">'.$this->form->get_hidden( 'plugin_bitly_login' ).
				$this->p->options['plugin_bitly_login'].'</td>',

				$this->p->util->th( 'Bit.ly API Key', null, null, 
				'The Bit.ly API key for this website. If you don\'t already have one, see 
				<a href="https://bitly.com/a/your_api_key" target="_blank">Your Bit.ly API Key</a>.' ).
				'<td class="blank mono">'.$this->form->get_hidden( 'plugin_bitly_api_key' ).
				$this->p->options['plugin_bitly_api_key'].'</td>',

				$this->p->util->th( 'Google Project Application BrowserKey', null, null, 
				'The Google BrowserKey for this website / project. If you don\'t already have one, visit
				<a href="https://cloud.google.com/console#/project" target="_blank">Google\'s Cloud Console</a>,
				create a new project for your website, and under the API &amp; auth - Registered apps, 
				register a new \'Web Application\' (name it \'NGFB Open Graph+\' for example), 
				and enter it\'s BrowserKey here.' ).
				'<td class="blank mono">'.$this->form->get_hidden( 'plugin_google_api_key' ).
				$this->p->options['plugin_google_api_key'].'</td>',

				$this->p->util->th( 'Google URL Shortener API is ON', null, null,
				'In order to use Google\'s URL Shortener for URLs in Tweets, you must turn on the 
				URL Shortener API from <a href="https://cloud.google.com/console#/project" 
				target="_blank">Google\'s Cloud Console</a>, under the API &amp; auth - APIs 
				menu options. Confirm that you have enabled Google\'s URL Shortener by checking 
				the \'Yes\' option here. You can then select the Google URL Shortener in the '.
				$this->p->util->get_admin_url( 'social', 'Twitter settings' ).'.' ).'<td class="blank">'.
				$this->form->get_fake_radio( 'plugin_google_shorten', array( '1' => 'Yes', '0' => 'No' ), null, null, true ).'</td>',
			);
		}

		protected function get_more_rewrite() {
			return array(
				'<td colspan="2" align="center">'.$this->p->msg->get( 'pro_feature' ).'</td>',

				$this->p->util->th( 'URL Length to Shorten', null, null, 
				'URLs shorter than this length will not be shortened (default is '.$this->p->opt->defaults['plugin_min_shorten'].').' ).
				'<td class="blank">'.$this->form->get_hidden( 'plugin_min_shorten' ).
					$this->p->options['plugin_min_shorten'].' characters</td>',

				$this->p->util->th( 'Static Content URL(s)', 'highlight', null, 
				'Rewrite image URLs in the Open Graph, Rich Pin, and Twitter Card meta tags, encoded image URLs shared by social buttons 
				(like Pinterest and Tumblr), and cached social media files. Leave this option blank to disable the URL rewriting feature 
				(default is disabled). Wildcarding and multiple CDN hostnames are supported -- see the 
				<a href="http://surniaulula.com/codex/plugins/nextgen-facebook/notes/url-rewriting/" target="_blank">URL Rewriting</a> 
				notes for more information and examples.' ) .
				'<td class="blank">'.$this->form->get_hidden( 'plugin_cdn_urls' ). 
					$this->p->options['plugin_cdn_urls'].'</td>',

				$this->p->util->th( 'Include Folders', null, null, '
				A comma delimited list of patterns to match. These patterns must be present in the URL for the rewrite to take place 
				(the default value is "<em>wp-content, wp-includes</em>").').
				'<td class="blank">'.$this->form->get_hidden( 'plugin_cdn_folders' ). 
					$this->p->options['plugin_cdn_folders'].'</td>',

				$this->p->util->th( 'Exclude Patterns', null, null,
				'A comma delimited list of patterns to match. If these patterns are found in the URL, the rewrite will be skipped (the default value is blank).
				If you are caching social website images and JavaScript (see <em>File Cache Expiry</em> option), 
				the URLs to this cached content will be rewritten as well (that\'s a good thing).
				To exclude the '.$this->p->cf['full'].' cache folder URLs from being rewritten, enter \'/nextgen-facebook/cache/\' as a value here.' ).
				'<td class="blank">'.$this->form->get_hidden( 'plugin_cdn_excl' ).
					$this->p->options['plugin_cdn_excl'].'</td>',

				$this->p->util->th( 'Not when Using HTTPS', null, null, 
				'Skip rewriting URLs when using HTTPS (useful if your CDN provider does not offer HTTPS, for example).' ).
				'<td class="blank">'.$this->form->get_fake_checkbox( 'plugin_cdn_not_https' ).'</td>',

				$this->p->util->th( 'www is Optional', null, null, 
				'The www hostname prefix (if any) in the WordPress site URL is optional (default is checked).' ).
				'<td class="blank">'.$this->form->get_fake_checkbox( 'plugin_cdn_www_opt' ).'</td>',
			);
		}
	}
}

?>
