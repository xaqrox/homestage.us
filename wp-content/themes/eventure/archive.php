<?php get_header();?>

	<div id="listing">
	<?php 
	/// if (function_exists('dimox_breadcrumbs')) dimox_breadcrumbs();
	
	//VAR SETUP
	$eventCat = get_option('themolitor_events_category');
	$args = 'cat=' . $eventCat . "&orderby=date&order=ASC";
	if($eventCat && !is_search() && in_category($eventCat)){query_posts( $args );}
	if (have_posts()) : while (have_posts()) : the_post(); 
	?>
	
		<div id="listdiv" <?php post_class(); ?>>
		
		<?php get_template_part("thumbnail"); ?>
		
		<a class="dateInfo" href="<?php the_permalink() ?>">
			<div class="dayInfo"><?php echo get_the_time('d'); ?></div>
			<div class="timeInfo"><?php echo get_the_time('D @ g:i a'); ?></div>
			<div class="monthInfo"><?php echo get_the_time('M Y'); ?></div>
		</a>
		
		<div class="listContent">
		<h2 class="posttitle"><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
		
		<!--- 
		<div class="smallMeta">
		<i class="icon-folder-close"></i>&nbsp;&nbsp;<?php the_category(', '); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="icon-user"></i>&nbsp;<?php the_author(); ?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="icon-comment"></i>&nbsp;<?php comments_popup_link(__('0 Comments','themolitor'), __('1 Comment','themolitor'), __('% Comments','themolitor')); if(in_category($eventCat,get_the_ID()) && strtotime(get_the_time('Y-m-d')) < strtotime(date('Y-m-d'))){?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="icon-exclamation red"></i>&nbsp;&nbsp;<?php _e('Event Expired','themolitor');?><br /><?php }?>		
		</div> 
	-->
		
		<!--- <?php the_excerpt(); ?> -->
		</div><!--end listContent-->
		
        <div class="clear"></div>
		</div><!--end post-->

		<?php endwhile; 

		get_template_part("navigation");

	else : ?>
		<h2 class='center'><?php _e('Not Found','themolitor');?></h2>
		<p class='center'><?php _e("Sorry, but you are looking for something that isn't here.",'themolitor');?></p>
	<?php endif; ?>
	
	</div><!--end listing-->
	
<?php 
get_sidebar();
get_footer(); 
?>