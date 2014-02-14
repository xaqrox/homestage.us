<?php
//VAR SETUP
$eventCat = get_option('themolitor_events_category');
$theEnd = get_theme_mod('themolitor_the_end');
?>

<!--FILTER-->
<div id="filter" class="blackBar">
<h2 id="calendarTitle">Upcoming Events<h2>
<ul>
	<li class="calendarFilter"><a class="allEvents" href="#">All Events</a></li>
	
	<?php
	$cats = wp_list_categories('child_of='.$eventCat.'&title_li=&echo=0&depth=1&orderby=count&order=DESC');
	if (!strpos($cats,'No categories') ){
		echo $cats;
	}
	?> 
</ul>
</div>

<!--UL DATE LIST-->
<ul id="dateList">
<?php
$prev_month = '';
$prev_year = '';
$temp = $wp_query;
$wp_query= null;
$wp_query = new WP_Query();
$wp_query->query('order=ASC&cat='.$eventCat.'&showposts=200'.'&paged='.$paged);
while ($wp_query->have_posts()) : $wp_query->the_post(); 

//CHECK IF EVENT HAS EXPIRED
if(strtotime(get_the_time('Y-m-d')) >= strtotime(date('Y-m-d'))){

if(get_the_time('M') != $prev_month || get_the_time('Y') != $prev_year){ 
?>

	<li class="box monthYear" id="<?php echo get_the_time('M'); echo get_the_time('y'); ?>">
		<a class="dateLink" href="<?php echo home_url(); ?>/<?php echo get_the_time('Y/m'); ?>">
			<span id="monthbox"><?php echo get_the_time('M'); ?></span><br />
			<?php echo get_the_time('Y'); ?>
		</a>
	</li>

<?php }	?>
	
	<li class="box postEvent<?php foreach((get_the_category()) as $category) {echo ' cat-item-'.$category->cat_ID.'';}?>">
		<a  href="<?php the_permalink(); ?>">
			<span class="theDay"><?php echo get_the_time('d'); ?></span><br />
			<p class="theTitle">
				<span id="daytime"><?php echo get_the_time('D @ g:i a'); ?></span><br />
				<?php echo the_title(); ?>
			</p>
		</a>                    
	</li>
	
<?php
$prev_month = get_the_time('M');
$prev_year = get_the_time('Y');	
}//END EXPIRATION CHECK
endwhile; 
$wp_query = null; 
$wp_query = $temp;

if($theEnd){ ?><li class="box" id="theEnd"><?php _e("The End.",'themolitor');?></li><?php } ?>
</ul><!--END DATE LIST-->