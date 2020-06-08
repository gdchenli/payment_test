<?php

namespace app\controller;

use app\BaseController;

class Index extends BaseController
{
    public function index()
    {
        $orgCode = ['alipay', 'epayments', 'allpay'];
        $methodCode = [
            ['name' => 'alipay', 'children' =>
                [
                    ['name' => 'alipay_payment', 'val' => '支付宝']
                ]
            ],
            ['name' => 'epayments', 'children' =>
                [
                    ['name' => 'wechat', 'val' => '微信']
                ]
            ],
            ['name' => 'allpay', 'children' =>
                [
                    ['name' => 'alipay_payment', 'val' => '支付宝'],
                    ['name' => 'vtpayment_payment', 'val' => '银联']
                ],
            ],
        ];
        $orderId = date('YmdHis') + mt_rand(100, 999);

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

        $param['user_agent_type'] = '1';
        if (isMobile()) {
            $param['user_agent_type'] = '2';
        }

        $curl = new \Curl\Curl();
        $host = env('payment_demo.host');
        $curl->post($host . '/payment/order/submit', $param);
        $result = json_decode($curl->response, true);
        if ($result['code'] == 0) {
            return $result['data'];
        }

        return $result['message'];

    }
}
