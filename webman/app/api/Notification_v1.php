<?php

namespace app\api;

use support\Request;
use support\Db;
use Firuze\Jwt\JwtToken;
use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\NestedValidationException;

class Notification_v1
{
    protected $noNeedLogin = ['index', 'device_sync', 'user_notification_sync', 'notifications_sync'];

    protected $validatorDesc = [
        'attribute' => 'Params [{{name}}] is required',
        'uuid' => '[{{name}}] must be a valid UUID',
        'stringType' => '[{{name}}] must be a string type',
        'dateTime' => '[{{name}}] must be a valid date/time',
        'arrayType' => '[{{name}}] must be a array type of string',
        'objectType' => '[{{name}}] must be type of object',
        'json' => '[{{name}}] must be a valid JSON string',
        'intType' => '[{{name}}] must be integer',
        'email' => '[{{name}}] must be a valid email',
        'boolType' => '[{{name}}] must be a boolean type',
        'length' => '[{{name}}] length must be between {{minValue}} and {{maxValue}}',
        'number' => '[{{name}}] must be a number',
        'notEmpty' => '[{{name}}] must not empty',
        'noWhitespace' => '[{{name}}] cannot contain spaces',
    ];

    public function index(Request $request)
    {
        return json(['message' => "This is Notification API v1"]);
        // $user = Db::table('users')->get();
        // return json($user);
    }

    public function create(Request $request)
    {
        $user_id = JwtToken::getCurrentId();
        $data = (object) $request->post();
        try {
            $inputValidator = v::attribute('topic', v::stringType()->noWhitespace()->notEmpty())
                ->attribute('title', v::stringType()->notEmpty())
                ->attribute('body', v::stringType()->notEmpty())
                ->attribute('image', v::nullable(v::stringType()->notEmpty()), false)
                ->attribute('payload', v::nullable(v::json()->notEmpty()), false)
                ->attribute('is_active', v::boolType()->notEmpty(), false)
                ->attribute('target_type', v::stringType()->notEmpty())
                ->attribute('user_id', v::nullable(v::uuid()->notEmpty()))
                ->attribute('device_id', v::nullable(v::stringType()->notEmpty()))
                ->attribute('publish_at', v::dateTime()->notEmpty(), false);
            $inputValidator->assert($data);
        } catch (NestedValidationException $e) {
            $errAttr = $e->getMessages($this->validatorDesc);
            $errMessage = join(", ", (array) $errAttr['attribute']);
            return jsonr(['message' => $errMessage]);
        }

        Db::beginTransaction();
        try {
            $fields = [];
            $table_fields = ['topic', 'title', 'body', 'image', 'payload', 'is_active', 'publish_at', 'target_type', 'user_id', 'device_id'];
            foreach ($data as $key => $value) {
                if (in_array($key, $table_fields)) {
                    $fields[$key] = $value;
                }
            }
            // post-processed fields
            $fields['created_by'] = $user_id;

            $insert_id = Db::table('notifications')->insertGetId($fields);
            $fields['id'] = $insert_id;

            $result = $fields;

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            return jsonr(['message' => $e->getMessage()]);
        }

        return json($result);
    }

    public function update(Request $request)
    {
        $data = (object) $request->post();
        try {
            $inputValidator = v::attribute('id', v::uuid())
                ->attribute('topic', v::stringType()->noWhitespace()->notEmpty(), false)
                ->attribute('title', v::stringType()->notEmpty(), false)
                ->attribute('body', v::stringType()->notEmpty(), false)
                ->attribute('image', v::nullable(v::stringType()->notEmpty()), false)
                ->attribute('payload', v::nullable(v::json()->notEmpty()), false)
                ->attribute('is_active', v::boolType()->notEmpty(), false)
                ->attribute('target_type', v::stringType()->notEmpty(), false)
                ->attribute('user_id', v::nullable(v::uuid()->notEmpty()), false)
                ->attribute('device_id', v::nullable(v::stringType()->notEmpty()), false)
                ->attribute('publish_at', v::dateTime()->notEmpty(), false);
            $inputValidator->assert($data);
        } catch (NestedValidationException $e) {
            $errAttr = $e->getMessages($this->validatorDesc);
            $errMessage = join(", ", (array) $errAttr['attribute']);
            return jsonr(['message' => $errMessage]);
        }

        Db::beginTransaction();
        try {
            $fields = [];
            $table_fields = ['topic', 'title', 'body', 'image', 'payload', 'is_active', 'publish_at', 'target_type', 'user_id', 'device_id'];
            foreach ($data as $key => $value) {
                if (in_array($key, $table_fields)) {
                    $fields[$key] = $value;
                }
            }

            Db::table('notifications')->where(['id' => $data->id])->update($fields);

            $fields['id'] = $data->id;
            $result = $fields;

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            return jsonr(['message' => $e->getMessage()]);
        }

        return json($result);
    }

