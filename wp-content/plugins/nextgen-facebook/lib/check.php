<?php
/*
License: GPLv3
License URI: http://surniaulula.com/wp-content/plugins/nextgen-facebook/license/gpl.txt
Copyright 2012-2013 - Jean-Sebastien Morisset - http://surniaulula.com/
*/

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbCheck' ) ) {

	class NgfbCheck {

		private $p;
		private $active_plugins;
		private $network_plugins;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			if ( is_object( $this->p->debug ) && 
				method_exists( $this->p->debug, 'mark' ) )
					$this->p->debug->mark();

			$this->active_plugins = get_option( 'active_plugins', array() ); 
			if ( is_multisite() ) {
				$this->network_plugins = array_keys( get_site_option( 'active_sitewide_plugins', array() ) );
				if ( $this->network_plugins )
					$this->active_plugins = array_merge( $this->active_plugins, $this->network_plugins );
			}
		}

		public function get_active() {
			return $this->active_plugins;
		}

		// used before any class objects are created, so keep in main class
		public function get_avail() {
			$ret = array();
			$ret['curl'] = function_exists( 'curl_init' ) ? true : false;
			$ret['mbdecnum'] = function_exists( 'mb_decode_numericentity' ) ? true : false;
			$ret['postthumb'] = function_exists( 'has_post_thumbnail' ) ? true : false;
			$ret['ngg'] = class_exists( 'nggdb' ) || class_exists( 'C_NextGEN_Bootstrap' ) ||
				in_array( 'nextgen-gallery/nggallery.php', $this->active_plugins ) ? true : false; 

			$ret['metatags'] = ( ! defined( 'NGFB_META_TAGS_DISABLE' ) || ! constant( 'NGFB_META_TAGS_DISABLE' ) ) &&
				empty( $_SERVER['NGFB_META_TAGS_DISABLE'] ) ? true : false;
			$ret['opengraph'] = file_exists( constant( 'NGFB_PLUGINDIR' ).'lib/opengraph.php' ) &&
				( ! defined( 'NGFB_OPEN_GRAPH_DISABLE' ) || ! constant( 'NGFB_OPEN_GRAPH_DISABLE' ) ) &&
				empty( $_SERVER['NGFB_OPEN_GRAPH_DISABLE'] ) &&
				class_exists( $this->p->cf['cca'].'Opengraph' ) ? true : false;
			$ret['ssb'] = file_exists( constant( 'NGFB_PLUGINDIR' ).'lib/social.php' ) &&
				( ! defined( 'NGFB_SOCIAL_SHARING_DISABLE' ) || ! constant( 'NGFB_SOCIAL_SHARING_DISABLE' ) ) &&
				empty( $_SERVER['NGFB_SOCIAL_SHARING_DISABLE'] ) &&
				class_exists( $this->p->cf['cca'].'Social' ) ? true : false;
			$ret['aop'] = file_exists( constant( 'NGFB_PLUGINDIR' ).'lib/pro/addon.php' ) &&
				class_exists( $this->p->cf['cca'].'AddonPro' ) ? true : false;

			foreach ( $this->p->cf['cache'] as $name => $val ) {
				$constant_name = 'NGFB_'.strtoupper( $name ).'_CACHE_DISABLE';
				$ret['cache'][$name] = defined( $constant_name ) &&
					constant( $constant_name ) ? false : true;
			}

			foreach ( $this->p->cf['lib']['pro'] as $sub => $libs ) {
				$ret[$sub] = array();
				$ret[$sub]['*'] = false;
				foreach ( $libs as $id => $name ) {
					$checkbox = false;
					$func_name = false;
					$class_name = false;
					$pluginbase = false;
					switch ( $id ) {
						case 'aioseop':
							$class_name = 'All_in_One_SEO_Pack';
							$pluginbase = 'all-in-one-seo-pack/all-in-one-seo-pack.php';
							break;
						case 'seou':
							$class_name = 'SEO_Ultimate'; 
							$pluginbase = 'seo-ultimate/seo-ultimate.php';
							break;
						case 'wpseo':
							$func_name = 'wpseo_init'; 
							$pluginbase = 'wordpress-seo/wp-seo.php';
							break;
						case 'woocommerce':
							$class_name = 'Woocommerce';
							$pluginbase = 'woocommerce/woocommerce.php';
							break;
						case 'marketpress':
							$class_name = 'MarketPress'; 
							$pluginbase = 'wordpress-ecommerce/marketpress.php';
							break;
						case 'wpecommerce':
							$class_name = 'WP_eCommerce';
							$pluginbase = 'wp-e-commerce/wp-shopping-cart.php';
							break;
						case 'bbpress':
							$class_name = 'bbPress'; 
							$pluginbase = 'bbpress/bbpress.php';
							break;
						case 'buddypress':
							$class_name = 'BuddyPress'; 
							$pluginbase = 'buddypress/bp-loader.php';
							break;
						case 'wistia':
							$checkbox = 'plugin_wistia_api';
							break;
					}
					if ( ( $func_name && function_exists( $func_name ) ) || 
						( $class_name && class_exists( $class_name ) ) ||
						( $pluginbase && in_array( $pluginbase, $this->active_plugins ) ) ||
						( $checkbox && ! empty( $this->p->options[$checkbox] ) ) )
							$ret[$sub]['*'] = $ret[$sub][$id] = true;
					else $ret[$sub][$id] = false;
				}
			}
			return $ret;
		}

		// called from ngfbAdmin
		public function conflicts() {

			if ( ! is_admin() ) return;	// warnings are only shown on admin pages anyway

			$conflict_log_prefix =  __( 'plugin conflict detected', NGFB_TEXTDOM ) . ' - ';
			$conflict_err_prefix =  __( 'Plugin conflict detected', NGFB_TEXTDOM ) . ' -- ';

			// PHP
			if ( $this->p->is_avail['mbdecnum'] !== true ) {
				$this->p->debug->log( 'mb_decode_numericentity() function missing (required to decode UTF8 entities)' );
				$this->p->notice->err( 
					sprintf( __( 'The <code><a href="%s" target="_blank">mb_decode_numericentity()</a></code> function (available since PHP v4.0.6) is missing.', NGFB_TEXTDOM ),
						__( 'http://php.net/manual/en/function.mb-decode-numericentity.php', NGFB_TEXTDOM ) ).' '.
					__( 'This function is required to decode UTF8 entities.', NGFB_TEXTDOM ).' '.
					__( 'Please update your PHP installation (install \'php-mbstring\' on most Linux distros).', NGFB_TEXTDOM ) );
			}

			// Yoast WordPress SEO
			if ( $this->p->is_avail['seo']['wpseo'] == true ) {
				$opts = get_option( 'wpseo_social' );
				if ( ! empty( $opts['opengraph'] ) ) {
					$this->p->debug->log( $conflict_log_prefix.'wpseo opengraph meta data option is enabled' );
					$this->p->notice->err( $conflict_err_prefix.
						sprintf( __( 'Please uncheck the \'<em>Open Graph meta data</em>\' Facebook option in the <a href="%s">Yoast WordPress SEO plugin Social settings</a>.', NGFB_TEXTDOM ), 
							get_admin_url( null, 'admin.php?page=wpseo_social' ) ) );
				}
				if ( ! empty( $this->p->options['tc_enable'] ) && ! empty( $opts['twitter'] ) ) {
					$this->p->debug->log( $conflict_log_prefix.'wpseo twitter meta data option is enabled' );
					$this->p->notice->err( $conflict_err_prefix.
						sprintf( __( 'Please uncheck the \'<em>Twitter Card meta data</em>\' Twitter option in the <a href="%s">Yoast WordPress SEO plugin Social settings</a>.', NGFB_TEXTDOM ), 
							get_admin_url( null, 'admin.php?page=wpseo_social' ) ) );
				}

				if ( ! empty( $this->p->options['link_publisher_url'] ) && ! empty( $opts['plus-publisher'] ) ) {
					$this->p->debug->log( $conflict_log_prefix.'wpseo google plus publisher option is defined' );
					$this->p->notice->err( $conflict_err_prefix.
						sprintf( __( 'Please remove the \'<em>Google Publisher Page</em>\' value entered in the <a href="%s">Yoast WordPress SEO plugin Social settings</a>.', NGFB_TEXTDOM ), 
							get_admin_url( null, 'admin.php?page=wpseo_social' ) ) );
				}
			}

			// SEO Ultimate
			if ( $this->p->is_avail['seo']['seou'] == true ) {
				$opts = get_option( 'seo_ultimate' );
				if ( ! empty( $opts['modules'] ) && is_array( $opts['modules'] ) ) {
					if ( array_key_exists( 'opengraph', $opts['modules'] ) && $opts['modules']['opengraph'] !== -10 ) {
						$this->p->debug->log( $conflict_log_prefix.'seo ultimate opengraph module is enabled' );
						$this->p->notice->err( $conflict_err_prefix.
							sprintf( __( 'Please disable the \'<em>Open Graph Integrator</em>\' module in the <a href="%s">SEO Ultimate plugin Module Manager</a>.', NGFB_TEXTDOM ), 
								get_admin_url( null, 'admin.php?page=seo' ) ) );
					}
				}
			}

			// All in One SEO Pack
			if ( $this->p->is_avail['seo']['aioseop'] == true ) {
				$opts = get_option( 'aioseop_options' );
				if ( array_key_exists( 'aiosp_google_disable_profile', $opts ) && empty( $opts['aiosp_google_disable_profile'] ) ) {
					$this->p->debug->log( $conflict_log_prefix.'aioseop google plus profile is enabled' );
					$this->p->notice->err( $conflict_err_prefix.
						sprintf( __( 'Please check the \'<em>Disable Google Plus Profile</em>\' option in the <a href="%s">All in One SEO Pack Plugin Options</a>.', NGFB_TEXTDOM ), 
							get_admin_url( null, 'admin.php?page=all-in-one-seo-pack/aioseop_class.php' ) ) );
				}
			}

			/*
			 * Other Conflicting Plugins
			 */

			// WooCommerce ShareYourCart Extension
			if ( class_exists( 'ShareYourCartWooCommerce' ) ) {
				$opts = get_option( 'woocommerce_shareyourcart_settings' );
				if ( ! empty( $opts['enabled'] ) ) {
					$this->p->debug->log( $conflict_log_prefix.'woocommerce shareyourcart extension is enabled' );
					$this->p->notice->err( $conflict_err_prefix.
						__( 'The WooCommerce ShareYourCart Extension does not provide an option to turn off its Open Graph meta tags.', NGFB_TEXTDOM ).' '.
						sprintf( __( 'Please disable the extension on the <a href="%s">ShareYourCart Integration Tab</a>.', NGFB_TEXTDOM ), 
							get_admin_url( null, 'admin.php?page=woocommerce&tab=integration&section=shareyourcart' ) ) );
				}
			}

			// Wordbooker
			if ( function_exists( 'wordbooker_og_tags' ) ) {
				$opts = get_option( 'wordbooker_settings' );
				if ( empty( $opts['wordbooker_fb_disable_og'] ) ) {
					$this->p->debug->log( $conflict_log_prefix.'wordbooker opengraph is enabled' );
					$this->p->notice->err( $conflict_err_prefix.
						sprintf( __( 'Please check the \'<em>Disable in-line production of OpenGraph Tags</em>\' option on the <a href="%s">Wordbooker Options Page</a>.', NGFB_TEXTDOM ), 
							get_admin_url( null, 'options-general.php?page=wordbooker' ) ) );
				}
			}

			// Facebook
  			if ( class_exists( 'Facebook_Loader' ) ) {
                                $this->p->debug->log( $conflict_log_prefix.'facebook plugin is active' );
                                $this->p->notice->err( $conflict_err_prefix. 
					sprintf( __( 'Please <a href="%s">deactivate the Facebook plugin</a> to prevent duplicate Open Graph meta tags in your webpage headers.', NGFB_TEXTDOM ), 
						get_admin_url( null, 'plugins.php' ) ) );
                        }

			// AddThis Social Bookmarking Widget
			if ( defined( 'ADDTHIS_INIT' ) && ADDTHIS_INIT && 
				( ! empty( $this->p->options['plugin_filter_content'] ) || ! empty( $this->p->options['plugin_filter_excerpt'] ) ) ) {

				$this->p->debug->log( $conflict_log_prefix.'addthis has broken excerpt / content filters' );
				$this->p->notice->err( $conflict_err_prefix. 
					__( 'The AddThis Social Bookmarking Widget has incorrectly coded content and excerpt filters.', NGFB_TEXTDOM ).' '.
					sprintf( __( 'Please uncheck the \'<em>Apply Content and Excerpt Filters</em>\' options on the <a href="%s">%s Advanced settings page</a>.', NGFB_TEXTDOM ),  
						$this->p->util->get_admin_url( 'advanced' ), $this->p->cf['full'] ) ).' '.
					__( 'Disabling content filters will prevent shortcodes from being expanded, which may lead to incorrect / incomplete description meta tags.', NGFB_TEXTDOM );
			}

			// Slick Social Share Buttons
			if ( class_exists( 'dc_jqslicksocial_buttons' ) ) {
				$opts = get_option( 'dcssb_options' );
				if ( empty( $opts['disable_opengraph'] ) ) {
					$this->p->debug->log( $conflict_log_prefix.'slick social share buttons opengraph is enabled' );
					$this->p->notice->err( $conflict_err_prefix.
						sprintf( __( 'Please check the \'<em>Disable Opengraph</em>\' option on the <a href="%s">Slick Social Share Buttons</a>.', NGFB_TEXTDOM ), 
							get_admin_url( null, 'admin.php?page=slick-social-share-buttons' ) ) );
				}
			}
		}

		public function is_aop() {
			if ( $this->p->is_avail['aop'] == true && 
				! empty( $this->p->options['plugin_tid'] ) && 
					empty( $this->p->update_error ) )
						return true;
			return false;
		}
	}
}
?>
