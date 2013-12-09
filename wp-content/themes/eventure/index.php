<?php 

get_header();

//VAR SETUP
$calendarOn = get_theme_mod('themolitor_customizer_calendar_onoff');
$sliderOn = get_theme_mod('themolitor_customizer_slider_onoff');
$welcomeMsg = get_theme_mod('themolitor_customizer_welcome'); 

//SLIDER Stuff
if($sliderOn){ get_template_part('slider'); } 

//TAGLINE Stuff
if($welcomeMsg){ ?>
<div id="tagLine"><?php echo $welcomeMsg;?></div>
<br />
<?php }

//CALENDAR Stuff
if($calendarOn){ get_template_part('calendar'); }


get_footer(); 

?>