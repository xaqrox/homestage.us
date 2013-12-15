<?php 

get_header();

?>


<?php

//VAR SETUP
$sliderOn = get_theme_mod('themolitor_customizer_slider_onoff');
$calendarOn = get_theme_mod('themolitor_customizer_calendar_onoff');
$welcomeMsg = get_theme_mod('themolitor_customizer_welcome');

//SLIDER Stuff
if($sliderOn){ get_template_part('slider'); }

//CALENDAR Stuff
if($calendarOn){ get_template_part('calendar'); } 

?>



<?php


get_footer(); 

?>