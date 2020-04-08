<?php


namespace app\api\controller;


use app\service\QrcodeServer;

class QrCode
{
    /**
     * 生成二维码
     * @param string $content 链接
     */
    public function code($content = ''){
        $qr_code = new QrcodeServer();
        $qr_img = $qr_code->createServer($content);
        echo $qr_img;
        exit();
    }
}