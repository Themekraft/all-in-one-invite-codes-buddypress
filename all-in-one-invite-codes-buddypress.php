<?php

/**
 * Plugin Name: All in One Invite Codes BuddyPress
 * Plugin URI: https://themekraft.com/products/all-in-one-invite-codes-buddypress/
 * Description: Create Invite only Forms
 * Version: 1.0.8
 * Requires PHP: 7.4
 * Requires Plugins: all-in-one-invite-codes
 * Author: ThemeKraft
 * Author URI: https://themekraft.com/
 * License: GPLv3
 * Text Domain: all-in-one-invite-codes-buddypress
 * Domain Path: /languages
 *
 * ****************************************************************************
 *
 * This script is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.    See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA    02111-1307    USA
 *
 ****************************************************************************
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'all_in_one_invite_codes_addon_plugin_dependencies_slug' ) ) {
	/**
	 * Let the running All in One Invite Codes copy (free or premium) satisfy
	 * "Requires Plugins: all-in-one-invite-codes".
	 *
	 * @param string $slug Dependency slug.
	 *
	 * @return string
	 */
	function all_in_one_invite_codes_addon_plugin_dependencies_slug( $slug ) {
		if ( 'all-in-one-invite-codes' === $slug && defined( 'TK_ALL_IN_ONE_INVITE_CODES_INSTALL_PATH' ) ) {
			return basename( TK_ALL_IN_ONE_INVITE_CODES_INSTALL_PATH );
		}

		return $slug;
	}

	add_filter( 'wp_plugin_dependencies_slug', 'all_in_one_invite_codes_addon_plugin_dependencies_slug' );
}

/**
 * Whether All in One Invite Codes (free or premium) is active and in place.
 *
 * Read from the active plugin lists because this add-on loads before its parent,
 * so the parent's functions do not exist yet when this file runs.
 *
 * @return bool
 */
