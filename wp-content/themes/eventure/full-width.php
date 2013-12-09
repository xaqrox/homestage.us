<?php
/*
Template Name: Full Width Page
*/

get_header();

//VAR SETUP
$eventCat = get_option('themolitor_events_category');
?>
		
	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
	<div  <?php post_class(); ?>>
		
		<div class="entry">
			<h2 id="postTitle"><?php the_title(); ?><?php edit_post_link(' <small>&#9997;</small>','',' '); ?></h2>
			<?php if (function_exists('dimox_breadcrumbs')) dimox_breadcrumbs();?>
			<div id="twoColumns">
				<?php the_content(); ?>
			</div>
			<div class="clear"></div>
        </div><!--end entry-->
        
                    
        <div id="commentsection">
			<?php comments_template(); ?>
        </div>
	
	</div><!--end post-->

 
	<div class="clear"></div>
	<?php endwhile; endif; ?>
	
<?php get_footer(); ?>