<?php
/*
License: GPLv3
License URI: http://surniaulula.com/wp-content/plugins/nextgen-facebook/license/gpl.txt
Copyright 2013 - Jean-Sebastien Morisset - http://surniaulula.com/
*/

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbPluginConfig' ) ) {

	class NgfbPluginConfig {

		private static $cf = array(
			'version' => '6.18.0.2',		// plugin version
			'lca' => 'ngfb',			// lowercase acronym
			'cca' => 'Ngfb',			// camelcase acronym
			'uca' => 'NGFB',			// uppercase acronym
			'slug' => 'nextgen-facebook',
			'menu' => 'Open Graph+',		// menu item label
			'full' => 'NGFB Open Graph+',		// full plugin name
			'full_pro' => 'NGFB Open Graph+ Pro',
			'update_hours' => 12,			// check for pro updates
			'cache' => array(
				'file' => true,
				'object' => true,
				'transient' => true,
			),
			'lib' => array(				// libraries
				'setting' => array (
					'general' => 'General',
					'advanced' => 'Advanced',
					'contact' => 'Contact Methods',
					'social' => 'Social Sharing',
					'style' => 'Social Style',
					'about' => 'About',
				),
				'site_setting' => array(
					'network' => 'Network',
				),
				'website' => array(
					'facebook' => 'Facebook', 
					'gplus' => 'GooglePlus',
					'twitter' => 'Twitter',
					'linkedin' => 'LinkedIn',
					'managewp' => 'ManageWP',
					'pinterest' => 'Pinterest',
					'stumbleupon' => 'StumbleUpon',
					'tumblr' => 'Tumblr',
					'youtube' => 'YouTube',
					'skype' => 'Skype',
				),
				'shortcode' => array(
					'ngfb' => 'Ngfb',
				),
				'widget' => array(
					'social' => 'SocialSharing',
				),
				'pro' => array(
					'seo' => array(
						'aioseop' => 'All in One SEO Pack',
						'seou' => 'SEO Ultimate',
						'wpseo' => 'WordPress SEO',
					),
					'ecom' => array(
						'woocommerce' => 'WooCommerce',
						'marketpress' => 'MarketPress',
						'wpecommerce' => 'WP e-Commerce',
					),
					'forum' => array(
						'bbpress' => 'bbPress',
					),
					'social' => array(
						'buddypress' => 'BuddyPress',
					),
					'media' => array(
						'wistia' => 'Wistia Video API',
					),
				),
			),
			'opt' => array(				// options
				'pre' => array(
					'facebook' => 'fb', 
					'gplus' => 'gp',
					'twitter' => 'twitter',
					'linkedin' => 'linkedin',
					'managewp' => 'managewp',
					'pinterest' => 'pin',
					'stumbleupon' => 'stumble',
					'tumblr' => 'tumblr',
					'youtube' => 'yt',
					'skype' => 'skype',
				),
			),
			'wp' => array(				// wordpress
				'min_version' => '3.0',		// minimum wordpress version
				'cm' => array(
					'aim' => 'AIM',
					'jabber' => 'Jabber / Google Talk',
					'yim' => 'Yahoo IM',
				),
			),
			'css' => array(				// filter with 'ngfb_style_tabs'
				'social' => 'Buttons Style',
				'excerpt' => 'Excerpt Style',
				'content' => 'Content Style',
				'shortcode' => 'Shortcode Style',
				'widget' => 'Widget Style',
			),
			'url' => array(
				'feed' => 'http://feed.surniaulula.com/category/application/wordpress/wp-plugins/ngfb/feed/',
				'readme' => 'http://plugins.svn.wordpress.org/nextgen-facebook/trunk/readme.txt',
				'purchase' => 'http://plugin.surniaulula.com/extend/plugins/nextgen-facebook/',
				'faq' => 'http://wordpress.org/plugins/nextgen-facebook/faq/',
				'notes' => 'http://wordpress.org/plugins/nextgen-facebook/other_notes/',
				'changelog' => 'http://wordpress.org/plugins/nextgen-facebook/changelog/',
				'support' => 'http://wordpress.org/support/plugin/nextgen-facebook',
				'pro_codex' => 'http://codex.ngfb.surniaulula.com/',
				'pro_support' => 'http://support.ngfb.surniaulula.com/',
				'pro_ticket' => 'http://ticket.ngfb.surniaulula.com/',
				'pro_update' => 'http://update.surniaulula.com/extend/plugins/nextgen-facebook/update/',
			),
			'follow' => array(
				'size' => 32,
				'src' => array(
					'facebook.png' => 'https://www.facebook.com/SurniaUlulaCom',
					'gplus.png' => 'https://plus.google.com/b/112667121431724484705/112667121431724484705/posts',
					'linkedin.png' => 'https://www.linkedin.com/in/jsmoriss',
					'twitter.png' => 'https://twitter.com/surniaululacom',
					'youtube.png' => 'https://www.youtube.com/user/SurniaUlulaCom',
					'feed.png' => 'http://feed.surniaulula.com/category/application/wordpress/wp-plugins/ngfb/feed/',
				),
			),
			'form' => array(
				'max_desc_hashtags' => 10,
				'max_media_items' => 20,
				'file_cache_hours' => array( 0, 1, 3, 6, 9, 12, 24, 36, 48, 72, 168 ),
			),
			'head' => array(
				'min_img_width' => 200,
				'min_img_height' => 200,
				'min_desc_len' => 156,
			),
			'social' => array(
				'show_on' => array( 
					'the_content' => 'Content', 
					'the_excerpt' => 'Excerpt', 
					'admin_sharing' => 'Edit Post/Page',
				),
			),
		);
		private static $cf_filtered = false;

		public static function get_config( $idx = '' ) { 
			if ( self::$cf_filtered === false ) {
				// remove the social sharing libs if disabled
				if ( defined( self::$cf['uca'].'_SOCIAL_SHARING_DISABLE' ) &&
					constant( self::$cf['uca'].'_SOCIAL_SHARING_DISABLE' ) ) {
					unset (
						self::$cf['lib']['setting']['social'],
						self::$cf['lib']['setting']['style'],
						self::$cf['lib']['shortcode']['ngfb'],
						self::$cf['lib']['widget']['social']
					);
					self::$cf['lib']['website'] = array();
				}
				self::$cf = apply_filters( self::$cf['lca'].'_get_config', self::$cf );
				self::$cf_filtered = true;
			}
			if ( ! empty( $idx ) ) {
				if ( array_key_exists( $idx, self::$cf ) )
					return self::$cf[$idx];
				else return false;
			} else return self::$cf;
		}

		public static function set_constants( $plugin_filepath ) { 

			$cf = self::get_config();
			$cp = $cf['uca'].'_';	// constant prefix

			// .../wordpress/wp-content/plugins/nextgen-facebook/nextgen-facebook.php
			define( $cp.'FILEPATH', $plugin_filepath );						

			// .../wordpress/wp-content/plugins/nextgen-facebook/
			define( $cp.'PLUGINDIR', trailingslashit( plugin_dir_path( $plugin_filepath ) ) );

			// nextgen-facebook/nextgen-facebook.php
			define( $cp.'PLUGINBASE', plugin_basename( $plugin_filepath ) );

			// nextgen-facebook
			define( $cp.'TEXTDOM', $cf['slug'] );

			// http://.../wp-content/plugins/nextgen-facebook/
			define( $cp.'URLPATH', trailingslashit( plugins_url( '', $plugin_filepath ) ) );

			define( $cp.'NONCE', md5( constant( $cp.'PLUGINDIR' ).'-'.$cf['version'] ) );

			define( 'AUTOMATTIC_README_MARKDOWN', constant( $cp.'PLUGINDIR' ).'lib/ext/markdown.php' );

			/*
			 * Allow some constants to be pre-defined in wp-config.php
			 */

			if ( defined( $cp.'DEBUG' ) && 
				! defined( $cp.'HTML_DEBUG' ) )
					define( $cp.'HTML_DEBUG', constant( $cp.'DEBUG' ) );

			if ( ! defined( $cp.'CACHEDIR' ) )
				define( $cp.'CACHEDIR', constant( $cp.'PLUGINDIR' ).'cache/' );

			if ( ! defined( $cp.'CACHEURL' ) )
				define( $cp.'CACHEURL', constant( $cp.'URLPATH' ).'cache/' );

			if ( ! defined( $cp.'OPTIONS_NAME' ) )
				define( $cp.'OPTIONS_NAME', $cf['lca'].'_options' );

			if ( ! defined( $cp.'SITE_OPTIONS_NAME' ) )
				define( $cp.'SITE_OPTIONS_NAME', $cf['lca'].'_site_options' );

			if ( ! defined( $cp.'META_NAME' ) )
				define( $cp.'META_NAME', '_'.$cf['lca'].'_meta' );

			if ( ! defined( $cp.'MENU_PRIORITY' ) )
				define( $cp.'MENU_PRIORITY', '99.10' );

			if ( ! defined( $cp.'INIT_PRIORITY' ) )
				define( $cp.'INIT_PRIORITY', 12 );

			if ( ! defined( $cp.'HEAD_PRIORITY' ) )
				define( $cp.'HEAD_PRIORITY', 10 );

			if ( ! defined( $cp.'SOCIAL_PRIORITY' ) )
				define( $cp.'SOCIAL_PRIORITY', 100 );
			
			if ( ! defined( $cp.'FOOTER_PRIORITY' ) )
				define( $cp.'FOOTER_PRIORITY', 100 );
			
			if ( ! defined( $cp.'OG_SIZE_NAME' ) )
				define( $cp.'OG_SIZE_NAME', $cf['lca'].'-open-graph' );

			if ( ! defined( $cp.'DEBUG_FILE_EXP' ) )
				define( $cp.'DEBUG_FILE_EXP', 300 );

			if ( ! defined( $cp.'CURL_USERAGENT' ) )
				define( $cp.'CURL_USERAGENT', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:18.0) Gecko/20100101 Firefox/18.0' );

			if ( ! defined( $cp.'CURL_CAINFO' ) )
				define( $cp.'CURL_CAINFO', constant( $cp.'PLUGINDIR' ).'share/curl/cacert.pem' );

		}

		public static function require_libs( $plugin_filepath ) {
			
			$cf = self::get_config();

			$plugin_dir = constant( $cf['uca'].'_'.'PLUGINDIR' );

			require_once( $plugin_dir.'lib/com/cache.php' );
			require_once( $plugin_dir.'lib/com/notice.php' );
			require_once( $plugin_dir.'lib/com/script.php' );
			require_once( $plugin_dir.'lib/com/style.php' );
			require_once( $plugin_dir.'lib/com/webpage.php' );
			require_once( $plugin_dir.'lib/com/opengraph.php' );

			require_once( $plugin_dir.'lib/check.php' );
			require_once( $plugin_dir.'lib/util.php' );
			require_once( $plugin_dir.'lib/options.php' );
			require_once( $plugin_dir.'lib/user.php' );
			require_once( $plugin_dir.'lib/postmeta.php' );
			require_once( $plugin_dir.'lib/media.php' );
			require_once( $plugin_dir.'lib/style.php' );		// extends lib/com/style.php

			if ( file_exists( $plugin_dir.'lib/social.php' ) &&
				( ! defined( $cf['uca'].'_SOCIAL_SHARING_DISABLE' ) || 
					! constant( $cf['uca'].'_SOCIAL_SHARING_DISABLE' ) ) )
						require_once( $plugin_dir.'lib/social.php' );

			if ( is_admin() ) {
				require_once( $plugin_dir.'lib/messages.php' );
				require_once( $plugin_dir.'lib/admin.php' );

				// settings classes extend lib/admin.php, and settings objects are created by lib/admin.php
				foreach ( $cf['lib']['setting'] as $id => $name )
					require_once( $plugin_dir.'lib/setting/'.$id.'.php' );

				// load the network settings if we're a multisite
				if ( is_multisite() )
					foreach ( $cf['lib']['site_setting'] as $id => $name )
						require_once( $plugin_dir.'lib/site_setting/'.$id.'.php' );

				require_once( $plugin_dir.'lib/com/form.php' );
				require_once( $plugin_dir.'lib/ext/parse-readme.php' );
			} else {
				require_once( $plugin_dir.'lib/head.php' );
				require_once( $plugin_dir.'lib/functions.php' );
			}

			if ( file_exists( $plugin_dir.'lib/opengraph.php' ) &&
				( ! defined( $cf['uca'].'_OPEN_GRAPH_DISABLE' ) || ! constant( $cf['uca'].'_OPEN_GRAPH_DISABLE' ) ) &&
				empty( $_SERVER['NGFB_OPEN_GRAPH_DISABLE'] ) )
					require_once( $plugin_dir.'lib/opengraph.php' );	// extends lib/com/opengraph.php

			// website classes extend both lib/social.php and lib/setting/social.php
			foreach ( $cf['lib']['website'] as $id => $name )
				if ( file_exists( $plugin_dir.'lib/website/'.$id.'.php' ) )
					require_once( $plugin_dir.'lib/website/'.$id.'.php' );

			// widgets are added to wordpress when library file is loaded
			// no need to create the class object later on
			foreach ( $cf['lib']['widget'] as $id => $name )
				if ( file_exists( $plugin_dir.'lib/widget/'.$id.'.php' ) )
					require_once( $plugin_dir.'lib/widget/'.$id.'.php' );

			// additional classes are loaded and extended by the pro addon construct
			if ( file_exists( $plugin_dir.'lib/pro/addon.php' ) )
				require_once( $plugin_dir.'lib/pro/addon.php' );
		}
	}
}
?>
