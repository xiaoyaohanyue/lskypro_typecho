<?php

/**
 * Lsky Pro图床全版本上传插件
 *
 * @package LskyPro
 * @author 妖月
 * @version 1.0.1
 * @link https://fjwr.xyz
 */

namespace TypechoPlugin\LskyPro;
include_once __DIR__ . '/autoload.php';
include_once __DIR__ . '/Action.php';
use Typecho\Plugin\PluginInterface;
use Typecho\Widget\Helper\Form;
use TypechoPlugin\LskyPro\inc\LskyUtils;
use TypechoPlugin\LskyPro\inc\LskyConfig;
use Utils\Helper;
use TypechoPlugin\LskyPro\inc\LskyCommon;


if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}


class Plugin implements PluginInterface
{
    public static function activate()
    {
        Helper::addAction('lskypro-ajax', 'LskyPro_Action');
        \Typecho\Plugin::factory('Widget_Upload')->uploadHandle = [LskyCommon::class, 'uploadHandle'];
        \Typecho\Plugin::factory('Widget_Upload')->attachmentHandle = [LskyCommon::class, 'attachmentHandle'];
        \Typecho\Plugin::factory('Widget_Upload')->deleteHandle = [LskyCommon::class, 'deleteHandle'];
        \Typecho\Plugin::factory('Widget_Upload')->modifyHandle = [LskyCommon::class,'modifyHandle'];

        
    }

    public static function deactivate()
    {
        Helper::removeAction('lskypro-ajax');
    }

    public static function config(Form $form)
    {
        LskyConfig::config($form);
    }

    public static function personalConfig(Form $form)
    {
    }

    public static function render()
    {
        LskyUtils::echotop('Lsky Pro图床全版本上传插件');
    }
}
