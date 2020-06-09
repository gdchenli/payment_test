<?php

namespace app\controller;

use app\BaseController;

class Index extends BaseController
{
    //支付机构
    const ALIPAY_ORG = 'alipay';
    const EPAYMENTS_ORG = 'epayments';
    const ALLPAY_ORG = 'allpay';

    //支付方式
    const ALIPAY_PAYMENT = 'alipay_payment';
    const WECHAT_PAYMENT = 'wechat';
    const VTPAYMENT_PAYMENT = 'vtpayment_payment';

    //订单来源
    const PC_HTC_X_TYPE = '1';
    const MOBILE_HTC_X_TYPE = '2';
    const ANDROID_APP_HTC_X_TYPE = '3';
    const IOS_APP_HTC_X_TYPE = '4';
    const WMP_HTC_X_TYPE = '5';
    const WECHAT_MINI_HTC_X_TYPE = '6';
    const ALIPAY_MINI_HTC_X_TYPE = '7';

    public function index()
    {
        $orgCode = [self::ALIPAY_ORG, self::EPAYMENTS_ORG, self::ALLPAY_ORG];
        $methodCode = [
            ['name' => self::ALIPAY_ORG, 'children' =>
                [
                    ['name' => self::ALIPAY_PAYMENT, 'val' => '支付宝']
                ]
            ],
            ['name' => self::EPAYMENTS_ORG, 'children' =>
                [
                    ['name' => self::WECHAT_PAYMENT, 'val' => '微信']
                ]
            ],
            ['name' => self::ALLPAY_ORG, 'children' =>
                [
                    ['name' => self::ALIPAY_PAYMENT, 'val' => '支付宝'],
                    ['name' => self::VTPAYMENT_PAYMENT, 'val' => '银联']
                ],
            ],
        ];
        $orderId = date('YmdHis') . mt_rand(100, 999);

        return view('index', ['orderId' => $orderId, 'orgCode' => $orgCode, 'methodCode' => json_encode($methodCode, JSON_UNESCAPED_UNICODE)]);
    }


    public function pay()
    {
        $param = [];
        $param['org_code'] = input('post.org_code');
        if (!$param['org_code']) {
            return '请选择支付机构';
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

        $param['currency'] = 'AUD';

        $param['user_agent_type'] = self::PC_HTC_X_TYPE;
        if (isMobile()) {
            $param['user_agent_type'] = self::MOBILE_HTC_X_TYPE;
        }
        if (isWeixin()) {
            $param['user_agent_type'] = self::WMP_HTC_X_TYPE;
        }

        $curl = new \Curl\Curl();
        $host = env('payment_demo.host');
        switch ($param['user_agent_type']) {
            case self::PC_HTC_X_TYPE:
                if ($param['org_code'] == 'epayments') {
                    $host .= '/payment/order/qrcode';
                    $curl->post($host, $param);
                    $result = json_decode($curl->response, true);
                    if ($result['code'] == 0) {
                        return '<p style="padding-left:30px">微信支付二维码</p>' .
                            '<p><img src = "' . $result['data'] . '" /></p>';
                    }
                    return $result['message'];
                } else {
                    $host .= '/payment/order/submit';
                    $curl->post($host, $param);
                    $result = json_decode($curl->response, true);
                    if ($result['code'] == 0) {
                        return $result['data'];
                    }
                    return $result['message'];
                }
                break;
            case self::MOBILE_HTC_X_TYPE:
            case self::WMP_HTC_X_TYPE:
                $host .= '/payment/order/submit';
                $curl->post($host, $param);
                $result = json_decode($curl->response, true);
                if ($result['code'] == 0) {
                    return $result['data'];
                }
                return $result['message'];
                break;
            default:
                return '暂时不支持';
        }


    }
}
