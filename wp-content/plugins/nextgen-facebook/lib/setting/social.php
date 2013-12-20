<?php
/*
License: GPLv3
License URI: http://surniaulula.com/wp-content/plugins/nextgen-facebook/license/gpl.txt
Copyright 2012-2013 - Jean-Sebastien Morisset - http://surniaulula.com/
*/

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbAdminSocial' ) && class_exists( 'NgfbAdmin' ) ) {

	class NgfbAdminSocial extends NgfbAdmin {

		public $website = array();

		public function __construct( &$plugin, $id, $name ) {
			$this->p =& $plugin;
			$this->p->debug->mark();
			$this->menu_id = $id;
			$this->menu_name = $name;
			$this->set_objects();
		}

		private function set_objects() {
			foreach ( $this->p->cf['lib']['website'] as $id => $name ) {
				$classname = __CLASS__.ucfirst( $id );
				if ( class_exists( $classname ) )
					$this->website[$id] = new $classname( $this->p );
			}
		}

		protected function show_on_checkboxes( $prefix, $show_on = array() ) {
			$html = '<table>';
			$cols = 0;
			foreach ( apply_filters( $this->p->cf['lca'].'_social_buttons_on', $show_on, $prefix ) as $suffix => $desc ) {
				$cols++;
				$html .= $cols === 1 ? '<tr><td>' : '<td>';
				$html .= $this->form->get_checkbox( $prefix.'_on_'.$suffix ).$desc.'&nbsp; ';
				$html .= $cols === 3 ? '</td></tr>' : '</td>';
			}
			$html .= $cols < 3 ? '</tr>' : '';
			$html .= '</table>';
			return $html;
		}

		protected function add_meta_boxes() {
			// add_meta_box( $id, $title, $callback, $post_type, $context, $priority, $callback_args );
			add_meta_box( $this->pagehook.'_social', 'Social Buttons', array( &$this, 'show_metabox_social' ), $this->pagehook, 'normal' );
			$col = 0;
			$row = 0;
			foreach ( $this->p->cf['lib']['website'] as $id => $name ) {
				$classname = __CLASS__.ucfirst( $id );
				if ( class_exists( $classname ) ) {
					$col = $col == 1 ? 2 : 1;
					$row = $col == 1 ? $row + 1 : $row;
					$pos_id = 'website-row-'.$row.'-col-'.$col;
					$name = $name == 'GooglePlus' ? 'Google+' : $name;
					add_meta_box( $this->pagehook.'_'.$id, $name, array( &$this->website[$id], 'show_metabox_website' ), $this->pagehook, $pos_id );
					add_filter( 'postbox_classes_'.$this->pagehook.'_'.$this->pagehook.'_'.$id, array( &$this, 'add_class_postbox_website' ) );
					$this->website[$id]->form = &$this->get_form_ref();
				}
			}
			// these metabox ids should be closed by default (array_diff() selects everything except)
			$closed_ids = array_diff( array_keys( $this->p->cf['lib']['website'] ), array( 'facebook', 'gplus' ) );
			$this->p->user->reset_metabox_prefs( $this->pagehook, $closed_ids, 'closed' );
		}

		public function add_class_postbox_website( $classes ) {
			array_push( $classes, 'admin_postbox_website' );
			return $classes;
		}

		public function show_metabox_website() {
			echo '<table class="sucom-setting">', "\n";
			foreach ( $this->get_rows() as $row ) echo '<tr>', $row, '</tr>';
			echo '</table>', "\n";
		}

		public function show_metabox_social() {
			echo '<table class="sucom-setting"><tr><td colspan="3">';
			echo '<p>The following social buttons can be added to the content, excerpt, and / or enabled within the ',
				ngfbWidgetSocialSharing::$fullname, ' widget as well (<a href="', 
				get_admin_url( null, 'widgets.php' ), '">see the widgets admin webpage</a>).</p>';
			echo '</td></tr><tr>';
			echo $this->p->util->th( 'Location in Content Text', null, null, '
				Individual social sharing button(s) must also be enabled below.' ); 
			echo '<td>', $this->form->get_select( 'buttons_location_the_content', 
				array( 'top' => 'Top', 'bottom' => 'Bottom', 'both' => 'Both Top and Bottom' ) ), '</td>';
			echo '</tr><tr>';
			echo $this->p->util->th( 'Location in Excerpt Text', null, null, '
				Individual social sharing button(s) must also be enabled below.' ); 
			echo '<td>', $this->form->get_select( 'buttons_location_the_excerpt', 
				array( 'top' => 'Top', 'bottom' => 'Bottom', 'both' => 'Both Top and Bottom' ) ), '</td>';
			echo '</tr><tr>';
			echo $this->p->util->th( 'Include on Index Webpages', null, null, '
				Add the following social sharing buttons to each entry of an index webpage (non-static homepage, category, archive, etc.). 
				By Default, social sharing buttons are <em>not</em> included on index webpages (default is unchecked).
				You must also enable the buttons you want to display by choosing to show the buttons on the content or excerpt.' ); 
			echo '<td>', $this->form->get_checkbox( 'buttons_on_index' ), '</td>';
			echo '</tr><tr>';
			echo $this->p->util->th( 'Include on Static Homepage', null, null, '
				If a static Post or Page has been chosen for the homepage, add the following
				social sharing buttons to the static homepage as well (default is unchecked).
				You must also enable the buttons you want to display by choosing to show the buttons on the content or excerpt.' ); 
			echo '<td>', $this->form->get_checkbox( 'buttons_on_front' ), '</td>';
			echo '</tr>';
			foreach ( $this->get_more_social() as $row ) echo '<tr>'.$row.'</tr>';
			echo '</table>';
		}

		protected function get_more_social() {
			$add_to_checkboxes = '';
			foreach ( $this->p->util->get_post_types( 'buttons' ) as $post_type )
				$add_to_checkboxes .= '<p>'.$this->form->get_fake_checkbox( 'buttons_add_to_'.$post_type->name ).' '.
					$post_type->label.' '.( empty( $post_type->description ) ? '' : '('.$post_type->description.')' ).'</p>';

			return array(
				'<td colspan="2" align="center">'.$this->p->msg->get( 'pro_feature' ).'</td>',

				$this->p->util->th( 'Include on Post Types', null, null, '
				Enabled social sharing buttons are added to the Post, Page, Media and Product custom post types by default.
				If your theme (or another plugin) supports additional custom post types, and you would like to include
				social sharing buttons on these webpages, check the appropriate option(s) here.' ) .
				'<td class="blank">'.$add_to_checkboxes.'</td>',
			);
		}
	}
}
?>
