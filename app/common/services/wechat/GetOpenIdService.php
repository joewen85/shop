<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2019/10/16
 * Time: 17:30
 */

namespace app\common\services\wechat;


use app\common\services\wechat\lib\WxPayApi;
use app\common\services\wechat\lib\WxPayConfig;
use app\common\services\wechat\lib\WxPayOpenId;

class GetOpenIdService
{
    /**
     * @param $auth_code
     * @return array|mixed|\stdClass
     * @throws lib\WxPayException
     */
    public static function getOpenid($auth_code)
    {
        $config = new WxPayConfig();
        $config->SetSignType('MD5');
        $request = new WxPayOpenId($config);
        $request->SetAuth_code($auth_code);

        $response = WxPayApi::authcodetoopenid($config, $request);
        $openid = $response['openid'];
        $set = \Setting::get('shop.wechat_set');
        if (!$set['is_independent'] && $set['sub_mchid']) {
            $openid = $response['sub_openid'];
        }

        return $openid;
    }

}