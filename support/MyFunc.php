<?php

namespace support;

use Exception;
use support\Request;

class MyFunc
{
    /**
     * Generate a "Random" Code with definition length
     *
     * @param	int	$len        number of characters
     * @param	string $type	Type of random string.  uppercase|lowercase|numeric
     * @return	string
     */
    static function generate_code(int $len = 6, string $type = 'numeric|uppercase')
    {
        $data = [
            'numeric'   => '123456789',
            'lowercase' => 'abcdefghijklmnopqrstuvwxyz',
            'uppercase' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        ];

        $pool = '';
        $types = explode('|', $type);
        foreach ($types as $v)
            $pool .= isset($data[$v]) ? $data[$v] : '';

        return substr(str_shuffle(str_repeat($pool, ceil($len / strlen($pool)))), 0, $len);
    }

    /**
     * Return a formatted string
     *
     * @param string $str
     * @param array $vars     Paired value array
     * @param string $prefix  Default '{'
     * @param string $suffix  Default '}'
     * @return void
     */
    static function sprintfx(string $str, array $vars, string $prefix = '{', string $suffix = '}')
    {
        if (array() === $vars)
            return $str;

        foreach ($vars as $key => $val)
            $arr[$prefix . $key . $suffix] = $val;

        return str_replace(array_keys($arr), array_values($arr), $str);
    }

    static function upload_file(Request $request, $config = [])
    {
        $userfile = $config['userfile'];
        $file_name = $config['file_name'];
        $upload_path = $config['upload_path'];
        $allowed_types = $config['allowed_types'];
        $max_size = $config['max_size'];     // in KB

        $file = $request->file($userfile);
        $file_ext = $file->getUploadExtension();
        $file_size = $file->getSize() / 1000;  // convert to KB, actual in Bytes

        // re-assign upload_path variable
        $upload_path = "{$upload_path}{$file_name}.{$file_ext}";

        if (!in_array($file_ext, $allowed_types)) {
            throw new Exception(message: 'File extension not allowed !', code: 1);
        }

        if ($file_size > $max_size) {
            throw new Exception(message: 'File size not allowed !', code: 2);
        }

        if ($file && $file->isValid()) {
            $file->move(public_path(path: $upload_path));

            $protocol = $request->header('x-forwarded-proto');
            $host = $request->host();

            return [
                // 'protocol' => $protocol,
                'host' => "{$protocol}://{$host}",
                'path' => $upload_path,
                'full_path' => "{$protocol}://{$host}{$upload_path}",
            ];
        }

        throw new Exception(message: 'File not found !');
    }
}


