<?php
/*
License: GPLv3
License URI: http://surniaulula.com/wp-content/plugins/nextgen-facebook/license/gpl.txt
Copyright 2012-2013 - Jean-Sebastien Morisset - http://surniaulula.com/
*/

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbWidgetSocialSharing' ) && class_exists( 'WP_Widget' ) ) {

	class NgfbWidgetSocialSharing extends WP_Widget {

		public static $fullname = 'NGFB Social Sharing';

		public function __construct() {
			global $ngfb;
			$widget_ops = array( 
				'classname' => 'ngfb-widget-buttons',
				'description' => 'The '.$ngfb->cf['full'].' social sharing buttons widget.'
			);
			$this->WP_Widget( 'ngfb-widget-buttons', self::$fullname, $widget_ops );
		}
	
		public function widget( $args, $instance ) {
			if ( is_feed() ) return;	// nothing to do in the feeds
			if ( ! empty( $_SERVER['NGFB_DISABLE'] ) ) return;

			global $ngfb;
			if ( is_object( $ngfb->social ) && $ngfb->social->is_disabled() ) {
				$ngfb->debug->log( 'widget buttons skipped: buttons disabled' );
				return;
			}
			extract( $args );

			if ( $ngfb->is_avail['cache']['transient'] ) {
				$sharing_url = $ngfb->util->get_sharing_url();
				$cache_salt = __METHOD__.'(lang:'.get_locale().'_widget:'.$this->id.'_sharing_url:'.$sharing_url.')';
				$cache_id = $ngfb->cf['lca'].'_'.md5( $cache_salt );
				$cache_type = 'object cache';
				$ngfb->debug->log( $cache_type.': widget_html transient salt '.$cache_salt );
				$widget_html = get_transient( $cache_id );
				if ( $widget_html !== false ) {
					$ngfb->debug->log( $cache_type.': widget_html retrieved from transient '.$cache_id );
					$ngfb->debug->show_html();
					echo $widget_html;
					return;
				}
			}

			// sort enabled social buttons by their preferred order
			$sorted_ids = array();
			foreach ( $ngfb->cf['opt']['pre'] as $id => $pre )
				if ( array_key_exists( $id, $instance ) && (int) $instance[$id] )
					$sorted_ids[$ngfb->options[$pre.'_order'].'-'.$id] = $id;
			unset ( $id, $pre );
			ksort( $sorted_ids );

			$atts = array( 'is_widget' => 1, 'css_id' => $args['widget_id'] );
			$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );

			$widget_html = '<!-- '.$ngfb->cf['lca'].' '.$args['widget_id'].' begin -->';
			$widget_html .= $before_widget;
			if ( $title ) 
				$widget_html .= $before_title.$title.$after_title;
			$widget_html .= $ngfb->social->get_html( $sorted_ids, $atts );
			$widget_html .= $after_widget;
			$widget_html .= '<!-- '.$ngfb->cf['lca'].' '.$args['widget_id'].' end -->';

			if ( $ngfb->is_avail['cache']['transient'] ) {
				set_transient( $cache_id, $widget_html, $ngfb->cache->object_expire );
				$ngfb->debug->log( $cache_type.': widget_html saved to transient '.$cache_id.' ('.$ngfb->cache->object_expire.' seconds)');
			}
			$ngfb->debug->show_html();
			echo $widget_html;
		}
	
		public function update( $new_instance, $old_instance ) {
			global $ngfb;
			$instance = $old_instance;
			$instance['title'] = strip_tags( $new_instance['title'] );
			foreach ( $ngfb->cf['lib']['website'] as $id => $name )
				$instance[$id] = empty( $new_instance[$id] ) ? 0 : 1;
			unset( $name, $id );
			return $instance;
		}
	
		public function form( $instance ) {
			global $ngfb;
			$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : 'Share It';
			echo "\n", '<p><label for="', $this->get_field_id( 'title' ), '">Title (Leave Blank for No Title):</label>',
				'<input class="widefat" id="', $this->get_field_id( 'title' ), 
					'" name="', $this->get_field_name( 'title' ), 
					'" type="text" value="', $title, '" /></p>', "\n";
	
			foreach ( $ngfb->cf['lib']['website'] as $id => $name ) {
				$classname = $ngfb->cf['cca'].'Social'.ucfirst( $id );
				if ( class_exists( $classname ) ) {
					$name = $name == 'GooglePlus' ? 'Google+' : $name;
					echo '<p><label for="', $this->get_field_id( $id ), '">', 
						'<input id="', $this->get_field_id( $id ), 
						'" name="', $this->get_field_name( $id ), 
						'" value="1" type="checkbox" ';
					if ( ! empty( $instance[$id] ) )
						echo checked( 1 , $instance[$id] );
					echo ' /> ', $name;
					switch ( $id ) {
						case 'pinterest' : echo ' (not added on indexes)'; break;
						case 'tumblr' : echo ' (shares link on indexes)'; break;
					}
					echo '</label></p>', "\n";
				}
			}
			unset( $id, $name );
		}
	}
	add_action( 'widgets_init', create_function( '', 'return register_widget( "NgfbWidgetSocialSharing" );' ) );
}
?>
