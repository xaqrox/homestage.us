<?php 
/*
Template Name: Submission Form
*/

//VAR SETUP
$eventCat = get_option('themolitor_events_category');
$sendEmail = get_theme_mod('themolitor_send_email');
$altEmail = get_theme_mod('themolitor_alt_email');

//ERROR VAR RESET
$postNameError = '';
$postEmailError = '';
$postTitleError = '';
$postContentError = '';
$postDateError = '';
$postTimeError = '';
$postTestError = '';
$confirmation = '';

if ( isset( $_POST['submitted'] ) && isset( $_POST['post_nonce_field'] ) && wp_verify_nonce( $_POST['post_nonce_field'], 'post_nonce' ) ) {

	//VAR SETUP
	$postName = $_POST['postName'];
	$postEmail = $_POST['postEmail'];
	$postTitle = $_POST['postFormTitle'];
	$postContent = $_POST['postContent'];
	$postDate = $_POST['postDate'];
	$postTime = $_POST['postTime'];
	$postDuration = $_POST['postDuration'];
	$postCost = $_POST['postCost'];
	$postAges = $_POST['postAges'];
	$postAddress = $_POST['postAddress'];
	$postCat = $_POST['cat'];
	$postTags = $_POST['postTags'];
	$postLink = $_POST['postLink'];
	$postTest = $_POST['postTest'];
	
 
 	//REQUIRED CHECK
    if (trim($postName) == '') {$postNameError = 'Required';}
    if (trim($postEmail) == '') {$postEmailError = 'Required';}
    if (trim($postTitle) == '') {$postTitleError = 'Required';}
    if (trim($postContent) == '') {$postContentError = 'Required';}
    if (trim($postDate) == '') {$postDateError = 'Required';}
    if (trim($postTime) == '') {$postTimeError = 'Required';}
    if (trim($postTest) != '102') {$postTestError = 'Required';}
 	
 	//WP INSERT POST SETTINGS
    $post_information = array(
        'post_title' => wp_strip_all_tags( $postTitle ),
        'post_content' => $postContent,
        'post_type' => 'post',
        'post_status' => 'pending',
        'tags_input'     => $postTags,
        'post_category'  => array($eventCat,$postCat),
        'post_date'      => $postDate.' '.$postTime.':00',
        'post_date_gmt'      => $postDate.' '.$postTime
    );
 
 	//GET POST ID
    $post_id = wp_insert_post($post_information);

	if($post_id) {
		$confirmation = '"'.$postTitle.'" '.__('has been successfully subitted. We will take a look and post it for you asap.  Thanks!','themolitor');
	
    	//UPDATE CUSTOM META
    	if(isset($postAddress)){update_post_meta($post_id, 'themolitor_address', esc_attr(strip_tags($postAddress)));}
    	if(isset($postDuration)){update_post_meta($post_id, 'themolitor_duration', esc_attr(strip_tags($postDuration)));}
    	if(isset($postCost)){update_post_meta($post_id, 'themolitor_cost', esc_attr(strip_tags($postCost)));}
    	if(isset($postAges)){update_post_meta($post_id, 'themolitor_ages', esc_attr(strip_tags($postAges)));}
    	if(isset($postLink)){update_post_meta($post_id, 'themolitor_link', esc_attr(strip_tags($postLink)));}
    	
    	//IF EMAIL NOTIFICATION ON
    	if(isset($sendEmail)){
    		//EMAIL VAR SETUP
    		$pendingUrl = admin_url('edit.php?post_status=pending&post_type=post');
    		$postEdit = admin_url('post.php?post='.$post_id.'&action=edit');
    		$optionsUrl = admin_url('customize.php');
    		$blogName = get_option('blogname');
    		$message = $postName." (".$postEmail.") ".__('has submitted a new post on','themolitor')." ".$blogName." ".__('titled','themolitor')." '".$postTitle."'.\n\n";
    		$message .= __('Review','themolitor')." '".$postTitle."' ".__('here','themolitor').": ".$postEdit."\n\n";
    		$message .= __('Review all pending posts here','themolitor').": ".$pendingUrl."\n\n";
    		$message .= __('You can turn off notifications like this on the "Front-end Submission Form" tab here','themolitor').": ".$optionsUrl; 
		
			//SEND EMAIL NOTICE TO ADMIN
			wp_mail($altEmail, $blogName.' '.__('Post Pending Review','themolitor').': "'.$postTitle.'"', $message);
  		}  		
	}
}

get_header(); 

if (have_posts()) : while (have_posts()) : the_post(); ?>

