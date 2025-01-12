<?php
/**
 * Plugin Name: Change Password API
 * Description: Provides secure REST API endpoints for generating tokens, changing passwords, retrieving user data, and verifying tokens using JWT.
 * Version: 1.1
 * Author: Chimpvine
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Include Firebase JWT library
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    wp_die('Please run `composer install` to install required dependencies.');
}
require_once __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWT_Secure_API {
    private $secret_key;
    private $algorithm = 'HS256';

    public function __construct() {
        $this->secret_key = $this->generate_secret_key();

        // Register REST API endpoints
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register API Routes
     */
    public function register_routes() {
        register_rest_route('jwt/v1', '/generate-token', [
            'methods' => 'POST',
            'callback' => [$this, 'generate_token_endpoint'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('jwt/v1', '/get-user-data', [
            'methods' => 'GET',
            'callback' => [$this, 'get_user_data'],
            'permission_callback' => [$this, 'authenticate_user'],
        ]);

        register_rest_route('jwt/v1', '/change-password', [
            'methods' => 'POST',
            'callback' => [$this, 'change_password'],
            'permission_callback' => [$this, 'authenticate_user'],
        ]);

        register_rest_route('jwt/v1', '/verify-token', [
            'methods' => 'POST',
            'callback' => [$this, 'verify_token_endpoint'],
            'permission_callback' => '__return_true',
        ]);
    }

    /**
     * Generate a secret key dynamically
     */
    private function generate_secret_key() {
        $key = get_option('jwt_secret_key');
        if (!$key) {
            $key = bin2hex(random_bytes(32)); // Generate a 256-bit key
            update_option('jwt_secret_key', $key);
        }
        return $key;
    }

    /**
     * Generate JWT token for a user
     */
    private function generate_jwt($user_id) {
        $user = get_userdata($user_id);
        $payload = [
            'user_id' => $user_id,
            'username' => $user->user_login,
            'email' => $user->user_email,
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24), // Token expires in 24 hours
        ];

        return JWT::encode($payload, $this->secret_key, $this->algorithm);
    }

    /**
     * Generate Token Endpoint
     */
    public function generate_token_endpoint($request) {
        $username = $request->get_param('username');
        $password = $request->get_param('password');

        $user = wp_authenticate($username, $password);

        if (is_wp_error($user)) {
            return new WP_Error('authentication_failed', 'Invalid username or password.', ['status' => 401]);
        }

        $token = $this->generate_jwt($user->ID);

        return [
            'token' => $token,
            'expires_in' => 86400, // 24 hours in seconds
        ];
    }

    /**
     * Authenticate user by JWT
     */
    public function authenticate_user($request) {
        $auth_header = $request->get_header('Authorization');
        if (!$auth_header) {
            return new WP_Error('missing_token', 'Authorization header is missing.', ['status' => 401]);
        }

        list($token) = sscanf($auth_header, 'Bearer %s');
        if (!$token) {
            return new WP_Error('invalid_token_format', 'Invalid token format.', ['status' => 401]);
        }

        try {
            $decoded = JWT::decode($token, new Key($this->secret_key, $this->algorithm));
            return $decoded;
        } catch (Exception $e) {
            return new WP_Error('invalid_token', $e->getMessage(), ['status' => 401]);
        }
    }

    /**
     * Get user data
     */
    public function get_user_data($request) {
        $auth = $this->authenticate_user($request);
        if (is_wp_error($auth)) {
            return $auth;
        }

        $user = get_userdata($auth->user_id);

        return [
            'user_id' => $user->ID,
            'username' => $user->user_login,
            'email' => $user->user_email,
            'user_first_name' => $user->first_name,
            'user_last_name' => $user->last_name,
            'user_profile_image' => get_avatar_url($user->ID),
        ];
    }

    /**
     * Change user password
     */
    public function change_password($request) {
        $auth = $this->authenticate_user($request);
        if (is_wp_error($auth)) {
            return $auth;
        }

        $params = $request->get_json_params();
        $current_password = $params['current_password'] ?? '';
        $new_password = $params['new_password'] ?? '';

        if (!$current_password || !$new_password) {
            return new WP_Error('missing_fields', 'Current and new passwords are required.', ['status' => 400]);
        }

        $user = get_userdata($auth->user_id);
        if (!wp_check_password($current_password, $user->user_pass)) {
            return new WP_Error('incorrect_password', 'Current password is incorrect.', ['status' => 403]);
        }

        wp_set_password($new_password, $user->ID);

        return ['success' => true, 'message' => 'Password updated successfully.'];
    }

    /**
     * Verify JWT Token Endpoint
     */
    public function verify_token_endpoint($request) {
        $token = $request->get_param('token');
        try {
            $decoded = JWT::decode($token, new Key($this->secret_key, $this->algorithm));
            return ['success' => true, 'data' => $decoded];
        } catch (Exception $e) {
            return new WP_Error('invalid_token', $e->getMessage(), ['status' => 401]);
        }
    }
}

// Initialize the plugin
new JWT_Secure_API();
