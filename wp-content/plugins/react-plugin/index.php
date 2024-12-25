<?php
/*
Plugin Name: My React Plugin
Description: A WordPress plugin that integrates a React app.
Version: 1.0
Author: Your Name
*/

// スクリプトとスタイルの読み込み
function enqueue_my_react_plugin_assets() {
    $plugin_url = plugin_dir_url(__FILE__);

    // ReactのJSを読み込み
    wp_enqueue_script(
        'my_react-plugin',
        $plugin_url . 'build/pages/project-post/assets/project-post.js',
        array('wp-element'), // WordPress内のReactライブラリを利用
        '1.0',
        true
    );

    // ReactのCSSを読み込み
    wp_enqueue_style(
        'my-react-plugin-styles',
        $plugin_url . 'build/pages/project-post/assets/project-post.css',
        array(),
        '1.0'
    );
}
add_action('wp_enqueue_scripts', 'enqueue_my_react_plugin_assets');

// ショートコードでReactアプリを表示
function my_react_plugin_shortcode() {
    return '<div id="root"></div>';
}
add_shortcode('my_react_app', 'my_react_plugin_shortcode');
