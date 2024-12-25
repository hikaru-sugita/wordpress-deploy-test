<?php
/*
Plugin Name: Simple JWT
Description: Basic JWT token generation
Version: 1.0
*/
// Check if composer autoload exists
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    die('Composer autoload not found. Please run composer install.');
}
// Verify JWT class exists
if (!class_exists('Firebase\JWT\JWT')) {
    die('Firebase JWT library not found. Please run composer require firebase/php-jwt');
}
use Firebase\JWT\JWT;
add_action('rest_api_init', function() {
    register_rest_route('jwt/v1', '/token', array(
        'methods' => 'POST',
        'callback' => 'generate_jwt_token',
        'permission_callback' => '__return_true'
    ));
});
function generate_jwt_token($request) {
    // Get username and password
    $username = $request->get_param('username');
    $password = $request->get_param('password');
    // Authenticate user
    $user = wp_authenticate($username, $password);
    if (is_wp_error($user)) {
        return new WP_Error(
            'invalid_credentials',
            'Invalid credentials',
            array('status' => 401)
        );
    }
    // Create token
    $secret_key = 'your-secret-key-123';  // Change this in production
    $payload = array(
        'user_id' => $user->ID,
        'email' => $user->user_email,
        'exp' => time() + 3600 // 1 hour expiration
    );
    try {
        $token = JWT::encode($payload, $secret_key, 'HS256');
        return array(
            'token' => $token,
            'user_id' => $user->ID,
            'email' => $user->user_email
        );
    } catch (Exception $e) {
        return new WP_Error(
            'jwt_error',
            $e->getMessage(),
            array('status' => 500)
        );
    }
}