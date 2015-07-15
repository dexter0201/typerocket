<?php
if ( !function_exists( 'add_action' ) ) { exit; }

/*
|--------------------------------------------------------------------------
| Core TypeRocket
|--------------------------------------------------------------------------
|
| Enhance WordPress by starting TypeRocket.
|
*/
new \TypeRocket\Core(true);

/*
|--------------------------------------------------------------------------
| Run Registry
|--------------------------------------------------------------------------
|
| Runs after hooks muplugins_loaded, plugins_loaded and setup_theme
| This allows the registry to work outside of the themes folder. Use
| the typerocket_loaded hook to access TypeRocket from your WP plugins.
|
*/
do_action( 'typerocket_loaded' );

add_action( 'after_setup_theme', function () {
	\TypeRocket\Registry::run();
} );

/*
|--------------------------------------------------------------------------
| Add APIs
|--------------------------------------------------------------------------
|
| Add a url that will allow you to save to forms using an API. This is not
| REST but more like RPC. This API is designed to create, update and
| delete data in WordPress. Item ID's should be sent via $_POST.
|
*/
add_action('admin_init', function() {

    // Controller API
    $regex = 'typerocket_rest_api/v1/([^/]*)/?$';
    $location = 'index.php?typerocket_rest_controller=$matches[1]';
    add_rewrite_rule( $regex, $location, 'top' );

    $regex = 'typerocket_rest_api/v1/([^/]*)/([^/]*)/?$';
    $location = 'index.php?typerocket_rest_controller=$matches[1]&typerocket_rest_item=$matches[2]';
    add_rewrite_rule( $regex, $location, 'top' );

    // Matrix API
    $regex = 'typerocket_matrix_api/v1/([^/]*)/([^/]*)/?$';
    $location = 'index.php?typerocket_matrix_group=$matches[1]&typerocket_matrix_type=$matches[2]';
    add_rewrite_rule( $regex, $location, 'top' );

    $regex = 'typerocket_matrix_api/v1/([^/]*)/([^/]*)/([^/]*)/?$';
    $location = 'index.php?typerocket_matrix_group=$matches[1]&typerocket_matrix_type=$matches[2]&typerocket_matrix_form=$matches[3]';
    add_rewrite_rule( $regex, $location, 'top' );
});

add_filter( 'query_vars', function($vars) {
    $vars[] = 'typerocket_rest_controller';
    $vars[] = 'typerocket_rest_item';
    $vars[] = 'typerocket_matrix_group';
    $vars[] = 'typerocket_matrix_type';
    $vars[] = 'typerocket_matrix_form';
    return $vars;
} );

add_filter( 'template_include', function($template) {

    $resource = get_query_var('typerocket_rest_controller', null);

    $load_template = ($resource);
    $load_template = apply_filters('tr_rest_api_template', $load_template);

    if($load_template) {
        require __DIR__ . '/api/rest-v1.php';
        exit();
    }

    return $template;
}, 99 );

add_filter( 'template_include', function($template) {

    $matrix_group = get_query_var('typerocket_matrix_group', null);
    $matrix_type = get_query_var('typerocket_matrix_type', null);

    $load_template = ($matrix_group && $matrix_type);
    $load_template = apply_filters('tr_matrix_api_template', $load_template);

    if($load_template) {
        require __DIR__ . '/api/matrix-v1.php';
        exit();
    }

    return $template;
}, 99 );

define( 'TR_END', microtime( true ) );