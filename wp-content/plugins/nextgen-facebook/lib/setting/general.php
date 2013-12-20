<?php
/*
License: GPLv3
License URI: http://surniaulula.com/wp-content/plugins/nextgen-facebook/license/gpl.txt
Copyright 2012-2013 - Jean-Sebastien Morisset - http://surniaulula.com/
*/

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'NgfbAdminGeneral' ) && class_exists( 'NgfbAdmin' ) ) {

	class NgfbAdminGeneral extends NgfbAdmin {

		public function __construct( &$plugin, $id, $name ) {
			$this->p =& $plugin;
			$this->p->debug->mark();
			$this->menu_id = $id;
			$this->menu_name = $name;
		}

		protected function add_meta_boxes() {
			// add_meta_box( $id, $title, $callback, $post_type, $context, $priority, $callback_args );
			add_meta_box( $this->pagehook.'_opengraph', 'Open Graph Settings', array( &$this, 'show_metabox_opengraph' ), $this->pagehook, 'normal' );
			add_meta_box( $this->pagehook.'_publishers', 'Publisher Settings', array( &$this, 'show_metabox_publishers' ), $this->pagehook, 'normal' );
		}

		public function show_metabox_opengraph() {
			$show_tabs = array( 
				'media' => 'Image and Video',
				'general' => 'Title and Description',
				'author' => 'Authorship',
			);
			$tab_rows = array();
			foreach ( $show_tabs as $key => $title )
				$tab_rows[$key] = $this->get_rows( $key );
			$this->p->util->do_tabs( 'og', $show_tabs, $tab_rows );
		}

		public function show_metabox_publishers() {
			$show_tabs = array( 
				'google' => 'Google',
				'facebook' => 'Facebook',
				'twitter' => 'Twitter',
			);
			$tab_rows = array();
			foreach ( $show_tabs as $key => $title )
				$tab_rows[$key] = $this->get_rows( $key );
			$this->p->util->do_tabs( 'pub', $show_tabs, $tab_rows );
		}

		protected function get_rows( $id ) {
			$ret = array();
			$user_ids = array();
			foreach ( get_users() as $user ) 
				$user_ids[$user->ID] = $user->display_name;
			$user_ids[0] = 'none';
			switch ( $id ) {

				case 'media' :

					$ret[] = $this->p->util->th( __( 'Image Dimensions', NGFB_TEXTDOM ), 'highlight', null, '
					The dimension of images used in the Open Graph / Rich Pin meta tags. The width and height must be 
					greater than '.$this->p->cf['head']['min_img_width'].'x'.$this->p->cf['head']['min_img_height'].', 
					and preferably smaller than 1500x1500
					(the defaults is '.$this->p->opt->get_defaults( 'og_img_width' ).'x'.$this->p->opt->get_defaults( 'og_img_height' ).', '.
					( $this->p->opt->get_defaults( 'og_img_crop' ) == 0 ? 'not ' : '' ).'cropped). 
					<strong>Facebook recommends an image size of 1200x630, 600x315 as a minimum, and will ignore any images less than 200x200</strong>.
					If the original image is smaller than the dimensions entered here, then the full-size image will be used instead.' ).
					'<td>Width '.$this->form->get_input( 'og_img_width', 'short' ).' x '.
					'Height '.$this->form->get_input( 'og_img_height', 'short' ).' &nbsp; '.
					'Cropped '.$this->form->get_checkbox( 'og_img_crop' ).' &nbsp; '.
					 'Auto-Resize Media Images<img src="'.NGFB_URLPATH.'images/question-mark.png" class="sucom_tooltip'.'" alt="'.
					 esc_attr( 'Automatically generate missing or incorrect image sizes for previously uploaded images in the 
					 WordPress Media Library (default is unchecked). You should enable this option unless you have custom /
					 manually cropped images, which will be lost when re-generating image sizes.' ).'" />'.
					 $this->form->get_checkbox( 'og_img_resize' ).
					 '</td>';
	
					$id_pre = array( 'wp' => 'Media Library' );
					if ( $this->p->is_avail['ngg'] == true ) $id_pre['ngg'] = 'NextGEN Gallery';
					$ret[] = $this->p->util->th( 'Default Image ID', 'highlight', null, '
					The ID number and location of your default image (example: 123). The <em>Default Image ID</em> 
					will be used as a fallback for Posts and Pages that do not have any images <em>featured</em>, 
					<em>attached</em>, or in their content. The Image ID number for images in the 
					WordPress Media Library can be found in the URL when editing an image (post=123 in the URL, for example). 
					The NextGEN Gallery Image IDs are easier to find -- it\'s the number in the first column when viewing a Gallery.' ).
					'<td>'.$this->form->get_input( 'og_def_img_id', 'short' ).' in the '.
					$this->form->get_select( 'og_def_img_id_pre', $id_pre ).'</td>';
	
					$ret[] = $this->p->util->th( 'Default Image URL', null, null, '
					You can also specify a <em>Default Image URL</em> (including the http:// prefix) instead of choosing a 
					<em>Default Image ID</em>.
					This allows you to use an image outside of a managed collection (WordPress Media Library or NextGEN Gallery). 
					The image should be at least '.$this->p->cf['head']['min_img_width'].'x'.$this->p->cf['head']['min_img_height'].' or more in width and height. 
					If both the <em>Default Image ID</em> and <em>Default Image URL</em> are defined, the <em>Default Image ID</em>
					will take precedence.' ).
					'<td>'.$this->form->get_input( 'og_def_img_url', 'wide' ).'</td>';
	
					$ret[] = $this->p->util->th( 'Default Image on Indexes', null, null, '
					Check this option if you would like to use the default image on index webpages (homepage, archives, categories, author, etc.). 
					If you leave this unchecked, '.$this->p->cf['full'].' will attempt to use image(s) from the first entry on the webpage 
					(default is checked).' ).
					'<td>'.$this->form->get_checkbox( 'og_def_img_on_index' ).'</td>';
	
					$ret[] = $this->p->util->th( 'Default Image on Search Results', null, null, '
					Check this option if you would like to use the default image on search result webpages as well (default is checked).' ).
					'<td>'.$this->form->get_checkbox( 'og_def_img_on_search' ).'</td>';
	
					if ( $this->p->is_avail['ngg'] == true ) {
						$ret[] = $this->p->util->th( 'Add Featured Image Tags', null, null, '
						If the <em>featured</em> image in a Post or Page is from a NextGEN Gallery, 
						then add that image\'s tags to the Open Graph / Rich Pin tag list (default is unchecked).' ).
						'<td>'.$this->form->get_checkbox( 'og_ngg_tags' ).'</td>';
					} else $ret[] = $this->form->get_hidden( 'og_ngg_tags' );
	
					$ret[] = $this->p->util->th( 'Maximum Images', 'highlight', null, '
					The maximum number of images to list in the Open Graph / Rich Pin meta property tags -- this includes 
					the <em>featured</em> or <em>attached</em> images, and any images found in the Post or Page content. 
					If you select \'0\', then no images will be listed in the Open Graph / Rich Pin meta tags.' ).
					'<td>'.$this->form->get_select( 'og_img_max', range( 0, $this->p->cf['form']['max_media_items'] ), 'short', null, true ).'</td>';
	
					$ret[] = $this->p->util->th( 'Maximum Videos', 'highlight', null, '
					The maximum number of videos, found in the Post or Page content, to include in the Open Graph / Rich Pin meta property tags. 
					If you select \'0\', then no videos will be listed in the Open Graph / Rich Pin meta tags. If you embed videos from Wistia,
					see the '.$this->p->util->get_admin_url( 'about', 'Other Notes' ).' to configure your Wistia API password.' ).
					'<td>'.$this->form->get_select( 'og_vid_max', range( 0, $this->p->cf['form']['max_media_items'] ), 'short', null, true ).'</td>';
	
					$ret[] = $this->p->util->th( 'Use HTTPS for Video APIs', null, null, '
					Use an HTTPS connection whenever possible to retrieve information about videos from YouTube, Vimeo, Wistia, etc. (default is checked).' ).
					'<td>'.$this->form->get_checkbox( 'og_vid_https' ).'</td>';
	
					break;

				case 'general' :

					$ret[] = $this->p->util->th( 'Website Topic', 'highlight', null, '
					The topic that best describes the Posts and Pages on your website.
					This name will be used in the \'article:section\' Open Graph / Rich Pin meta tag. 
					Select \'[none]\' if you prefer to exclude the \'article:section\' meta tag.
					The Pro version also allows you to select a custom Topic for each individual Post and Page.' ).
					'<td>'.$this->form->get_select( 'og_art_section', $this->p->util->get_topics() ).'</td>';

					$ret[] = $this->p->util->th( 'Site Name', 'highlight', null, '
					By default, the Site Title from the <a href="'.get_admin_url( null, 'options-general.php' ).'">WordPress General Settings</a>
					page is used for the Open Graph, Rich Pin site name (og:site_name meta tag). You may override the default Site Title value here.' ).
					'<td>'.$this->form->get_input( 'og_site_name', 
						null, null, null, get_bloginfo( 'name', 'display' ) ).'</td>';

					$ret[] = $this->p->util->th( 'Site Description', 'highlight', null, '
					By default, the Tagline in the <a href="'.get_admin_url( null, 'options-general.php' ).'">WordPress General Settings</a>
					page is used as a description for the index home page, and as fallback for the Open Graph, Rich Pin 
					description field (og:description meta tag). You may override that default value here.' ).
					'<td>'.$this->form->get_input( 'og_site_description', 
						'wide', null, null, get_bloginfo( 'description', 'display' ) ).'</td>';

					$ret[] = $this->p->util->th( 'Title Separator', 'highlight', null, '
					One or more characters used to separate values (category parent names, page numbers, etc.) 
					within the Open Graph / Rich Pin title string (default is \''.
					$this->p->opt->get_defaults( 'og_title_sep' ).'\').' ).
					'<td>'.$this->form->get_input( 'og_title_sep', 'short' ).'</td>';

					$ret[] = $this->p->util->th( 'Title Length', null, null, '
					The maximum length of text used in the Open Graph / Rich Pin title tag 
					(default is '.$this->p->opt->get_defaults( 'og_title_len' ).' characters).' ).
					'<td>'.$this->form->get_input( 'og_title_len', 'short' ).' characters or less</td>';

					$ret[] = $this->p->util->th( 'Description Length', null, null, '
					The maximum length of text used in the Open Graph / Rich Pin description tag. 
					The length should be at least '.$this->p->cf['head']['min_desc_len'].' characters or more, and the
					default is '.$this->p->opt->get_defaults( 'og_desc_len' ).' characters.' ).
					'<td>'.$this->form->get_input( 'og_desc_len', 'short' ).' characters or less</td>';

					$ret[] = $this->p->util->th( 'Add Page Title in Tags', null, null, '
					Add the title of the <em>Page</em> to the Open Graph / Rich Pin article tags and Hashtag list (default is unchecked). 
					If the <em>Add Page Ancestor Tags</em> option is checked, all the titles of the ancestor Pages will be added as well. 
					This option works well if the title of your Pages are short (one or two words) and subject-oriented.' ).
					'<td>'.$this->form->get_checkbox( 'og_page_title_tag' ).'</td>';
	
					$ret[] = $this->p->util->th( 'Add Page Ancestor Tags', null, null, '
					Add the WordPress tags from the <em>Page</em> ancestors (parent, parent of parent, etc.) 
					to the Open Graph / Rich Pin article tags and Hashtag list (default is unchecked).' ).
					'<td>'.$this->form->get_checkbox( 'og_page_parent_tags' ).'</td>';
	
					$ret[] = $this->p->util->th( 'Number of Hashtags to Include', 'highlight', null, '
					The maximum number of tag names (not their slugs), converted to hashtags, to include in the 
					Open Graph / Rich Pin description, tweet text, and social captions.
					Each tag name is converted to lowercase with any whitespaces removed. 
					Select \'0\' (the default) to disable this feature.' ).
					'<td>'.$this->form->get_select( 'og_desc_hashtags', 
						range( 0, $this->p->cf['form']['max_desc_hashtags'] ), 'short', null, true ).' tag names</td>';
	
					$ret[] = $this->p->util->th( 'Content Begins at First Paragraph', null, null, '
					For a Page or Post <em>without</em> an excerpt, if this option is checked, 
					the plugin will ignore all text until the first html paragraph tag in the content. 
					If an excerpt exists, then this option is ignored, and the complete text of that 
					excerpt is used instead.' ).
					'<td>'.$this->form->get_checkbox( 'og_desc_strip' ).'</td>';

					break;

				case 'author' :

					$ret[] = $this->p->util->th( 'Author Profile URL', null, null, '
					Select the profile field to use for Posts and Pages in the \'article:author\' Open Graph / Rich Pin meta tag.
					The URL should point to an author\'s <em>personal</em> website or social page.
					This Open Graph / Rich Pin meta tag is primarily used by Facebook, so the preferred (and default) 
					value is the author\'s Facebook webpage URL.
					See the Google Settings below for an <em>Author Link URL</em> for Google.' ).
					'<td>'.$this->form->get_select( 'og_author_field', $this->author_fields() ).'</td>';

					$ret[] = $this->p->util->th( 'Fallback to Author Index', null, null, '
					If the <em>Author Profile URL</em> (and the <em>Author Link URL</em> in the Google Settings below) 
					is not a valid URL, then '.$this->p->cf['full'].' can fallback to using the author index on this 
					website (\''.trailingslashit( site_url() ).'author/username\' for example). 
					Uncheck this option to disable the fallback feature (default is unchecked).' ).
					'<td>'.$this->form->get_checkbox( 'og_author_fallback' ).'</td>';
	
					$ret[] = $this->p->util->th( 'Default Author', null, null, '
					A default author for webpages <em>missing authorship information</em> (for example, an index webpage without posts). 
					If you have several authors on your website, you should probably leave this option set to <em>[none]</em> (the default).' ).
					'<td>'.$this->form->get_select( 'og_def_author_id', $user_ids, null, null, true ).'</td>';
	
					$ret[] = $this->p->util->th( 'Default Author on Indexes', null, null, '
					Check this option if you would like to force the <em>Default Author</em> on index webpages 
					(homepage, archives, categories, author, etc.). 
					If this option is checked, index webpages will be labeled as a an \'article\' with authorship 
					attributed to the <em>Default Author </em> (default is unchecked).
					If the <em>Default Author</em> is <em>[none]</em>, then the index webpages will be labeled as a \'website\'.' ).
					'<td>'.$this->form->get_checkbox( 'og_def_author_on_index' ).' defines index webpages as articles</td>';
	
					$ret[] = $this->p->util->th( 'Default Author on Search Results', null, null, '
					Check this option if you would like to force the <em>Default Author</em> on search result webpages as well.
					If this option is checked, search results will be labeled as a an \'article\' with authorship
					attributed to the <em>Default Author </em> (default is unchecked).' ).
					'<td>'.$this->form->get_checkbox( 'og_def_author_on_search' ).' defines search webpages as articles</td>';

					$ret[] = $this->p->util->th( 'Article Publisher Page URL', 'highlight', null, '
					The URL of your website\'s social page (usually a Facebook page). 
					For example, the Publisher Page URL for <a href="http://surniaulula.com/" target="_blank">Surnia Ulula</a> 
					is <a href="https://www.facebook.com/SurniaUlulaCom" target="_blank">https://www.facebook.com/SurniaUlulaCom</a>.
					The Publisher Page URL will be included on <em>article</em> type webpages (not indexes).
					See the Google Settings below for a <em>Publisher Link URL</em> for Google.' ).
					'<td>'.$this->form->get_input( 'og_publisher_url', 'wide' ).'</td>';

					break;

				case 'facebook' :

					$ret[] = $this->p->util->th( 'Facebook Admin(s)', 'highlight', null, '
					The <em>Facebook Admin(s)</em> user names are used by Facebook to allow access to 
					<a href="https://developers.facebook.com/docs/insights/" target="_blank">Facebook Insight</a> data.
					Note that these are <em>user</em> account names, not Facebook <em>page</em> names.
					<p>Enter one or more Facebook user names, separated with commas. 
					When viewing your own Facebook wall, your user name is located in the URL 
					(example: https://www.facebook.com/<strong>user_name</strong>). 
					Enter only the user user name(s), not the URL(s).</p>
					<a href="https://www.facebook.com/settings?tab=account&section=username&view" target="_blank">Update 
					your user name in the Facebook General Account Settings</a>.' ).
					'<td>'.$this->form->get_input( 'fb_admins' ).'</td>';

					$ret[] = $this->p->util->th( 'Facebook Application ID', null, null, '
					If you have a <a href="https://developers.facebook.com/apps" target="_blank">Facebook Application</a> 
					ID for your website, enter it here. Facebook Application IDs are used by Facebook to allow 
					access to <a href="https://developers.facebook.com/docs/insights/" target="_blank">Facebook Insight</a> 
					data for <em>accounts associated with the Application ID</em>.' ).
					'<td>'.$this->form->get_input( 'fb_app_id' ).'</td>';

					$ret[] = $this->p->util->th( 'Default Language', null, null, '
					The language / locale for your website content. This option also controls the language of the 
					Facebook social sharing button.' ).
					'<td>'.$this->form->get_select( 'fb_lang', 
						$this->p->util->get_lang( 'facebook' ) ).'</td>';

					break;

				case 'google' :
			
					$ret[] = $this->p->util->th( 'Description Length', null, null, '
					The maximum length of text used for the Google Search description meta tag.
					The length should be at least '.$this->p->cf['head']['min_desc_len'].' characters or more 
					(the default is '.$this->p->opt->get_defaults( 'meta_desc_len' ).' characters).' ).
					'<td>'.$this->form->get_input( 'meta_desc_len', 'short' ).' characters or less</td>';

					$ret[] = $this->p->util->th( 'Author Link URL', null, null, 
					$this->p->cf['full'].' can include an <em>author</em> and <em>publisher</em> link in your webpage headers.
					These are not Open Graph / Rich Pin meta property tags - they are used primarily by Google\'s search engine 
					to associate Google+ profiles with search results.' ).
					'<td>'.$this->form->get_select( 'link_author_field', $this->author_fields() ).'</td>';

					$ret[] = $this->p->util->th( 'Default Author', null, null, '
					A default author for webpages missing authorship information (for example, an index webpage without posts). 
					If you have several authors on your website, you should probably leave this option set to <em>[none]</em> (the default).
					This option is similar to the Open Graph / Rich Pin <em>Default Author</em>, except that it\'s applied to the Link meta tag instead.' ).
					'<td>'.$this->form->get_select( 'link_def_author_id', $user_ids, null, null, true ).'</td>';

					$ret[] = $this->p->util->th( 'Default Author on Indexes', null, null, '
					Check this option if you would like to force the <em>Default Author</em> on index webpages 
					(homepage, archives, categories, author, etc.).' ).
					'<td>'.$this->form->get_checkbox( 'link_def_author_on_index' ).'</td>';

					$ret[] = $this->p->util->th( 'Default Author on Search Results', null, null, '
					Check this option if you would like to force the <em>Default Author</em> on search result webpages as well.' ).
					'<td>'.$this->form->get_checkbox( 'link_def_author_on_search' ).'</td>';
			
					$ret[] = $this->p->util->th( 'Publisher Link URL', 'highlight', null, '
					If you have a <a href="http://www.google.com/+/business/" target="_blank">Google+ business page for your website</a>, 
					you may use it\'s URL as the Publisher Link. 
					For example, the Publisher Link URL for <a href="http://surniaulula.com/" target="_blank">Surnia Ulula</a> 
					is <a href="https://plus.google.com/u/1/103457833348046432604/posts" target="_blank">https://plus.google.com/u/1/103457833348046432604/posts</a>.
					The <em>Publisher Link URL</em> may take precedence over the <em>Author Link URL</em> in Google\'s search results.' ).
					'<td>'.$this->form->get_input( 'link_publisher_url', 'wide' ).'</td>';

					break;

				case 'twitter' :

					$ret = $this->get_rows_twitter();

					break;

			}
			return $ret;
		}

		protected function get_rows_twitter() {
			return array(
				'<td colspan="2" align="center">'.$this->p->msg->get( 'pro_feature' ).'</td>',

				$this->p->util->th( 'Enable Twitter Cards', 'highlight', null, 
				'Add Twitter Card meta tags to all webpage headers.
				<strong>Your website must be "authorized" by Twitter for each type of Twitter Card you support</strong>. 
				See the FAQ entry titled <a href="http://surniaulula.com/codex/plugins/nextgen-facebook/faq/why-dont-my-twitter-cards-show-on-twitter/" 
				target="_blank">Why don’t my Twitter Cards show on Twitter?</a> for more information on Twitter\'s 
				authorization process.' ).
				'<td class="blank">'.$this->form->get_fake_checkbox( 'tc_enable' ).'</td>',

				$this->p->util->th( 'Maximum Description Length', null, null, '
				The maximum length of text used for the Twitter Card description.
				The length should be at least '.$this->p->cf['head']['min_desc_len'].' characters or more 
				(the default is '.$this->p->opt->get_defaults( 'tc_desc_len' ).' characters).' ).
				'<td class="blank">'.$this->form->get_hidden( 'tc_desc_len' ).
					$this->p->options['tc_desc_len'].' characters or less</td>',

				$this->p->util->th( 'Website @username to Follow', 'highlight', null, 
				'The Twitter username for your website and / or company (not your personal Twitter username).
				As an example, the Twitter username for <a href="http://surniaulula.com/" target="_blank">Surnia Ulula</a> 
				is <a href="https://twitter.com/surniaululacom" target="_blank">@surniaululacom</a>.' ).
				'<td class="blank">'.$this->form->get_hidden( 'tc_site' ).
					$this->p->options['tc_site'].'</td>',

				$this->p->util->th( '<em>Summary</em> Card Image Size', null, null, 
				'The size of content images provided for the
				<a href="https://dev.twitter.com/docs/cards/types/summary-card" target="_blank">Summary Card</a>
				(should be at least 120x120, larger than 60x60, and less than 1MB).' ).
				'<td class="blank">'.$this->form->get_hidden( 'tc_sum_size' ).
					$this->p->options['tc_sum_size'].'</td>',

				$this->p->util->th( '<em>Large Image Summary</em> Card Image Size', null, null, 
				'The size of Post Meta, Featured or Attached images provided for the
				<a href="https://dev.twitter.com/docs/cards/types/large-image-summary-card" target="_blank">Large Image Summary Card</a>
				(must be larger than 280x150 and less than 1MB).' ).
				'<td class="blank">'.$this->form->get_hidden( 'tc_large_size' ).
					$this->p->options['tc_large_size'].'</td>',

				$this->p->util->th( '<em>Photo</em> Card Image Size', 'highlight', null, 
				'The size of ImageBrowser or Attachment Page images provided for the 
				<a href="https://dev.twitter.com/docs/cards/types/photo-card" target="_blank">Photo Card</a> 
				(should be at least 560x750 and less than 1MB).' ).
				'<td class="blank">'.$this->form->get_hidden( 'tc_photo_size' ).
					$this->p->options['tc_photo_size'].'</td>',

				$this->p->util->th( '<em>Gallery</em> Card Image Size', null, null, 
				'The size of gallery images provided for the
				<a href="https://dev.twitter.com/docs/cards/types/gallery-card" target="_blank">Gallery Card</a>.' ).
				'<td class="blank">'.$this->form->get_hidden( 'tc_gal_size' ).
					$this->p->options['tc_gal_size'].'</td>',

				$this->p->util->th( '<em>Gallery</em> Card Minimum Images', null, null, 
				'The minimum number of images found in a gallery to qualify for the
				<a href="https://dev.twitter.com/docs/cards/types/gallery-card" target="_blank">Gallery Card</a>.' ).
				'<td class="blank">'.$this->form->get_hidden( 'tc_gal_min' ).
					$this->p->options['tc_gal_min'].'</td>',

				$this->p->util->th( '<em>Product</em> Card Image Size', null, null, 
				'The size of a featured product image for the
				<a href="https://dev.twitter.com/docs/cards/types/product-card" target="_blank">Product Card</a>.
				The product card requires an image of size 160 x 160 or greater. A square (aka cropped) image is better, 
				but Twitter can crop/resize oddly shaped images to fit, as long as both dimensions are greater 
				than or equal to 160 pixels. ' ).
				'<td class="blank">'.$this->form->get_hidden( 'tc_prod_size' ).
					$this->p->options['tc_prod_size'].'</td>',

				$this->p->util->th( '<em>Product</em> Card Default 2nd Attribute', null, null, '
				The <em>Product</em> Twitter Card needs a minimum of two product attributes.
				The first attribute will be the product price, and if your product has additional attribute fields associated with it 
				(weight, size, color, etc), these will be included in the <em>Product</em> Card as well (maximum of 4 attributes). 
				<strong>If your product does not have additional attributes beyond just a price</strong>, then this default second attribute label and value will be used. 
				You may modify both the Label <em>and</em> Value for whatever is most appropriate for your website and/or products.' ).
				'<td class="blank">'.
				$this->form->get_hidden( 'tc_prod_def_l2' ).'Label: '.$this->p->options['tc_prod_def_l2'].' &nbsp; '.
				$this->form->get_hidden( 'tc_prod_def_d2' ).'Value: '.$this->p->options['tc_prod_def_d2'].
				'</td>',

			);
		}

		private function author_fields() {
			return $this->p->user->add_contact_methods( 
				array( 'none' => '', 'author' => 'Author Index', 'url' => 'Website' ) 
			);
		}
	}
}
?>
