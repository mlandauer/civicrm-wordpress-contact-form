<?php
/*
Plugin Name: CiviCRM Contact Form
Plugin URI: 
Description: Easily embed a contact form anywhere and capture the entries straight into CiviCRM
Author: Matthew Landauer - OpenAustralia Foundation
Version: 0.0.1
Author URI: 
*/

function civicrm_form_shortcode($attrs)
{
	if ($_POST) {
		echo "<p>Values just submitted:</p>";
		echo "<p>First Name: {$_POST["first_name"]}</p>";
		echo "<p>Last Name: {$_POST["last_name"]}</p>";
		echo "<p>Email: {$_POST["email"]}</p>";
	}
	else {
?>
	<form action="" method="post" accept-charset="utf-8" id="contact">
	<p>
		Your First Name: <br>
		<input type="text" name="first_name" value="" id="first_name" />
	</p>
	
	<p>
		Your Last Name:<br>
		<input type="text" name="last_name" value="" id="last_name" />
	</p>
	
	<p>
		Email Address:<br>
		<input type="text" name="email" value="" id="email" />
	</p>
	
	<input type="submit" value="contact" name="contact">
</form>
<?php
	}
}

add_shortcode('civicrm', 'civicrm_form_shortcode');	

?>
