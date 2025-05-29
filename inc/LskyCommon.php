<?php
namespace TypechoPlugin\LskyPro\inc;

use TypechoPlugin\LskyPro\inc\LskyUtils;
use Widget\Upload;
use Typecho\Common;
use Widget\Options;

class LskyCommon

{
    public static $originUploadHandle = null;
    const UPLOAD_DIR  = '/usr/uploads';

    public static function uploadHandle(array $file)
    {
        if (empty($file['name'])) {
            return false;
        }
        $ext = self::getSafeName($file['name']);

        if (!Upload::checkFileType($ext)) {
            return false;
        }
        if (self::isImage($file['type']) || self::isImage_ext($ext)) {

            return self::uploadImg($file, $ext);
        }

        return self::uploadOtherFile($file, $ext);

    }

    private static function uploadOtherFile(array $file, string $ext)
    {
        $date = new \Typecho\Date();
        $path = Common::url(
            defined('__TYPECHO_UPLOAD_DIR__') ? __TYPECHO_UPLOAD_DIR__ : self::UPLOAD_DIR,
            defined('__TYPECHO_UPLOAD_ROOT_DIR__') ? __TYPECHO_UPLOAD_ROOT_DIR__ : __TYPECHO_ROOT_DIR__
        ) . '/' . $date->year . '/' . $date->month;

        if (!is_dir($path)) {
            if (!self::makeUploadDir($path)) {
                return false;
            }
        }

        $fileName = sprintf('%u', crc32(uniqid())) . '.' . $ext;
        $path = $path . '/' . $fileName;

        if (isset($file['tmp_name'])) {
            if (!@move_uploaded_file($file['tmp_name'], $path)) {
                return false;
            }
        } elseif (isset($file['bytes'])) {
            if (!file_put_contents($path, $file['bytes'])) {
                return false;
            }
        } elseif (isset($file['bits'])) {
            if (!file_put_contents($path, $file['bits'])) {
                return false;
            }
        } else {
            return false;
        }

        if (!isset($file['size'])) {
            $file['size'] = filesize($path);
        }

        return [
            'name' => $file['name'],
            'path' => (defined('__TYPECHO_UPLOAD_DIR__') ? __TYPECHO_UPLOAD_DIR__ : self::UPLOAD_DIR)
                . '/' . $date->year . '/' . $date->month . '/' . $fileName,
            'size' => $file['size'],
            'type' => $ext,
            'mime' => Common::mimeContentType($path)
        ];
    }

    private static function makeUploadDir(string $path): bool
    {
        $path = preg_replace("/\\\+/", '/', $path);
        $current = rtrim($path, '/');
        $last = $current;

        while (!is_dir($current) && false !== strpos($path, '/')) {
            $last = $current;
            $current = dirname($current);
        }

        if ($last == $current) {
            return true;
        }

        if (!@mkdir($last, 0755)) {
            return false;
        }

        return self::makeUploadDir($path);
    }

    private static function uploadImg(array $file, string $ext)
    {
        $apiVersion = LskyUtils::getPluginConfig('lskypro_api_version');
        $file_tmp = $file['tmp_name'] ?? ($file['bytes'] ?? ($file['bits'] ?? ''));
        $file_name = self::makeSafeName($file['name']);
        if (!@move_uploaded_file($file_tmp, $file_name)) {
            if (!@rename($file_tmp, $file_name)) {
                return false;
            }
        }
        if ($apiVersion == 'v1') {
            $res = LskyAPIV1::img_upload($file_name);
        } else {
            $res = LskyAPIV2::img_upload($file_name);
        }
        
        unlink($file_name);
        if (!$res) {
            return false;
        }
      

        if ($res['status'] === false) {
            return false;
        }
        
        $data = $res['data'];
        return [
            'img_key' => $data['key'],
            'img_id' => $data['md5'],
            'name'   => $data['origin_name'],
            'path'   => $data['links']['url'],
            'size'   => $file['size'],
            'type'   => $data['extension'],
            'mime'   => $data['mimetype'],
			'description'  => $data['mimetype'],
        ];
    }

    public static function attachmentHandle(array $content)
    {
		$arr = unserialize($content['text']);
        if (self::isImage($arr["mime"])) {

            return $content['attachment']->path ?? '';
        }
        $options = Options::alloc();
        return Common::url(
            $content['attachment']->path,
            defined('__TYPECHO_UPLOAD_URL__') ? __TYPECHO_UPLOAD_URL__ : $options->siteUrl
        );
    }

    public static function deleteHandle(array $content): bool
    {
        
		$ext = $content['attachment']->mime;
        if (self::isImage($ext)) {
            $apiVersion = LskyUtils::getPluginConfig('lskypro_api_version');
            if ($apiVersion == 'v1') {
                $res = LskyAPIV1::img_delete($content['attachment']->img_key);
            } else {
                $res = LskyAPIV2::img_delete($content['attachment']->img_key);
            }
            if (!$res) {
                return false;
            }
            if ($res['status'] === true) {
                return true;
            }
            return false;
        }

        return unlink($content['attachment']->path);
    }

    public static function modifyHandle($content, $file)
    {
        if (empty($file['name'])) {

            return false;
        }
        $ext = self::getSafeName($file['name']);
        $mime = $content['attachment']->mime;
        if ($content['attachment']->type != $ext) {

            return false;
        }

        if (!$file['tmp_name'] ?? ($file['bytes'] ?? ($file['bits'] ?? ''))) {

            return false;
        }

        if (self::isImage($mime)) {
            self::deleteHandle($content);

            return self::uploadImg($file, $ext);
        }
      
        return self::uploadOtherFile($file, $ext);
    }

    private static function makeSafeName(string &$name): string
    {
        $name = str_replace(['"', '<', '>', '/', '\\', ':', '*', '?', '|', ' '], '', $name);
        $name = str_replace('\\', '/', $name);
        $name = false === strpos($name, '/') ? ('a' . $name) : str_replace('/', '/a', $name);
        return $name;
    }

    private static function getSafeName(string &$name): string
    {
        $name = str_replace(['"', '<', '>'], '', $name);
        $name = str_replace('\\', '/', $name);
        $name = false === strpos($name, '/') ? ('a' . $name) : str_replace('/', '/a', $name);
        $info = pathinfo($name);
        $name = substr($info['basename'], 1);

        return isset($info['extension']) ? strtolower($info['extension']) : '';
    }

    private static function isImage(string $mimetype): bool
    {
        return strpos($mimetype, 'image') !== false;
    }

    private static function isImage_ext($ext): bool
    {
        $img_ext_arr = array('gif','jpg','jpeg','png','tiff','bmp','ico','psd','webp','JPG','BMP','GIF','PNG','JPEG','ICO','PSD','TIFF','WEBP'); //允许的图片扩展名
        return in_array($ext, $img_ext_arr);
    }


}