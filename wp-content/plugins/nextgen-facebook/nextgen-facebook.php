<?php
/*
Plugin Name: NGFB Open Graph+
Plugin URI: http://surniaulula.com/extend/plugins/nextgen-facebook/
Author: Jean-Sebastien Morisset
Author URI: http://surniaulula.com/
License: GPLv3
License URI: http://surniaulula.com/wp-content/plugins/nextgen-facebook/license/gpl.txt
Description: Improve the appearance and ranking of WordPress Posts, Pages, and eCommerce Products in Google Search and social website shares
Version: 6.18.0.2

Copyright 2012-2013 - Jean-Sebastien Morisset - http://surniaulula.com/
*/

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbPlugin' ) ) {

	class NgfbPlugin {

		// class object variables
		public $debug, $util, $notice, $opt, $user, $media, $meta,
			$style, $script, $cache, $admin, $head, $og, $webpage,
			$social, $seo, $pro, $update, $reg;

		public $cf = array();		// config array defined in construct method
		public $is_avail = array();	// assoc array for other plugin checks
		public $options = array();	// individual blog/site options
		public $site_options = array();	// multisite options
		public $ngg_options = array();	// nextgen gallery options
		public $ngg_version = 0;	// nextgen gallery version

		public function __construct() {

			require_once ( dirname( __FILE__ ).'/lib/config.php' );
			$this->cf = NgfbPluginConfig::get_config();
			NgfbPluginConfig::set_constants( __FILE__ );
			NgfbPluginConfig::require_libs( __FILE__ );	// keep in construct for widgets

			require_once ( dirname( __FILE__ ).'/lib/register.php' );
			$reg_class = __CLASS__.'Register';
			$this->reg = new $reg_class( $this );

			add_action( 'init', array( &$this, 'init_plugin' ), NGFB_INIT_PRIORITY );
		}

		// called by WP init action
		public function init_plugin() {
			if ( is_feed() ) return;	// nothing to do in the feeds
			if ( ! empty( $_SERVER['NGFB_DISABLE'] ) ) return;

			load_plugin_textdomain( NGFB_TEXTDOM, false, dirname( NGFB_PLUGINBASE ).'/languages/' );
			$this->set_objects();
			if ( $this->debug->is_on() == true ) {
				foreach ( array( 'wp_head', 'wp_footer' ) as $action ) {
					foreach ( array( 1, 9999 ) as $prio )
						add_action( $action, create_function( '', 
							"echo '<!-- ".$this->cf['lca']." add_action( \'$action\' ) priority $prio test = PASSED -->\n';" ), $prio );
				}
			}
		}

		// get the options, upgrade the options (if necessary), and validate their values
		public function set_objects( $activate = false ) {

			$plugin_dir = constant( $this->cf['uca'].'_'.'PLUGINDIR' );

			/*
			 * plugin options
			 */
			$this->set_options();				// local method for early load
			$this->update_error = get_option( $this->cf['lca'].'_update_error' );
			$this->check = new NgfbCheck( $this );
			$this->is_avail = $this->check->get_avail();	// uses options
			if ( $this->is_avail['aop'] == true ) 
				$this->cf['full'] = $this->cf['full_pro'];
			if ( $this->is_avail['ngg'] == true ) {
				$this->ngg_options = get_option( 'ngg_options' );
				if ( defined( 'NEXTGEN_GALLERY_PLUGIN_VERSION' ) && NEXTGEN_GALLERY_PLUGIN_VERSION )
					$this->ngg_version = NEXTGEN_GALLERY_PLUGIN_VERSION;
			}
	
			/*
			 * essential class objects
			 */
			$html_debug = ! empty( $this->options['plugin_debug'] ) || 
				( defined( 'NGFB_HTML_DEBUG' ) && NGFB_HTML_DEBUG ) ? true : false;
			$wp_debug = defined( 'NGFB_WP_DEBUG' ) && NGFB_WP_DEBUG ? true : false;
			if ( $html_debug || $wp_debug ) {
				require_once( $plugin_dir.'lib/com/debug.php' );
				$this->debug = new SucomDebug( $this, array( 'html' => $html_debug, 'wp' => $wp_debug ) );
			} else $this->debug = new NgfbNoDebug();

			$this->notice = new SucomNotice( $this );

			$this->util = new NgfbUtil( $this );
			$this->opt = new NgfbOptions( $this );

			// uses ngfbOptions class, so must be after object creation, not in set_options() above
			if ( is_multisite() && ( ! is_array( $this->site_options ) || empty( $this->site_options ) ) )
				$this->site_options = $this->opt->get_site_defaults();

			/*
			 * plugin is being activated - create default options, if necessary, and exit
			 */
			if ( $activate == true || ( 
				! empty( $_GET['action'] ) && $_GET['action'] == 'activate-plugin' &&
				! empty( $_GET['plugin'] ) && $_GET['plugin'] == NGFB_PLUGINBASE ) ) {

				$this->debug->log( 'plugin activation detected' );

				if ( ! is_array( $this->options ) || empty( $this->options ) ||
					! empty( $this->options['plugin_reset'] ) || ( defined( 'NGFB_RESET' ) && NGFB_RESET ) ) {

					$this->options = $this->opt->get_defaults();
					$this->options['options_version'] = $this->opt->options_version;
					delete_option( NGFB_OPTIONS_NAME );
					add_option( NGFB_OPTIONS_NAME, $this->options, null, 'yes' );
					$this->debug->log( 'default options have been added to the database' );
				}
				$this->debug->log( 'exiting early: init_plugin() to follow' );
				// no need to continue, init_plugin() will handle the rest
				return;
			}

			/*
			 * remaining object classes
			 */
			$this->cache = new SucomCache( $this );
			$this->script = new SucomScript( $this );
			$this->webpage = new SucomWebpage( $this );		// title, desc, etc., plus shortcodes

			$this->user = new NgfbUser( $this );
			$this->meta = new NgfbPostMeta( $this );
			$this->media = new NgfbMedia( $this );			// images, videos, etc., plug ngg
			$this->style = new NgfbStyle( $this );			// extends SucomStyle

			if ( $this->is_avail['ssb'] == true )
				$this->social = new NgfbSocial( $this );	// wp_head and wp_footer js and buttons

			if ( is_admin() ) {
				$this->og = new SucomOpengraph( $this );	// og parsing method
				$this->msg = new NgfbMessages( $this );
				$this->admin = new NgfbAdmin( $this );
			} else {
				$this->head = new NgfbHead( $this );		// adds opengraph and twitter card meta tags

				if ( $this->is_avail['opengraph'] )
					$this->og = new NgfbOpengraph( $this );
			}

			// create pro class object last - it extends several previous classes
			if ( $this->is_avail['aop'] == true )
				$this->pro = new NgfbAddonPro( $this );

			/*
			 * check options array read from database - upgrade options if necessary
			 */
			$this->options = $this->opt->check_options( NGFB_OPTIONS_NAME, $this->options );
			if ( is_multisite() )
				$this->site_options = $this->opt->check_options( NGFB_SITE_OPTIONS_NAME, $this->site_options );

			/*
			 * setup class properties, etc. based on option values
			 */
			$this->debug->log( 'calling add_image_size('.NGFB_OG_SIZE_NAME.', '.
				$this->options['og_img_width'].', '.
				$this->options['og_img_height'].', '.
				( empty( $this->options['og_img_crop'] ) ? 'false' : 'true' ).')' );
			add_image_size( NGFB_OG_SIZE_NAME, 
				$this->options['og_img_width'], 
				$this->options['og_img_height'], 
				( empty( $this->options['og_img_crop'] ) ? false : true ) );

			$this->cache->object_expire = $this->options['plugin_object_cache_exp'];
			if ( $this->check->is_aop() ) {
				if ( $this->debug->is_on( 'wp' ) == true ) 
					$this->cache->file_expire = NGFB_DEBUG_FILE_EXP;
				else $this->cache->file_expire = $this->options['plugin_file_cache_hrs'] * 60 * 60;
			} else $this->cache->file_expire = 0;
			$this->is_avail['cache']['file'] = $this->cache->file_expire > 0 ? true : false;

			// set the object cache expiration value
			if ( $this->debug->is_on( 'html' ) == true ) {
				foreach ( array( 'object', 'transient' ) as $name ) {
					$constant_name = 'NGFB_'.strtoupper( $name ).'_CACHE_DISABLE';
					$this->is_avail['cache'][$name] = defined( $constant_name ) && ! constant( $constant_name ) ? true : false;
				}
				$cache_msg = 'object cache '.( $this->is_avail['cache']['object'] ? 'could not be' : 'is' ).
					' disabled, and transient cache '.( $this->is_avail['cache']['transient'] ? 'could not be' : 'is' ).
					' disabled.';
				$this->debug->log( 'HTML debug mode active: '.$cache_msg );
				$this->notice->inf( 'HTML debug mode active -- '.$cache_msg.' '.
					__( 'Informational messages are being added to webpages as hidden HTML comments.', NGFB_TEXTDOM ) );
			}

			// setup the update checks if we have an Authentication ID
			if ( ! empty( $this->options['plugin_tid'] ) ) {
				add_filter( $this->cf['lca'].'_installed_version', array( &$this, 'filter_installed_version' ), 10, 1 );
				require_once( $plugin_dir.'lib/com/update.php' );
				$this->update = new SucomUpdate( $this );
				if ( is_admin() ) {
					// if update_hours * 2 has passed without an update check, then force one now
					$last_update = get_option( $this->cf['lca'].'_update_time' );
					if ( empty( $last_update ) || 
						( ! empty( $this->cf['update_hours'] ) && $last_update + ( $this->cf['update_hours'] * 7200 ) < time() ) )
							$this->update->check_for_updates();
				}
			}

		}

		public function filter_installed_version( $version ) {
			if ( $this->is_avail['aop'] == true )
				return $version;
			else return '0.'.$version;
		}

		public function set_options() {
			$this->options = get_option( NGFB_OPTIONS_NAME );
			if ( is_multisite() ) {
				$this->site_options = get_site_option( NGFB_SITE_OPTIONS_NAME );

				// if multisite options are found, check for overwrite of site specific options
				if ( is_array( $this->options ) && is_array( $this->site_options ) ) {
					foreach ( $this->site_options as $key => $val ) {
						if ( array_key_exists( $key, $this->options ) && 
							array_key_exists( $key.':use', $this->site_options ) ) {

							switch ( $this->site_options[$key.':use'] ) {
								case'force':
									$this->options[$key.':is'] = 'disabled';
									$this->options[$key] = $this->site_options[$key];
									break;
								case 'empty':
									if ( empty( $this->options[$key] ) )
										$this->options[$key] = $this->site_options[$key];
									break;
							}
						}
					}
				}
			}
			$this->options = apply_filters( $this->cf['lca'].'_get_options', $this->options );
			$this->site_options = apply_filters( $this->cf['lca'].'_get_site_options', $this->site_options );
		}
	}

        global $ngfb;
	$ngfb = new NgfbPlugin();
}

if ( ! class_exists( 'NgfbNoDebug' ) ) {

	class NgfbNoDebug {
		public function mark() { return; }
		public function args() { return; }
		public function log() { return; }
		public function show_html() { return; }
		public function get_html() { return; }
		public function is_on() { return false; }
	}
}

?>
