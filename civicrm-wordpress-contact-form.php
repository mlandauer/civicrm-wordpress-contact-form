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
	// TODO: These values need to be configurable
	// Key for the whole site (CIVICRM_SITE_KEY in /etc/drupal/6/sites/default/civicrm.settings.php)
	$site_key = "tOw8DDyH8jKOKo40JCXq";
	$civicrm_root_url = "http://localhost/drupal6/sites/all/modules/civicrm";
	// Username and password for the CiviCRM user associated with the actions of this plugin
	$username = "matthew";
	$password = "password";

	if ($_POST) {
		$url = "{$civicrm_root_url}/extern/rest.php?q=civicrm/login&key={$site_key}&name={$username}&pass={$password}&json=1";
		$result = wp_remote_get($url);
		$json = json_decode($result["body"], true);
		$api_key = $json["api_key"];

		// TODO: Clean up input
		$first_name = $_POST["first_name"];
		$last_name = $_POST["last_name"];
		$email = $_POST["email"];
		
		echo "<p>Values just submitted:</p>";
		echo "<p>First Name: {$first_name}</p>";
		echo "<p>Last Name: {$last_name}</p>";
		echo "<p>Email: {$email}</p>";
		$url = "{$civicrm_root_url}/extern/rest.php?q=civicrm/contact/add&key={$site_key}&api_key={$api_key}&first_name={$first_name}&last_name={$last_name}&email={$email}&contact_type=Individual";
		wp_remote_post($url);
		echo "<p>URL: {$url}</p>";
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
