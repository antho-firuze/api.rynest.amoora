<?php

namespace app\api\controller;

use RuntimeException;
use SplFileInfo;
use support\Request;
use support\Redis;
use support\Db;


class FooController
{
    /**
     * Methods that do not require login
     */
    protected $noNeedLogin = ['test'];

    public function index(Request $request)
    {
        $user = session('user');
        return json(['msg' => "Welcome to Webman API, {$user['name']} !"]);
    }

    public function view(Request $request)
    {
        return view('index/view', ['name' => 'webman']);
    }

    public function json(Request $request)
    {
        return json([
            'server' => $_SERVER,
            'env' => getenv(),
        ]);
    }

    public function request(Request $request)
    {
        return json($request->host());
    }

    public function test(Request $request)
    {
        return $request->path();
        // return json(['ENV' => getenv()])->withStatus(500);
    }

    public function redis(Request $request)
    {
        $key = 'test_key';
        Redis::set($key, rand());
        return response(Redis::get($key));
    }

    public function db(Request $request)
    {
        $data = Db::table('speaker')->get();
        return json($data);
        // $default_uid = 29;
        // $uid = $request->get('uid', $default_uid);
        // $name = Db::table('users')->where('uid', $uid)->value('username');
        // return response("hello $name");
    }

    public function upload_file(Request $request)
    {
        $result = [];
        $basePath = public_path().DIRECTORY_SEPARATOR."files/";
        $baseUrl = 'http://localhost/files/';

        $file = $request->file();
        if ($file && $file->isValid()) {
            $saveFilename = 'myfile.' . $file->getUploadExtension();
            $savePath = $basePath . $saveFilename;
            $file->move($savePath);
            $info = new SplFileInfo($savePath);
            $temp = [
                'save_path' => $savePath,
                'url' => $baseUrl . $saveFilename,
                'size' => $info->getSize(),
                'mime_type' => $file->getUploadMimeType(),
                'extension' => $file->getUploadExtension(),
            ];
            array_push($result, $temp);
            return json($result);
        }
        return json(['code' => 1, 'msg' => 'file not found']);

        // foreach ($request->file() as $file) {
        //     if (!$file->isValid()) {
        //         $err = $file->getUploadErrorCode();
        //         throw new RuntimeException("Not Valid File: [{$err}]");
        //     }
        // }

        // return json(['OK']);
    }

    public function upload_files(Request $request)
    {
        $result = [];
        $basePath = public_path().DIRECTORY_SEPARATOR."files/";
        $baseUrl = 'http://localhost/files/';

        $files = $request->file();
        foreach ($files as $key => $file) {
            $saveFilename = "myfile-$key." . $file->getUploadExtension();
            $savePath = $basePath.$saveFilename;
            $file->move($savePath);
            $info = new SplFileInfo($savePath);
            $temp = [
                'save_path' => $savePath,
                'url' => $baseUrl . $saveFilename,
                'size' => $info->getSize(),
                'mime_type' => $file->getUploadMimeType(),
                'extension' => $file->getUploadExtension(),
            ];
            array_push($result, $temp);
        }
        return json($result);
    }
}
