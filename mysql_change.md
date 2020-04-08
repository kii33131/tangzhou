##2019-06-12 huiting
#mysql

```
ALTER TABLE `cms_store`
MODIFY COLUMN `end_hours`  time NULL DEFAULT '00:00:00' COMMENT '营业结束时间' AFTER `start_hours`,
ADD COLUMN `user_id`  int(11) NOT NULL DEFAULT 0 COMMENT '用户id' AFTER `id`,
ADD INDEX `user_id` (`user_id`) USING BTREE ;

CREATE TABLE `cms_banner` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `img3` varchar(255) NOT NULL DEFAULT '',
  `img2` varchar(255) NOT NULL DEFAULT '',
  `img1` varchar(255) NOT NULL DEFAULT '' COMMENT '图片地址',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '跳转地址',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '0内部跳转1外部跳转',
  `state` tinyint(2) NOT NULL DEFAULT '1' COMMENT '1启用0禁用',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `city` varchar(50) NOT NULL DEFAULT '' COMMENT '城市名称',
  `province` varchar(50) NOT NULL DEFAULT '' COMMENT '省份',
  `area` varchar(50) NOT NULL DEFAULT '' COMMENT '区',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;



ALTER TABLE `cms_coupon`
ADD COLUMN `industry_category_id`  int(11) NOT NULL DEFAULT 0 COMMENT '行业分类id' AFTER `store_id`;

ALTER TABLE `cms_store`
ADD INDEX `industry_category_id` (`industry_category_id`) USING BTREE ;

ALTER TABLE `cms_store`
ADD COLUMN `longitude`  varchar(255) NOT NULL DEFAULT '' COMMENT '经度' AFTER `reject_reason`,
ADD COLUMN `latitude`  varchar(255) NOT NULL DEFAULT '' COMMENT '纬度' AFTER `longitude`;

ALTER TABLE `cms_member`
ADD COLUMN `longitude`  varchar(255) NOT NULL DEFAULT '' COMMENT '经度' AFTER `create_time`,
ADD COLUMN `latitude`  varchar(255) NOT NULL DEFAULT '' COMMENT '纬度' AFTER `longitude`;

ALTER TABLE `cms_store`
ADD COLUMN `store_mobile`  varchar(50) NOT NULL DEFAULT '' COMMENT '门店电话' AFTER `latitude`;
    
ALTER TABLE `cms_coupon`
ADD COLUMN `template_id`  int(11) NOT NULL DEFAULT 0 COMMENT '模板id' AFTER `industry_category_id`;

ALTER TABLE `cms_coupon`
MODIFY COLUMN `state`  tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '1：待审核   2：待发布     3：已发布     4：已下架     5：已拒绝   6:删除 7:撤销发布' AFTER `total`;


CREATE TABLE `cms_template` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '名称',
  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '内内容',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `state` tinyint(2) NOT NULL DEFAULT '0' COMMENT '0正常1禁用',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='模板表';




CREATE TABLE `cms_integral_balance` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `value` int(11) NOT NULL DEFAULT '0' COMMENT '变化值',
  `type` tinyint(2) DEFAULT '0' COMMENT '1充值2消费',
  `reason` varchar(50) NOT NULL DEFAULT '' COMMENT '原因',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='余额变化表';


CREATE TABLE `cms_user_download_coupon` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `coupon_id` int(11) NOT NULL DEFAULT '0' COMMENT '优惠券id',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `num` int(10) NOT NULL DEFAULT '0' COMMENT '数量',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `coupon_id` (`coupon_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商户下载优惠券表';



