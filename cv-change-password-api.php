<?php
/**
 * Plugin Name: Change Password Plugin
 * Description: Allows users to change their password with JWT token verification and current password validation.
 * Version: 1.1
 * Author: Your Name
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Check for the presence of the required JWT plugin and function.
 */
add_action('plugins_loaded', function () {
    if (!function_exists('verify_jwt_token')) {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>The Change Password Plugin requires the JWT plugin to be active.</p></div>';
        });
        return;
    }
});

/**
 * Register the REST API endpoint for changing the password.
 */
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/change-password', array(
        'methods' => 'POST',
        'callback' => 'handle_change_password',
        'permission_callback' => '__return_true',
    ));
});

/**
 * Handle the password change request.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function handle_change_password(WP_REST_Request $request) {
    $auth_header = $request->get_header('Authorization');
    $token = null;

    // Extract token from the Authorization header
    if ($auth_header && strpos($auth_header, 'Bearer ') === 0) {
        $token = str_replace('Bearer ', '', $auth_header);
    } else {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => 'Authorization header is missing or invalid.',
        ), 400);
    }

    // Ensure a token is provided
    if (!$token) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => 'Token is required.',
        ), 400);
    }

    // Verify the token using the existing plugin's function
    $verification_result = apply_filters('plugin_a_verify_jwt_token', $token);

    if (!$verification_result['success']) {
        return new WP_REST_Response($verification_result, 401);
    }

    $user_id = $verification_result['user_id'];
    $current_password = $request->get_param('current_password');
    $new_password = $request->get_param('new_password');

    // Validate the current password
    $user = get_user_by('ID', $user_id);
    if (!$user || !wp_check_password($current_password, $user->user_pass, $user_id)) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => 'The current password is incorrect.',
        ), 401);
    }

    // Validate the new password
    if (empty($new_password)) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => 'New password is required.',
        ), 400);
    }

    if (strlen($new_password) < 8) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => 'New password must be at least 8 characters long.',
        ), 400);
    }

    // Change the user's password
    wp_set_password($new_password, $user_id);

    return new WP_REST_Response(array(
        'success' => true,
        'message' => 'Password changed successfully.',
    ), 200);
}
