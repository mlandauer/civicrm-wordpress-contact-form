<?php
/*
Plugin Name: CiviCRM Contact Form
Plugin URI: 
Description: Easily embed a contact form anywhere and capture the entries straight into CiviCRM
Author: Matthew Landauer - OpenAustralia Foundation
Version: 0.0.1
Author URI: 
*/

// TODO: Put all these functions in a class

function civicrm_add_contact($civicrm_drupal_root_url, $site_key, $api_key, $first_name, $last_name, $email)
{
    $rest_url = "{$civicrm_drupal_root_url}/sites/all/modules/civicrm/extern/rest.php?key={$site_key}&q=civicrm";
    $url = "{$rest_url}/contact/add&api_key={$api_key}&first_name={$first_name}&last_name={$last_name}&email={$email}&contact_type=Individual";
    wp_remote_post($url);
    // TODO: Error checking
    echo "<p>URL: {$url}</p>";
}

function civicrm_get_api_key($civicrm_drupal_root_url, $site_key, $username, $password)
{
    $rest_url = "{$civicrm_drupal_root_url}/sites/all/modules/civicrm/extern/rest.php?key={$site_key}&q=civicrm";
    $url = "{$rest_url}/login&name={$username}&pass={$password}&json=1";
    $result = wp_remote_get($url);
    $json = json_decode($result["body"], true);
    // TODO: Error checking
    return $json["api_key"];
}

function civicrm_form_shortcode($attrs)
{	
	if ($_POST) {
        $option = get_option('civicrm');
		$civicrm_drupal_root_url = $option['drupal_root_url'];
		$site_key = $option['site_key'];
		$username = $option['username'];
		$password = $option['password'];

        $api_key = civicrm_get_api_key($civicrm_drupal_root_url, $site_key, $username, $password);

		// TODO: Clean up input
		$first_name = $_POST["first_name"];
		$last_name = $_POST["last_name"];
		$email = $_POST["email"];
		
		echo "<p>Values just submitted:</p>";
		echo "<p>First Name: {$first_name}</p>";
		echo "<p>Last Name: {$last_name}</p>";
		echo "<p>Email: {$email}</p>";
        civicrm_add_contact($civicrm_drupal_root_url, $site_key, $api_key, $first_name, $last_name, $email);
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
            <?php do_settings_sections('civicrm_admin_options'); ?>
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
 	add_settings_section('civicrm_server_settings', 'CiviCRM server settings', null, 'civicrm_admin_options');

    add_settings_field('civicrm_drupal_root_url', 'Drupal Home URL', 'civicrm_drupal_root_url_callback_function', 'civicrm_admin_options', 'civicrm_server_settings');
    add_settings_field('civicrm_site_key', 'Site Key', 'civicrm_site_key_callback_function', 'civicrm_admin_options', 'civicrm_server_settings');

 	add_settings_section('civicrm_user_settings', 'CiviCRM user with API access', 'civicrm_user_settings_callback_function', 'civicrm_admin_options');
    add_settings_field('civicrm_username', 'Username', 'civicrm_username_callback_function', 'civicrm_admin_options', 'civicrm_user_settings');
    add_settings_field('civicrm_password', 'Password', 'civicrm_password_callback_function', 'civicrm_admin_options', 'civicrm_user_settings');

    register_setting( 'civicrm-settings-group', 'civicrm' );
}

add_action('admin_menu', 'civicrm_register_options_page');
add_action('admin_init', 'civicrm_register_settings' );

function civicrm_drupal_root_url_callback_function()
{
    $option = get_option("civicrm");
    $value = $option['drupal_root_url'];
    echo "<input type='text' name='civicrm[drupal_root_url]' value='{$value}'  size=35/> <br/>Where CiviCRM is installed. e.g. http://www.foo.com/drupal6";
}

function civicrm_site_key_callback_function()
{
    $option = get_option("civicrm");
    $value = $option['site_key'];
    echo "<input type='text' name='civicrm[site_key]' value='{$value}' size=35 /><br/> See CIVICRM_SITE_KEY in /etc/drupal/6/sites/default/civicrm.settings.php";
}

function civicrm_user_settings_callback_function()
{
    echo "Here, we need a CiviCRM user that has REST API access. See the <a href='http://wiki.civicrm.org/confluence/display/CRMDOC32/REST+interface'>CiviCRM wiki</a> for details of the excrutiating process you have to go through to create an API key for a particular user.";
}

function civicrm_username_callback_function()
{
    $option = get_option("civicrm");
    $value = $option['username'];
    echo "<input type='text' name='civicrm[username]' value='{$value}' />";
}

function civicrm_password_callback_function()
{
    $option = get_option("civicrm");
    $value = $option['password'];
    echo "<input type='password' name='civicrm[password]' value='{$value}' />";
}
?>