CREATE TABLE `cms_user_coupon` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `coupon_id` int(11) NOT NULL DEFAULT '0' COMMENT '优惠券id',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `state` tinyint(2) DEFAULT '0' COMMENT '1正常2已核销',
  `download_coupon_id` int(11) NOT NULL DEFAULT '0' COMMENT '下载优惠券id',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `download_coupon_id` (`download_coupon_id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `coupon_id` (`coupon_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户优惠券表';



CREATE TABLE `cms_user_collection` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL DEFAULT '0' COMMENT '店铺id',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `state` tinyint(2) DEFAULT '0' COMMENT '1正常2取消',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `store_id` (`store_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='店铺收藏表';


CREATE TABLE `cms_gift_package` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '名称',
  `gift` varchar(50) NOT NULL DEFAULT '' COMMENT '实物奖励',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `state` tinyint(2) DEFAULT '0' COMMENT '1正常2使用3删除',
  `pay_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '线下支付金额',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '发布数量',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT '0' COMMENT '剩余库存',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='礼包表';




CREATE TABLE `cms_gift_package_detail` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `coupon_id` int(11) NOT NULL DEFAULT '0',
  `state` tinyint(2) DEFAULT '0' COMMENT '0正常1删除',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '发布数量',
  `stock` int(11) NOT NULL DEFAULT '0' COMMENT '剩余库存',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `gift_package_id` int(11) NOT NULL DEFAULT '0' COMMENT 'gift_package_id',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `coupon_id` (`coupon_id`) USING BTREE,
  KEY `gift_package_id` (`gift_package_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='礼包明细表';


```

##2019-06-13 qingmeng
#mysql

```

ALTER TABLE `cms_coupon` ADD COLUMN `stock` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '剩余量' AFTER `template_id`;

CREATE TABLE `cms_integral_record`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `member_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  `store_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '门店id',
  `integral` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '积分变动数量',
  `residual_integral` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '剩余积分',
  `amount` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '充值金额',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '类型  1：充值   2：消费',
  `mode` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '消费方式  1：下载抢购券   2：下载促销券',
  `create_time` int(11) NOT NULL DEFAULT 0 COMMENT '充值/消费时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

ALTER TABLE `cms_store` ADD COLUMN `is_pay` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否支付   0：未支付   1：已支付' AFTER `store_mobile`;

ALTER TABLE `cms_store` ADD COLUMN `is_delete` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除   0：未删除     1：已删除' AFTER `is_pay`;

ALTER TABLE `cms_store` MODIFY COLUMN `apply_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '申请时间' AFTER `phone`;

ALTER TABLE `cms_store` MODIFY COLUMN `end_hours` time(0) NOT NULL DEFAULT '00:00:00' COMMENT '营业结束时间' AFTER `start_hours`;

DROP TABLE IF EXISTS `cms_member`;
CREATE TABLE `cms_member` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `openid` varchar(50) NOT NULL DEFAULT '' COMMENT '微信openid',
  `picture` varchar(100) NOT NULL DEFAULT '' COMMENT '用户头像',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '昵称',
  `balance` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户余额',
  `integral` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户积分',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '注册时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='用户表';

  
ALTER TABLE `cms_config` CHANGE `appsecret` `appsecret` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '微信公众号appsecret';

ALTER TABLE `cms_member` ADD UNIQUE (`openid`);

```
##2019-06-14 qingmeng
#mysql

```
ALTER TABLE `cms_industry_category` ADD `icon` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '图标' AFTER `name`;

```

##2019-06-15 qingmeng
#mysql

```
ALTER TABLE `cms_member` CHANGE `picture` `picture` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户头像';

ALTER TABLE `cms_store` CHANGE `is_pay` `is_pay` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否支付 0：未支付 1：已支付';

ALTER TABLE `cms_config` ADD `mch_id` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '商户号' AFTER `appsecret`;

ALTER TABLE `cms_config` ADD `pay_key` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '支付密钥' AFTER `appsecret`;

ALTER TABLE `cms_store` ADD `order_no` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '订单号' AFTER `store_mobile`;
```
##2019-06-16 qingmeng
#mysql

```
ALTER TABLE `cms_user_collection` DROP `state`;
```
##2019-06-17 qingmeng
#mysql

```
ALTER TABLE `cms_banner` ADD `imgs` VARCHAR(200) NOT NULL DEFAULT '' COMMENT '图片' AFTER `area`;

ALTER TABLE `cms_banner`
  DROP `img3`,
  DROP `img2`,
  DROP `img1`,
  DROP `url`,
  DROP `type`,
  DROP `state`,
  DROP `sort`;
  DROP `updated_at`;
  
ALTER TABLE `cms_banner` CHANGE `created_at` `create_time` INT(11) NULL DEFAULT '0';

ALTER TABLE `cms_banner` CHANGE `area` `district` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '区';

ALTER TABLE `cms_member` ADD `extension_code` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '推广码' AFTER `integral`;

ALTER TABLE `cms_store` CHANGE `exhibition` `exhibition` VARCHAR(300) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '[]' COMMENT '商家店铺图';

ALTER TABLE `cms_banner` CHANGE `imgs` `imgs` VARCHAR(300) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '[]' COMMENT '图片';

ALTER TABLE `cms_template`
  DROP `created_at`,
  DROP `updated_at`,
  DROP `state`;
  
ALTER TABLE `cms_template` ADD `create_time` INT(11) NOT NULL DEFAULT '0' COMMENT '创建时间' AFTER `content`;

ALTER TABLE `cms_template` ADD `user_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '管理员id' AFTER `content`;

ALTER TABLE `cms_coupon` DROP `template_id`;

ALTER TABLE `cms_coupon` DROP `industry_category_id`;

ALTER TABLE `cms_coupon` ADD `create_time` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间' AFTER `stock`;

###2019-06-19 huiting
```$xslt
CREATE TABLE `cms_integral_order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `store_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '门店id',
  `integral` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '充值积分',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '充值金额',
  `state` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类型  1：支付   0：未支付',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '下单时间',
  `order_no` varchar(50) NOT NULL DEFAULT '' COMMENT '订单号',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `cms_member_request_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '请求url',
  `parameters` varchar(255) NOT NULL DEFAULT '' COMMENT '请求参数',
  `create_time` datetime DEFAULT NULL COMMENT '请求时间',
  `method` varchar(255) DEFAULT '' COMMENT '请求方式',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `cms_user_download_coupon`
ADD COLUMN `store_id`  int(11) NOT NULL DEFAULT 0 AFTER `num`;

ALTER TABLE `cms_member_request_log`
ADD COLUMN `return_data`  varchar(255) NOT NULL DEFAULT '' AFTER `method`;


ALTER TABLE `cms_member_request_log`
MODIFY COLUMN `return_data`  text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL AFTER `method`;





```

###2019-06-20 qingmeng
```
ALTER TABLE `cms_store` DROP `extension_code`;


```

###2019-06-21 qingmeng
```
ALTER TABLE `cms_user_download_coupon` CHANGE `created_at` `create_time` INT(11) UNSIGNED NULL DEFAULT '0' COMMENT '创建时间';
ALTER TABLE `cms_user_download_coupon` CHANGE `updated_at` `update_time` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '修改时间';
ALTER TABLE `cms_user_download_coupon` ADD `is_delete` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否删除 0：未删除 1：已删除' AFTER `store_id`;
ALTER TABLE `cms_user_download_coupon` CHANGE `num` `total` INT(10) NOT NULL DEFAULT '0' COMMENT '数量';
ALTER TABLE `cms_user_download_coupon` ADD `stock` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '剩余量' AFTER `total`;
```
###2019-06-22 huiting
```$xslt

ALTER TABLE `cms_coupon`
ADD INDEX `store_id` (`store_id`) USING BTREE ;

ALTER TABLE `cms_integral_order`
ADD INDEX `member_id` (`member_id`) USING BTREE ,
ADD INDEX `store_id` (`store_id`) USING BTREE ;

ALTER TABLE `cms_integral_record`
ADD INDEX `member_id` (`member_id`) USING BTREE ,
ADD INDEX `store_id` (`store_id`) USING BTREE ;
CREATE TABLE `cms_help_center` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '名称',
  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '内容',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '管理员id',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='帮助中心表';
```
###2019-06-24 huiting

````$xslt
CREATE TABLE `cms_agent` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `phone` int(20) NOT NULL DEFAULT '0' COMMENT '电话号码',
  `password` varchar(50) NOT NULL DEFAULT '' COMMENT '密码',
  `province` varchar(50) NOT NULL DEFAULT '',
  `city` varchar(50) NOT NULL DEFAULT '' COMMENT '市',
  `district` varchar(50) NOT NULL DEFAULT '',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '联系人',
  `account` varchar(255) NOT NULL DEFAULT '' COMMENT '帐号',
  `pumping_ratio` varchar(50) NOT NULL DEFAULT '' COMMENT '抽水比例',
  `residence_rebate` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='代理表';


````
###2019-06-24 qingmeng
```
ALTER TABLE `cms_gift_package_detail` ADD `download_coupon_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '下载券id' AFTER `coupon_id`;

ALTER TABLE `cms_integral_record` CHANGE `mode` `mode` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '消费方式 1：下载抢购券 2：下载促销券 3:积分充值 4：入驻赠送积分';
```
###2019-06-24 qingmeng
```
CREATE TABLE `cms_feedback` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `phone` varchar(20) NOT NULL COMMENT '手机号',
  `content` varchar(500) NOT NULL DEFAULT '' COMMENT '内容',
  `email` varchar(50) NOT NULL DEFAULT '' COMMENT '邮箱',
  `qq` varchar(20) NOT NULL DEFAULT '' COMMENT 'QQ',
  `user_id` int(11) unsigned NOT NULL COMMENT '用户id',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='意见反馈表';
```

###2019-09-25
````$xslt
CREATE TABLE `cms_user_coupon_order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '游客id',
  `coupon_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '优惠券id',
  `download_coupon_id` int(11) NOT NULL DEFAULT '0' COMMENT '下载券id',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '充值金额',
  `state` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类型  1：支付   0：未支付',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '下单时间',
  `order_no` varchar(50) NOT NULL DEFAULT '' COMMENT '订单号',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '领取数量',
  `expiration_time` int(11) NOT NULL DEFAULT '0' COMMENT '过期时间',
  `is_expiration` tinyint(2) NOT NULL DEFAULT '0' COMMENT '0未过期1已过期',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `member_id` (`member_id`) USING BTREE,
  KEY `coupon_id` (`coupon_id`) USING BTREE,
  KEY `download_coupon_id` (`download_coupon_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;


````

###2019-09-25 qingmeng
````
ALTER TABLE `cms_agent` ADD `level` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '级别 1：省代 2：市代 3：区代' AFTER `district`;

ALTER TABLE `cms_agent` ADD `is_delete` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否删除 0：未删除 1：已删除' AFTER `residence_rebate`;

ALTER TABLE `cms_user_coupon` ADD `amount` DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT '0' COMMENT '领取金额' AFTER `create_time`;

CREATE TABLE `chongdianbao`.`cms_platform` ( `id` INT UNSIGNED NOT NULL AUTO_INCREMENT , `balance` DECIMAL(10,2) NOT NULL DEFAULT '0' COMMENT '平台余额' , PRIMARY KEY (`id`)) ENGINE = InnoDB COMMENT = '平台表';

CREATE TABLE `chongdianbao`.`cms_store_staff` ( `id` INT UNSIGNED NOT NULL AUTO_INCREMENT , `store_id` INT(11) UNSIGNED NOT NULL COMMENT '门店id' , `user_id` INT(11) UNSIGNED NOT NULL COMMENT '用户id' , `create_time` INT(11) NOT NULL DEFAULT '0' , PRIMARY KEY (`id`)) ENGINE = InnoDB COMMENT = '门店员工表';

CREATE TABLE `chongdianbao`.`cms_platform_balance_order` ( `id` INT UNSIGNED NOT NULL AUTO_INCREMENT , `amount` DECIMAL(10,2) NOT NULL COMMENT '充值金额' , `pay_type` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '充值方式 1：微信支付' , `user_id` INT(11) NOT NULL COMMENT '充值人员' , `create_time` INT(11) NOT NULL DEFAULT '0' COMMENT '充值时间' , `order_no` VARCHAR(50) NOT NULL COMMENT '订单号' , `is_pay` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '未支付' , PRIMARY KEY (`id`)) ENGINE = InnoDB COMMENT = '平台余额订单表';

ALTER TABLE `cms_user_coupon` ADD `receive_user_id` INT(11) NOT NULL COMMENT '领取人用户ID' AFTER `user_id`;

ALTER TABLE `cms_user_coupon` ADD `residual_amount` DECIMAL(10,2) NOT NULL DEFAULT '0' COMMENT '剩余支付金额' AFTER `amount`;

ALTER TABLE `cms_user_coupon` ADD `residual_is_pay` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '剩余金额是否支付 0：否 1：是' AFTER `residual_amount`;

CREATE TABLE `chongdianbao`.`cms_user_coupon_residual_amount_order` ( `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT , `user_coupon_id` INT(11) NOT NULL COMMENT '用户卡券ID' , `is_pay` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否支付 0：未支付 1：已支付' , `order_no` VARCHAR(50) NOT NULL COMMENT '订单号' , `create_time` INT(11) NOT NULL DEFAULT '0' , PRIMARY KEY (`id`)) ENGINE = InnoDB COMMENT = '用户卡券剩余支付金额订单表';

ALTER TABLE `cms_user_coupon_residual_amount_order` ADD `amount` DECIMAL(10,2) NOT NULL DEFAULT '0' COMMENT '支付金额' AFTER `user_coupon_id`;

ALTER TABLE `cms_user_coupon` ADD `is_delete` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否删除 0：未删除 1：已删除' AFTER `update_time`;

ALTER TABLE `cms_user_coupon` ADD `code` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '核销码' AFTER `download_coupon_id`;
````



