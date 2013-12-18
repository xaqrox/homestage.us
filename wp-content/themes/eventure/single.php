<?php 
get_header(); 

//VAR SETUP
$eventCat = get_option('themolitor_events_category');

if (have_posts()) : while (have_posts()) : the_post();
$data = get_post_meta( $post->ID, 'key', true ); 
$imageInstead = get_post_meta( $post->ID, 'themolitor_image_not_map', TRUE ); 
$address = get_post_meta( $post->ID, 'themolitor_address', TRUE );
$addressLink = str_replace(" ", "+", $address); 
$duration = get_post_meta( $post->ID, 'themolitor_duration', TRUE );
$cost = get_post_meta( $post->ID, 'themolitor_cost', TRUE );
$ages = get_post_meta( $post->ID, 'themolitor_ages', TRUE );
$link = get_post_meta( $post->ID, 'themolitor_link', TRUE );

//SHOW IMAGE INSTEAD OF ADDRESS
if($imageInstead && has_post_thumbnail()) { 
	the_post_thumbnail('post',array('itemprop' => 'photo')); 
//SHOW ADDRESS
} elseif($address){ ?>
	<div class="mapEmbed" id="bannerMap">
		<iframe width="900" height="300" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://maps.google.com/?q=<?php echo $address; ?>&amp;output=embed&amp;iwloc=near&amp;z=14"></iframe>
	</div><!--end mapEmbed-->
<?php 
//LEGACY SUPPORT FOR GOOGLE URL
} elseif ($data[ 'google_map' ]) { ?>
	<div class="mapEmbed" id="bannerMap">
		<iframe width="900" height="300" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="<?php echo $data[ 'google_map' ]; ?>&amp;output=embed&amp;iwloc=near"></iframe>
	</div><!--end mapEmbed-->
<?php 
//SHOW FEATURED IMAGE
} elseif (has_post_thumbnail()){ the_post_thumbnail('post',array('itemprop' => 'photo')); } 

//The following div indicates to Google that the item is an event.
if(in_category($eventCat)){ echo '<div itemscope itemtype="http://data-vocabulary.org/Event">'; } ?>

