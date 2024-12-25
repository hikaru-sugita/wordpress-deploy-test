<?php
/*
Plugin Name: Custom File Upload API
Description: ファイルアップロード用のカスタムREST APIエンドポイントを提供します。
Version: 1.0
Author: Your Name
*/

add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/upload', array(
        'methods' => 'POST',
        'callback' => 'handle_file_upload',
        'permission_callback' => function () {
            return is_user_logged_in(); // ユーザーがログイン済みか確認
        },
    ));
});

// if (!is_user_logged_in()) {
//     return new WP_Error('rest_forbidden', 'この操作は許可されていません', array('status' => 403));
// }

function handle_file_upload(WP_REST_Request $request) {
    $current_user = wp_get_current_user();
    error_log($current_user->ID);
    error_log($current_user->user_email);
    if ($current_user->ID) {
        return new WP_Error('rest_forbidden', 'この操作は許可されていません', array('status' => 403));
    }

    if (empty($_FILES['file'])) {
        return new WP_REST_Response(['message' => 'ファイルがありません'], 400);
    }

    $file = $_FILES['file'];

    // 単一ファイルを取り出す（最初のファイル）
    $file_name = $file['name'][0];
    $tmp_name = $file['tmp_name'][0];
    $file_type = $file['type'][0];
    $file_error = $file['error'][0];
    $file_size = $file['size'][0];

    // デバッグ用ログ
    error_log("アップロードされたファイル名: " . $file_name);
    error_log("一時ファイルのパス: " . $tmp_name);

    // ファイルエラーがないか確認
    if ($file_error !== UPLOAD_ERR_OK) {
        return new WP_REST_Response(['message' => 'ファイルアップロードエラー: ' . $file_error], 400);
    }

    // ファイルアップロード処理
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    $uploaded_file = [
        'name' => $file_name,
        'type' => $file_type,
        'tmp_name' => $tmp_name,
        'error' => $file_error,
        'size' => $file_size,
    ];

    $upload = wp_handle_upload($uploaded_file, ['test_form' => false]);

    if (isset($upload['error'])) {
        return new WP_REST_Response(['message' => 'アップロード失敗: ' . $upload['error']], 500);
    }

    $file_url = $upload['url'];

    return new WP_REST_Response(['message' => 'アップロード成功', 'file_url' => $file_url], 200);
}


add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/project', array(
        'methods' => 'POST',
        'callback' => 'post_project',
        'permission_callback' => '__return_true', // 必要に応じて変更
    ));
});

function post_project(WP_REST_Request $request) {
    // if (empty($_FILES['file'])) {
    //     return new WP_REST_Response(['message' => 'ファイルがありません'], 400);
    // }

    // $file = $_FILES['file'];

    // // 単一ファイルを取り出す（最初のファイル）
    // $file_name = $file['name'][0];
    // $tmp_name = $file['tmp_name'][0];
    // $file_type = $file['type'][0];
    // $file_error = $file['error'][0];
    // $file_size = $file['size'][0];

    // // デバッグ用ログ
    // error_log("アップロードされたファイル名: " . $file_name);
    // error_log("一時ファイルのパス: " . $tmp_name);

    // // ファイルエラーがないか確認
    // if ($file_error !== UPLOAD_ERR_OK) {
    //     return new WP_REST_Response(['message' => 'ファイルアップロードエラー: ' . $file_error], 400);
    // }

    // // ファイルアップロード処理
    // require_once(ABSPATH . 'wp-admin/includes/file.php');
    // require_once(ABSPATH . 'wp-admin/includes/media.php');
    // require_once(ABSPATH . 'wp-admin/includes/image.php');

    // $uploaded_file = [
    //     'name' => $file_name,
    //     'type' => $file_type,
    //     'tmp_name' => $tmp_name,
    //     'error' => $file_error,
    //     'size' => $file_size,
    // ];

    // $upload = wp_handle_upload($uploaded_file, ['test_form' => false]);

    // if (isset($upload['error'])) {
    //     return new WP_REST_Response(['message' => 'アップロード失敗: ' . $upload['error']], 500);
    // }

    // $file_url = $upload['url'];

    return new WP_REST_Response(['message' => 'Hello World'], 200);
}