function aioic_buddypress_parent_is_active() {
	$active_plugins = (array) get_option( 'active_plugins', array() );
	if ( is_multisite() ) {
		$active_plugins = array_merge( $active_plugins, array_keys( (array) get_site_option( 'active_sitewide_plugins', array() ) ) );
	}

	foreach ( array( 'all-in-one-invite-codes', 'all-in-one-invite-codes-premium' ) as $folder ) {
		$basename = $folder . '/all-in-one-invite-codes.php';
		if ( in_array( $basename, $active_plugins, true ) && file_exists( WP_PLUGIN_DIR . '/' . $basename ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Admin notice shown while All in One Invite Codes is missing.
 */
function aioic_buddypress_parent_missing_notice() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	printf(
		'<div class="notice notice-error"><p>%s</p></div>',
		esc_html__( 'All in One Invite Codes BuddyPress is not running because All in One Invite Codes is not active.', 'all-in-one-invite-codes-buddypress' )
	);
}

// Without the parent, every hook below would call functions that do not exist.
if ( ! aioic_buddypress_parent_is_active() ) {
	add_action( 'admin_notices', 'aioic_buddypress_parent_missing_notice' );

	return;
}

add_action('wp_enqueue_scripts', 'aioic_buddypress_scripts', 100, 1);
require_once('aioic-invites-widget.php');
add_action('widgets_init', 'aioic_register_widget');
add_action( 'init', 'load_plugin_textdomain_aioic_buddypress' ) ;
function aioic_register_widget()
{
    register_widget('Aioic_Invites_Widget');
}

/**
		 * Load the textdomain for the plugin
		 *
		 * @package all_in_one_invite_codes
		 * @since  0.1
		 */
function load_plugin_textdomain_aioic_buddypress() {

	// phpcs:ignore PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound -- Kept on purpose: premium copies and builds not hosted on wordpress.org load their own translations from /languages.
	load_plugin_textdomain( 'all-in-one-invite-codes-buddypress', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

function aioic_buddypress_scripts()
{
    $version = get_file_data(__FILE__, array('Version' => 'Version'))['Version'];
    wp_enqueue_style('aioic_style', plugins_url('/', __FILE__) . 'assets/css/main.css', array(), $version, false);
    wp_enqueue_style('jquery-ui-styles', plugins_url('/', __FILE__) . 'assets/css/jquery-ui.css', array(), $version, false);
    // bp-api-request only exists while BuddyPress is active; WordPress would drop the script without it anyway.
    if (wp_script_is('bp-api-request', 'registered')) {
        wp_enqueue_script('aioic_buddypress',  plugins_url('/', __FILE__) . 'assets/js/aioic_buddypress.js', array('jquery', 'bp-api-request'), $version, true);
    }
    wp_enqueue_style('aioic_bb_icons', plugins_url('/', __FILE__) . 'assets/icons/aioic-bb-icons.css', array(), $version, false);
    wp_enqueue_script('buddyforms-loadingoverlay', plugins_url('/', __FILE__) . 'assets/loadingoverlay/loadingoverlay.min.js', array('jquery'), $version, false);
    wp_enqueue_script('jquery-validation', plugins_url('/', __FILE__) . 'assets/js/jquery.validate.js', array('jquery'), $version, false);
    wp_enqueue_script('jquery-ui', plugins_url('/', __FILE__) . 'assets/js/jquery-ui.js', array('jquery'), $version, false);

    $front_js_arguments = array(
        'admin_url'                => admin_url('admin-ajax.php'),

    );
    wp_localize_script("aioic_buddypress", "aioicBuddyformsGlobal", $front_js_arguments);
}
function aioic_buddypress_load_plugin_textdomain()
{
    
    require_once('form-ajax.php');
}
add_action('init', 'aioic_buddypress_load_plugin_textdomain');

function all_in_one_invite_codes_profile_tab()
{
    global $bp;


    bp_core_new_nav_item(array(
        'name'                => __('Invite Friends', 'all-in-one-invite-codes-buddypress'),
        'slug'                => apply_filters('all_in_one_invite_codes_profile_tab_slug', 'all-in-one-invite-codes'),
        'screen_function'     => 'all_in_one_invite_codes_profile_tab_screen',
        'position'            => 40,
        'parent_url'          => bp_loggedin_user_domain() . '/' . apply_filters('all_in_one_invite_codes_profile_tab_slug', 'all-in-one-invite-codes') . '/',
        'parent_slug'         => $bp->profile->slug,
        'default_subnav_slug' => apply_filters('all_in_one_invite_codes_profile_tab_slug1', 'all-in-one-invite-codes'),
    ));
    $access       = bp_core_can_edit_settings();


    bp_core_new_subnav_item(array(
        'name'            => __('Single Invites ', 'all-in-one-invite-codes-buddypress'),
        'slug'            => 'single-invites',
        'parent_url'      => bp_loggedin_user_domain() . '/' . apply_filters('all_in_one_invite_codes_profile_tab_slug', 'all-in-one-invite-codes') . '/',
        'parent_slug'     => apply_filters('all_in_one_invite_codes_profile_tab_slug', 'all-in-one-invite-codes'),
        'screen_function' => 'all_in_one_invite_codes_profile_tab_screen',
        'user_has_access' => $access,
        'position'        => 15,
    ));
    bp_core_new_subnav_item(array(
        'name'            => __('Multiple Invites ', 'all-in-one-invite-codes-buddypress'),
        'slug'            => 'multiple-invites',
        'parent_url'      => bp_loggedin_user_domain() . '/' . apply_filters('all_in_one_invite_codes_profile_tab_slug', 'all-in-one-invite-codes') . '/',
        'parent_slug'     => apply_filters('all_in_one_invite_codes_profile_tab_slug', 'all-in-one-invite-codes'),
        'screen_function' => 'all_in_one_invite_codes_profile_tab_multiple_invite_screen',
        'user_has_access' => $access,
        'position'        => 21,
    ));
    bp_core_new_subnav_item(array(
        'name'            => __('Sent Invites ', 'all-in-one-invite-codes-buddypress'),
        'slug'            => 'aioic_sent-invites',
        'parent_url'      => bp_loggedin_user_domain() . '/' . apply_filters('all_in_one_invite_codes_profile_tab_slug', 'all-in-one-invite-codes') . '/',
        'parent_slug'     => apply_filters('all_in_one_invite_codes_profile_tab_slug', 'all-in-one-invite-codes'),
        'screen_function' => 'all_in_one_invite_codes_profile_tab_aioic_sent_invites_screen',
        'user_has_access' => $access,
        'position'        => 25,
    ));
}

$all_in_one_invite_codes_buddypress_options = get_option('all_in_one_invite_codes_buddypress');
$invite_friends_restricted =  isset($all_in_one_invite_codes_buddypress_options['restrict_invite_friends']) ? $all_in_one_invite_codes_buddypress_options['restrict_invite_friends'] === 'enabled' ? true : false : false;
// "Profile Integration" turns the Invite Friends profile tab off.
$profile_tab_disabled = isset($all_in_one_invite_codes_buddypress_options['profile_tab']) && 'disable' === $all_in_one_invite_codes_buddypress_options['profile_tab'];
if($invite_friends_restricted ==false && !$profile_tab_disabled){
    add_action('bp_setup_nav', 'all_in_one_invite_codes_profile_tab');
}



function all_in_one_invite_codes_profile_tab_aioic_sent_invites_screen()
{
    add_action('bp_template_title', 'all_in_one_invite_codes_profile_tab_tab_title');
    add_action('bp_template_content', 'all_in_one_invite_codes_profile_tab_aioic_sent_invite_content');
    bp_core_load_template('buddypress/members/single/plugins');
}
function all_in_one_invite_codes_profile_tab_screen()
{
    add_action('bp_template_title', 'all_in_one_invite_codes_profile_tab_tab_title');
    add_action('bp_template_content', 'all_in_one_invite_codes_profile_tab_tab_content');
    bp_core_load_template('buddypress/members/single/plugins');
}
function all_in_one_invite_codes_profile_tab_multiple_invite_screen()
{
    add_action('bp_template_title', 'all_in_one_invite_codes_profile_tab_tab_title');
    add_action('bp_template_content', 'all_in_one_invite_codes_profile_tab_multiple_invite_content');
    bp_core_load_template('buddypress/members/single/plugins');
}

function all_in_one_invite_codes_profile_tab_tab_title()
{
    echo ''; //__( 'Invite Friends', 'all-in-one-invite-codes' );
}
function all_in_one_invite_codes_profile_tab_aioic_sent_invite_content()
{


    require 'aioic-sent-invites.php';
}

function all_in_one_invite_codes_profile_tab_multiple_invite_content()
{


    require 'multiple-invites.php';
}
function all_in_one_invite_codes_profile_tab_tab_content()
{
    global $post;
    $post_id = '';
    $args = array(
        'author'         => get_current_user_id(),
        'posts_per_page' => -1,
        'post_type'      => 'tk_invite_codes', //you can use also 'any'
    );
    AllinOneInviteCodes::setNeedAssets(true, 'buddypress');
    $all_in_one_invite_codes_buddypress = get_option('all_in_one_invite_codes_buddypress');
    $create_invites_restricted =  isset($all_in_one_invite_codes_buddypress['restrict_create_invites']) ? $all_in_one_invite_codes_buddypress['restrict_create_invites'] === 'enabled' ? true : false : false;
    ?>

    <?php if ($create_invites_restricted == false) : ?>

        <p>
            <a id="all_in_one_invite_codes_profile" href="#TB_inline?width=500&height=auto&inlineId=all_in_one_invite_codes_profile_modal" title="" class="thickbox button"><?php esc_html_e('Create Invite', 'all-in-one-invite-codes-buddypress') ?></a>
        </p>

        <div id="all_in_one_invite_codes_profile_modal" style="display:none;">
            <div id="buddyforms_invite_wrap">
                <?php do_shortcode('[all_in_one_invite_codes_create]'); ?>
                <button id="tk_all_in_one_invite_code_buddypress_create" data-post_id="<?php echo esc_attr($post_id) ?>" href="#" class="button"><?php esc_html_e('Create Invite', 'all-in-one-invite-codes-buddypress') ?></button>
                <img width="30" height="30" src="<?php echo esc_url(plugin_dir_url(__FILE__) . '/spinner/spinner.gif') ?>" id="loader" style="display: none">
            </div>
        </div>

    <?php endif ?>


    <?php
    echo '<div id="all_in_one_invite_codes_list_codes">';
    echo all_in_one_invite_codes_list_codes($args); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup built and escaped by All in One Invite Codes.
    echo '</div>';
}


/**
 * Register Settings Options
 *
 */
function all_in_one_invite_codes_buddypress_register_option()
{

    // General Settings
    register_setting('all_in_one_invite_codes_buddypress', 'all_in_one_invite_codes_buddypress', 'all_in_one_invite_codes_default_sanitize');
}

add_action('admin_init', 'all_in_one_invite_codes_buddypress_register_option');


add_filter('all_in_one_invite_codes_admin_tabs', 'all_in_one_invite_codes_buddypress_admin_tabs', 2, 10);

function all_in_one_invite_codes_buddypress_admin_tabs($tabs)
{

    $tabs['buddypress'] = 'BuddyPress';

    return $tabs;
}

add_action('all_in_one_invite_codes_settings_page_tab', 'all_in_one_invite_codes_buddypress_settings_page_tab');
function all_in_one_invite_codes_buddypress_settings_page_tab($tab)
{

    if ($tab != 'buddypress') {
        return;
    }

    $all_in_one_invite_codes_buddypress = get_option('all_in_one_invite_codes_buddypress'); ?>
    <div class="metabox-holder">
        <div class="postbox all_in_one_invite_codes-metabox">

            <div class="inside">

                <form method="post" action="options.php">

                    <?php settings_fields('all_in_one_invite_codes_buddypress'); ?>


                    <table class="form-table">
                        <tbody>
                            <tr valign="top">
                                <th scope="row" valign="top">
                                    <?php esc_html_e('Profile Integration', 'all-in-one-invite-codes-buddypress'); ?>
                                </th>
                                <td>
                                    <?php
                                    $pages['enabled'] = __('Enable', 'all-in-one-invite-codes-buddypress');
                                    $pages['disable'] = __('Disable', 'all-in-one-invite-codes-buddypress');

                                    if (isset($pages) && is_array($pages)) {
                                        echo '<select name="all_in_one_invite_codes_buddypress[profile_tab]" id="all_in_one_invite_codes_buddypress_profile_tab">';

                                        foreach ($pages as $page_id => $page_name) {
                                            echo '<option ' . selected(isset($all_in_one_invite_codes_buddypress['profile_tab']) ? $all_in_one_invite_codes_buddypress['profile_tab'] : '', $page_id) . 'value="' . esc_attr($page_id) . '">' . esc_html($page_name) . '</option>';
                                        }
                                        echo '</select>';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" valign="top">
                                    <?php esc_html_e('BuddyPress Registration', 'all-in-one-invite-codes-buddypress'); ?>
                                </th>
                                <td>
                                    <?php
                                    $pages['enabled'] = __('Enable', 'all-in-one-invite-codes-buddypress');
                                    $pages['disable'] = __('Disable', 'all-in-one-invite-codes-buddypress');

                                    if (isset($pages) && is_array($pages)) {
                                        echo '<select name="all_in_one_invite_codes_buddypress[registration]" id="all_in_one_invite_codes_buddypress_registration">';

                                        foreach ($pages as $page_id => $page_name) {
                                            echo '<option ' . selected(isset($all_in_one_invite_codes_buddypress['registration']) ? $all_in_one_invite_codes_buddypress['registration'] : '', $page_id) . 'value="' . esc_attr($page_id) . '">' . esc_html($page_name) . '</option>';
                                        }
                                        echo '</select>';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" valign="top">
                                    <?php esc_html_e('Auto Follow', 'all-in-one-invite-codes-buddypress'); ?>
                                </th>
                                <td>
                                    <?php
                                    $pages['enabled'] = __('Enable', 'all-in-one-invite-codes-buddypress');
                                    $pages['disable'] = __('Disable', 'all-in-one-invite-codes-buddypress');

                                    if (isset($pages) && is_array($pages)) {
                                        echo '<select name="all_in_one_invite_codes_buddypress[autofollow]" id="all_in_one_invite_codes_buddypress_autofollow">';

                                        foreach ($pages as $page_id => $page_name) {
                                            echo '<option ' . selected(isset($all_in_one_invite_codes_buddypress['autofollow']) ? $all_in_one_invite_codes_buddypress['autofollow'] : '', $page_id) . 'value="' . esc_attr($page_id) . '">' . esc_html($page_name) . '</option>';
                                        }
                                        echo '</select>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php esc_html_e('Automatically follow the activity of the inviter profile', 'all-in-one-invite-codes-buddypress'); ?>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" valign="top">
                                    <?php esc_html_e('Auto Connect', 'all-in-one-invite-codes-buddypress'); ?>
                                </th>
                                <td>
                                    <?php
                                    $pages['enabled'] = __('Enable', 'all-in-one-invite-codes-buddypress');
                                    $pages['disable'] = __('Disable', 'all-in-one-invite-codes-buddypress');

                                    if (isset($pages) && is_array($pages)) {
                                        echo '<select name="all_in_one_invite_codes_buddypress[autoconnect]" id="all_in_one_invite_codes_buddypress_autoconnect">';

                                        foreach ($pages as $page_id => $page_name) {
                                            echo '<option ' . selected(isset($all_in_one_invite_codes_buddypress['autoconnect']) ? $all_in_one_invite_codes_buddypress['autoconnect'] : '', $page_id) . 'value="' . esc_attr($page_id) . '">' . esc_html($page_name) . '</option>';
                                        }
                                        echo '</select>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php esc_html_e('Automatically send a friend request to the inviter profile', 'all-in-one-invite-codes-buddypress'); ?>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row" valign="top">
                                    <?php esc_html_e('Restrict create invite codes', 'all-in-one-invite-codes-buddypress'); ?>
                                </th>
                                <td>
                                    <?php
                                    $pages['enabled'] = __('Enable', 'all-in-one-invite-codes-buddypress');
                                    $pages['disable'] = __('Disable', 'all-in-one-invite-codes-buddypress');



                                    if (isset($pages) && is_array($pages)) {
                                        echo '<select  name="all_in_one_invite_codes_buddypress[restrict_create_invites]" id="all_in_one_invite_codes_buddypress_resctric_create_invites">';

                                        foreach ($pages as $page_id => $page_name) {
                                            echo '<option ' . selected(isset($all_in_one_invite_codes_buddypress['restrict_create_invites']) ? $all_in_one_invite_codes_buddypress['restrict_create_invites'] : '', $page_id) . 'value="' . esc_attr($page_id) . '">' . esc_html($page_name) . '</option>';
                                        }
                                        echo '</select>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php esc_html_e('Restrict the invite codes creation to the backend area, users can\'t create invites codes on the frontend', 'all-in-one-invite-codes-buddypress'); ?>

                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row" valign="top">
                                    <?php esc_html_e('Restrict invite friends', 'all-in-one-invite-codes-buddypress'); ?>
                                </th>
                                <td>
                                    <?php
                                    $pages['enabled'] = __('Enable', 'all-in-one-invite-codes-buddypress');
                                    $pages['disable'] = __('Disable', 'all-in-one-invite-codes-buddypress');



                                    if (isset($pages) && is_array($pages)) {
                                        echo '<select  name="all_in_one_invite_codes_buddypress[restrict_invite_friends]" id="all_in_one_invite_codes_buddypress_resctric_invite_friends">';

                                        foreach ($pages as $page_id => $page_name) {
                                            echo '<option ' . selected(isset($all_in_one_invite_codes_buddypress['restrict_invite_friends']) ? $all_in_one_invite_codes_buddypress['restrict_invite_friends'] : '', $page_id) . 'value="' . esc_attr($page_id) . '">' . esc_html($page_name) . '</option>';
                                        }
                                        echo '</select>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php esc_html_e('Restrict the invite friends option to the backend area, users can\'t send invites to friends on the frontend', 'all-in-one-invite-codes-buddypress'); ?>

                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <?php submit_button(); ?>

                </form>
            </div><!-- .inside -->
        </div><!-- .postbox -->
    </div><!-- .metabox-holder -->
<?php


}

/**
 * Whether a setting of the BuddyPress tab is on. Settings that were never saved
 * keep the behaviour the add-on always had.
 *
 * @param string $key     Setting key.
 * @param bool   $default Value when the setting was never saved.
 *
 * @return bool
 */
function aioic_buddypress_setting_enabled($key, $default)
{
    $options = get_option('all_in_one_invite_codes_buddypress');
    if (!is_array($options) || !isset($options[$key]) || '' === $options[$key]) {
        return $default;
    }

    return 'enabled' === $options[$key];
}

/**
 * Whether the BuddyPress registration asks for an invite code.
 *
 * The "BuddyPress Registration" setting was never applied before, so some sites
 * have it saved as "Disable" without knowing it. Applying it now would open their
 * registration on update, so the code stays required until the setting gets a
 * confirmation step in wp-admin. Sites that want open registration can use the
 * filter.
 *
 * @return bool
 */
function aioic_buddypress_registration_enabled()
{
    return (bool) apply_filters('aioic_buddypress_registration_requires_code', true);
}

/**
 * Apply "Restrict create invite codes" and "Restrict invite friends" to the
 * front-end requests as well, not only to the forms: members who are not
 * administrators cannot create codes or send invites while they are on.
 */
function aioic_buddypress_enforce_restrictions()
{
    if (current_user_can('manage_options')) {
        return;
    }

    $action = current_action();
    if (in_array($action, array('wp_ajax_all_in_one_invite_codes_create_code', 'wp_ajax_aioic_send_multiple_invites'), true)
        && aioic_buddypress_setting_enabled('restrict_create_invites', false)) {
        wp_send_json(array('error' => esc_html__('Creating invite codes has been disabled. Contact the Administrator.', 'all-in-one-invite-codes-buddypress')));
    }

    if ('wp_ajax_all_in_one_invite_codes_send_invite' === $action && aioic_buddypress_setting_enabled('restrict_invite_friends', false)) {
        wp_send_json(array('error' => esc_html__('Sending invites has been disabled. Contact the Administrator.', 'all-in-one-invite-codes-buddypress')));
    }
}
add_action('wp_ajax_all_in_one_invite_codes_create_code', 'aioic_buddypress_enforce_restrictions', 1);
add_action('wp_ajax_aioic_send_multiple_invites', 'aioic_buddypress_enforce_restrictions', 1);
add_action('wp_ajax_all_in_one_invite_codes_send_invite', 'aioic_buddypress_enforce_restrictions', 1);

add_action('bp_signup_profile_fields', 'all_in_one_invite_codes_bp_after_profile_field_content');
// Without Extended Profiles BuddyPress has no profile fields section: show the field with the account details.
add_action('bp_account_details_fields', 'aioic_buddypress_account_details_invite_field');
function aioic_buddypress_account_details_invite_field()
{
    if (!function_exists('bp_is_active') || !bp_is_active('xprofile')) {
        all_in_one_invite_codes_bp_after_profile_field_content();
    }
}

function all_in_one_invite_codes_bp_after_profile_field_content()
{
    if (!aioic_buddypress_registration_enabled()) {
        return;
    }

    // Check if the invite code is coming from a link
    $tk_invite_code = (!empty($_GET['invite_code'])) ? sanitize_key(wp_unslash($_GET['invite_code'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only invite code from the invite link, only prefills the field.

?>
    <div class="editfield required-field">
        <label for="tk_invite_code"><?php esc_html_e('Invitation Code', 'all-in-one-invite-codes-buddypress') ?></label>
        <?php do_action('bp_tk_invite_code_errors') ?>
        <input type="text" name="tk_invite_code" id="tk_invite_code" class="input" required="required" value="<?php echo esc_attr($tk_invite_code); ?>" size="25" />
    </div>
<?php

}

add_action('bp_signup_validate', 'test_bp_signup_validate');
function test_bp_signup_validate()
{
    global $bp;

    if (!aioic_buddypress_registration_enabled()) {
        return;
    }

    // phpcs:disable WordPress.Security.NonceVerification.Missing -- BuddyPress verifies the bp_new_signup nonce before bp_signup_validate.
    // Check if the field has a code
    if (empty($_POST['tk_invite_code']) || !empty($_POST['tk_invite_code']) && trim(sanitize_text_field(wp_unslash($_POST['tk_invite_code']))) == '') {
        $bp->signup->errors['tk_invite_code'] = __('Please enter a Invite Code.', 'all-in-one-invite-codes-buddypress');
    } else {

        $tk_invite_code = sanitize_key(wp_unslash($_POST['tk_invite_code']));

        // Validate teh code
        $result = all_in_one_invite_codes_validate_code($tk_invite_code, isset($_POST['signup_email']) ? sanitize_email(wp_unslash($_POST['signup_email'])) : '');
        if (isset($result['error'])) {
            $bp->signup->errors['tk_invite_code'] = sprintf('<strong>%s</strong>: %s', __('ERROR', 'all-in-one-invite-codes-buddypress'), $result['error']);
        }
    }
    // phpcs:enable WordPress.Security.NonceVerification.Missing
}

/**
 * Keep the invite code with the pending signup: BuddyPress creates the account
 * only when it is activated, when the registration form data is gone.
 *
 * @param array $usermeta Signup meta.
 *
 * @return array
 */
function aioic_buddypress_signup_usermeta($usermeta)
{
    // BuddyPress verified the registration nonce and bp_signup_validate ran before this filter.
    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $code = !empty($_POST['tk_invite_code']) ? sanitize_key(wp_unslash($_POST['tk_invite_code'])) : '';
    if (aioic_buddypress_registration_enabled() && '' !== $code) {
        $usermeta['tk_invite_code'] = $code;
    }

    return $usermeta;
}
add_filter('bp_signup_usermeta', 'aioic_buddypress_signup_usermeta');

/**
 * Look up an invite code post id.
 *
 * @param string $code Invite code.
 *
 * @return int
 */
function aioic_buddypress_get_code_id($code)
{
    $code = sanitize_key($code);
    if ('' === $code) {
        return 0;
    }

    $ids = get_posts(array(
        'post_type'      => 'tk_invite_codes',
        'post_status'    => 'any',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- codes are looked up by their meta value.
        'meta_query'     => array(
            array(
                'key'   => 'tk_all_in_one_invite_code',
                'value' => $code,
            ),
        ),
    ));

    return $ids ? (int) $ids[0] : 0;
}

/**
 * Use up the invite code when the signup is saved, so a pending signup cannot
 * share a single use code: single use codes become Used and keep the email of
 * the signup, multiple use codes lose one use.
 *
 * @param int|false $user_id    User id when BuddyPress created the account already.
 * @param string    $user_login Login.
 * @param string    $password   Password.
 * @param string    $user_email Email.
 * @param array     $usermeta   Signup meta.
 */
function aioic_buddypress_reserve_signup_code($user_id, $user_login, $password, $user_email, $usermeta)
{
    if (empty($usermeta['tk_invite_code'])) {
        return;
    }

    // All in One Invite Codes processed the code already when the account was created right away.
    if ($user_id && get_user_meta($user_id, 'tk_all_in_one_invite_code', true)) {
        return;
    }

    $code_id = aioic_buddypress_get_code_id($usermeta['tk_invite_code']);
    if (!$code_id) {
        return;
    }

    $options = get_post_meta($code_id, 'all_in_one_invite_codes_options', true);
    $options = is_array($options) ? $options : array();
    if (isset($options['multiple_use'])) {
        $left                      = max(0, (isset($options['generate_codes']) ? (int) $options['generate_codes'] : 0) - 1);
        $options['generate_codes'] = $left;
        update_post_meta($code_id, 'all_in_one_invite_codes_options', $options);
        if (0 === $left) {
            update_post_meta($code_id, 'tk_all_in_one_invite_code_status', 'Used');
        }
    } else {
        $options['email'] = sanitize_email($user_email);
        update_post_meta($code_id, 'all_in_one_invite_codes_options', $options);
        update_post_meta($code_id, 'tk_all_in_one_invite_code_status', 'Used');
    }
}
add_action('bp_core_signup_user', 'aioic_buddypress_reserve_signup_code', 10, 5);

/**
 * Give the activated member what All in One Invite Codes gives on registration:
 * the code is recorded on the member (invite tree, invited by) and a single use
 * code hands out its new codes.
 *
 * @param int    $user_id User id.
 * @param string $key     Activation key.
 * @param array  $user    Activation data, with the signup meta.
 */
function aioic_buddypress_process_activated_code($user_id, $key, $user)
{
    $code = is_array($user) && isset($user['meta']['tk_invite_code']) ? sanitize_key($user['meta']['tk_invite_code']) : '';
    if (!$user_id || '' === $code || get_user_meta($user_id, 'tk_all_in_one_invite_code', true)) {
        return;
    }

    update_user_meta($user_id, 'tk_all_in_one_invite_code', $code);

    $code_id = aioic_buddypress_get_code_id($code);
    if (!$code_id) {
        return;
    }

    $options = get_post_meta($code_id, 'all_in_one_invite_codes_options', true);
    if (!is_array($options) || isset($options['multiple_use'])) {
        return;
    }

    $amount = isset($options['generate_codes']) ? (int) $options['generate_codes'] : 0;
    for ($i = 1; $i <= $amount; $i++) {
        $new_code_id = wp_insert_post(array(
            'post_type'   => 'tk_invite_codes',
            'post_author' => $user_id,
            'post_parent' => $code_id,
            'post_status' => 'publish',
            'post_title'  => '',
        ));
        if (!$new_code_id || is_wp_error($new_code_id)) {
            continue;
        }

        $new_code = sanitize_key(all_in_one_invite_codes_md5($new_code_id));
        wp_update_post(array(
            'ID'         => $new_code_id,
            'post_title' => $new_code,
        ));
        update_post_meta($new_code_id, 'tk_all_in_one_invite_code', $new_code);
        update_post_meta($new_code_id, 'all_in_one_invite_codes_options', array('generate_codes' => $amount));
        update_post_meta($new_code_id, 'tk_all_in_one_invite_code_status', 'Active');
    }
}
add_action('bp_core_activated_user', 'aioic_buddypress_process_activated_code', 5, 3);

add_action('bp_core_activated_user', 'aioic_buddypress_after_activation_process', 10, 3);
function aioic_buddypress_after_activation_process($user_id, $key, $user)
{
    $user = get_user_by('ID', $user_id);
    if (!$user) {
        return;
    }

    $autofollow_enabled  = aioic_buddypress_setting_enabled('autofollow', false) && function_exists('bp_start_following');
    $autoconnect_enabled = aioic_buddypress_setting_enabled('autoconnect', false) && function_exists('friends_add_friend') && bp_is_active('friends');
    if (!$autoconnect_enabled && !$autofollow_enabled) {
        return;
    }

    // The inviter is the owner of the code used to sign up, or else of a code sent to this email.
    $invited_by = 0;
    $code_id    = aioic_buddypress_get_code_id((string) get_user_meta($user->ID, 'tk_all_in_one_invite_code', true));
    if ($code_id) {
        $invited_by = (int) get_post_field('post_author', $code_id);
    }

    if (!$invited_by) {
        $the_query = new WP_Query(array(
            'posts_per_page' => -1,
            'post_type'      => 'tk_invite_codes',
            'orderby'        => 'post_author',
            'order'          => 'ASC',
        ));
        foreach ($the_query->posts as $code_post) {
            $all_in_one_invite_codes_options = get_post_meta($code_post->ID, 'all_in_one_invite_codes_options', true);
            $email_needle                    = empty($all_in_one_invite_codes_options['email']) ? '' : $all_in_one_invite_codes_options['email'];
            if ($user->user_email == $email_needle) {
                $invited_by = (int) $code_post->post_author;
                break;
            }
        }
    }

    if (!$invited_by || $invited_by === (int) $user->ID || !get_user_by('ID', $invited_by)) {
        return;
    }

    if ($autoconnect_enabled) {
        if (!friends_add_friend($user->ID, $invited_by)) {
            bp_core_add_message(__('Friendship could not be requested.', 'buddypress'), 'error');
        } else {
            bp_core_add_message(__('Friendship requested', 'buddypress'));
        }
    }
    if ($autofollow_enabled) {
        $params = array(
            'leader_id'   => $invited_by,
            'follower_id' => $user->ID
        );
        if (!bp_start_following($params)) {
            bp_core_add_message(__('Error when start following user.', 'buddypress'), 'error');
        } else {
            bp_core_add_message(__('Started following user with success', 'buddypress'));
        }
    }
}

if (!function_exists('br_fs')) {
    // Create a helper function for easy SDK access.
    function br_fs()
    {
        global $br_fs;

        if (!isset($br_fs)) {
            // The Freemius SDK comes with All in One Invite Codes, which loads it before this add-on initializes.
            if (!function_exists('fs_dynamic_init')) {
                return false;
            }

            $br_fs = fs_dynamic_init(array(
                'id'               => '3323',
                'slug'             => 'buddypress',
                'premium_slug'     => 'buddypress-registration-premium',
                'type'             => 'plugin',
                'public_key'       => 'pk_25bbf96c6d7f5376ee564ad54df6d',
                'is_premium'       => true,
                'is_premium_only'  => true,
                'has_paid_plans'   => true,
                'is_org_compliant' => false,
                'trial'            => array(
                    'days'               => 7,
                    'is_require_payment' => false,
                ),
                'parent'           => array(
                    'id'         => '3322',
                    'slug'       => 'all-in-one-invite-codes',
                    'public_key' => 'pk_955be38b0c4d2a2914a9f4bc98355',
                    'name'       => 'All in One Invite Codes',
                ),
                'bundle_license_auto_activation' => true,
                'menu'             => array(
                    'support' => false,
                )
            ));
        }

        return $br_fs;
    }
}
function br_fs_is_parent_active_and_loaded()
{
    // Check if the parent's init SDK method exists.
    return function_exists('all_in_one_invite_codes_core_fs');
}

function br_fs_is_parent_active()
{
    $active_plugins = get_option('active_plugins', array());

    if (is_multisite()) {
        $network_active_plugins = get_site_option('active_sitewide_plugins', array());
        $active_plugins         = array_merge($active_plugins, array_keys($network_active_plugins));
    }

    foreach ($active_plugins as $basename) {
        if (
            0 === strpos($basename, 'all-in-one-invite-codes/') ||
            0 === strpos($basename, 'all-in-one-invite-codes-premium/')
        ) {
            return true;
        }
    }

    return false;
}

function br_fs_init()
{
    if (br_fs_is_parent_active_and_loaded()) {
        // Init Freemius.
        br_fs();


        // Signal that the add-on's SDK was initiated.
        do_action('br_fs_loaded');

        // Parent is active, add your init code here.

    } else {
        // Parent is inactive, add your error handling here.
    }
}

if (br_fs_is_parent_active_and_loaded()) {
    // If parent already included, init add-on.
    br_fs_init();
} else if (br_fs_is_parent_active()) {
    // Init add-on only after the parent is loaded.
    add_action('all_in_one_invite_codes_core_fs_loaded', 'br_fs_init');
} else {
    // Even though the parent is not activated, execute add-on for activation / uninstall hooks.
    br_fs_init();
}
