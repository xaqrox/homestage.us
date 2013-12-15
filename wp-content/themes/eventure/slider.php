<?php
//VAR SETUP
$sliderCat = get_option('themolitor_slider_category');
$sliderNumber = get_theme_mod('themolitor_customizer_slider_number');
?>

<!--SLIDER-->
<div id="slider">
	<ul class="slides">
		<?php $showPostsInCategory = new WP_Query(); $showPostsInCategory->query('cat='.$sliderCat.'&showposts='.$sliderNumber);  ?>
		<?php if ($showPostsInCategory->have_posts()) :?>
		<?php while ($showPostsInCategory->have_posts()) : $showPostsInCategory->the_post(); ?>
		<li>
			<div class="sliderInfo">
				<div class="sliderDate"><?php echo get_the_time('m.d.Y'); ?></div>
				<a class="sliderTitle" href="<?php the_permalink();?>"><?php the_title();?></a><br />
				
			</div>
			<?php the_post_thumbnail('slider'); ?>	
		</li>
		<?php endwhile; endif; ?>
	</ul>
</div>