    public function delete(Request $request)
    {
        $data = (object) $request->post();
        try {
            $inputValidator = v::attribute('id', v::arrayType()->notEmpty());
            $inputValidator->assert($data);
        } catch (NestedValidationException $e) {
            $errAttr = $e->getMessages($this->validatorDesc);
            $errMessage = join(", ", (array) $errAttr['attribute']);
            return jsonr(['message' => $errMessage]);
        }

        Db::beginTransaction();
        try {

            Db::table('notifications')->whereIn('id', $data->id)->delete();

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            return jsonr(['message' => $e->getMessage()]);
        }

        return json(['message' => 'done']);
    }

    // User state read
    public function user_read(Request $request)
    {
        $data = (object) $request->post();
        try {
            $inputValidator = v::attribute('notification_id', v::stringType()->noWhitespace()->notEmpty())
                ->attribute('device_id', v::stringType()->noWhitespace()->notEmpty())
                ->attribute('user_id', v::nullable(v::uuid()->noWhitespace()->notEmpty()));
            $inputValidator->assert($data);
        } catch (NestedValidationException $e) {
            $errAttr = $e->getMessages($this->validatorDesc);
            $errMessage = join(", ", (array) $errAttr['attribute']);
            return jsonr(['message' => $errMessage]);
        }

        Db::beginTransaction();
        try {

            Db::table('user_notifications')->updateOrInsert(
                ['notification_id' => $data->notification_id, 'user_id' => $data->user_id, 'device_id' => $data->device_id],
                ['is_read' => true, 'updated_at' => date('Y-m-d H:i:s')]
            );

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            return jsonr(['message' => $e->getMessage()]);
        }

        return json(['message' => 'done']);
    }

    // User state archive
    public function user_archive(Request $request)
    {
        $data = (object) $request->post();
        try {
            $inputValidator = v::attribute('notification_id', v::stringType()->noWhitespace()->notEmpty())
                ->attribute('device_id', v::stringType()->noWhitespace()->notEmpty())
                ->attribute('user_id', v::nullable(v::uuid()->noWhitespace()->notEmpty()));
            $inputValidator->assert($data);
        } catch (NestedValidationException $e) {
            $errAttr = $e->getMessages($this->validatorDesc);
            $errMessage = join(", ", (array) $errAttr['attribute']);
            return jsonr(['message' => $errMessage]);
        }

        Db::beginTransaction();
        try {

            Db::table('user_notifications')->updateOrInsert(
                ['notification_id' => $data->notification_id, 'user_id' => $data->user_id, 'device_id' => $data->device_id],
                ['is_archived' => true, 'updated_at' => date('Y-m-d H:i:s')]
            );

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            return jsonr(['message' => $e->getMessage()]);
        }

        return json(['message' => 'done']);
    }