<div id="postDetails">
		
		<?php 
		$args = array('post_type' => 'attachment','post_mime_type' => 'image' ,'post_status' => null, 'post_parent' => $post->ID);
		$attachments = get_posts($args);
		?>
		
		<ul id="detailsTabs">
			<li class="activeTab"><?php _e('Details','themolitor');?></li>
			<?php if ($attachments) { ?><li><?php _e('Gallery','themolitor');?></li><?php } ?>
			<li><?php _e('Tags','themolitor');?></li>
			<li><?php _e('More','themolitor');?></li>
		</ul>
		
		<ul id="metaStuff">
			<!--FIRST-->
			<li class="currentInfo">
			
				<h2 id="postTitle"><?php the_title(); ?><?php edit_post_link(' <small>&#9997;</small>','',' '); ?></h2>
				<?php if (function_exists('dimox_breadcrumbs')) dimox_breadcrumbs();?>	
			
				<?php  $postDate = get_the_date('m/d/y g:ia'); if ( strtotime($postDate) > time()) {?>
				<div id="countDown">
					<script type="text/javascript">
					CountActive = true;
					TargetDate = "<?php the_time('m/d/Y g:i a'); ?>";
					DisplayFormat = "<span>%%D%%</span> d &nbsp;&nbsp;&nbsp; <span>%%H%%</span> h &nbsp;&nbsp;&nbsp; <span>%%M%%</span> m &nbsp;&nbsp;&nbsp; <span>%%S%%</span> s";
					FinishMessage = "<?php _e('Countdown Complete','themolitor');?>";
					</script>
					<script src="<?php echo get_template_directory_uri(); ?>/scripts/countdown.js" type="text/javascript"></script>
				</div><!--end countDown-->
				<?php  } ?>
								
				<div class="smallMeta">
					<?php if(in_category($eventCat) && strtotime(get_the_time('Y-m-d')) < strtotime(date('Y-m-d'))){?><i class="icon-exclamation red"></i><?php _e('Event Expired','themolitor');?><br /><?php }?>
					<i class="icon-calendar"></i><time itemprop="startDate" datetime="<?php the_time('Y-m-d'); ?>T<?php the_time('G:i');?>"><?php the_time('l, F jS, Y'); ?></time><br />
					<i class="icon-time"></i><?php the_time('g:i a'); ?><br />
					<?php if($duration){?><i class="icon-refresh"></i><?php echo $duration; ?><br /><?php } ?>
					<?php if($address){?><i class="icon-map-marker"></i><a target="blank" href="http://maps.google.com/?q=<?php echo $addressLink; ?>&amp;z=14"><span itemprop="location" itemscope itemtype="http://data-vocabulary.org/Organization"><?php echo $address; ?></span></a><br /><?php } 
					elseif ($data[ 'google_map' ]) { ?><i class="icon-map-marker"></i><a target="blank" href="<?php echo $data[ 'google_map' ]; ?>"><?php _e('Get Directions','themolitor');?></a><br /><?php } ?>
					<?php if($cost){?><i class="icon-money"></i><span itemprop="price"><?php echo $cost; ?></span><br /><?php } ?>
					<?php if($ages){?><i class="icon-group"></i><?php echo $ages; ?><br /><?php } ?>
					<?php if($link){?><i class="icon-link"></i><a target="_blank" href="<?php echo $link; ?>"><?php echo $link; ?></a><br /><?php } ?>
					<i class="icon-folder-close"></i><?php the_category(', '); ?><br />
					<i class="icon-user"></i><?php the_author(); ?><br />
					<i class="icon-comment"></i><?php comments_popup_link(__('0 Comments','themolitor'), __('1 Comment','themolitor'), __('% Comments','themolitor')); ?><br />
				</div>
			</li>

			<!--SECOND-->
			<?php if ($attachments) { ?>
			<li>
				<ul class="galleryBox">
       				<?php attachment_toolbox('small'); ?>
       			</ul>
			</li>
			<?php } ?>
			
			<!--THIRD-->
			<li id="theTags">
				<?php the_tags('','');?>
				<div class="clear"></div>
			</li>
			
			<li><?php get_sidebar();?></li>

		</ul>
	</div>

	<div id="entrycontent" <?php post_class(); ?>>
		
		<div class="entry">
		
			<div id="postNav">
				<div id="nextpage" class="pagenav"><?php next_post_link('%link','&rarr;',true) ?></div>
				<div id="backpage" class="pagenav"><?php previous_post_link('%link','&larr;',true) ?></div>
			</div><!--end postNav-->
			
			<h2 id="postTitle"><?php the_title(); ?><?php edit_post_link(' <small>&#9997;</small>','',' '); ?></h2>
			<?php if (function_exists('dimox_breadcrumbs')) dimox_breadcrumbs();?>	
			
			<div class="socialButton">	
				<a href="http://twitter.com/share" class="twitter-share-button" data-count="none"><?php _e('Tweet','themolitor');?></a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
			</div>
			<div class="socialButton">	
				<script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>
				<g:plusone size="medium" count="false"></g:plusone>
			</div>	
			<div class="socialButton" id="facebookLike">
				<div id="fb-root"></div><script src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script><fb:like href="<?php the_permalink() ?>" send="false" layout="button_count" width="90" height="21" show_faces="true" action="like" colorscheme="light" font=""></fb:like>
			</div>	
			<div class="clear"></div>
			
			<?php 
			if(in_category($eventCat)){echo '<div itemprop="description">';}
			the_content(); 
			wp_link_pages(); 
			if(in_category($eventCat)){echo '</div>';}
			?>
			
			<div class="clear"></div>	
			
        </div><!--end entry-->
        
        <br />
                    
        <div id="commentsection">
			<?php comments_template(); ?>
        </div>
			
	</div><!--end post-->
	
<?php
//The following closing div indicates to Google that the item is an event.
if(in_category($eventCat)){ echo '</div><!--end event-->'; }
 
endwhile; endif;

?>


<?php

get_footer(); 



?>