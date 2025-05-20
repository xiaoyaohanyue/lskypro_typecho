<?php

/**
 * Lsky Pro图床全版本上传插件
 *
 * @package LskyPro
 * @author 妖月
 * @version 1.0.0
 * @link https://fjwr.xyz
 */
include_once __DIR__ . '/autoload.php';
use Typecho\Request;
use TypechoPlugin\LskyPro\inc\LskyAPIV1;
use TypechoPlugin\LskyPro\inc\LskyAPIV2;
use TypechoPlugin\LskyPro\inc\LskyUtils;
class LskyPro_Action extends Typecho_Widget implements Widget_Interface_Do
{
    public function execute()
    {
        $request = Request::getInstance();
        $action = $request->get('action', 'action');

        if (method_exists($this, $action)) {
            $this->$action();
        } else {
            $this->action();
        }
    }

    public function action()
    {
        $request = Request::getInstance();
        $param = $request->get('param');

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 'success',
            'param_received' => $param,
            'message' => 'AJAX 请求成功'
        ]);
        exit;
    }
    public function update_token()
    {
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $request = Request::getInstance();
            $api = $request->get('api');
            $username = $request->get('username');
            $password = $request->get('password');

            if (empty($api) || empty($username) || empty($password)) {
                echo json_encode([
                    'status' => false,
                    'message' => '请填写完整的API地址、用户名和密码',
                    'token' => '请填写完整的API地址、用户名和密码'
                ]);
                exit;
            }

            $token = LskyAPIV1::refreash_token($api, $username, $password);
            if (empty($token)) {
                echo json_encode([
                    'status' => false,
                    'message' => '获取Token失败，请检查用户名和密码是否正确',
                    'token' => '获取Token失败，请检查用户名和密码是否正确'
                ]);
                exit;
            }
            LskyUtils::updatePluginConfig(['lskypro_token' => $token]);
            echo json_encode([
                'status' => true,
                'message' => 'Token更新成功',
                'token' => $token
            ]);
            exit;
        }
    }

    public static function getAlbums(){
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $request = Request::getInstance();
            $api = $request->get('api');
            $token = $request->get('token');
            $albums = [];
            $album_resp = LskyAPIV2::get_album($api, $token);
            if ($album_resp['status'] == 'success') {
                foreach ($album_resp['data']['data'] as $item) {
                    $albums[] = ['id' => $item['id'], 'name' => $item['name']];
                }
            } 
            $storages = [];
            $storage_resp = LskyAPIV2::get_storage($api, $token);
            if ($storage_resp['status'] == 'success') {
                foreach ($storage_resp['data']['storages'] as $item) {
                    $storages[] = ['id' => $item['id'], 'name' => $item['name']];
                }
            }
            $response = [
                'status' => true,
                'albums' => $albums,
                'storages' => $storages
            ];
            echo json_encode($response);
            exit;
        }
    }
}
