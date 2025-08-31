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

function wordpress_version(): WP_HTTP_Response
{

    global $wp_version;

    return rest_ensure_response([
        'version' => $wp_version
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

    return rest_ensure_response($postsData);
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
        'post_id' => $post_id,
    ]);
}
