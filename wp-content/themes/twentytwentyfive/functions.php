<?php
/**
 * Twenty Twenty-Five functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_Five
 * @since Twenty Twenty-Five 1.0
 */

// Adds theme support for post formats.
if ( ! function_exists( 'twentytwentyfive_post_format_setup' ) ) :
	/**
	 * Adds theme support for post formats.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_post_format_setup() {
		add_theme_support( 'post-formats', array( 'aside', 'audio', 'chat', 'gallery', 'image', 'link', 'quote', 'status', 'video' ) );
	}
endif;
add_action( 'after_setup_theme', 'twentytwentyfive_post_format_setup' );

// Enqueues editor-style.css in the editors.
if ( ! function_exists( 'twentytwentyfive_editor_style' ) ) :
	/**
	 * Enqueues editor-style.css in the editors.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_editor_style() {
		add_editor_style( get_parent_theme_file_uri( 'assets/css/editor-style.css' ) );
	}
endif;
add_action( 'after_setup_theme', 'twentytwentyfive_editor_style' );

// Enqueues style.css on the front.
if ( ! function_exists( 'twentytwentyfive_enqueue_styles' ) ) :
	/**
	 * Enqueues style.css on the front.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_enqueue_styles() {
		wp_enqueue_style(
			'twentytwentyfive-style',
			get_parent_theme_file_uri( 'style.css' ),
			array(),
			wp_get_theme()->get( 'Version' )
		);
	}
endif;
add_action( 'wp_enqueue_scripts', 'twentytwentyfive_enqueue_styles' );

// Registers custom block styles.
if ( ! function_exists( 'twentytwentyfive_block_styles' ) ) :
	/**
	 * Registers custom block styles.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_block_styles() {
		register_block_style(
			'core/list',
			array(
				'name'         => 'checkmark-list',
				'label'        => __( 'Checkmark', 'twentytwentyfive' ),
				'inline_style' => '
				ul.is-style-checkmark-list {
					list-style-type: "\2713";
				}

				ul.is-style-checkmark-list li {
					padding-inline-start: 1ch;
				}',
			)
		);
	}
endif;
add_action( 'init', 'twentytwentyfive_block_styles' );

// Registers pattern categories.
if ( ! function_exists( 'twentytwentyfive_pattern_categories' ) ) :
	/**
	 * Registers pattern categories.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_pattern_categories() {

		register_block_pattern_category(
			'twentytwentyfive_page',
			array(
				'label'       => __( 'Pages', 'twentytwentyfive' ),
				'description' => __( 'A collection of full page layouts.', 'twentytwentyfive' ),
			)
		);

		register_block_pattern_category(
			'twentytwentyfive_post-format',
			array(
				'label'       => __( 'Post formats', 'twentytwentyfive' ),
				'description' => __( 'A collection of post format patterns.', 'twentytwentyfive' ),
			)
		);
	}
endif;
add_action( 'init', 'twentytwentyfive_pattern_categories' );

// Registers block binding sources.
if ( ! function_exists( 'twentytwentyfive_register_block_bindings' ) ) :
	/**
	 * Registers the post format block binding source.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_register_block_bindings() {
		register_block_bindings_source(
			'twentytwentyfive/format',
			array(
				'label'              => _x( 'Post format name', 'Label for the block binding placeholder in the editor', 'twentytwentyfive' ),
				'get_value_callback' => 'twentytwentyfive_format_binding',
			)
		);
	}
endif;
add_action( 'init', 'twentytwentyfive_register_block_bindings' );

// Registers block binding callback function for the post format name.
if ( ! function_exists( 'twentytwentyfive_format_binding' ) ) :
	/**
	 * Callback function for the post format name block binding source.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return string|void Post format name, or nothing if the format is 'standard'.
	 */
	function twentytwentyfive_format_binding() {
		$post_format_slug = get_post_format();

		if ( $post_format_slug && 'standard' !== $post_format_slug ) {
			return get_post_format_string( $post_format_slug );
		}
	}
endif;

add_action('um_after_user_login', 'um_generate_jwt_token_on_login', 10, 2);

