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

Route::any('/', function () {
    return json(["message" => "Welcome to Rynest API Server !"]);
});

Route::group('/api/v1/auth', function () {
    Route::post('/', [app\api\Auth_v1::class, 'index']);
    Route::post('/signin', [app\api\Auth_v1::class, 'signin']);
    Route::post('/signup', [app\api\Auth_v1::class, 'signup']);
    Route::post('/send_forgot_code', [app\api\Auth_v1::class, 'send_forgot_code']);
    Route::post('/send_verification_code', [app\api\Auth_v1::class, 'send_verification_code']);
    Route::post('/confirm_verification_code', [app\api\Auth_v1::class, 'confirm_verification_code']);
    Route::post('/reset_pwd', [app\api\Auth_v1::class, 'reset_pwd']);
    Route::post('/change_pwd', [app\api\Auth_v1::class, 'change_pwd']);
    Route::post('/refresh_token', [app\api\Auth_v1::class, 'refresh_token']);
    Route::post('/closing_account', [app\api\Auth_v1::class, 'closing_account']);
})->middleware([
    app\middleware\VerifyAPIToken::class,
]);

Route::group('/api/v1/member', function () {
    Route::post('/', [app\api\Member_v1::class, 'index']);
    Route::post('/profile', [app\api\Member_v1::class, 'profile']);
    Route::post('/update_profile', [app\api\Member_v1::class, 'update_profile']);
    Route::post('/upload_photo', [app\api\Member_v1::class, 'upload_photo']);
})->middleware([
    app\middleware\VerifyAPIToken::class,
]);

Route::group('/api/v1/maps', function () {
    Route::post('/', [app\api\Maps_v1::class, 'index']);
    Route::post('/save_location', [app\api\Maps_v1::class, 'save_location']);
    Route::post('/live_location', [app\api\Maps_v1::class, 'live_location']);
    Route::post('/log', [app\api\Maps_v1::class, 'log']);
})->middleware([
    app\middleware\VerifyAPIToken::class,
]);

Route::group('/api/v1/broadcast', function () {
    Route::post('/', [app\api\Broadcast_v1::class, 'index']);
    Route::post('/check_existing_live', [app\api\Broadcast_v1::class, 'check_existing_live']);
    Route::post('/start', [app\api\Broadcast_v1::class, 'start']);
    Route::post('/stop', [app\api\Broadcast_v1::class, 'stop']);
    Route::post('/presenter_heartbeat', [app\api\Broadcast_v1::class, 'presenter_heartbeat']);
    // Route::post('/online_host', [app\api\Broadcast_v1::class, 'online_host']);
    // Route::post('/online_audience', [app\api\Broadcast_v1::class, 'online_audience']);
    Route::post('/join_channel', [app\api\Broadcast_v1::class, 'join_channel']);
    Route::post('/leave_channel', [app\api\Broadcast_v1::class, 'leave_channel']);
    Route::post('/audience_heartbeat', [app\api\Broadcast_v1::class, 'audience_heartbeat']);
})->middleware([
    app\middleware\VerifyAPIToken::class,
]);

Route::group('/api/v1/streamer', function () {
    Route::post('/', [app\api\Streamer_v1::class, 'index']);
    Route::post('/start', [app\api\Streamer_v1::class, 'start']);
    Route::post('/stop', [app\api\Streamer_v1::class, 'stop']);
    Route::post('/heartbeat', [app\api\Streamer_v1::class, 'heartbeat']);
    // Route::post('/all', [app\api\Streamer_v1::class, 'all']);
    Route::post('/byid', [app\api\Streamer_v1::class, 'byid']);
    Route::post('/join', [app\api\Streamer_v1::class, 'join']);
    // Route::post('/push_send', [app\api\Streamer_v1::class, 'push_send']);
    Route::post('/ice_server_config', [app\api\Streamer_v1::class, 'ice_server_config']);
})->middleware([
    app\middleware\VerifyAPIToken::class,
]);

Route::group('/api/v1/signaling', function () {
    Route::post('/', [app\api\Signaling_v1::class, 'index']);
    Route::post('/createPresenter', [app\api\Signaling_v1::class, 'createPresenter']);
    Route::post('/updatePresenter', [app\api\Signaling_v1::class, 'updatePresenter']);
    Route::post('/removePresenter', [app\api\Signaling_v1::class, 'removePresenter']);
    Route::post('/createAudience', [app\api\Signaling_v1::class, 'createAudience']);
    Route::post('/updateAudience', [app\api\Signaling_v1::class, 'updateAudience']);
    Route::post('/removeAudience', [app\api\Signaling_v1::class, 'removeAudience']);
    Route::post('/removeAudienceByPresenterId', [app\api\Signaling_v1::class, 'removeAudienceByPresenterId']);
})->middleware([
    app\middleware\VerifyAPIToken::class,
]);

Route::group('/api/v1/product', function () {
    Route::post('/', [app\api\Product_v1::class, 'index']);
    Route::post('/all', [app\api\Product_v1::class, 'all']);
    Route::post('/list', [app\api\Product_v1::class, 'list']);
    Route::post('/byId', [app\api\Product_v1::class, 'byId']);
})->middleware([
    app\middleware\VerifyAPIToken::class,
]);

Route::group('/api/v1/carousel', function () {
    Route::post('/', [app\api\Carousel_v1::class, 'index']);
    Route::post('/all', [app\api\Carousel_v1::class, 'all']);
})->middleware([
    app\middleware\VerifyAPIToken::class,
]);

Route::group('/api/v1/notification', function () {
    Route::post('/', [app\api\Notification_v1::class, 'index']);
    Route::post('/create', [app\api\Notification_v1::class, 'create']);
    Route::post('/update', [app\api\Notification_v1::class, 'update']);
    Route::post('/delete', [app\api\Notification_v1::class, 'delete']);
})->middleware([
    app\middleware\VerifyAPIToken::class,
]);

Route::group('/api/v1/pusher', function () {
    Route::any('/', [app\api\Pusher_v1::class, 'index']);
    Route::post('/auth', [app\api\Pusher_v1::class, 'auth']);
    Route::post('/trigger_all', [app\api\Pusher_v1::class, 'trigger_all']);
    Route::post('/channels', [app\api\Pusher_v1::class, 'channels']);
    Route::post('/channel_info', [app\api\Pusher_v1::class, 'channel_info']);
    Route::post('/channel_info_users', [app\api\Pusher_v1::class, 'channel_info_users']);
})->middleware([
    app\middleware\VerifyAPIToken::class,
]);

Route::fallback(function (Request $request) {
    // Return JSON for AJAX requests
    $isTypeFormData = false !== strpos($request->header('Content-Type', ''), 'form-data');
    $isTypeAppJson = false !== strpos($request->header('Content-Type', ''), 'json');
    // return json($isTypeAppJson);
    return jsonr(['message' => '404 not found'], 404);

    if ($request->expectsJson() || $isTypeFormData || $isTypeAppJson) {
        return jsonr(['message' => '404 not found'], 404);
    }
    // Return the 404.html template for page requests
    return view('404', ['error' => 'some error'])->withStatus(404);
});