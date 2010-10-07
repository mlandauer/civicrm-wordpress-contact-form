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
		$civicrm_root_url = get_option('civicrm_root_url');
		$site_key = get_option('civicrm_site_key');
		$username = get_option('civicrm_username');
		$password = get_option('civicrm_password');

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

function civicrm_options_page()
{
	?>
	<div class="wrap">
		<h2>CiviCRM Contact Form Settings</h2>
		<form method="post" action="options.php">
			<?php settings_fields( 'civicrm-settings-group' ); ?>
			<table class="form-table">
				<tr valign="top">
				<th scope="row">CiviCRM root URL</th>
				<td><input type="text" name="civicrm_root_url" value="<?php echo get_option('civicrm_root_url'); ?>" /></td>
				</tr>
				 
				<tr valign="top">
				<th scope="row">CiviCRM site key</th>
				<td><input type="text" name="civicrm_site_key" value="<?php echo get_option('civicrm_site_key'); ?>" /></td>
				</tr>
				 
				<tr valign="top">
				<th scope="row">CiviCRM username</th>
				<td><input type="text" name="civicrm_username" value="<?php echo get_option('civicrm_username'); ?>" /></td>
				</tr>
				
				<tr valign="top">
				<th scope="row">CiviCRM password</th>
				<td><input type="text" name="civicrm_password" value="<?php echo get_option('civicrm_password'); ?>" /></td>
				</tr>
			</table>
			
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>

		</form>
	</div>
	<?php
}

function civicrm_register_options_page()
{
	add_options_page( 'My Plugin Options', 'CiviCRM Contact Form', 'manage_options', 'civicrm-contact-form', 'civicrm_options_page');

}

function civicrm_register_settings()
{
	register_setting( 'civicrm-settings-group', 'civicrm_root_url' );
	register_setting( 'civicrm-settings-group', 'civicrm_site_key' );
	register_setting( 'civicrm-settings-group', 'civicrm_username' );
	register_setting( 'civicrm-settings-group', 'civicrm_password' );
}

add_action('admin_menu', 'civicrm_register_options_page');
add_action('admin_init', 'civicrm_register_settings' );

?>
