<?php

use think\migration\Migrator;
use Phinx\Db\Adapter\MysqlAdapter;

class Config extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('config', ['engine' => 'InnoDB', 'comment' => '小程序基本设置']);
        $table->addColumn('appid', 'string',['limit' => 30, 'default'=>'', 'comment'=>'微信小程序appid'])
            ->addColumn('appsecret', 'string',['limit' => 30, 'default'=>'', 'comment'=>'微信公众号appsecret'])
            ->addColumn('wechat_title', 'string',['limit' => 30, 'default'=>'', 'comment'=>'小程序标题'])
            ->addColumn('platform_name', 'string',['limit' => 30, 'default'=>'', 'comment'=>'平台名称'])
            ->addColumn('logo', 'string',['limit' => 50, 'default'=>'', 'comment'=>'门店LOGO'])
            ->addColumn('platform_phone', 'string',['limit' => 20, 'default'=>'', 'comment'=>'平台联系电话'])
            ->addColumn('introduce', 'string',['limit' => 150, 'default'=>'', 'comment'=>'简介'])
            ->addColumn('entry_agreement', 'text',['comment'=>'入驻协议'])
            ->addColumn('panic_buying_coupon_release_agreement', 'text',['comment'=>'抢购券发布协议'])
            ->addColumn('promotion_coupon_release_agreement', 'text',['comment'=>'促销券发布协议'])
            ->addColumn('entry_amount', 'decimal',['precision' => 10, 'scale' => 2,  'default'=>'0','comment'=>'入驻金额'])
            ->addColumn('entry_gift_points', 'integer',['limit' => 11, 'default'=>'0','comment'=>'入驻赠送积分'])
            ->addColumn('coupon_amount', 'decimal',['precision' => 10, 'scale' => 2,  'default'=>'1','comment'=>'卡券领取金额'])
            ->addColumn('entry_audit', 'integer',['limit' => MysqlAdapter::INT_TINY, 'default'=>'0','comment'=>'入驻自动审核 0：不自动审核 1：自动审核'])
            ->addColumn('coupon_audit', 'integer',['limit' => MysqlAdapter::INT_TINY, 'default'=>'0','comment'=>'优惠券自动审核 0：不自动审核 1：自动审核'])
            ->addColumn('cash_audit', 'integer',['limit' => MysqlAdapter::INT_TINY, 'default'=>'0','comment'=>'提现自动审核 0：不自动审核 1：自动审核'])
            ->addColumn('service_charge', 'string',['limit' => 5, 'default'=>'0', 'comment'=>'提现手续费比例'])
            ->create();
    }
}
