<?php
/*
   Plugin Name: Mailjet for Wordpress
   Version: 1.0.1
   Plugin URI: https://www.mailjet.com/plugin/wordpress.htm
   Description: Reconfigures the wp_mail() function to use Mailjet SMTP instead of mail() and creates an options page to manage the settings.
   Author: Mailjet
   Author URI: http://www.mailjet.com/
*/

/**
 * @author Callum Macdonald
 * @copyright Callum Macdonald, 2007-8, All Rights Reserved
 * This code is released under the GPL licence version 3 or later, available here
 * http://www.gnu.org/licenses/gpl.txt
 */
/*  Copyright 2012  MAILJET  (email : PLUGINS@MAILJET.COM)

   This program is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License, version 2, as
   published by the Free Software Foundation.

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software
   Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define ('MJ_HOST', 'in.mailjet.com');

// Array of options and their default values
global $wpms_options; // This is horrible, should be cleaned up at some point
global $wpms_core_options; // This is horrible, should be cleaned up at some point

$wpms_options = array (
	'mj_enabled' => '',
	'mj_test' => '',
    'mj_test_address' => '',
	'mj_port' => '',
	'mj_ssl' => '',
	'mj_username' => '',
	'mj_password' => ''
);

/**
 * Activation function. This function creates the required options and defaults.
 */
if (!function_exists('wp_mailjet_smtp_activate')) :
function wp_mailjet_smtp_activate() {

    global $wpms_options;

    $opts = $wpms_options;

    // Create the required options...
    foreach ($opts as $name => $val) {
        add_option($name,$val);
    }

}
endif;

if (!function_exists('wp_mailjet_smtp_whitelist_options')) :
function wp_mailjet_smtp_whitelist_options($whitelist_options) {

    global $wpms_options;

    // Add our options to the array
    $whitelist_options['email'] = array_keys($wpms_options);

    return $whitelist_options;

}
endif;

// To avoid any (very unlikely) clashes, check if the function alredy exists
if (!function_exists('phpmailer_init_smtp')) :
    // This code is copied, from wp-includes/pluggable.php as at version 2.2.2
function phpmailer_init_smtp ($phpmailer)
{
    if (! get_option ('mj_enabled') || 0 == get_option ('mj_enabled')) return;

    $phpmailer->Mailer = 'smtp';
    $phpmailer->SMTPSecure = get_option ('mj_ssl');

    $phpmailer->Host = MJ_HOST;
    $phpmailer->Port = get_option ('mj_port');

    $phpmailer->SMTPAuth = TRUE;
    $phpmailer->Username = get_option('mj_username');
    $phpmailer->Password = get_option('mj_password');

    $phpmailer->From = get_option ('admin_email');

    $phpmailer->AddCustomHeader ('X-Mailer:Mailjet-for-Wordpress/1.0');

} // End of phpmailer_init_smtp() function definition
endif;



/**
 * This function outputs the plugin options page.
 */
