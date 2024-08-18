<?php
/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

use support\Request;
use Webman\Route;
use Webman\Push\Api;

/**
 * Push JS client file
 */
Route::get('/plugin/webman/push/push.js', function (Request $request) {
    return response()->file(base_path().'/vendor/webman/push/src/push.js');
});

/**
 * Private channels are verified. 
 * Here you should use session to identify the current user identity, 
 * and then determine whether the user has the right to monitor the Channel_name
 */
Route::post(config('plugin.webman.push.app.auth'), function (Request $request) {
    $pusher = new Api(str_replace('0.0.0.0', '127.0.0.1', config('plugin.webman.push.app.api')), config('plugin.webman.push.app.app_key'), config('plugin.webman.push.app.app_secret'));
    $channel_name = $request->post('channel_name');
    $session = $request->session();
    // Here you should determine whether the current user has the right to listen to Channel_namehas the right to listen to Channel_namehas the right to listen to Channel_name
    $has_authority = true;
    if ($has_authority) {
        return response($pusher->socketAuth($channel_name, $request->post('socket_id')));
    } else {
        return response('Forbidden', 403);
    }
});

/**
 * The callbacks triggered when the channel is online and the offline
 * Channel online: It means that a channel has never connected to online to connect to online
 * Channel offline: refers to all connections of a certain channel disconnecting triggered
 */
Route::post(parse_url(config('plugin.webman.push.app.channel_hook'), PHP_URL_PATH), function (Request $request) {

    // No X-Pusher-Signature head is deemed to be forged requests
    if (!$webhook_signature = $request->header('x-pusher-signature')) {
        return response('401 Not authenticated', 401);
    }

    $body = $request->rawBody();

    // Calculate the signature, $ app_secret is the key used by both parties.
    $expected_signature = hash_hmac('sha256', $body, config('plugin.webman.push.app.app_secret'), false);

    // Safety verification, if the signature is inconsistent, it may be a fake request, and return the 401 status code
    if ($webhook_signature !== $expected_signature) {
        return response('401 Not authenticated', 401);
    }

    // Storage here is stored online, Channel data
    $payload = json_decode($body, true);

    $channels_online = $channels_offline = [];

    foreach ($payload['events'] as $event) {
        if ($event['name'] === 'channel_added') {
            $channels_online[] = $event['channel'];
        } else if ($event['name'] === 'channel_removed') {
            $channels_offline[] = $event['channel'];
        }
    }

    // The business handles the chaannel as required. For example
    // All Channel on the line
    echo 'online channels: ' . implode(',', $channels_online) . "\n";
    // All Channel offline
    echo 'offline channels: ' . implode(',', $channels_offline) . "\n";

    return 'OK';
});



