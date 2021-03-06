<?php
/*
License: GPLv3
License URI: http://surniaulula.com/wp-content/uploads/license/gpl.txt
Copyright 2012-2013 - Jean-Sebastien Morisset - http://surniaulula.com/
*/

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'SucomForm' ) ) {

	class SucomForm {
	
		private $p;
		private $options = array();
		private $defaults = array();

		public $options_name;

		public function __construct( &$plugin, $opts_name, &$opts, &$def_opts ) {
			$this->p =& $plugin;
			$this->p->debug->mark();
			$this->options_name =& $opts_name;
			$this->options =& $opts;
			$this->defaults =& $def_opts;
		}

		public function get_hidden( $name, $value = '' ) {
			if ( empty( $name ) ) return;	// just in case
			// hide the current options value, unless one is given as an argument to the method
			$value = empty( $value ) && $this->in_options( $name ) ? $this->options[$name] : $value;
			return '<input type="hidden" name="'.$this->options_name.'['.$name.']" value="'.$value.'" />';
		}

		public function get_checkbox( $name, $check = array( 1, 0 ), $class = '', $id = '', $disabled = false ) {
			if ( empty( $name ) ) return;	// just in case
			if ( ! is_array( $check ) ) $check = array( 1, 0 );
			if ( $this->in_options( $name.':is' ) && 
				$this->options[$name.':is'] == 'disabled' )
					$disabled = true;
			$html = $disabled === true ? $this->get_hidden( $name ) : $this->get_hidden( 'is_checkbox_'.$name, 1 );
			$html .= '<input type="checkbox"'.
				( $disabled === true ? ' disabled="disabled"' : ' name="'.$this->options_name.'['.$name.']" value="'.$check[0].'"' ).
				( empty( $class ) ? '' : ' class="'.$class.'"' ).
				( empty( $id ) ? '' : ' id="'.$id.'"' ).
				( $this->in_options( $name ) ? checked( $this->options[$name], $check[0], false ) : '' ).
				' title="default is '.( $this->in_defaults( $name ) && $this->defaults[$name] == $check[0] ? 'checked' : 'unchecked' ).
				( $disabled === true ? ' (option disabled)' : '' ).'" />';
			return $html;
		}

		public function get_fake_checkbox( $name, $check = array( '1', '0' ), $class = '', $id = '' ) {
			return $this->get_checkbox( $name, $check, $class, $id, true );
		}

		public function get_radio( $name, $values = array(), $class = '', $id = '', $is_assoc = false, $disabled = false ) {
			if ( empty( $name ) || ! is_array( $values ) ) return;
			if ( $is_assoc == false ) 
				$is_assoc = $this->p->util->is_assoc( $values );
			if ( $this->in_options( $name.':is' ) && 
				$this->options[$name.':is'] == 'disabled' )
					$disabled = true;
			$html = $disabled === true ? $this->get_hidden( $name ) : '';
			foreach ( $values as $val => $desc ) {
				// if the array is NOT associative (so regular numered array), 
				// then the description is used as the saved value as well
				if ( $is_assoc == false ) $val = $desc;
				$html .= '<input type="radio"'.
					( $disabled === true ? ' disabled="disabled"' : ' name="'.$this->options_name.'['.$name.']" value="'.$val.'"' ).
					( empty( $class ) ? '' : ' class="'.$class.'"' ).
					( empty( $id ) ? '' : ' id="'.$id.'"' ).
					( $this->in_options( $name ) ? checked( $this->options[$name], $val, false ) : '' ).
					( $this->in_defaults( $name ) ? ' title="default is '.$values[$this->defaults[$name]].'"' : '' ).
					'/> '.$desc.'&nbsp;&nbsp;';
			}
			return $html;
		}

		public function get_fake_radio( $name, $values = array(), $class = '', $id = '', $is_assoc = false ) {
			return $this->get_radio( $name, $values, $class, $id, $is_assoc, true );
		}

		public function get_select( $name, $values = array(), $class = '', $id = '', $is_assoc = false ) {
			if ( empty( $name ) || ! is_array( $values ) ) return;
			if ( $is_assoc == false ) $is_assoc = $this->p->util->is_assoc( $values );
			$html = '<select name="'.$this->options_name.'['.$name.']"'.
				( empty( $class ) ? '' : ' class="'.$class.'"' ).
				( empty( $id ) ? '' : ' id="'.$id.'"' ).'>';
			foreach ( $values as $val => $desc ) {
				// if the array is NOT associative (so regular numered array), 
				// then the description is used as the saved value as well
				if ( $is_assoc == false ) $val = $desc;
				if ( $val == -1 ) $desc = '(value from settings)';
				else {
					switch ( $name ) {
						case 'og_img_max': if ( $desc === 0 ) $desc .= ' (no images)'; break;
						case 'og_vid_max': if ( $desc === 0 ) $desc .= ' (no videos)'; break;
						default: if ( $desc === '' || $desc === 'none' ) $desc = '[none]'; break;
					}
					if ( $this->in_defaults( $name ) && $val == $this->defaults[$name] ) 
						$desc .= ' (default)';
				}
				$html .= '<option value="'.$val.'"';
				if ( $this->in_options( $name ) )
					$html .= selected( $this->options[$name], $val, false );
				$html .= '>'.$desc.'</option>';
			}
			$html .= '</select>';
			return $html;
		}

		public function get_select_img_size( $name ) {
			if ( empty( $name ) ) return;	// just in case
			global $_wp_additional_image_sizes;
			$size_names = get_intermediate_image_sizes();
			natsort( $size_names );
			$html = '<select name="'.$this->options_name.'['.$name.']">';
			foreach ( $size_names as $size_name ) {
				if ( is_integer( $size_name ) ) continue;
				$size = $this->p->media->get_size_info( $size_name );
				$html .= '<option value="'.$size_name.'" ';
				if ( $this->in_options( $name ) )
					$html .= selected( $this->options[$name], $size_name, false );
				$html .= '>'.$size_name.' [ '.$size['width'].'x'.$size['height'].( $size['crop'] ? " cropped" : "" ).' ]';
				if ( $this->in_defaults( $name ) && $size_name == $this->defaults[$name] ) 
					$html .= ' (default)';
				$html .= '</option>';
			}
			unset ( $size_name );
			$html .= '</select>';
			return $html;
		}

		private function get_id_jquery( $id ) {
			return ( empty( $id ) ? '' : '<script type="text/javascript">
				jQuery(document).ready(function(){
					jQuery(\'#'.$id.'\').focus(function(){ sucomTextLen(\''.$id.'\'); });
					jQuery(\'#'.$id.'\').keyup(function(){ sucomTextLen(\''.$id.'\'); });
				});</script>' );
		}

		public function get_input( $name, $class = '', $id = '', $len = 0, $placeholder = '' ) {
			if ( empty( $name ) ) return;	// just in case
			if ( $this->in_options( $name.':is' ) && 
				$this->options[$name.':is'] == 'disabled' )
					return $this->get_fake_input( $name, $class, $id );
			$html = '';
			$value = $this->in_options( $name ) ? $this->options[$name] : '';
			if ( ! empty( $len ) && ! empty( $id ) )
				$html .= $this->get_id_jquery( $id );
			
			$html .= '<input type="text" name="'.$this->options_name.'['.$name.']"'.
				( empty( $class ) ? '' : ' class="'.$class.'"' ).
				( empty( $id ) ? '' : ' id="'.$id.'"' ).
				( empty( $len ) ? '' : ' maxLength="'.$len.'"' ).
				( empty( $placeholder ) ? '' : ' placeholder="'.$placeholder.'"'.
					' onFocus="if ( this.value == \'\' ) this.value = \''.esc_js( $placeholder ).'\';"'.
					' onBlur="if ( this.value == \''.esc_js( $placeholder ).'\' ) this.value = \'\';"' ).
				' value="'.esc_attr( $value ).'" />';
			return $html;
		}

		public function get_fake_input( $name, $class = '', $id = '' ) {
			return $this->get_hidden( $name ).'<input type="text" disabled="disabled"'.
				( empty( $class ) ? '' : ' class="'.$class.'"' ).
				( empty( $id ) ? '' : ' id="'.$id.'"' ).
				' value="'.esc_attr( $this->options[$name] ).'" />';
		}

		public function get_textarea( $name, $class = '', $id = '', $len = 0, $placeholder = '' ) {
			if ( empty( $name ) ) return;	// just in case
			$html = '';
			$value = $this->in_options( $name ) ? $this->options[$name] : '';
			if ( ! empty( $len ) && ! empty( $id ) )
				$html .= $this->get_id_jquery( $id );

			$html .= '<textarea name="'.$this->options_name.'['.$name.']"'.
				( empty( $class ) ? '' : ' class="'.$class.'"' ).
				( empty( $id ) ? '' : ' id="'.$id.'"' ).
				( empty( $len ) ? '' : ' maxLength="'.$len.'"' ).
				( empty( $len ) && empty( $class ) ? '' : ' rows="'.round($len / 100).'"' ).
				( empty( $placeholder ) ? '' : ' placeholder="'.$placeholder.'"'.
					' onFocus="if ( this.value == \'\' ) this.value = \''.esc_js( $placeholder ).'\';"'.
					' onBlur="if ( this.value == \''.esc_js( $placeholder ).'\' ) this.value = \'\';"' ).
				'>'.esc_attr( $value ).'</textarea>';
			return $html;
		}

		public function get_button( $value, $class = '', $id = '', $url = '', $newtab = false ) {
			$js = $newtab == true ? 
				"window.open('".$url."', '_blank');" :
				"location.href='".$url."';";
			$html = '<input type="button" '.
				( empty( $class ) ? '' : ' class="'.$class.'"' ).
				( empty( $id ) ? '' : ' id="'.$id.'"' ).
				( empty( $url ) ? '' : ' onClick="'.$js.'"' ).
				' value="'.esc_attr( $value ).'" />';
			return $html;
		}

		public function get_text( $value, $class = '', $id = '' ) {
			$html = '<input type="text" '.
				( empty( $class ) ? '' : ' class="'.$class.'"' ).
				( empty( $id ) ? '' : ' id="'.$id.'"' ).
				' value="'.esc_attr( $value ).'" 
				onFocus="this.select();" 
				onMouseUp="return false;" />';
			return $html;
		}

		private function in_options( $name ) {
			return is_array( $this->options ) && 
				array_key_exists( $name, $this->options ) ? true : false;
		}

		private function in_defaults( $name ) {
			return is_array( $this->defaults ) && 
				array_key_exists( $name, $this->defaults ) ? true : false;
		}
	}
}

?>