function um_generate_jwt_token_on_login($user_id, $args) {
    // ログイン時にログ出力
    error_log('User logged in: User ID ' . $user_id);
    
    // JWTトークンの生成に必要な情報を取得
    $user = get_user_by('id', $user_id);
    if (!$user) {
        error_log('User not found for ID: ' . $user_id);
        return;
    }

    // ユーザー名とパスワードを取得
    $username = $user->user_login;
    $password = isset($args['password']) ? $args['password'] : '';

    // ログイン情報を確認
    error_log('Attempting to generate JWT for user: ' . $username);

    // JWTトークンを生成
    $token = um_generate_jwt_token($username, $password);

    // JWTトークンをデータベースに保存
    if ($token) {
        error_log('JWT token generated successfully for user: ' . $username);

        // JWTトークンをデータベースに保存
        update_user_meta($user_id, 'jwt_token', $token);

        // JWTトークンをHTTPOnlyクッキーに保存（セキュリティのため）
        if (setcookie('jwt_token', $token, time() + 3600, '/', $_SERVER['HTTP_HOST'], true, true)) {
            error_log('JWT token saved to HTTPOnly cookie for user: ' . $username);
        } else {
            error_log('Failed to save JWT token to HTTPOnly cookie for user: ' . $username);
        }
    } else {
        error_log('Failed to generate JWT token for user: ' . $username);
    }
}

function um_generate_jwt_token($username, $password) {
    // JWTのエンドポイント（`JWT Authentication for WP REST API`のトークン生成エンドポイント）
    $url = site_url('/wp-json/jwt-auth/v1/token');

    // トークンを生成するためにPOSTリクエストを送信
    $response = wp_remote_post($url, [
        'body' => [
            'username' => $username,
            'password' => $password,
        ]
    ]);

    // レスポンスをチェック
    if (is_wp_error($response)) {
        error_log('Error in wp_remote_post: ' . $response->get_error_message());
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // トークンが正常に生成された場合、トークンを返す
    if (isset($data['token'])) {
        error_log('JWT token successfully retrieved for user: ' . $username);
        return $data['token'];
    } else {
        error_log('JWT token not found in response for user: ' . $username);
    }

    return false;
}

function display_cookie_and_get_jwt_shortcode() {
    $cookie_name = 'wordpress_logged_in_77f7fe59932af1302c2636baa0f7a505'; // 固定のCookie名

    // 特定のCookieを取得
    if (!isset($_COOKIE[$cookie_name])) {
        return "Cookie '" . esc_html($cookie_name) . "' は存在しません。";
    }

    $cookie_value = $_COOKIE[$cookie_name];

    // REST APIでJWTを取得するエンドポイント
    $api_endpoint = get_site_url() . '/wp-json/jwt-auth/v2/users/me';

    // REST APIへのリクエスト
    $response = wp_remote_post($api_endpoint, array(
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode(array(
            'cookie' => $cookie_value,
        )),
    ));

    // REST APIのレスポンスをチェック
    if (is_wp_error($response)) {
        return 'REST APIリクエストに失敗しました: ' . $response->get_error_message();
    }

    $response_body = wp_remote_retrieve_body($response);
    $response_data = json_decode($response_body, true);

    if (!isset($response_data['token'])) {
        return 'JWTトークンの取得に失敗しました。レスポンス: ' . esc_html($response_body);
    }

    // JWTを取得
    $jwt_token = $response_data['token'];

    return "<p>JWTトークン:</p><pre>" . esc_html($jwt_token) . "</pre>";
}

// ショートコードを追加
add_shortcode('get_jwt_from_cookie', 'display_cookie_and_get_jwt_shortcode');



// 現在ログイン中のユーザーを取得して表示するショートコード
function display_logged_in_user_shortcode() {
    // ユーザーがログインしているか確認
    if (is_user_logged_in()) {
        // 現在のログインユーザー情報を取得
        $current_user = wp_get_current_user();

        // ユーザー情報を表示
        $output = '<div class="logged-in-user-info">';
        $output .= '<p>ログイン中のユーザー:</p>';
        $output .= '<ul>';
        $output .= '<li>ユーザーid: ' . esc_html($current_user->id) . '</li>';
        $output .= '<li>ユーザー名: ' . esc_html($current_user->user_login) . '</li>';
        $output .= '<li>表示名: ' . esc_html($current_user->display_name) . '</li>';
        $output .= '<li>メールアドレス: ' . esc_html($current_user->user_email) . '</li>';
        $output .= '</ul>';
        $output .= '</div>';

        return $output;
    } else {
        // ユーザーがログインしていない場合のメッセージ
        return '<p>現在ログインしているユーザーはいません。</p>';
    }
}

// ショートコードを追加
add_shortcode('logged_in_user', 'display_logged_in_user_shortcode');



// ユーザーIDをCookieに登録するショートコード
function set_user_id_cookie_shortcode() {
    // ユーザーがログインしているか確認
    if (is_user_logged_in()) {
        // 現在のログインユーザー情報を取得
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;

        // CookieにユーザーIDを保存 (1時間有効)
        setcookie('user_id', $user_id, time() + 3600, '/');

        // フロントエンドに通知
        return "<p>ユーザーIDをCookieに登録しました。ユーザーID: " . esc_html($user_id) . "</p>";
    } else {
        // ユーザーがログインしていない場合のメッセージ
        return "<p>現在ログインしているユーザーがいません。</p>";
    }
}

// ショートコードを登録
add_shortcode('set_user_id_cookie', 'set_user_id_cookie_shortcode');
?>

<?php
// REST APIでv2/users/meにリクエストを送るショートコード
function get_current_user_via_rest_shortcode() {
    // 特定のCookieを取得
    $cookie_name = 'wordpress_logged_in_77f7fe59932af1302c2636baa0f7a505'; // 固定のCookie名
    if (!isset($_COOKIE[$cookie_name])) {
        return '<p>ログインCookieが見つかりません。</p>';
    }

    $cookie_value = $_COOKIE[$cookie_name];

    // REST APIエンドポイント
    $api_endpoint = get_site_url() . '/wp-json/wp/v2/users/me';

    // REST APIへのリクエスト
    $response = wp_remote_get($api_endpoint, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $cookie_value, // JWTやトークンを認証ヘッダーに追加
            'Content-Type' => 'application/json',
        ),
    ));

    // REST APIのレスポンスをチェック
    if (is_wp_error($response)) {
        return '<p>REST APIリクエストに失敗しました: ' . $response->get_error_message() . '</p>';
    }

    $response_body = wp_remote_retrieve_body($response);
    $user_data = json_decode($response_body, true);

    if (!$user_data || isset($user_data['code'])) {
        return '<p>ユーザー情報の取得に失敗しました。レスポンス: ' . esc_html($response_body) . '</p>';
    }

    // ユーザー情報を表示
    $output = "<p>ログイン中のユーザー情報:</p>";
    $output .= "<ul>";
    $output .= "<li><strong>ID:</strong> " . esc_html($user_data['id']) . "</li>";
    $output .= "<li><strong>名前:</strong> " . esc_html($user_data['name']) . "</li>";
    $output .= "<li><strong>メールアドレス:</strong> " . esc_html($user_data['email']) . "</li>";
    $output .= "</ul>";

    return $output;
}