    // User state delete
    public function user_delete(Request $request)
    {
        $data = (object) $request->post();
        try {
            $inputValidator = v::attribute('notification_id', v::stringType()->noWhitespace()->notEmpty())
                ->attribute('device_id', v::stringType()->noWhitespace()->notEmpty())
                ->attribute('user_id', v::nullable(v::uuid()->noWhitespace()->notEmpty()));
            $inputValidator->assert($data);
        } catch (NestedValidationException $e) {
            $errAttr = $e->getMessages($this->validatorDesc);
            $errMessage = join(", ", (array) $errAttr['attribute']);
            return jsonr(['message' => $errMessage]);
        }

        Db::beginTransaction();
        try {

            Db::table('user_notifications')->updateOrInsert(
                ['notification_id' => $data->notification_id, 'user_id' => $data->user_id, 'device_id' => $data->device_id],
                ['is_deleted' => true, 'updated_at' => date('Y-m-d H:i:s')]
            );

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            return jsonr(['message' => $e->getMessage()]);
        }

        return json(['message' => 'done']);
    }

    /**
     *  Device Sync from Mobile Apps triggerd by Apps Start
     *  
     *  {
     *      "id": "emu64a:AE3A.240806.036",
     *      "name": "GOOGLE sdk_gphone64_arm64",
     *      "platform": "android",
     *      "user_id": null
     *  }
     */
    public function device_sync(Request $request)
    {
        $data = (object) $request->post();
        try {
            $inputValidator = v::attribute('id', v::stringType()->noWhitespace()->notEmpty())
                ->attribute('name', v::stringType()->notEmpty())
                ->attribute('platform', v::stringType()->notEmpty())
                ->attribute('user_id', v::nullable(v::stringType()->noWhitespace()->notEmpty()), false);
            $inputValidator->assert($data);
        } catch (NestedValidationException $e) {
            $errAttr = $e->getMessages($this->validatorDesc);
            $errMessage = join(", ", (array) $errAttr['attribute']);
            return jsonr(['message' => $errMessage]);
        }

        // MIDDLE STAGE (Main Process)
        // ===========================
        Db::beginTransaction();
        try {
            Db::table('devices')->updateOrInsert(
                ['id' => $data->id],
                ['name' => $data->name, 'platform' => $data->platform, 'updated_at' => date('Y-m-d H:i:s')]
            );

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            return jsonr(['message' => $e->getMessage()]);
        }

        return json(['message' => 'done']);
    }

