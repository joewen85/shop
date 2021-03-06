<?php


namespace app\frontend\modules\payment\paymentSettings\shop;


class AlipayPayHjSetting extends BaseSetting
{
    public function canUse()
    {
        $set = \Setting::get('plugin.convergePay_set.alipay.alipay_status');
        return $set;
    }

    public function exist()
    {
        return \Setting::get('plugin.convergePay_set') !== null;
    }
}