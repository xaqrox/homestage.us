this.molitorscripts = function () {
	
	//FILTER EFFECTS & APPEARANCE
	var filterLink = jQuery('#filter a');
	filterLink.click(function(){
	
		jQuery('li.box').removeClass('hideMe');
		
		filterLink.not(this).removeClass('active');
		jQuery(this).addClass('active');
		
		if(jQuery(this).hasClass('allEvents')){
			jQuery('li.postEvent').removeClass('hideMe').children().stop(true,true).animate({opacity:"1"},350);
		} else {
			var activeCat = jQuery(this).parent('li').removeClass('cat-item').attr('class');
			jQuery('li.postEvent').not('li.'+ activeCat).addClass('hideMe').children().stop(true,true).animate({opacity:".1"},350);
			jQuery('li.'+ activeCat).children().stop(true,true).animate({opacity:"1"},350);
		}		
		
		return false;
	});
	jQuery('#filter li').not(':first').prepend("/ &nbsp;&nbsp;&nbsp;");
	

	//MENU
	jQuery("#dropmenu a").removeAttr("title");
	jQuery("#dropmenu ul").css("display", "none"); // Opera Fix
	jQuery("#dropmenu li").hover(function(){
		jQuery(this).find('ul:first').stop(true,true).slideDown(150);
		},function(){
		jQuery(this).find('ul:first').stop(true,true).slideUp(150);
	});
	
	jQuery("#selectMenu").change(function() {
  		window.location = jQuery(this).find("option:selected").val();
	});
		
	//PRETTY PHOTO
	jQuery("a[href$='jpg'],a[href$='png'],a[href$='gif']").not(".wrapperli a").attr({rel: "prettyPhoto"});
	jQuery(".gallery-icon > a[href$='jpg'],.gallery-icon > a[href$='png'],.gallery-icon > a[href$='gif']").attr({rel: "prettyPhoto[pp_gal]"});
	jQuery("a[rel^='prettyPhoto']").prettyPhoto({
		animation_speed: 'normal', // fast/slow/normal 
		opacity: 0.35, 
		show_title: false, 
		allow_resize: true,
		overlay_gallery: false,
		counter_separator_label: ' of ',
		//theme: 'light_rounded', // light_rounded / dark_rounded / light_square / dark_square 
		hideflash: true,
		modal: false // If set to true, only the close button will close the window 
	});
	
	//WHEN PAGE LOADS...
	jQuery(window).load(function(){
	
		//STICKY FILTER
		// jQuery("#filter").sticky({ topSpacing: 0, className: 'sticky' });
		
		//SLIDER
		jQuery('#slider').flexslider({
			pauseOnHover: true,
			animation: "fade",      //"fade" or "slide"
			slideshowSpeed: 7000,   //Set the speed of the slideshow cycling, in milliseconds
			animationDuration: 600, //Set the speed of animations, in milliseconds
			controlNav: true,      
			keyboardNav: true,      
			prevText: "&larr;",     
			nextText: "&rarr;"     
		});
	});
	
	//SOCIAL ICON EFFECT
	jQuery('.socialIcon').hover(function(){
		jQuery(this).stop(true,true).animate({marginTop:"-46px"},200);
	},function(){
		jQuery(this).stop(true,true).animate({marginTop:"0px"},200);
	});
	
	
	//DATE HOVER EFFECT
	jQuery('li.box a').live({
  		mouseenter: function() { 
    		jQuery(this).not('li.monthYear a').parent().prevAll('.monthYear:first').addClass('activeMonth');
  		},
  		mouseleave: function () {
    		jQuery(this).not('li.monthYear a').parent().prevAll('.monthYear:first').removeClass('activeMonth');
  		}
	});
	    
    //TABS
    var tabs = jQuery('#detailsTabs > li'),
    	metaInfo = jQuery('ul#metaStuff > li');
    	
    tabs.click(function(){
    	var tabIndex = jQuery(this).index();
 
    	tabs.removeClass('activeTab');
    	jQuery(this).addClass('activeTab');
    	
    	metaInfo.removeClass('currentInfo').hide().eq(tabIndex).addClass('currentInfo').fadeIn(200);
    	   	
    });
    
    //CALENDAR LOADING
    jQuery('.ajax_calendar_widget a').live('click',function(){
    	jQuery('.ajax_calendar_widget caption').addClass('loading');
    });
    
    //FORM VALIDATION
	jQuery("#primaryPostForm").submit(function(event) {  		
	        			
	    var postTest = jQuery('#postTest'),
	    	requiredField = jQuery("#primaryPostForm .required").not('#primaryPostForm #postTest');
	    		
	    //CHECK REQUIRED FIELDS
		requiredField.each(function(){
			var imRequired = jQuery(this);
			if (imRequired.val() === "" || imRequired.val() === "-1") {
				imRequired.css({background:"#fcd1c8"});
				event.preventDefault();
			} else {
				imRequired.css({background:"#fff"});
			}
		});
		
		//CHECK FORM TEST
		if(postTest.val() != "102"){
			postTest.css({background:"#fcd1c8"});
			event.preventDefault();
			alert('Test answer incorrect. Please try again.');
		} else {
			postTest.css({background:"#fff"});
		}
	});

    //DATE PICKER
    jQuery('#postDate').datepicker({ 
    	altFormat: "yy-mm-dd",
    	dateFormat: "yy-mm-dd",
    	nextText: "&rarr;",
    	prevText: "&larr;",
    	dayNamesMin: [ "S", "M", "T", "W", "T", "F", "S" ]
    });      
}
jQuery.noConflict(); jQuery(document).ready(function(){molitorscripts();});