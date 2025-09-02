<?php

/**
 *  Validates Request via HTTP Headers
 *    and return actual token as response
 */

function manawp_validated()
{

    $headers = getallheaders();

    return rest_ensure_response([
        'success' => true,
        'message' => 'You can now save your connection',
        'connection' => [
            'website' => get_site_url() . 'wp-json/manawp/v1',
            'token' => isset($headers['manatoken']) ? md5($headers['manatoken']) : null
        ]
    ]);
}


/**
 *   Return current WP version
 *     as Response
 */

function wordpress_details(): WP_HTTP_Response
{

    global $wp_version;
    global $wpdb;


    return rest_ensure_response([
        'host' => site_url(),
        'name' => get_bloginfo('name'),
        'tag' => get_bloginfo('description'),
        'wps' => $wp_version, // wordpress version
        'php' => phpversion(), // php version
    ]);
}


/**
 *  Return WordPress Posts
 * 
 */
function wordpress_posts(): WP_HTTP_Response
{
    $args = [
        'post_type'      => 'post',
        'posts_per_page' => -1,
        'post_status'    => 'publish'
    ];

    $postsList = get_posts($args);
    $postsData = [];

    foreach ($postsList as $post) {
        $postsData[] = [
            'id'      => $post->ID,
            'title'   => get_the_title($post),
            'date' => get_the_date('c', $post),
        ];
    }

    return rest_ensure_response([
        'found' => count($postsData),
        'posts' => $postsData
    ]);
}




/**
 *   Delete selected POST via ID
 *    passed as parameter
 */


function wordpress_delete_post(WP_REST_Request $request)
{
    $post_id = (int) $request['id'];

    $post = get_post($post_id);
    if (! $post) {
        return new WP_Error(
            'not_found',
            'Post not found',
            ['status' => 404]
        );
    }

    $deleted = wp_delete_post($post_id, true);

    if (! $deleted) {
        return new WP_Error(
            'delete_failed',
            'Could not delete post',
            ['status' => 500]
        );
    }

    return rest_ensure_response([
        'deleted' => true,
        'message' => "The post with {$post_id} was deleted",
        'post_id' => $post_id,
    ]);
}


/**
 *   Find single POST by ID
 */

function wordpress_single_post(WP_REST_Request $request)
{

    $post_id = (int) $request['id'];

    $post = get_post($post_id);

    if (! $post) {
        return new WP_Error(
            'not_found',
            'Post not found',
            ['status' => 404]
        );
    }

    // return single post object
    return rest_ensure_response([
        'found' => true,
        'status' => $post->post_status,
        'post' => [
            'id' => $post->ID,
            'title' => $post->post_title,
            'comments' => $post->comment_count,
            'dates' => [
                'published' => $post->post_date,
                'modified'  => $post->post_modified,
            ]
        ]

    ]);
}



/**
 *   Return List of WordPress Plugins
 *     Active and Inactive
 */
function wordpress_get_plugins(): WP_REST_Response
{
    if (! function_exists('get_plugins')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    $all = get_plugins();
    $act = get_option('active_plugins', []);


    $plugins = [];

    foreach ($all as $plugin_file => $plugin_data) {
        $is_active = in_array($plugin_file, $act, true);

        $plugins[] = [
            'author'  => $plugin_data['Author'],
            'name'    => $plugin_data['Name'],
            'version' => $plugin_data['Version'],
            'status'  => $is_active ? 'active' : 'inactive',
        ];
    }

    return rest_ensure_response([
        'found' => count($plugins),
        'plugins' => $plugins
    ]);
}


/**
 *   Return List of WordPress Themes
 *     Current and Inactive
 */

function wordpress_get_themes(): WP_REST_Response
{
    $themes = wp_get_themes();
    $data = [];

    foreach ($themes as $slug => $theme) {
        $data[] = [
            'slug'    => $slug,
            'name'    => $theme->get('Name'),
            'version' => $theme->get('Version'),
            'author'  => $theme->get('Author'),

        ];
    }

    return rest_ensure_response([
        'found' =>  count($data),
        'themes' => $data
    ]);
}



function wordpress_get_users($request)
{
    $users = get_users();
    $data = [];

    foreach ($users as $user) {
        $data[] = [
            'ID'       => $user->ID,
            'username' => $user->user_login,
            'email'    => $user->user_email,
            'name'     => $user->display_name,
            'role'     => $user->roles,
        ];
    }

    return rest_ensure_response($data);
}
