<?php

namespace app\controller;

use app\BaseController;
use Curl\Curl;


class Index extends BaseController
{
    //支付机构
    const ALIPAY_ORG = 'alipay';
    const EPAYMENTS_ORG = 'epayments';
    const ALLPAY_ORG = 'allpay';
    const JD_ORG = 'jd';

    //支付方式
    const ALIPAY_PAYMENT = 'alipay_payment';
    const WECHAT_PAYMENT = 'wechat';
    const VTPAYMENT_PAYMENT = 'vtpayment_payment';
    const JD_PAYMENT = 'jd_payment';

    //订单来源
    const PC_HTC_X_TYPE = '1';          //pc
    const MOBILE_HTC_X_TYPE = '2';      //移动端
    const ANDROID_APP_HTC_X_TYPE = '3'; //安卓app
    const IOS_APP_HTC_X_TYPE = '4';     //ios app
    const WMP_HTC_X_TYPE = '5';         //微信浏览器
    const WECHAT_MINI_HTC_X_TYPE = '6'; //微信小程序
    const ALIPAY_MINI_HTC_X_TYPE = '7'; //支付宝小程序

    public function index()
    {
        $orgCode    = [self::ALIPAY_ORG, self::EPAYMENTS_ORG, self::ALLPAY_ORG, self::JD_ORG];
        $methodCode = [
            ['name' => self::ALIPAY_ORG, 'children' => [['name' => self::ALIPAY_PAYMENT, 'val' => '支付宝', 'currency' => 'AUD']]],
            ['name' => self::EPAYMENTS_ORG, 'children' => [['name' => self::WECHAT_PAYMENT, 'val' => '微信', 'currency' => 'NZD']]],
            ['name' => self::ALLPAY_ORG, 'children' =>
                [
                    ['name' => self::ALIPAY_PAYMENT, 'val' => '支付宝', 'currency' => 'JPY'],
                    ['name' => self::VTPAYMENT_PAYMENT, 'val' => '银联', 'currency' => 'CNY']
                ],
            ],
            ['name' => self::JD_ORG, 'children' => [['name' => self::JD_PAYMENT, 'val' => '京东支付', 'currency' => 'CNY']]],
        ];
        $orderId    = date('YmdHis') . mt_rand(100, 999);

        return view('index', [
            'orderId'    => $orderId,
            'orgCode'    => $orgCode,
            'methodCode' => json_encode($methodCode, JSON_UNESCAPED_UNICODE)]);
    }


    public function pay()
    {
        $param             = [];
        $param['org_code'] = input('post.org_code');
        if (!$param['org_code']) {
            return '请选择支付机构';
        }
        if ($param['org_code'] == self::JD_ORG) {
            $param['user_id'] = 1;
        }

        $param['method_code'] = input('post.method_code');
        if (!$param['method_code']) {
            return '请选择支付方式';
        }

        $param['order_id'] = input('post.order_id');
        if (!$param['order_id']) {
            return '请输入订单号';
        }

        $param['total_fee'] = input('post.total_fee');
        if (!$param['total_fee']) {
            return '支付金额';
        }

        $param['currency'] = input('post.currency');;

        $param['user_agent_type'] = self::PC_HTC_X_TYPE;
        if (isMobile()) {
            $param['user_agent_type'] = self::MOBILE_HTC_X_TYPE;
        }
        if (isWeiXin()) {
            $param['user_agent_type'] = self::WMP_HTC_X_TYPE;
        }

        $curl = new Curl();
        $host = env('payment_demo.host');
        $host .= '/payment/pay';
        $curl->post($host, $param);
        $result = json_decode($curl->response, true);

        if ($curl->error_code != 0) {
            return "网络错误";
        }
        if ($result["code"] != 0) {
            return $result["message"];
        }

        return redirect($result["data"]);
    }
}
