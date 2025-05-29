<?php
namespace TypechoPlugin\LskyPro\inc;


use Typecho\Widget\Helper\Form;
use Typecho\Widget\Helper\Form\Element\Password;
use Typecho\Widget\Helper\Form\Element\Text;
use Typecho\Widget\Helper\Form\Element\Select;
use Typecho\Widget\Helper\Form\Element\Radio;
use TypechoPlugin\LskyPro\inc\LskyUtils;
use Typecho\Widget\Helper\Form\Element\Hidden;
use Typecho\Widget\Helper\Form\Element\Submit;
use Widget\Options;

class LskyConfig {

    public static function config(Form $form)
    {   

        if (isset($_POST['updateTokens'])) {
            echo '<script>alert("更新成功");</script>';
        }
        $form->setAttribute('id', 'lskypro-config');
        $api_version = new Radio('lskypro_api_version',['v1' => 'v1', 'v2' => 'v2'], 'v2', _t('API版本'), _t('选择图床API版本'));
        $form->addInput($api_version);

        $lskypro_opensource = new Radio('lskypro_opensource', ['1' => '开源版', '0' => '商业版'], '1', _t('是否开源'), _t('LskyPro授权版本'));
        $lskypro_opensource->container->setAttribute('class', 'api-v1');
        $form->addInput($lskypro_opensource);

        $api = new Text('lskypro_api_url', null, 'https://fjwr.xyz/api/v1', _t('API地址'), _t('输入图床API地址'));
        $form->addInput($api);

        $username = new Text('lskypro_username', null, '', _t('用户名'), _t('输入图床用户名'));
        $username->container->setAttribute('class', 'api-v1');
        $username->container->setAttribute('class', 'opensource');
        $form->addInput($username);

        $password = new Password('lskypro_password', null, '', _t('密码'), _t('输入图床密码'));
        $password->container->setAttribute('class', 'api-v1');
        $password->container->setAttribute('class', 'opensource');
        $form->addInput($password);

        $token = new Text('lskypro_token', null, '', _t('Token'), _t('输入图床Token'));
        $token->input->setAttribute('id', 'v1orv2');
        $form->addInput($token);

        $updatetkflag = new Hidden('updateTokens', null, '0');
        $updatetkflag->input->setAttribute('id', 'updateTokens');
        $updatetkflag->container->setAttribute('class', 'opensource');
        $form->addInput($updatetkflag);

        $updateBtn = new Submit(null, null, '更新 Tokens');
        $updateBtn->container->setAttribute('class', 'api-v1');
        $updateBtn->input->setAttribute('type', 'button');
        $updateBtn->input->setAttribute('id', "update-token-btn");
        $updateBtn->input->setAttribute('class', 'btn primary');
        $updateBtn->input->setAttribute('class', 'opensource');
        $form->addItem($updateBtn);

        $permission = new Select('lskypro_permission', ['1' => '公开', '0' => '私密'], '0', _t('权限'), _t('选择图片权限'));
        $form->addInput($permission);
        
        $album_id = new Select('lskypro_album_id', ['-1' => '加载中...'], '', _t('相册'), _t('选择上传目标相册（点击按钮加载）'));
        $album_id->container->setAttribute('class', 'api-v2');
        $album_id->input->setAttribute('id', 'lskypro_album_id');
        $form->addInput($album_id);

        $storage_id = new Select('lskypro_storage_id', ['-1' => '加载中...'], '', _t('存储'), _t('选择上传目标存储（点击按钮加载）'));
        $storage_id->container->setAttribute('class', 'api-v2');
        $storage_id->input->setAttribute('id', 'lskypro_storage_id');
        $form->addInput($storage_id);

        $jsUrl = LskyUtils::getPluginUrl() . '/static/js/config.js';
        echo '<script src="' . $jsUrl . '?v=1.0.0"> </script>';


        $savedAlbumId = null;
        $savedStorageId = null;
        try {
            $savedAlbumId = LskyUtils::getPluginConfig('lskypro_album_id');
        } catch (\Exception $e) {
            $savedAlbumId = '1';
        }
        try {
            $savedStorageId = LskyUtils::getPluginConfig('lskypro_storage_id');
        } catch (\Exception $e) {
            $savedStorageId = '1';
        }
        echo '<script>var savedAlbumId = "'. $savedAlbumId .'"; var savedStorageId = "'. $savedStorageId .'"; </script>';

    }

}