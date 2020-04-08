<?php
use \think\facade\Route;
//Token
Route::post('api/token/user', 'api/Token/getToken');

//user
Route::rule('api/user/userinfo', 'api/User/userInfo');
Route::rule('api/user/extension_code', 'api/User/getExtensionCode');
Route::rule('api/user/integral_record', 'api/User/integralRecord');
Route::rule('api/user/collection', 'api/User/collection');
Route::rule('api/user/download_coupon_list', 'api/User/downloadCouponList');
Route::post('api/user/feedback', 'api/user/feedback');//意见反馈
Route::post('api/user/my_coupon', 'api/user/myCoupon');//我领取的优惠券列表
Route::post('api/user/del_coupon', 'api/user/delCoupon');//我领取的优惠券列表
Route::post('api/user/balance_record', 'api/user/balanceRecord');//余额记录
Route::post('api/user/pay_balance', 'api/user/payBalance');//充值余额
Route::post('api/user/balance_withdrawal', 'api/user/balanceWithdrawal');//余额记录
Route::post('api/user/withdrawal_record', 'api/user/withdrawalRecord');//提现记录
Route::post('api/user/get_store_list', 'api/user/getStoreList');//我加入的门店
Route::post('api/user/extension_records', 'api/user/extensionRecords');//推广记录
Route::post('api/user/img', 'api/User/img');


//home
Route::rule('api/home/get_banner', 'api/Home/getBanner');
Route::rule('api/home/get_all_catetgory', 'api/Home/getAllCatetgory');
Route::rule('api/home/store', 'api/Home/storeList');
Route::rule('api/home/message', 'api/Home/message');

//coupon
Route::rule('api/coupon/index', 'api/Coupon/index');
Route::rule('api/coupon/detail', 'api/Coupon/detail');
Route::rule('api/coupon/templat_list', 'api/Coupon/templatList');
Route::rule('api/coupon/add_coupon', 'api/Coupon/addCoupon');
Route::rule('api/coupon/my_release_coupon', 'api/Coupon/myReleaseCoupon');
Route::rule('api/coupon/update_state', 'api/Coupon/updateState');
Route::rule('api/coupon/add_num', 'api/Coupon/addNum');
Route::rule('api/coupon/edit_detail', 'api/Coupon/editDetail');
Route::rule('api/coupon/update_coupon', 'api/Coupon/updateCoupon');
Route::rule('api/coupon/download', 'api/Coupon/download');
Route::rule('api/coupon/delete_download_coupon', 'api/Coupon/deleteDownloadCoupon');
Route::rule('api/coupon/user_receive', 'api/Coupon/userReceive');
//Route::rule('api/coupon/download_list', 'api/Coupon/downloadList');
Route::rule('api/coupon/download_detail', 'api/Coupon/downloadDetail');
Route::rule('api/coupon/receipt_list', 'api/Coupon/receiptList');//优惠券领取列表/下载列表/核销列表
Route::rule('api/coupon/share_coupon', 'api/Coupon/shareCoupon');//商家分享优惠券

//TouristCoupon
Route::rule('api/tourist_coupon/detail', 'api/TouristCoupon/detail');
Route::rule('api/tourist_coupon/receive', 'api/TouristCoupon/receive');//游客领取优惠券
Route::rule('api/tourist_coupon/receive_pay', 'api/TouristCoupon/receivePay');//游客领取优惠券-支付
Route::rule('api/tourist_coupon/pay_coupon_residual_amount', 'api/TouristCoupon/payCouponResidualAmount');//优惠券剩余金额支付
Route::rule('api/tourist_coupon/pay_coupon_residual_amount_pay', 'api/TouristCoupon/payCouponResidualAmountPay');//优惠券剩余金额确认支付
Route::rule('api/tourist_coupon/write_off_detail', 'api/TouristCoupon/writeOffDetail');//核销券详情
Route::rule('api/tourist_coupon/receiving_gift_coupon', 'api/TouristCoupon/receivingGiftCoupon');//领取用户赠送的卡券
Route::rule('api/tourist_coupon/gift_coupon', 'api/TouristCoupon/giftCoupon');//用户赠送卡券
Route::rule('api/tourist_coupon/gift_coupon_detail', 'api/TouristCoupon/giftCouponDetail');//用户赠送卡券详情
Route::rule('api/tourist_coupon/my_coupon_detail', 'api/TouristCoupon/myCouponDetail');//用户赠送卡券详情


//store
Route::rule('api/store/settled_in', 'api/Store/settledIn');
Route::rule('api/store/get_entry_agreement', 'api/Store/getEntryAgreement');
Route::rule('api/store/get_catetgory', 'api/Store/getCatetgory');
Route::rule('api/store/detail', 'api/Store/detail');
Route::rule('api/store/do_collection', 'api/Store/doCollection');
Route::rule('api/store/settled_state', 'api/Store/settledState');
Route::rule('api/store/update', 'api/Store/update');//修改门店信息
Route::rule('api/store/write_off_coupon', 'api/Store/writeOffCoupon');//核销卡券
Route::rule('api/store/marketing_center', 'api/Store/marketingCenter');//营销中心
Route::rule('api/store/release_coupon_list', 'api/store/releaseCouponList');//门店下发布的卡券列表
Route::rule('api/store/user_downLoad_coupon_list', 'api/store/userDownLoadCouponList');//门店下的卡券下载列表
Route::rule('api/store/user_receive_coupon_list', 'api/store/userReceiveCouponList');//门店下的用户领取列表
Route::rule('api/store/use_coupon_list', 'api/store/useCouponList');//门店下的用户使用列表

//upload
Route::rule('api/upload/img', 'api/Upload/img');

//confg
Route::rule('api/config/wx_config', 'api/Config/getWxConfig');

//Integral
Route::rule('api/integral/integral_commodity', 'api/Integral/integralCommodity');
Route::post('api/integral/pay_integral', 'api/Integral/payIntegral');

//gift
Route::post('api/gift/index', 'api/Gift/index');
Route::post('api/gift/detail', 'api/Gift/detail');
Route::post('api/gift/detailed_list', 'api/Gift/detailedList');
Route::post('api/gift/created_package', 'api/Gift/createdPackage');
Route::post('api/gift/delete_package', 'api/Gift/deletePackage');
Route::post('api/gift/get_giving_code', 'api/Gift/getGivingCode');//获取转赠小程序码
//Route::post('api/gift/receive', 'api/Gift/receive');//领取礼包
Route::post('api/gift/pre_receive', 'api/Gift/preReceive');//用户预领取礼包
Route::post('api/gift/get_pre_receive_user', 'api/Gift/getPreReceiveUser');//商家获取预领取用户信息（轮询）
Route::post('api/gift/confirm_receive', 'api/Gift/confirmReceive');//商家确认领取礼包

//help_center
Route::rule('api/help_center/index', 'api/HelpCenter/index');//帮助中心列表

//store_staff
Route::rule('api/store_staff/add_staff_code', 'api/StoreStaff/addStaffCode');//添加员工小程序码
Route::rule('api/store_staff/add_staff', 'api/StoreStaff/addStaff');//添加员工
Route::rule('api/store_staff/index', 'api/StoreStaff/index');//添加员工
Route::rule('api/store_staff/del_staff', 'api/StoreStaff/delStaff');//删除员工

//share_img
Route::rule('api/share_img/coupon', 'api/shareImg/coupon');//优惠券分享



//脚本访问
//Route::get('api/autoscript/releaseinventory', 'api/AutoScript/releaseInventory');