if (!function_exists('wp_mailjet_smtp_options_page')) :
// Define the function
function wp_mailjet_smtp_options_page() {

// Load the options
global $wpms_options, $phpmailer;

$fields = $wpms_options;
$test_sent = FALSE;
$errors = array ();

// Make sure the PHPMailer class has been instantiated
// (copied verbatim from wp-includes/pluggable.php)
// (Re)create it, if it's gone missing
if ( !is_object( $phpmailer ) || !is_a( $phpmailer, 'PHPMailer' ) ) {
    require_once ABSPATH . WPINC . '/class-phpmailer.php';
    require_once ABSPATH . WPINC . '/class-smtp.php';
    $phpmailer = new PHPMailer();
}

if (isSet ($_POST ['mj_test_address']) && isSet ($_POST ['mj_username']) && isSet ($_POST ['mj_password']))
{
    $fields ['mj_enabled'] = isSet ($_POST ['mj_enabled']);
    $fields ['mj_test'] = isSet ($_POST ['mj_test']);
    $fields ['mj_test_address'] = strip_tags ($_POST ['mj_test_address']);
    $fields ['mj_username'] = strip_tags ($_POST ['mj_username']);
    $fields ['mj_password'] = strip_tags ($_POST ['mj_password']);

    if ($fields ['mj_test'] && empty ($fields ['mj_test_address']))
    {
        $errors [] = 'mj_test_address';
    }

    if (! empty ($fields ['mj_test_address']))
    {
        if (! validate_email ($fields ['mj_test_address'], FALSE))
        {
            $errors [] = 'mj_test_address';
        }
    }

    if (empty ($fields ['mj_username']))
    {
        $errors [] = 'mj_username';
    }

    if (empty ($fields ['mj_password']))
    {
        $errors [] = 'mj_password';
    }

    if (! count ($errors))
    {
        update_option ('mj_enabled', $fields ['mj_enabled']);
        update_option ('mj_test', $fields ['mj_test']);
        update_option ('mj_test_address', $fields ['mj_test_address']);
        update_option ('mj_username', $fields ['mj_username']);
        update_option ('mj_password', $fields ['mj_password']);

        $configs = array (array ('ssl://', 465),
                          array ('tls://', 587),
                          array ('', 587),
                          array ('', 588),
                          array ('tls://', 25),
                          array ('', 25));

        $host = MJ_HOST;
        $connected = FALSE;

        for ($i = 0; $i < count ($configs); ++$i)
        {
            $soc = @ fSockOpen ($configs [$i] [0].$host, $configs [$i] [1], $errno, $errstr, 5);

            if ($soc)
            {
                fClose ($soc);

                $connected = TRUE;

                break;
            }
        }

        if ($connected)
        {
            if ('ssl://' == $configs [$i] [0])
            {
                update_option ('mj_ssl', 'ssl');
            }
            elseif ('tls://' == $configs [$i] [0])
            {
                update_option ('mj_ssl', 'tls');
            }
            else
            {
                update_option ('mj_ssl', '');
            }

            update_option ('mj_port', $configs [$i] [1]);

            if ($fields ['mj_test'])
            {
                $subject = __('Your test mail from Mailjet', 'wp_mailjet_smtp');
                $message = __('Your Mailjet configuration is ok!', 'wp_mailjet_smtp');

                $enabled = get_option ('mj_enabled');

                update_option ('mj_enabled', 1);

                wp_mail ($fields ['mj_test_address'], $subject, $message);

                update_option ('mj_enabled', $enabled);

                $test_sent = TRUE;
            }
        }
        else
        {
            echo '<h1>'.sPrintF (__ ('Please contact Mailjet support to sort this out.<br /><br />%d - %s', $errno, $errstr)).'</h1>';
        }
    }
}
else
{
    $fields ['mj_enabled'] = get_option ('mj_enabled');
    $fields ['mj_test'] = get_option ('mj_test');
    $fields ['mj_test_address'] = get_option ('mj_test_address');
    $fields ['mj_username'] = get_option ('mj_username');
    $fields ['mj_password'] = get_option ('mj_password');
}

?>
<style type="text/css">
.mj-infos {background: #f3f3f3; padding: 10px 20px; border: 1px solid #eaeaea;}
.mj-infos h2 {font-size: 14px;}
.mj-infos ul {list-style-type: disc; margin-left: 30px;}
.mj-infos li {margin-bottom: 3px;}
.mj_wrap {padding: 20px;}
.mj_wrap th {width: 200px; text-align: left; padding-left: 20px; padding-top: 4px;}
.mj_wrap td {font-size: 11px;}

</style>
<div class="mj_wrap">

<div class="mj-infos">

<div class="icon32" style="background: url('<?php echo plugins_url ('images/mj_logo_small.png', __FILE__); ?>') 0 5px no-repeat;"><br /></div>
<h2><?php _e ('Mailjet - Cloud Emailing Solution', 'wp_mailjet_smtp'); ?></h2>


	<h4><?php _e ('To use the plugin you will need :', 'wp_mailjet_smtp'); ?></h4>

	<ul>
		<li><?php _e ('<strong>A Mailjet account</strong> and its API keys.', 'wp_mailjet_smtp'); ?></li>

		<li><?php _e ('<strong>Verified email address</strong> to use as sender for email.', 'wp_mailjet_smtp'); ?></li>
	</ul>
	<p><?php echo sPrintF (__ ('For detailed setup instructions please see %s', 'wp_mailjet_smtp'), '<a href="'.plugins_url ('readme.txt', __FILE__).'" target="_tab">readme.txt</a>'); ?></p>

<?php

if ($test_sent)
{

?>

<h2><?php _e('A test mail has been sent to your address.', 'wp_mailjet_smtp'); ?></h2>

<?php

}

?>

<h2><?php _e('General settings', 'wp_mailjet_smtp'); ?></h2>
<form method="post">
	<fieldset class="options">
	<table class="optiontable">
		<tr valign="top">
			<th scope="row"><label for="mj_enabled"><?php _e('Enabled :', 'wp_mailjet_smtp'); ?></label></th>
			<td><input name="mj_enabled" type="checkbox" id="mj_enabled" <?php echo (1 == $fields ['mj_enabled'] ? 'checked="checked"' : ''); ?> value="1" /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="mj_test"><?php _e('Send test mail now :', 'wp_mailjet_smtp'); ?></label></th>
			<td><input name="mj_test" type="checkbox" id="mj_test" <?php echo (1 == $fields ['mj_test'] ? 'checked="checked"' : ''); ?> value="1" /></td>
		</tr>
		<tr valign="top">
			<th scope="row" style="width:142px;"><label for="mj_test_address" <?php if (in_array ('mj_test_address', $errors)) echo 'style="color: red;"'; ?>><?php _e('Recipient of test mail :', 'wp_mailjet_smtp'); ?></label></th>
			<td><input name="mj_test_address" type="text" id="mj_test_address" value="<?php echo $fields ['mj_test_address']; ?>" size="40" class="code" /></td>
		</tr>
	</table>

<h2><?php _e('Mailjet settings', 'wp_mailjet_smtp'); ?></h2>
<table class="optiontable">
<tr valign="top">
	<th scope="row"><label for="mj_username"<?php if (in_array ('mj_username', $errors)) echo 'style="color: red;"'; ?>><?php _e('API Key :', 'wp_mailjet_smtp'); ?></label></th>
	<td><input name="mj_username" type="text" id="mj_username" value="<?php echo $fields ['mj_username']; ?>" size="40" class="code" /></td>
</tr>
<tr valign="top">
	<th scope="row"><label for="mj_password" <?php if (in_array ('mj_password', $errors)) echo 'style="color: red;"'; ?>><?php _e('Secret Key :', 'wp_mailjet_smtp'); ?></label></th>
	<td><input name="mj_password" type="text" id="mj_password" value="<?php echo $fields ['mj_password']; ?>" size="40" class="code" /></td>
</tr>
</table>

<p class="submit"><input type="submit" name="Submit" value="<?php _e('Save &raquo;', 'wp_mailjet_smtp'); ?>" />

</p>
</fieldset>
</form>
</div>
</div>
	<?php

} // End of wp_mailjet_smtp_options_page() function definition
endif;


/**
 * This function adds the required page (only 1 at the moment).
 */
if (!function_exists('wp_mailjet_smtp_menus')) :
function wp_mailjet_smtp_menus() {

    if (function_exists('add_submenu_page')) {
        add_options_page(__('Mailjet settings', 'wp_mailjet_smtp'),__('Mailjet settings', 'wp_mailjet_smtp'),'manage_options',__FILE__,'wp_mailjet_smtp_options_page');
    }

} // End of wp_mailjet_smtp_menus() function definition
endif;


/**
 * This is copied directly from WPMU wp-includes/wpmu-functions.php
 */
if (!function_exists('validate_email')) :
function validate_email( $email, $check_domain = true) {
    if (ereg('^[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+'.'@'.
        '[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.'.
        '[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+$', $email))
    {
        if ($check_domain && function_exists('checkdnsrr')) {
            list (, $domain)  = explode('@', $email);

            if (checkdnsrr($domain.'.', 'MX') || checkdnsrr($domain.'.', 'A')) {
                return true;
            }
            return false;
        }
        return true;
    }
    return false;
} // End of validate_email() function definition
endif;


/**
 * This function sets the from email value
 */
if (!function_exists('wp_mailjet_smtp_mail_from')) :
function wp_mailjet_smtp_mail_from ($orig) {

    return get_option ('admin_email');

} // End of wp_mailjet_smtp_mail_from() function definition
endif;

/**
 * This function grabs the from email value and sets it as Sender
 */
if (!function_exists('wp_mailjet_smtp_set_sender')) :
function wp_mailjet_smtp_set_sender($from_email) {
    global $phpmailer;
    $phpmailer->Sender = $from_email;
    return $from_email;
}
endif;


/**
 * This function sets the from name value
 */
if (!function_exists('wp_mailjet_smtp_mail_from_name')) :
function wp_mailjet_smtp_mail_from_name ($orig) {

    return $orig;

} // End of wp_mailjet_smtp_mail_from_name() function definition
endif;

function wp_mail_plugin_action_links( $links, $file ) {
    if ( $file != plugin_basename( __FILE__ ))
        return $links;

    $settings_link = '<a href="options-general.php?page=mailjet-for-wordpress/wp_mailjet_smtp.php">' . __( 'Settings', 'wp_mailjet_smtp' ) . '</a>';

    array_unshift( $links, $settings_link );

    return $links;
}

// Add an action on phpmailer_init
add_action('phpmailer_init','phpmailer_init_smtp');

if (!defined('WPMS_ON') || !WPMS_ON) {
    // Whitelist our options
    add_filter('whitelist_options', 'wp_mailjet_smtp_whitelist_options');
    // Add the create pages options
    add_action('admin_menu','wp_mailjet_smtp_menus');
    // Add an activation hook for this plugin
    register_activation_hook(__FILE__,'wp_mailjet_smtp_activate');
    // Adds "Settings" link to the plugin action page
    add_filter( 'plugin_action_links', 'wp_mail_plugin_action_links',10,2);
}

// Add filters to replace the mail from name and emailaddress
add_filter ('wp_mail_from','wp_mailjet_smtp_mail_from');
add_filter ('wp_mail_from','wp_mailjet_smtp_set_sender', 99);
add_filter ('wp_mail_from_name','wp_mailjet_smtp_mail_from_name');

load_plugin_textdomain ('wp_mailjet_smtp', FALSE, dirname (plugin_basename(__FILE__)) . '/langs');

?>