<div  <?php post_class(); ?>>
		
	<div class="entry">
		<h2 id="postTitle"><?php the_title(); ?><?php edit_post_link(' <small>&#9997;</small>','',' '); ?></h2>
		<?php 
		if (function_exists('dimox_breadcrumbs')) dimox_breadcrumbs();
		the_content();
			
		if ( !post_password_required() ) { ?>
		
		<form id="primaryPostForm" method="POST">
					
       		<p class="alignleft"><!--NAME-->
       		<label for="postName"><?php _e('Your Name', 'themolitor') ?><span class="red">*</span></label><?php if ($postNameError != '') { ?> <span class="error"><?php echo $postNameError; ?></span><?php } ?><br />
       		<input type="text" name="postName" id="postName" class="required" value="<?php if(isset($_POST['postName']) && $_SERVER['REQUEST_METHOD'] != "POST"){ echo $_POST['postName']; } ?>" /><br />
			</p>
			
       		<p class="alignleft"><!--EMAIL-->
       		<label for="postEmail"><?php _e('Your Email', 'themolitor') ?><span class="red">*</span></label><?php if ($postEmailError != '') { ?> <span class="error"><?php echo $postEmailError; ?></span><?php } ?>&nbsp;&nbsp;<span class="formExample">- <?php _e('will not be published','themolitor');?></span><br />
       		<input type="email" name="postEmail" id="postEmail" class="required" value="<?php if(isset($_POST['postEmail']) && $_SERVER['REQUEST_METHOD'] != "POST"){ echo $_POST['postEmail']; } ?>" /><br />
			</p>
			
			<div class="clear"></div>
			
       		<p class="alignleft"><!--TITLE-->
       		<label for="postFormTitle"><?php _e('Event Title', 'themolitor') ?><span class="red">*</span></label><?php if ($postTitleError != '') { ?> <span class="error"><?php echo $postTitleError; ?></span><?php } ?><br />
       		<input type="text" name="postFormTitle" id="postFormTitle" class="required" value="<?php if(isset($_POST['postTitle']) && $_SERVER['REQUEST_METHOD'] != "POST"){ echo $_POST['postTitle']; } ?>" /><br />
			</p>
			
			<p class="alignleft"><!--DATE & TIME-->
			<label for="postDate"><?php _e('Date & Time', 'themolitor') ?><span class="red">*</span></label><?php if ($postDateError != '' || $postTimeError != '') { ?> <span class="error"><?php _e('Required','themolitor');?></span><?php } ?><br />
			<input type="text" name="postDate" id="postDate" value="<?php if(isset($_POST['postDate']) && $_SERVER['REQUEST_METHOD'] != "POST"){ echo $_POST['postDate']; } else { echo date('Y-m-d'); }?>" class="required dateStuff" />
			<select id="postTime" name="postTime" class="required dateStuff">
				<option value="00:00">12:00 am</option>
				<option value="00:30">12:30 am</option>
				<option value="01:00">1:00 am</option>
				<option value="01:30">1:30 am</option>
				<option value="02:00">2:00 am</option>
				<option value="02:30">2:30 am</option>
				<option value="03:00">3:00 am</option>
				<option value="03:30">3:30 am</option>
				<option value="04:00">4:00 am</option>
				<option value="04:30">4:30 am</option>
				<option value="05:00">5:00 am</option>
				<option value="05:30">5:30 am</option>
				<option value="06:00">6:00 am</option>
				<option value="06:30">6:30 am</option>
				<option value="07:00">7:00 am</option>
				<option value="07:30">7:30 am</option>
				<option value="08:00">8:00 am</option>
				<option value="08:30">8:30 am</option>
				<option value="09:00">9:00 am</option>
				<option value="09:30">9:30 am</option>
				<option value="10:00">10:00 am</option>
				<option value="10:30">10:30 am</option>
				<option value="11:00">11:00 am</option>
				<option value="11:30">11:30 am</option>
				<option value="11:00">11:00 am</option>
				<option value="11:30">11:30 am</option>
				<option value="12:00" selected>12:00 pm</option>
				<option value="12:30">12:30 pm</option>
				<option value="13:00">1:00 pm</option>
				<option value="13:30">1:30 pm</option>
				<option value="14:00">2:00 pm</option>
				<option value="14:30">2:30 pm</option>
				<option value="15:00">3:00 pm</option>
				<option value="15:30">3:30 pm</option>
				<option value="16:00">4:00 pm</option>
				<option value="16:30">4:30 pm</option>
				<option value="17:00">5:00 pm</option>
				<option value="17:30">5:30 pm</option>
				<option value="18:00">6:00 pm</option>
				<option value="18:30">6:30 pm</option>
				<option value="19:00">7:00 pm</option>
				<option value="19:30">7:30 pm</option>
				<option value="20:00">8:00 pm</option>
				<option value="20:30">8:30 pm</option>
				<option value="21:00">9:00 pm</option>
				<option value="21:30">9:30 pm</option>
				<option value="22:00">10:00 pm</option>
				<option value="22:30">10:30 pm</option>
				<option value="23:00">11:00 pm</option>
				<option value="23:30">11:30 pm</option>
				<option value="24:00">12:00 pm</option>
			</select>
			</p>
			
			<div class="clear"></div>
			
      		<p class="alignleft"><!--DURATION-->
       		<label for="postDuration"><?php _e('Duration', 'themolitor') ?></label><br />
       		<input type="text" name="postDuration" id="postDuration" value="<?php if(isset($_POST['postDuration']) && $_SERVER['REQUEST_METHOD'] != "POST"){ echo $_POST['postDuration']; } ?>" /><br />
			</p>
			
			<p class="alignleft"><!--COST-->
       		<label for="postCost"><?php _e('Cost', 'themolitor') ?></label><br />
       		<input type="text" name="postCost" id="postCost" value="<?php if(isset($_POST['postCost']) && $_SERVER['REQUEST_METHOD'] != "POST"){ echo $_POST['postCost']; } ?>" /><br />
			</p>
			
			<div class="clear"></div>
			
			<p class="alignleft"><!--AGES-->
       		<label for="postAges"><?php _e('Ages', 'themolitor') ?></label><br />
       		<input type="text" name="postAges" id="postAges" value="<?php if(isset($_POST['postAges']) && $_SERVER['REQUEST_METHOD'] != "POST"){ echo $_POST['postAges']; } ?>" /><br />
			</p>
			
			<p class="alignleft"><!--ADDRESS-->
			<label for="postAddress"><?php _e('Address', 'themolitor') ?></label><br />
			<input type="text" name="postAddress" id="postAddress" value="<?php if(isset($_POST['postAddress']) && $_SERVER['REQUEST_METHOD'] != "POST"){ echo $_POST['postAddress']; }?>" />
			</p>
			
			<div class="clear"></div>
			
			<p class="alignleft"><!--TYPE-->
			<label for="cat"><?php _e('Event Type', 'themolitor') ?></label><br />
			<?php wp_dropdown_categories( 'show_option_none='.__('Select','themolitor').'&taxonomy=category&child_of='.$eventCat ); ?>
			</p>			
 			
 			<p class="alignleft"><!--TAGS-->
 			<label for="postTags"><?php _e('Keyword Tags', 'themolitor') ?></label>&nbsp;&nbsp;<span class="formExample">- <?php _e('separated, by, comma','themolitor');?></span><br />
 			<input type="text" name="postTags" id="postTags" value="<?php if(isset($_POST['postTags']) && $_SERVER['REQUEST_METHOD'] != "POST"){ echo $_POST['postTags']; }?>" />
 			</p>
 			
 			<div class="clear"></div>
 			
 			<p><!--WEB LINK-->
 			<label for="postLink"><?php _e('Web Link', 'themolitor') ?></label><br />
 			<input type="text" name="postLink" id="postLink" value="<?php if(isset($_POST['postLink']) && $_SERVER['REQUEST_METHOD'] != "POST"){ echo $_POST['postLink']; }?>" />
 			</p>
 			
 			<div class="clear"></div>
 			
 			<p><!--CONTENT-->
       		<label for="postContent"><?php _e('Details', 'themolitor') ?><span class="red">*</span></label><?php if ($postContentError != '') { ?> <span class="error"><?php echo $postContentError; ?></span><?php } ?><br />
       		If you have youtube or soundcloud assets to share, copy the link into this box on its own line and it will display when published.
 			<textarea name="postContent" id="postContent" rows="8" cols="30" class="required"><?php if(isset( $_POST['postContent']) && $_SERVER['REQUEST_METHOD'] != "POST"){ if(function_exists('stripslashes')){ echo stripslashes($_POST['postContent']); } else { echo $_POST['postContent'];} } ?></textarea>
 			</p>
 			 			 			
 			<p><!--TEST-->
 			<label for="postTest">100 + <?php _e('Two', 'themolitor') ?> = <span class="red">*</span></label>
 			<input type="text" name="postTest" id="postTest" value="<?php if(isset($_POST['postTest']) && $_SERVER['REQUEST_METHOD'] != "POST"){ echo $_POST['postTest']; }?>" class="required" /><?php if ($postTestError != '') { ?> <span class="error"><?php _e('Required','themolitor');?></span><?php } ?>
 			</p>
 			
 			<p><!--SUBMIT-->
       		<input type="hidden" name="submitted" id="submitted" value="true" />
       		<?php wp_nonce_field( 'post_nonce', 'post_nonce_field' ); ?>
       		<input id="postSubmit" type="submit" value="<?php _e('Submit for Review', 'themolitor') ?>" /> 
       		</p>
       		
 		</form><!--end form-->
 		<?php } ?>
 		
		<div class="clear"></div>
    </div><!--end entry-->
       	
	<div class="clear"></div>
</div><!--end post--> 
	
<?php 
endwhile; endif;
//get_sidebar();
get_footer(); 
if($confirmation){echo "<script type='text/javascript'>alert('".$confirmation."');</script>";}
?>