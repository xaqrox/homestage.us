<?php 

get_header();

//VAR SETUP
$calendarOn = get_theme_mod('themolitor_customizer_calendar_onoff');
$sliderOn = get_theme_mod('themolitor_customizer_slider_onoff');
$welcomeMsg = get_theme_mod('themolitor_customizer_welcome');

//CALENDAR Stuff
if($calendarOn){ get_template_part('calendar'); } 

//SLIDER Stuff
if($sliderOn){ get_template_part('slider'); }




get_footer(); 

?>