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

function wordpress_version() : WP_HTTP_Response{

    global $wp_version;

    return rest_ensure_response([
        'version' => $wp_version
    ]);

}