// ショートコードを登録
add_shortcode('get_current_user_rest', 'get_current_user_via_rest_shortcode');
?>

<?php
/**
 * Shortcode to get JWT token via REST API
 * Usage: [get_jwt_token_fixed]
 */
// Shortcode function to send request to JWT REST API and display response
function get_jwt_token_fixed_shortcode() {
    // Fixed credentials
    $username = 'hikaru';
    $password = 'HiKaRu!4215';
    // Get REST API endpoint URL
    $api_endpoint = get_site_url() . '/wp-json/jwt/v1/token';
    // Send POST request to REST API
    $response = wp_remote_post($api_endpoint, array(
        'headers' => array(
            'Content-Type' => 'application/x-www-form-urlencoded'
        ),
        'body' => array(
            'username' => $username,
            'password' => $password
        )
    ));
    // Check for request errors
    if (is_wp_error($response)) {
        return '<p>REST APIリクエストに失敗しました: ' . esc_html($response->get_error_message()) . '</p>';
    }
    // Get and decode response body
    $response_body = wp_remote_retrieve_body($response);
    $response_data = json_decode($response_body, true);
    // Check for authentication errors
    if (isset($response_data['code']) || isset($response_data['error'])) {
        return '<p>認証エラー: ' . esc_html($response_data['message'] ?? '不明なエラー') . '</p>';
    }
    // Display token information
    $output = '<p>JWTトークンを取得しました:</p>';
    $output .= '<ul>';
    $output .= '<li><strong>トークン:</strong> ' . esc_html($response_data['token']) . '</li>';
    $output .= '<li><strong>ユーザーID:</strong> ' . esc_html($response_data['user_id']) . '</li>';
    $output .= '<li><strong>メールアドレス:</strong> ' . esc_html($response_data['email']) . '</li>';
    $output .= '</ul>';
    return $output;
}
// Register shortcode
add_shortcode('get_jwt_token_fixed', 'get_jwt_token_fixed_shortcode');

add_action('rest_api_init', function () {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-WP-Nonce');
});


add_action('wp_enqueue_scripts', function () {
    wp_localize_script('your-script-handle', 'wpApiSettings', [
        'root'  => esc_url_raw(rest_url()),
        'nonce' => wp_create_nonce('wp_rest'),
    ]);
});

add_filter('rest_authentication_errors', function ($result) {
    if (!is_wp_error($result)) {
        return $result;
    }

    error_log(print_r($result, true)); // 認証エラーの詳細をログに記録
    return $result;
});