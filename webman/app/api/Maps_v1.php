<?php

namespace app\api;

use support\Request;
use support\Db;
use Firuze\Jwt\JwtToken;
use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\NestedValidationException;

class Maps_v1
{
    protected $noNeedLogin = ['index'];

    public function index(Request $request)
    {
        return json(['message' => "Maps API v1"]);
    }

    public function save_location(Request $request)
    {
        // FIRST STAGE (Parameters)
        // ========================
        $data = (object) $request->post();
        try {
            $inputValidator = v::attribute('lat', v::floatType())
                ->attribute('lng', v::floatType())
                ->attribute('label', v::stringType());
            $inputValidator->assert($data);
        } catch (NestedValidationException $e) {
            $errAttr = $e->getMessages([
                'attribute' => 'Params [{{name}}] is required',
                'floatType' => '[{{name}}] must be a floating point number',
                'notEmpty' => '[{{name}}] must not empty',
            ]);
            $errMessage = join(", ", (array) $errAttr['attribute']);
            return jsonr(['message' => $errMessage]);
        }

        // MIDDLE STAGE (Main Process)
        // ===========================
        Db::beginTransaction();
        try {
            $user_id = JwtToken::getCurrentId();

            $log = [
                'user_id' => $user_id,
                'lat' => $data->lat,
                'lng' => $data->lng,
                'time' => $data->heartbeat ?? date('Y-m-d H:i:s'),
            ];
            Db::table('log_location')->insert($log);

            $live = [
                'user_id' => $user_id,
                'label' => $data->label,
                'lat' => $data->lat,
                'lng' => $data->lng,
                'heartbeat' => $data->heartbeat ?? date('Y-m-d H:i:s'),
            ];
            Db::table('live_location')->updateOrInsert(['user_id' => $user_id], $live);

            $result = $live;

            if ($data->is_testing ?? false) {
                Db::rollBack();
                $result['is_testing'] = $data->is_testing;
            } else {
                Db::commit();
            }
        } catch (\Throwable $th) {
            Db::rollBack();
            return jsonr(["message" => $th->getMessage(), "trace" => $th->getTrace()]);
        }

        // LAST STAGE (Output Process)
        // ===========================
        return json($result);
    }

    public function log(Request $request)
    {
        // FIRST STAGE (Parameters)
        // ========================
        $data = (object) $request->post();
        try {
            $inputValidator = v::attribute('time', v::dateTime());
            $inputValidator->assert($data);
        } catch (NestedValidationException $e) {
            $errAttr = $e->getMessages([
                'attribute' => 'Params [{{name}}] is required',
                'dateTime' => '[{{name}}] must be a valid date/time',
                'notEmpty' => '[{{name}}] must not empty',
            ]);
            $errMessage = join(", ", (array) $errAttr['attribute']);
            return jsonr(['message' => $errMessage]);
        }

        // MIDDLE STAGE (Main Process)
        // ===========================
        Db::beginTransaction();
        try {
            $user_id = $data->user_id ?? JwtToken::getCurrentId();

            $log_location = Db::table('log_location')
                ->where(['user_id' => $user_id])
                ->whereRaw("DATE_FORMAT(time, '%Y-%m-%d') = ?", $data->time)
                ->get();

            $result = $log_location;

            Db::commit();
        } catch (\Throwable $th) {
            Db::rollBack();
            return jsonr(["message" => $th->getMessage(), "trace" => $th->getTrace()]);
        }

        // LAST STAGE (Output Process)
        // ===========================
        return json($result);
    }
}
