<?php 

get_header(); 

//VAR SETUP
$eventCat = get_option('themolitor_events_category');

if (have_posts()) : while (have_posts()) : the_post(); ?>
	<div  <?php post_class(); ?>>
		
		<div class="entry">
			<h2 id="postTitle"><?php the_title(); ?><?php edit_post_link(' <small>&#9997;</small>','',' '); ?></h2>
			<?php if (function_exists('dimox_breadcrumbs')) dimox_breadcrumbs();?>
			<?php the_content(); ?>
			<div class="clear"></div>
        </div><!--end entry-->
        
                    
        <div id="commentsection">
			<?php comments_template(); ?>
        </div>
	
	<div class="clear"></div>
	</div><!--end post--> 
	
	<?php endwhile; endif; ?>

<?php get_sidebar();?>
	
<?php get_footer(); ?>