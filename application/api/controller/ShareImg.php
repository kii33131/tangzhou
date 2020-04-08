<?php


namespace app\api\controller;


use app\model\CouponModel;

class ShareImg
{
    public function coupon($id){
        $coupon = CouponModel::alias('c')
            ->field('c.*,s.name store_name')
            ->join('store s','s.id = c.store_id')
            ->get($id);
        if(!$coupon){
            error('卡券不存在',40001);
        }
        $background = env('root_path') . 'public/assets/share_img/coupon_background.jpg';//背景图片
        //商品LOGO
        if(empty($coupon['logo'])){
            $logo = env('root_path') . 'public/assets/share_img/no_picture.png';
        }else{
            $logo = config('upload_file') . $coupon['logo'];
            if(!file_exists($logo)){
                $logo = env('root_path') . 'public/assets/share_img/no_picture.png';
            }
        }
        $fontFile = env('root_path') . 'public/assets/share_img/msyh.ttf';//字体路径

        $couponName = $this->subtext($coupon['name'],8);//卡券名称
        $storeName = $this->subtext($coupon['store_name'],6);//门店名称
        $validity_time = $coupon['end_time'];//有效期
        $buyingPrice = $this->formatPrice($coupon['buying_price']);//价值
        $type = $coupon['type'] == 1 ? '体验/抢购价' : '价值';
        $button = $coupon['type'] == 1 ? '限时抢购' : '立即领取';
        $originalPrice = $this->formatPrice($coupon['original_price']);//原价
        //创建背景
        $backgroundImg=imagecreatefromjpeg($background);
        //创建商品LOGO
        $logoInfo = getimagesize($logo);
        switch ($logoInfo[2]){
            case 1:
                $logoImg = imagecreatefromgif($logo);
                break;
            case 2:
                $logoImg = imagecreatefromjpeg($logo);
                break;
            case 3:
                $logoImg = imagecreatefrompng($logo);
                break;
            default:
                exception('分享图片创建失败');
                break;
        }
        $goodsLogoW = imagesx($logoImg);//商品LOGO宽度
        $goodsLogoH = imagesy($logoImg);//商品LOGO高度

        //复制商品LOGO到背景
        imagecopyresampled($backgroundImg,$logoImg,15,55,0,0,340,340,$goodsLogoW,$goodsLogoH);
        $black = imagecolorallocate($backgroundImg, 0, 0, 0);//黑色
        $red = imagecolorallocate($backgroundImg, 253, 65, 83);//红色
        $white = imagecolorallocate($backgroundImg, 255, 255, 255);//白色
        imagettftext($backgroundImg,34,0,380,100,$black,$fontFile,$couponName);//卡券名称
//        imagettftext($backgroundImg,20,0,15,35,$black,$fontFile,$storeName);//门店名称
//        imagettftext($backgroundImg,12,0,200,105,$black,$fontFile,'有效期至：'.$validity_time);//有效期
        imagettftext($backgroundImg,30,0,400,185,$red,$fontFile,$type);//类型
        imagettftext($backgroundImg,34,0,400,295,$red,$fontFile,'￥' );
        if($type == '体验/抢购价'){
            imagettftext($backgroundImg,62,0,450,295,$red,$fontFile,$buyingPrice);//体验/抢购价
            imagettftext($backgroundImg,26,0,400,370,$black,$fontFile,'原价：￥' . $originalPrice);//原价
            $lineX2 = 540 + strlen($originalPrice) * 22;
            imageline($backgroundImg, 400, 358, $lineX2, 358, $black);
        }else{
            imagettftext($backgroundImg,62,0,450,295,$red,$fontFile,$originalPrice);//价值
        }
        imagettftext($backgroundImg,44,0,290,528,$white,$fontFile,$button);//体验/抢购价
        //告诉浏览器以图像数据来显示
        header("content-type:image/png");
        //输出图像
        imagepng($backgroundImg);
        //关闭图像释放资源
        imagedestroy($backgroundImg);
        exit();
    }



    /**
     * 获取文件扩展名
     * @param $file_name string 文件名
     * @return mixed
     */
    private function getExt($file_name)
    {
        $file_name=basename($file_name);
        $path=parse_url($file_name);
        $str=explode('.',$path['path']);
        return $str[1];
    }

    /**
     * 截取字符串
     * @param $text
     * @param $length
     * @return string
     */
    private function subtext($text, $length)
    {
        if(mb_strlen($text, 'utf8') > $length) {
            return mb_substr($text, 0, $length, 'utf8').'...';
        } else {
            return $text;
        }
    }

    private function formatPrice($price){
        $tempArr = explode('.',$price);
        if(isset($tempArr[1])){
            if($tempArr[1] == 00){
                return $tempArr[0];
            }
            if($tempArr[1] % 10 == 0){
                return $tempArr[0] . '.' . $tempArr[1][0];
            }
        }
        return $price;
    }
}