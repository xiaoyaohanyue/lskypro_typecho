<?php

namespace TypechoPlugin\LskyPro\inc;
use TypechoPlugin\LskyPro\inc\LskyUtils;
use CURLFile;

class LskyAPIV1
{

    public static function generate_token($api,$username,$password)
    {
        if (empty($api)){
            $lsky_api = LskyUtils::getPluginConfig('lskypro_api_url');
        }
        else{
            $lsky_api = $api;
        }
        $url = $lsky_api . '/tokens';
        $post_data = [
            'email' => $username,
            'password' => $password
        ];
        $headers = [
            'Content-Type: application/json'
        ];
        $response = LskyUtils::curl_post($url, json_encode($post_data), $headers);
        if (true == $response['status']){
            return $response['data']['token'];
        }else{
            return $response['message'];
        }
    }

    public static function removealltoken($api){
        if (empty($api)){
            $lsky_api = LskyUtils::getPluginConfig('lskypro_api_url');
        }
        else{
            $lsky_api = $api;
        }
        
        $url = $lsky_api . '/tokens';
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . LskyUtils::getPluginConfig('lskypro_token')
        ];
        $response = LskyUtils::curl_delete($url, $headers);
        return $response['status'];
    }

    public static function refreash_token($api, $username,$password){

        if (empty(LskyUtils::getPluginConfig('lskypro_token'))){
            $token = self::generate_token($api,$username,$password);
        }
        elseif (self::removealltoken($api) == true){
            $token = self::generate_token($api,$username,$password);
        }else{
            $token = self::generate_token($api,$username,$password);
        }
        return $token;
    }

    public static function img_upload($imgname){
        $data['file'] = $imgname;
        $data['api'] = LskyUtils::getPluginConfig('lskypro_api_url');
        $data['token'] = LskyUtils::getPluginConfig('lskypro_token');
        $url = $data["api"] . '/upload';
        $post_data = [
            'file' => new CURLFile($data['file']),
            'permission' => LskyUtils::getPluginConfig('lskypro_permission')
        ];
        $headers = [
            'Content-Type: multipart/form-data',
            'Authorization: Bearer ' . $data["token"]
        ];
        $response = LskyUtils::curl_post($url, $post_data, $headers);
        return $response;
    }

    public static function img_delete($key ){
        $api = LskyUtils::getPluginConfig('lskypro_api_url');
        $token = LskyUtils::getPluginConfig('lskypro_token');
        $url = $api . '/images/' . $key;
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ];
        $response = LskyUtils::curl_delete($url, $headers,null);
        return $response;
    }
    


}