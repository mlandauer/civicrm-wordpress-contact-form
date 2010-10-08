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

function civicrm_api($civicrm_rest_url, $site_key, $command, $method, $params)
{
    $params = array_merge($params, array("key" => $site_key, "q" => "civicrm/{$command}", "json" => 1));
    $url = $civicrm_rest_url."?".http_build_query($params);
    $result = wp_remote_request($url, array("method" => $method));
    $json = json_decode($result["body"], true);
    // TODO: Error checking
    return $json;
}

function civicrm_add_contact($civicrm_rest_url, $site_key, $api_key, $first_name, $last_name, $email)
{
    $params = array( "api_key" => $api_key, "first_name" => $first_name, "last_name" => $last_name,
        "email" => $email, "contact_type" => "Individual");
    civicrm_api($civicrm_rest_url, $site_key, "contact/add", "POST", $params);
}

function civicrm_get_api_key($civicrm_rest_url, $site_key, $username, $password)
{
    $params2 = array("name" => $username, "pass" => $password);
    $result = civicrm_api($civicrm_rest_url, $site_key, "login", "POST", $params2);    
    return $result["api_key"];
}

function civicrm_form_shortcode($attrs)
{
    if ($_POST) {
        $option = get_option('civicrm');

        // TODO: Clean up input
        $first_name = $_POST["first_name"];
        $last_name = $_POST["last_name"];
        $email = $_POST["email"];

        echo "<p>Thanks for getting in touch! Your message has been sent</p>";
        civicrm_add_contact($option['rest_url'], $option['site_key'], $option['api_key'], $first_name, $last_name, $email);
    }
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

add_shortcode('civicrm', 'civicrm_form_shortcode');

function civicrm_options_page()
{
    ?>
    <div class="wrap">
        <h2>CiviCRM Contact Form Settings</h2>
        <div class="postbox-container" style="width:70%;">
            <form method="post" action="options.php">
                <?php settings_fields( 'civicrm-settings-group' ); ?>
                <?php do_settings_sections('civicrm_admin_options'); ?>
                <p class="submit">
                <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
                </p>
            </form>
        </div>
        <div class="postbox-container" style="width:20%;">
            <p>First fill in the details on the left.</p>
            <p>Then copy the following shortcode into a post or page:
                <pre style="padding:5px 10px;margin:10px 0;background-color:lightyellow;">[civicrm]</pre>
            </p>
        </div>
    </div>
    <?php
}

function civicrm_admin_warning() {
    $options  = get_option('civicrm');
    if (!isset($options['api_key']) || empty($options['api_key']))
        echo "<div id='message' class='error'><p><strong>CiviCRM Contact Form is almost ready. You must <a href='".admin_url( 'options-general.php?page=civicrm-contact-form')."'>enter some details</a> for it to work.</p></div>";
}

function civicrm_register_options_page()
{
    add_options_page( 'CiviCRM Contact Form Settings', 'CiviCRM Contact Form', 'manage_options', 'civicrm-contact-form', 'civicrm_options_page');

}

function civicrm_register_settings()
{
    add_settings_section('civicrm_server_settings', 'CiviCRM server settings', 'civicrm_server_settings_callback_function', 'civicrm_admin_options');

    add_settings_field('civicrm_cms', 'CMS on which it is installed', 'civicrm_cms_callback_function', 'civicrm_admin_options', 'civicrm_server_settings');
    add_settings_field('civicrm_root_url', 'Home URL', 'civicrm_root_url_callback_function', 'civicrm_admin_options', 'civicrm_server_settings');
    add_settings_field('civicrm_site_key', 'Site Key', 'civicrm_site_key_callback_function', 'civicrm_admin_options', 'civicrm_server_settings');

    add_settings_section('civicrm_user_settings', 'CiviCRM user with API access', 'civicrm_user_settings_callback_function', 'civicrm_admin_options');
    add_settings_field('civicrm_username', 'Username', 'civicrm_username_callback_function', 'civicrm_admin_options', 'civicrm_user_settings');
    add_settings_field('civicrm_password', 'Password', 'civicrm_password_callback_function', 'civicrm_admin_options', 'civicrm_user_settings');

    register_setting( 'civicrm-settings-group', 'civicrm', 'civicrm_validate' );
}

add_action('admin_menu', 'civicrm_register_options_page');
add_action('admin_init', 'civicrm_register_settings' );
add_action('admin_footer', 'civicrm_admin_warning');

function civicrm_validate($input)
{
    // Store away the rest_url in the config as well
    if ($input['cms'] == 'Drupal')
        $input['rest_url'] = $input['root_url']."/sites/all/modules/civicrm/extern/rest.php";
    else
        $input['rest_url'] = $input['root_url']."/administrator/components/com_civicrm/civicrm/extern/rest.php";

    // Store away the api key in the config as well
    $input['api_key'] = civicrm_get_api_key($input['rest_url'], $input['site_key'], $input['username'], $input['password']);
    if (!$input['api_key'])
        add_settings_error('civicrm_root_url', '', __("Error in talking to CiviCRM {$api_key}"));
        
    return $input;
}

function civicrm_cms_callback_function()
{
    $option = get_option("civicrm");
    if ($option['cms'] == 'Joomla!')
        echo "<select name='civicrm[cms]'><option>Drupal</option><option selected='selected'>Joomla!</option></select>";
    else
        echo "<select name='civicrm[cms]'><option selected='selected'>Drupal</option><option>Joomla!</option></select>";
}

function civicrm_root_url_callback_function()
{
    $option = get_option("civicrm");
    $value = $option['root_url'];
    echo "<input type='text' name='civicrm[root_url]' value='{$value}'  size=35/> <br/>Where CiviCRM is installed. e.g. http://www.foo.com/ - This should be the root of the Drupal or Joomla site.";
}

function civicrm_site_key_callback_function()
{
    $option = get_option("civicrm");
    $value = $option['site_key'];
    echo "<input type='text' name='civicrm[site_key]' value='{$value}' size=35 /><br/> See <a href='http://wiki.civicrm.org/confluence/display/CRMDOC/Command-line+Script+Configuration'>CIVICRM_SITE_KEY</a> in your civicrm.settings.php";
}

function civicrm_server_settings_callback_function()
{
    echo "Enter the details of your CiviCRM server.";
}

function civicrm_user_settings_callback_function()
{
    echo "Here, we need a CiviCRM user that has REST API access. See the <a href='http://wiki.civicrm.org/confluence/display/CRMDOC/REST+interface'>CiviCRM wiki</a> for details of the excruciating process you have to go through to create an API key for a particular user.";
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
