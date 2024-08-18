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

use Webman\Route;

Route::any('/', function () {
    return json(["message" => "Welcome to Amoora Travel API !"]);
});

Route::group('/api/v1/auth', function () {
    Route::post('/', [app\api\controller\Auth_v1::class, 'index']);
    Route::post('/signin', [app\api\controller\Auth_v1::class, 'signin']);
    Route::post('/signup', [app\api\controller\Auth_v1::class, 'signup']);
    Route::post('/send_code', [app\api\controller\Auth_v1::class, 'send_code']);
    Route::post('/send_verification_code', [app\api\controller\Auth_v1::class, 'send_verification_code']);
    Route::post('/confirm_verification_code', [app\api\controller\Auth_v1::class, 'confirm_verification_code']);
    Route::post('/reset_pwd', [app\api\controller\Auth_v1::class, 'reset_pwd']);
    Route::post('/change_pwd', [app\api\controller\Auth_v1::class, 'change_pwd']);
    Route::post('/refresh_token', [app\api\controller\Auth_v1::class, 'refresh_token']);
    Route::post('/closing_account', [app\api\controller\Auth_v1::class, 'closing_account']);
})->middleware([
    app\middleware\VerifyAPIToken::class,
]);

Route::group('/api/v1/user', function () {
    Route::post('/', [app\api\controller\User_v1::class, 'index']);
    Route::post('/profile', [app\api\controller\User_v1::class, 'profile']);
    Route::post('/update_profile', [app\api\controller\User_v1::class, 'update_profile']);
    Route::post('/upload_photo', [app\api\controller\User_v1::class, 'upload_photo']);
})->middleware([
    app\middleware\VerifyAPIToken::class,
]);

Route::group('/api/v1/maps', function () {
    Route::post('/', [app\api\controller\Maps_v1::class, 'index']);
    Route::post('/save_location', [app\api\controller\Maps_v1::class, 'save_location']);
    Route::post('/log', [app\api\controller\Maps_v1::class, 'log']);
})->middleware([
    app\middleware\VerifyAPIToken::class,
]);

Route::group('/api/v1/broadcast', function () {
    Route::post('/', [app\api\controller\Broadcast_v1::class, 'index']);
    Route::post('/check_existing_live', [app\api\controller\Broadcast_v1::class, 'check_existing_live']);
    Route::post('/start', [app\api\controller\Broadcast_v1::class, 'start']);
    Route::post('/stop', [app\api\controller\Broadcast_v1::class, 'stop']);
    Route::post('/presenter_heartbeat', [app\api\controller\Broadcast_v1::class, 'presenter_heartbeat']);
    // Route::post('/online_host', [app\api\controller\Broadcast_v1::class, 'online_host']);
    // Route::post('/online_audience', [app\api\controller\Broadcast_v1::class, 'online_audience']);
    Route::post('/join_channel', [app\api\controller\Broadcast_v1::class, 'join_channel']);
    Route::post('/leave_channel', [app\api\controller\Broadcast_v1::class, 'leave_channel']);
    Route::post('/audience_heartbeat', [app\api\controller\Broadcast_v1::class, 'audience_heartbeat']);
})->middleware([
    app\middleware\VerifyAPIToken::class,
]);

Route::group('/api/v1/signaling', function () {
    Route::post('/', [app\api\controller\Signaling_v1::class, 'index']);
    Route::post('/createPresenter', [app\api\controller\Signaling_v1::class, 'createPresenter']);
    Route::post('/updatePresenter', [app\api\controller\Signaling_v1::class, 'updatePresenter']);
    Route::post('/removePresenter', [app\api\controller\Signaling_v1::class, 'removePresenter']);
    Route::post('/createAudience', [app\api\controller\Signaling_v1::class, 'createAudience']);
    Route::post('/updateAudience', [app\api\controller\Signaling_v1::class, 'updateAudience']);
    Route::post('/removeAudience', [app\api\controller\Signaling_v1::class, 'removeAudience']);
    Route::post('/removeAudienceByPresenterId', [app\api\controller\Signaling_v1::class, 'removeAudienceByPresenterId']);
})->middleware([
    app\middleware\VerifyAPIToken::class,
]);

Route::group('/api/v1/product', function () {
    Route::post('/', [app\api\controller\Product_v1::class, 'index']);
    Route::post('/all', [app\api\controller\Product_v1::class, 'all']);
    Route::post('/list', [app\api\controller\Product_v1::class, 'list']);
    Route::post('/byId', [app\api\controller\Product_v1::class, 'byId']);
})->middleware([
    app\middleware\VerifyAPIToken::class,
]);

Route::group('/api/v1/carousel', function () {
    Route::post('/', [app\api\controller\Carousel_v1::class, 'index']);
    Route::post('/all', [app\api\controller\Carousel_v1::class, 'all']);
})->middleware([
    app\middleware\VerifyAPIToken::class,
]);

Route::group('/api/v1/notification', function () {
    Route::post('/', [app\api\controller\Notification_v1::class, 'index']);
    Route::post('/create', [app\api\controller\Notification_v1::class, 'create']);
    Route::post('/update', [app\api\controller\Notification_v1::class, 'update']);
    Route::post('/delete', [app\api\controller\Notification_v1::class, 'delete']);
})->middleware([
    app\middleware\VerifyAPIToken::class,
]);

Route::group('/api/v1/pusher', function () {
    Route::post('/', [app\api\controller\Notification_v1::class, 'index']);
    Route::post('/trigger', [app\api\controller\Pusher_v1::class, 'trigger']);
})->middleware([
    app\middleware\VerifyAPIToken::class,
]);


Route::disableDefaultRoute();
