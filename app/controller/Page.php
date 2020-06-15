<?php


namespace app\controller;


use Curl\Curl;

class Page
{
    public function qrcode()
    {
        $params   = input('get.');
        $paramStr = http_build_query($params);

        $curl = new Curl();
        $host = env('payment_demo.host');
        $host .= '/payment/qrcodeimg?'.$paramStr;
        $curl->get($host);
        $result = json_decode($curl->response, true);

        if($curl->error_code!=0){
            return "网络错误";
        }
        if($result["code"]!=0) {
            return $result["message"];
        }

        return view('qrcode', [
            'orderId'    => $params["order_id"],
            'qrcode'    => $result["data"],
           ]);
    }
}