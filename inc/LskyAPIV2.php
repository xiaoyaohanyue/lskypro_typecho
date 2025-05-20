<?php

namespace TypechoPlugin\LskyPro\inc;
use TypechoPlugin\LskyPro\inc\LskyUtils;
use CURLFile;

class LskyAPIV2
{

    public static function get_album($api, $token){
        $url = $api . '/user/albums';
        $headers = [
            'Content-Type: application/json',
            'User-Agent: yaoyue/lsky-api-client',
            'Authorization: Bearer ' . $token
        ];
        $response = LskyUtils::curl_get($url, $headers);
        return $response;
    }

    public static function get_storage($api, $token){
        $url = $api . '/group';
        $headers = [
            'Content-Type: application/json',
            'User-Agent: yaoyue/lsky-api-client',
            'Authorization: Bearer ' . $token
        ];
        $response = LskyUtils::curl_get($url, $headers);
        return $response;
    }

    public static function img_upload($imgname){
        $data['file'] = $imgname;
        $data['api'] = LskyUtils::getPluginConfig('lskypro_api_url');
        $data['token'] = LskyUtils::getPluginConfig('lskypro_token');
        $data['storage_id'] = LskyUtils::getPluginConfig('lskypro_storage_id');
        $data['album_id'] = LskyUtils::getPluginConfig('lskypro_album_id');
        $data['is_public'] = LskyUtils::getPluginConfig('lskypro_permission');
        $url = $data["api"] . '/upload';
        $post_data = [ 
            'file' => new CURLFile($data['file']),
            'album_id' => $data["album_id"],
            'is_public' => $data["is_public"],
            'storage_id' => $data["storage_id"]
        ];
        $headers = [
            'Content-Type: multipart/form-data',
            'Authorization: Bearer ' . $data["token"],
            'User-Agent: yaoyue/lsky-api-client'
        ];
        $response = LskyUtils::curl_post($url, $post_data, $headers);
        $res_new = [
            'status' => $response['status'],
            'data' => [
                'key' => $response['data']['id'],
                'md5' => $response['data']['md5'],
                'origin_name' => $response['data']['filename'],
                'links' => [
                    'url' => $response['data']['public_url']
                ],
                'size' => '0',
                'extension' => $response['data']['extension'],
                'mimetype' => $response['data']['mimetype']
            ]
        ];
        return $res_new;
    }

    public static function img_delete($key ){
        $api = LskyUtils::getPluginConfig('lskypro_api_url');
        $token = LskyUtils::getPluginConfig('lskypro_token');
        $url = $api . '/user/photos';
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
            'User-Agent: yaoyue/lsky-api-client'
        ];
        $response = LskyUtils::curl_delete($url, $headers,'['.$key.']');
        return $response;
    }
    



}