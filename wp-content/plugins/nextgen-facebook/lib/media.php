<?php
/*
License: GPLv3
License URI: http://surniaulula.com/wp-content/uploads/license/gpl.txt
Copyright 2012-2013 - Jean-Sebastien Morisset - http://surniaulula.com/
*/

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbMedia' ) ) {

	class NgfbMedia {

		private $p;

		public $ngg;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->debug->mark();

			if ( $this->p->is_avail['ngg'] === true ) {
				require_once ( constant( $this->p->cf['uca'].'_PLUGINDIR' ).'lib/ngg.php' );
				$this->ngg = new NgfbNgg( $plugin );
			}

			add_filter( 'wp_get_attachment_image_attributes', array( &$this, 'add_attachment_image_attributes' ), 10, 2 );
		}

		// $attr = apply_filters( 'wp_get_attachment_image_attributes', $attr, $attachment );
		public function add_attachment_image_attributes( $attr, $attach ) {
			$attr['data-wp-pid'] = $attach->ID;
			return $attr;
		}

		public function get_size_info( $size_name = 'thumbnail' ) {
			global $_wp_additional_image_sizes;
			if ( is_integer( $size_name ) ) return;
			if ( is_array( $size_name ) ) return;
			if ( isset( $_wp_additional_image_sizes[$size_name]['width'] ) )
				$width = intval( $_wp_additional_image_sizes[$size_name]['width'] );
			else $width = get_option( $size_name.'_size_w' );
			if ( isset( $_wp_additional_image_sizes[$size_name]['height'] ) )
				$height = intval( $_wp_additional_image_sizes[$size_name]['height'] );
			else $height = get_option( $size_name.'_size_h' );
			if ( isset( $_wp_additional_image_sizes[$size_name]['crop'] ) )
				$crop = intval( $_wp_additional_image_sizes[$size_name]['crop'] );
			else $crop = get_option( $size_name.'_crop' ) == 1 ? 1 : 0;
			return array( 'width' => $width, 'height' => $height, 'crop' => $crop );
		}

		public function num_remains( &$arr, $num = 0 ) {
			$remains = 0;
			if ( ! is_array( $arr ) ) return false;
			if ( $num > 0 && $num >= count( $arr ) )
				$remains = $num - count( $arr );
			return $remains;
		}

		public function get_post_images( $num = 0, $size_name = 'thumbnail', $post_id, $check_dupes = true ) {
			$this->p->debug->args( array( 'num' => $num, 'size_name' => $size_name, 'post_id' => $post_id, 'check_dupes' => $check_dupes ) );
			$og_ret = array();
			$num_remains = $this->num_remains( $og_ret, $num );
			$og_ret = array_merge( $og_ret, $this->get_meta_image( $num_remains, $size_name, $post_id, $check_dupes ) );
			if ( ! $this->p->util->is_maxed( $og_ret, $num ) ) {
				$num_remains = $this->num_remains( $og_ret, $num );
				$og_ret = array_merge( $og_ret, $this->get_featured( $num_remains, $size_name, $post_id, $check_dupes ) );
			}
			if ( ! $this->p->util->is_maxed( $og_ret, $num ) ) {
				$num_remains = $this->num_remains( $og_ret, $num );
				$og_ret = array_merge( $og_ret, $this->get_attached_images( $num_remains, $size_name, $post_id, $check_dupes ) );
			}
			return $og_ret;
		}

		public function get_featured( $num = 0, $size_name = 'thumbnail', $post_id, $check_dupes = true ) {
			$this->p->debug->args( array( 'num' => $num, 'size_name' => $size_name, 'post_id' => $post_id, 'check_dupes' => $check_dupes ) );
			$og_ret = array();
			$og_image = array();
			if ( ! empty( $post_id ) && $this->p->is_avail['postthumb'] == true && has_post_thumbnail( $post_id ) ) {
				$pid = get_post_thumbnail_id( $post_id );

				// featured images from ngg pre-v2 had 'ngg-' prefix
				if ( $this->p->is_avail['ngg'] === true && is_string( $pid ) && substr( $pid, 0, 4 ) == 'ngg-' ) {
					list( $og_image['og:image'], $og_image['og:image:width'], $og_image['og:image:height'],
						$og_image['og:image:cropped'] ) = $this->ngg->get_image_src( $pid, $size_name, $check_dupes );
				} else {
					list( $og_image['og:image'], $og_image['og:image:width'], $og_image['og:image:height'], 
						$og_image['og:image:cropped'] ) = $this->get_attachment_image_src( $pid, $size_name, $check_dupes );
				}
				if ( ! empty( $og_image['og:image'] ) )
					$this->p->util->push_max( $og_ret, $og_image, $num );
			}
			return apply_filters( $this->p->cf['lca'].'_og_featured', $og_ret, $num, $size_name, $post_id, $check_dupes );
		}

		public function get_first_attached_image_id( $post_id ) {
			if ( ! empty( $post_id ) ) {
				$images = get_children( array( 'post_parent' => $post_id, 'post_type' => 'attachment', 'post_mime_type' => 'image' ) );
				$attach = reset( $images );
				if ( ! empty( $attach->ID ) )
					return $attach->ID;
			}
			return;
		}

		public function get_attachment_image( $num = 0, $size_name = 'thumbnail', $attach_id, $check_dupes = true ) {
			$this->p->debug->args( array( 'num' => $num, 'size_name' => $size_name, 'attach_id' => $attach_id, 'check_dupes' => $check_dupes ) );
			$og_ret = array();
			if ( ! empty( $attach_id ) ) {
				if ( wp_attachment_is_image( $attach_id ) ) {	// since wp 2.1.0 
					$og_image = array();
					list( $og_image['og:image'], $og_image['og:image:width'], $og_image['og:image:height'],
						$og_image['og:image:cropped'] ) = $this->get_attachment_image_src( $attach_id, $size_name, $check_dupes );
					if ( ! empty( $og_image['og:image'] ) &&
						$this->p->util->push_max( $og_ret, $og_image, $num ) )
							return $og_ret;
				} else $this->p->debug->log( 'attachment id '.$attach_id.' is not an image' );
			}
			return $og_ret;
		}

		public function get_attached_images( $num = 0, $size_name = 'thumbnail', $post_id, $check_dupes = true ) {
			$this->p->debug->args( array( 'num' => $num, 'size_name' => $size_name, 'post_id' => $post_id, 'check_dupes' => $check_dupes ) );
			$og_ret = array();
			if ( ! empty( $post_id ) ) {
				$images = get_children( array( 'post_parent' => $post_id, 'post_type' => 'attachment', 'post_mime_type' => 'image') );
				if ( is_array( $images ) )
					$attach_ids = array();
					foreach ( $images as $attach ) {
						if ( ! empty( $attach->ID ) )
							$attach_ids[] = $attach->ID;
					}
					rsort( $attach_ids, SORT_NUMERIC ); 
					$this->p->debug->log( 'found '.count( $attach_ids ).' attached images for post id '.$post_id );
					$attach_ids = apply_filters( $this->p->cf['lca'].'_attached_image_ids', $attach_ids, $post_id );
					foreach ( $attach_ids as $pid ) {
						$og_image = array();
						list( $og_image['og:image'], $og_image['og:image:width'], $og_image['og:image:height'],
							$og_image['og:image:cropped'] ) = $this->get_attachment_image_src( $pid, $size_name, $check_dupes );
						if ( ! empty( $og_image['og:image'] ) &&
							$this->p->util->push_max( $og_ret, $og_image, $num ) )
								break;	// end foreach and apply filters
					}
			}
			return apply_filters( $this->p->cf['lca'].'_og_attached_images', $og_ret, $num, $size_name, $post_id, $check_dupes );
		}

		public function get_attachment_image_src( $pid, $size_name = 'thumbnail', $check_dupes = true ) {
			$this->p->debug->args( array( 'pid' => $pid, 'size_name' => $size_name, 'check_dupes' => $check_dupes ) );
			$size_info = $this->get_size_info( $size_name );
			$img_url = '';
			$img_width = -1;
			$img_height = -1;
			$img_inter = true;
			$img_cropped = empty( $size_info['crop'] ) ? 'false' : 'true';	// visual feedback, not a real true/false
			$ret_empty = array( null, null, null, null );

			if ( ! wp_attachment_is_image( $pid ) ) {
				$this->p->debug->log( 'exiting early: attachment '.$pid.' is not an image' ); 
				return $ret_empty; 
			}

			list( $img_url, $img_width, $img_height, $img_inter ) = image_downsize( $pid, $size_name );	// since wp 2.5.0
			$this->p->debug->log( 'image_downsize() = '.$img_url.' ('.$img_width.'x'.$img_height.')' );

			// make sure the returned image size matches the size we requested, if not then possibly resize the image
			// we do this because image_downsize() does not always return accurate image sizes
			if ( ! empty( $this->p->options['og_img_resize'] ) && $size_name == NGFB_OG_SIZE_NAME ) {

				// get the actual image sizes from the metadata array
				$img_meta = wp_get_attachment_metadata( $pid );

				// are our intermediate image sizes correct in the metadata array?
				if ( empty( $img_meta['sizes'][$size_name] ) ) {
					$this->p->debug->log( $size_name.' size not defined in the image meta' );
					$is_correct_width = false;
					$is_correct_height = false;
				} else {
					$is_correct_width = ! empty( $img_meta['sizes'][$size_name]['width'] ) &&
						$img_meta['sizes'][$size_name]['width'] == $size_info['width'] ? true : false;
					$is_correct_height = ! empty( $img_meta['sizes'][$size_name]['height'] ) &&
						$img_meta['sizes'][$size_name]['height'] == $size_info['height'] ? true : false;
				}

				if ( empty( $img_meta['width'] ) || empty( $img_meta['height'] ) ) {
					$this->p->debug->log( 'wp_get_attachment_metadata() returned empty original image sizes' );

				// if the full / original image size is too small, get the full size image URL instead
				} elseif ( $img_meta['width'] < $size_info['width'] && $img_meta['height'] < $size_info['height'] ) {

					$this->p->debug->log( 'original meta sizes '.$img_meta['width'].'x'.$img_meta['height'].' smaller than '.
						$size_name.' '.$size_info['width'].'x'.$size_info['height'].' - fetching "full" image size attributes' );

					list( $img_url, $img_width, $img_height, $img_inter ) = image_downsize( $pid, 'full' );
					$this->p->debug->log( 'image_downsize() = '.$img_url.' ('.$img_width.'x'.$img_height.')' );

				// wordpress returns image sizes based on the information in the metadata array
				// check to see if our intermediate image sizes are correct in the metadata array
				} elseif ( ( empty( $size_info['crop'] ) && ( ! $is_correct_width && ! $is_correct_height ) ) ||
					( ! empty( $size_info['crop'] ) && ( ! $is_correct_width || ! $is_correct_height ) ) ) {

					$this->p->debug->log( 'image metadata ('.$img_meta['sizes'][$size_name]['width'].'x'.
						$img_meta['sizes'][$size_name]['height'].') does not match '.$size_name.
						' ('.$size_info['width'].'x'.$size_info['height'].', cropped '.$img_cropped.')' );

					$fullsizepath = get_attached_file( $pid );
					$this->p->debug->log( 'calling image_make_intermediate_size()' );
					$resized = image_make_intermediate_size( $fullsizepath, $size_info['width'], $size_info['height'], $size_info['crop'] );
					$this->p->debug->log( 'image_make_intermediate_size() reported '.( $resized === false ? 'failure' : 'success' ) );

					// update the image metadata 
					if ( $resized !== false ) {
						$img_meta['sizes'][$size_name] = $resized;
						wp_update_attachment_metadata( $pid, $img_meta );
						// request the image size again to validate
						list( $img_url, $img_width, $img_height, $img_inter ) = image_downsize( $pid, $size_name );
						$this->p->debug->log( 'image_downsize() = '.$img_url.' ('.$img_width.'x'.$img_height.')' );
					}
				}
			}
			if ( empty( $img_url ) ) { 
				$this->p->debug->log( 'exiting early: returned image_downsize() url is empty' );
				return $ret_empty;
			}
			if ( ! empty( $this->p->options['plugin_ignore_small_img'] ) ) {
				$is_correct_width = $img_width >= $size_info['width'] ? true : false;
				$is_correct_height = $img_height >= $size_info['height'] ? true : false;
				if ( ( empty( $size_info['crop'] ) && ( ! $is_correct_width && ! $is_correct_height ) ) ||
					( ! empty( $size_info['crop'] ) && ( ! $is_correct_width || ! $is_correct_height ) ) ) {
						$this->p->debug->log( 'exiting early: returned image dimensions are smaller than '.
							'('.$size_info['width'].'x'.$size_info['height'].', cropped '.$img_cropped.')' );
						return $ret_empty;
				}

			}
			$img_url = $this->p->util->fix_relative_url( $img_url );
			if ( $check_dupes == false || $this->p->util->is_uniq_url( $img_url ) )
				return array( $this->p->util->rewrite_url( $img_url ), $img_width, $img_height, $img_cropped );
		}

		public function get_meta_image( $num = 0, $size_name = 'thumbnail', $post_id, $check_dupes = true ) {
			$this->p->debug->args( array( 'num' => $num, 'size_name' => $size_name, 'post_id' => $post_id, 'check_dupes' => $check_dupes ) );
			$image = array();
			$og_ret = array();
			if ( empty( $post_id ) )	// post id must be > 0 to have post meta
				return $og_ret;

			$pid = $this->p->meta->get_options( $post_id, 'og_img_id' );
			$pre = $this->p->meta->get_options( $post_id, 'og_img_id_pre' );
			$img_url = $this->p->meta->get_options( $post_id, 'og_img_url' );

			if ( $pid > 0 ) {
				if ( $this->p->is_avail['ngg'] === true && $pre == 'ngg' ) {
					$this->p->debug->log( 'found custom meta image id = '.$pre.'-'.$pid );
					$image = $this->ngg->get_image_src( $pre.'-'.$pid, $size_name, $check_dupes );
				} else {
					$this->p->debug->log( 'found custom meta image id = '.$pid );
					$image = $this->get_attachment_image_src( $pid, $size_name, $check_dupes );
				}
			} elseif ( ! empty( $img_url ) ) {
				$this->p->debug->log( 'found custom meta image url = "'.$img_url.'"' );
				array_push( $image, $img_url, -1, -1, -1 );	// must have four elements for list() to follow
			}
			if ( ! empty( $image ) ) {
				list( $og_image['og:image'], $og_image['og:image:width'], 
					$og_image['og:image:height'], $og_image['og:image:cropped'] ) = $image;
				if ( ! empty( $og_image['og:image'] ) &&
					$this->p->util->push_max( $og_ret, $og_image, $num ) )
						return $og_ret;
			}
			return $og_ret;
		}

		public function get_default_image( $num = 0, $size_name = 'thumbnail', $check_dupes = true ) {
			$this->p->debug->args( array( 'num' => $num, 'size_name' => $size_name, 'check_dupes' => $check_dupes ) );
			$og_ret = array();
			$og_image = array();
			$pid = empty( $this->p->options['og_def_img_id'] ) ? '' : $this->p->options['og_def_img_id'];
			$pre = empty( $this->p->options['og_def_img_id_pre'] ) ? '' : $this->p->options['og_def_img_id_pre'];
			$url = empty( $this->p->options['og_def_img_url'] ) ? '' : $this->p->options['og_def_img_url'];
			if ( $pid === '' && $url === '' ) {
				$this->p->debug->log( 'exiting early: no default image defined' );
				return $og_ret;
			}
			if ( $pid > 0 ) {
				if ( $this->p->is_avail['ngg'] === true && $pre == 'ngg' )
					list( $og_image['og:image'], $og_image['og:image:width'], $og_image['og:image:height'], 
						$og_image['og:image:cropped'] ) = $this->ngg->get_image_src( $pre.'-'.$pid, $size_name, $check_dupes );
				else
					list( $og_image['og:image'], $og_image['og:image:width'], $og_image['og:image:height'],
						$og_image['og:image:cropped'] ) = $this->get_attachment_image_src( $pid, $size_name, $check_dupes );
			}
			if ( empty( $og_image['og:image'] ) && ! empty( $url ) ) {
				$og_image = array();	// clear all array values
				$og_image['og:image'] = $url;
				$this->p->debug->log( 'using default img url = '.$og_image['og:image'] );
			}
			// returned array must be two-dimensional
			if ( ! empty( $og_image['og:image'] ) && 
				$this->p->util->push_max( $og_ret, $og_image, $num ) )
					return $og_ret;
			return $og_ret;
		}

		public function get_content_images( $num = 0, $size_name = 'thumbnail', $use_post = true, $check_dupes = true, $content = null ) {
			$this->p->debug->args( array( 'num' => $num, 'size_name' => $size_name, 'use_post' => $use_post, 'check_dupes' => $check_dupes, 'content' => strlen( $content ).' chars' ) );
			$og_ret = array();
			$size_info = $this->get_size_info( $size_name );
			// allow custom content to be passed
			if ( empty( $content ) )
				$content = $this->p->webpage->get_content( $use_post );
			if ( empty( $content ) ) { 
				$this->p->debug->log( 'exiting early: empty post content' ); 
				return $og_ret; 
			}
			// check html tags for ngg images
			if ( $this->p->is_avail['ngg'] === true ) {
				$og_ret = $this->ngg->get_content_images( $num, $size_name, $use_post, $check_dupes, $content );
				if ( $this->p->util->is_maxed( $og_ret, $num ) )
					return $og_ret;
			}
			// img attributes in order of preference
			if ( preg_match_all( '/<(img)[^>]*? (data-wp-pid)=[\'"]([^\'"]+)[\'"][^>]*>/is', $content, $match, PREG_SET_ORDER ) ||
				preg_match_all( '/<(img)[^>]*? (data-share-src|src)=[\'"]([^\'"]+)[\'"][^>]*>/is', $content, $match, PREG_SET_ORDER ) ) {

				$this->p->debug->log( count( $match ).' x matching <img/> html tag(s) found' );

				foreach ( $match as $img_num => $img_arr ) {
					$tag_value = $img_arr[0];
					$tag_name = $img_arr[1];
					$attr_name = $img_arr[2];
					$attr_value = $img_arr[3];
					$this->p->debug->log( 'match '.$img_num.': '.$tag_name.' '.$attr_name.'="'.$attr_value.'"' );

					$og_image = array();
					switch ( $attr_name ) {
						case 'data-wp-pid' :
							list( $og_image['og:image'], $og_image['og:image:width'], $og_image['og:image:height'], 
								$og_image['og:image:cropped'] ) = $this->get_attachment_image_src( $attr_value, $size_name, $check_dupes );
							break;
						default :
							$og_image = array(
								'og:image' => $this->p->util->fix_relative_url( $attr_value ),
								'og:image:width' => -1,
								'og:image:height' => -1,
							);
							// check for ngg pre-v2 image pids in the url
							if ( $this->p->is_avail['ngg'] === true && 
								preg_match( '/\/cache\/([0-9]+)_(crop)?_[0-9]+x[0-9]+_[^\/]+$/', $og_image['og:image'], $match) ) {
		
								list( $og_image['og:image'], $og_image['og:image:width'], $og_image['og:image:height'],
									$og_image['og:image:cropped'] ) = $this->ngg->get_image_src( 'ngg-'.$match[1], $size_name, $check_dupes );
	
							// recognize gravatar images in the content
							} elseif ( preg_match( '/^https?:\/\/([^\.]+\.)?gravatar\.com\/avatar\/[a-zA-Z0-9]+/', $og_image['og:image'], $match) ) {

								$og_image['og:image'] = $match[0].'?s='.$size_info['width'].'&d=404&r=G';
								$og_image['og:image:width'] = $size_info['width'];
								$og_image['og:image:height'] = $size_info['width'];

							// try and get the width and height from the image tag
							} elseif ( ! empty( $og_image['og:image'] ) ) {

								if ( preg_match( '/ width=[\'"]?([0-9]+)[\'"]?/i', $tag_value, $match) ) 
									$og_image['og:image:width'] = $match[1];
								if ( preg_match( '/ height=[\'"]?([0-9]+)[\'"]?/i', $tag_value, $match) ) 
									$og_image['og:image:height'] = $match[1];
							}

							// make sure the image width and height are large enough
							if ( $attr_name == 'data-share-src' || 
								( $attr_name == 'src' && empty( $this->p->options['plugin_ignore_small_img'] ) ) ||
								( $attr_name == 'src' && $size_info['crop'] === 1 && 
									$og_image['og:image:width'] >= $size_info['width'] && $og_image['og:image:height'] >= $size_info['height'] ) ||
								( $attr_name == 'src' && $size_info['crop'] !== 1 && 
									( $og_image['og:image:width'] >= $size_info['width'] || $og_image['og:image:height'] >= $size_info['height'] ) ) ) {
								// data-share-src attribute used and/or image size is acceptable
							} else {
								$this->p->debug->log( $attr_name.' image rejected: width and height attributes missing or too small ('.
									$og_image['og:image:width'].'x'.$og_image['og:image:height'].')' );
								$og_image = array();
							}
							break;
					}
					if ( ! empty( $og_image['og:image'] ) )
						if ( $check_dupes === false || $this->p->util->is_uniq_url( $og_image['og:image'] ) )
							if ( $this->p->util->push_max( $og_ret, $og_image, $num ) )
								return $og_ret;
				}
				return $og_ret;
			}
			$this->p->debug->log( 'no matching <img/> html tag(s) found' );
			return $og_ret;
		}

		// called by TwitterCard class to build the Gallery Card
		public function get_gallery_images( $num = 0, $size_name = 'large', $want_this = 'gallery', $check_dupes = false ) {
			$this->p->debug->args( array( 'num' => $num, 'size_name' => $size_name, 'want_this' => $want_this, 'check_dupes' => $check_dupes ) );
			global $post;
			$og_ret = array();
			if ( $want_this == 'gallery' ) {
				if ( empty( $post ) ) { 
					$this->p->debug->log( 'exiting early: empty post object' ); 
					return $og_ret;
				} elseif ( empty( $post->post_content ) ) { 
					$this->p->debug->log( 'exiting early: empty post content' ); 
					return $og_ret;
				}
				if ( preg_match( '/\[(gallery)[^\]]*\]/im', $post->post_content, $match ) ) {
					$shortcode_type = strtolower( $match[1] );
					$this->p->debug->log( '['.$shortcode_type.'] shortcode found' );
					switch ( $shortcode_type ) {
						case 'gallery' :
							$content = do_shortcode( $match[0] );
							$content = preg_replace( '/\['.$shortcode_type.'[^\]]*\]/', '', $content );	// prevent loops, just in case
							// provide the expanded content and extract images
							$og_ret = array_merge( $og_ret, 
								$this->p->media->get_content_images( $num, $size_name, null, $check_dupes, $content ) );
							if ( ! empty( $og_ret ) ) 
								return $og_ret;		// return immediately and ignore any other type of image
							break;
					}
				} else $this->p->debug->log( '[gallery] shortcode not found' );
			}
			// check for ngg gallery
			if ( $this->p->is_avail['ngg'] === true ) {
				$og_ret = $this->ngg->get_gallery_images( $num , $size_name, $want_this, $check_dupes );
				if ( $this->p->util->is_maxed( $og_ret, $num ) )
					return $og_ret;
			}
			$this->p->util->slice_max( $og_ret, $num );
			return $og_ret;
		}

		public function get_meta_video( $num = 0, $post_id, $check_dupes = true ) {
			$this->p->debug->args( array( 'num' => $num, 'post_id' => $post_id, 'check_dupes' => $check_dupes ) );
			$og_ret = array();
			if ( empty( $post_id ) ) 	// post id must be > 0 to have post meta
				return $og_ret;

			$video_url = $this->p->meta->get_options( $post_id, 'og_vid_url' );

			if ( ( $check_dupes == false && ! empty( $video_url ) ) || $this->p->util->is_uniq_url( $video_url ) ) {
				$this->p->debug->log( 'found custom meta video url = "'.$video_url.'"' );
				$og_video = $this->get_video_info( $video_url );
				if ( empty( $og_video ) )	// fallback to custom video URL
					$og_video['og:video'] = $video_url;
				if ( $this->p->util->push_max( $og_ret, $og_video, $num ) ) 
					return $og_ret;
			}
			return $og_ret;
		}

		/* Purpose: Check the content for generic <iframe|embed/> html tags. Apply ngfb_content_videos filter for more specialized checks. */
		public function get_content_videos( $num = 0, $use_post = true, $check_dupes = true ) {
			$this->p->debug->args( array( 'num' => $num, 'check_dupes' => $check_dupes ) );
			$og_ret = array();
			$content = $this->p->webpage->get_content( $use_post );
			if ( empty( $content ) ) { 
				$this->p->debug->log( 'exiting early: empty post content' ); 
				return $og_ret; 
			}
			// detect standard iframe/embed tags - use the ngfb_content_videos filter for custom html5/javascript methods
			// <iframe src="//player.vimeo.com/video/80574920?title=0&amp;byline=0&amp;portrait=0" width="500" height="281" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe> <p><a href="http://vimeo.com/80574920">This is Localism!</a> from <a href="http://vimeo.com/user23071933">Localism!</a> on <a href="https://vimeo.com">Vimeo</a>.</p>
			if ( preg_match_all( '/<(iframe|embed)[^>]*? src=[\'"]([^\'"]+\/(embed|video)\/[^\'"]+)[\'"][^>]*>/i', $content, $match_all, PREG_SET_ORDER ) ) {
				$this->p->debug->log( count( $match_all ).' x video <iframe|embed/> html tag(s) found' );
				foreach ( $match_all as $media ) {
					$this->p->debug->log( '<'.$media[1].'/> html tag found = '.$media[2] );
					$embed_url = $media[2];
					if ( strpos( $embed_url, '?' ) !== false ) {	// remove the query string
						$embed_url_parts = explode( '?', $embed_url );
						$embed_url = reset( $embed_url_parts );
					}
					if ( ( $check_dupes == false && ! empty( $embed_url ) ) || $this->p->util->is_uniq_url( $embed_url ) ) {
						$embed_width = preg_match( '/ width=[\'"]?([0-9]+)[\'"]?/i', $media[0], $match) ? $match[1] : -1;
						$embed_height = preg_match( '/ height=[\'"]?([0-9]+)[\'"]?/i', $media[0], $match) ? $match[1] : -1;
						$og_video = $this->get_video_info( $embed_url, $embed_width, $embed_height );
						if ( ! empty( $og_video ) && 
							$this->p->util->push_max( $og_ret, $og_video, $num ) ) 
								return $og_ret;
					}
				}
			} else $this->p->debug->log( 'no <iframe|embed/> html tag(s) found' );

			$filter_name = $this->p->cf['lca'].'_content_videos';
			if ( has_filter( $filter_name ) ) {
				$this->p->debug->log( 'applying filter '.$filter_name ); 
				// should return an array of arrays
				if ( ( $match_all = apply_filters( $this->p->cf['lca'].'_content_videos', false, $content ) ) !== false ) {
					if ( is_array( $match_all ) ) {
						$this->p->debug->log( count( $match_all ).' x videos returned by '.$filter_name.' filter' );
						foreach ( $match_all as $media ) {
							// media = array( url, width, height )
							if ( ( $check_dupes == false && ! empty( $media[0] ) ) || $this->p->util->is_uniq_url( $media[0] ) ) {
								$og_video = $this->get_video_info( $media[0], $media[1], $media[2] );	// url, width, height
								if ( ! empty( $og_video ) && 
									$this->p->util->push_max( $og_ret, $og_video, $num ) ) 
										return $og_ret;
							}
						}
					} else $this->p->debug->log( $filter_name.' filter did not return false or an array' ); 
				}
			}
			return $og_ret;
		}

		private function get_video_info( $embed_url, $embed_width = 0, $embed_height = 0 ) {
			if ( empty( $embed_url ) ) 
				return array();
			$og_video = array(
				'og:video' => '',
				'og:video:type' => 'application/x-shockwave-flash',
				'og:video:width' => $embed_width,
				'og:video:height' => $embed_height,
				'og:image' => '',
				'og:image:width' => -1,
				'og:image:height' => -1,
			);
			$prot = empty( $this->p->options['og_vid_https'] ) ? 'http://' : 'https://';
			/*
			 * YouTube video API
			 */
			if ( preg_match( '/^.*(youtube\.com|youtube-nocookie\.com|youtu\.be)\/(watch\?v=)?([^\?\&\#]+).*$/', $embed_url, $match ) ) {
				$vid_name = preg_replace( '/^.*\//', '', $match[3] );
				$og_video['og:video'] = $prot.'www.youtube.com/v/'.$vid_name;
				$og_video['og:image'] = $prot.'img.youtube.com/vi/'.$vid_name.'/0.jpg';	// 0, hqdefault, maxresdefault
				if ( function_exists( 'simplexml_load_string' ) ) {
					$api_url = $prot.'gdata.youtube.com/feeds/api/videos?q='.$vid_name.'&max-results=1&format=5';
					$this->p->debug->log( 'fetching video details from '.$api_url );
					$xml = @simplexml_load_string( $this->p->cache->get( $api_url, 'raw', 'transient' ) );
					if ( ! empty( $xml->entry[0] ) ) {
						$this->p->debug->log( 'setting og:video and og:image from youtube api xml' );
						$media = $xml->entry[0]->children( 'media', true );
						$content = $media->group->content[0]->attributes();
						if ( $content['type'] == 'application/x-shockwave-flash' )
							$og_video['og:video'] = (string) $content['url'];
						// find the largest thumbnail available (by width)
						foreach ( $media->group->thumbnail as $thumb ) {
							$thumb_attr = $thumb->attributes();
							if ( ! empty( $thumb_attr['width'] ) ) {
								$thumb_url = (string) $thumb_attr['url'];
								$thumb_width = (string) $thumb_attr['width'];
								$thumb_height = (string) $thumb_attr['height'];
								if ( empty( $og_video['og:image:width'] ) || $thumb_width > $og_video['og:image:width'] )
									list( $og_video['og:image'], $og_video['og:image:width'], $og_video['og:image:height'] ) = 
										array( $thumb_url, $thumb_width, $thumb_height );
							}
						}
					}
				} else $this->p->debug->log( 'simplexml_load_string function is missing' );

				// the google youtube api does not provide video width/height (seriously), 
				// so if missing from args, get them from the youtube opengraph meta tags
				if ( ! empty( $og_video['og:video'] ) && 
					( $og_video['og:video:width'] <= 0 || $og_video['og:video:height'] <= 0 ) ) {

					$og_fetch = $prot.'www.youtube.com/watch?v='.$vid_name;
					$this->p->debug->log( 'fetching missing video width/height from '.$og_fetch );
					if ( ( $og_html = $this->p->cache->get( $og_fetch, 'raw', 'transient' ) ) !== false ) {
						$og_meta = $this->p->og->parse( $og_html );	// in SucomOpengraph parent
						$og_video['og:video:width'] = $og_meta['og:video:width'];
						$og_video['og:video:height'] = $og_meta['og:video:height'];
					}
				}
			/*
			 * Vimeo video API
			 */
			} elseif ( preg_match( '/^.*(vimeo\.com)\/(.*\/)?([^\/\?\&\#]+).*$/', $embed_url, $match ) ) {
				$vid_name = preg_replace( '/^.*\//', '', $match[3] );
				$og_video['og:video'] = $prot.'vimeo.com/moogaloop.swf?clip_id='.$vid_name;
				if ( function_exists( 'simplexml_load_string' ) ) {
					$api_url = $prot.'vimeo.com/api/oembed.xml?url=http%3A//vimeo.com/'.$vid_name;
					$this->p->debug->log( 'fetching video details from '.$api_url );
					$xml = @simplexml_load_string( $this->p->cache->get( $api_url, 'raw', 'transient' ) );
					if ( ! empty( $xml->thumbnail_url ) ) {
						$this->p->debug->log( 'setting og:video and og:image from vimeo api xml' );
						$og_video['og:image'] = (string) $xml->thumbnail_url;
						$og_video['og:image:width'] = $og_video['og:video:width'] = (string) $xml->thumbnail_width;
						$og_video['og:image:height'] = $og_video['og:video:height'] = (string) $xml->thumbnail_height;
					}
				} else $this->p->debug->log( 'simplexml_load_string function is missing' );
			}
			/*
			 * Other video APIs
			 */
			$og_video = apply_filters( $this->p->cf['lca'].'_video_info', $og_video, $embed_url, $embed_width, $embed_height );

			$this->p->debug->log( 'video = '.$og_video['og:video'].' ('.$og_video['og:video:width'].'x'.$og_video['og:video:height'].')' );
			$this->p->debug->log( 'image = '.$og_video['og:image'].' ('.$og_video['og:image:width'].'x'.$og_video['og:image:height'].')' );

			if ( empty( $og_video['og:video'] ) ) {
				unset ( 
					$og_video['og:video'],
					$og_video['og:video:type'],
					$og_video['og:video:width'],
					$og_video['og:video:height']
				);
			}

			if ( empty( $og_video['og:video'] ) && empty( $og_video['og:image'] ) ) 
				return array();
			else return $og_video;
		}
	}
}
?>