    /**
     *  Get latest notification triggered by Push-Notification/Pusher/WebSocket/First App Start/Background Sync
     *  
     *  {
     *       "last_sync": "2026-07-23 12:00:00",
     *       "device_id": "emu64a:AE3A.240806.036",
     *       "user_id": null,
     *       "topic": ["maintenance","promo","general","registration","newcommers"]
     *  }
     */
    public function notifications_sync(Request $request)
    {
        $data = (object) $request->post();
        try {
            $inputValidator = v::attribute('last_sync', v::nullable(v::dateTime()))
                ->attribute('device_id', v::stringType()->noWhitespace()->notEmpty())
                ->attribute('user_id', v::nullable(v::uuid()->noWhitespace()->notEmpty()))
                ->attribute('topic', v::arrayType()->notEmpty());
            $inputValidator->assert($data);
        } catch (NestedValidationException $e) {
            $errAttr = $e->getMessages($this->validatorDesc);
            $errMessage = join(", ", (array) $errAttr['attribute']);
            return jsonr(['message' => $errMessage]);
        }

        Db::beginTransaction();
        try {
            $data->last_sync = $data->last_sync ?? date('Y-m-d H:i:s');

            $notifications = Db::table('notifications')
                ->selectRaw('id, topic, title, body, image, payload, publish_at, target_type, device_id, user_id')
                ->where('publish_at', '>', $data->last_sync)
                ->whereIn('topic', $data->topic)
                ->where('is_active', true)
                ->orderBy('publish_at', 'desc')
                ->get();

            $output = [];
            foreach ($notifications as $notif) {
                switch ($notif->target_type) {
                    case 'GUEST':
                        if ($data->device_id != null && $data->user_id == null) {
                            unset($notif->target_type, $notif->device_id, $notif->user_id);
                            $notif->user_id = $data->user_id;
                            $output[] = $notif;
                        }
                        break;

                    case 'USER':
                        if ($data->user_id != null) {
                            unset($notif->target_type, $notif->device_id, $notif->user_id);
                            $notif->user_id = $data->user_id;
                            $output[] = $notif;
                        }
                        break;

                    case 'SPECIFIC_USER':
                        if ($notif->user_id == $data->user_id) {
                            unset($notif->target_type, $notif->device_id, $notif->user_id);
                            $notif->user_id = $data->user_id;
                            $output[] = $notif;
                        }
                        break;

                    case 'SPECIFIC_GUEST':
                        if ($notif->device_id == $data->device_id) {
                            unset($notif->target_type, $notif->device_id, $notif->user_id);
                            $notif->user_id = $data->user_id;
                            $output[] = $notif;
                        }
                        break;

                    default:
                        unset($notif->target_type, $notif->device_id, $notif->user_id);
                        $notif->user_id = $data->user_id;
                        $output[] = $notif;
                        break;
                }
            }


            $result['last_sync'] = date('Y-m-d H:i:s');
            $result['notifications'] = [];
            $result['states'] = [];
            if (count($output) > 0) {
                $result['last_sync'] = $output[0]->publish_at;
                $result['notifications'] = $output;

                $user_notifications = Db::table('user_notifications')
                    ->selectRaw('notification_id, device_id, user_id, is_read, is_deleted, is_archived, updated_at')
                    ->whereIn('notification_id', array_column($output, 'id'))
                    ->where('device_id', '=', $data->device_id)
                    ->where('user_id', '=', $data->user_id)
                    ->get();

                if (count($user_notifications) > 0) {
                    $result['states'] = $user_notifications;
                }
            }

            // Check whenever Update State > Last Sync
            $user_notifications = Db::table('user_notifications')
                ->selectRaw('notification_id, device_id, user_id, is_read, is_deleted, is_archived, updated_at')
                ->whereNotIn('notification_id', array_column($output, 'id'))
                ->where('device_id', '=', $data->device_id)
                ->where('user_id', '=', $data->user_id)
                ->where('updated_at', '>', $data->last_sync)
                ->get();

            if (count($user_notifications) > 0) {
                $result['states'] = [...$result['states'], ...$user_notifications];
                // $result['states'] = array_merge($result['states'], $user_notifications);
            }

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            return jsonr(['message' => $e->getMessage()]);
        }

        return json($result);
        return json($notifications);
    }

    /**
     *  Syncronized user notification states From Mobile Apps triggerd by onChanged or with Background Sync
     *  
     *  {
     *      "notification_id": "fd46407b-ec54-4888-ab8d-03948102171f",
     *      "device_id": "emu64a:AE3A.240806.036",
     *      "user_id": null,
     *      "is_read": true,
     *      "is_delete": true
     *  }
     *  
     */
    public function user_notification_sync(Request $request)
    {
        $data = (object) $request->post();
        try {
            $inputValidator = v::attribute('notification_id', v::stringType()->noWhitespace()->notEmpty())
                ->attribute('device_id', v::stringType()->noWhitespace()->notEmpty())
                ->attribute('user_id', v::nullable(v::uuid()->noWhitespace()->notEmpty()))
                ->attribute('is_read', v::boolType())
                ->attribute('is_deleted', v::boolType())
                ->attribute('is_archived', v::boolType());
            $inputValidator->assert($data);
        } catch (NestedValidationException $e) {
            $errAttr = $e->getMessages($this->validatorDesc);
            $errMessage = join(", ", (array) $errAttr['attribute']);
            return jsonr(['message' => $errMessage]);
        }

        Db::beginTransaction();
        try {

            Db::table('user_notifications')->updateOrInsert(
                ['notification_id' => $data->notification_id, 'user_id' => $data->user_id, 'device_id' => $data->device_id],
                ['is_read' => $data->is_read, 'is_deleted' => $data->is_deleted, 'is_archived' => $data->is_archived, 'updated_at' => date('Y-m-d H:i:s')]
            );

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            return jsonr(['message' => $e->getMessage()]);
        }

        return json(['message' => 'done']);
    }
}
