<?php

use Phinx\Db\Adapter\MysqlAdapter;
use think\migration\Migrator;
use think\migration\db\Column;

class Coupon extends Migrator
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
        $table = $this->table('coupon', ['engine' => 'InnoDB', 'comment' => '卡券表']);
        $table->addColumn('name', 'string',['limit' => 30, 'default'=>'', 'comment'=>'卡券名称'])
            ->addColumn('logo', 'string',['limit' => 50, 'default'=>'','comment'=>'LOGO'])
            ->addColumn('type', 'integer',['limit' => MysqlAdapter::INT_TINY, 'default'=>'1','comment'=>'1：抢购券 2：促销券'])
            ->addColumn('pattern', 'integer',['limit' => MysqlAdapter::INT_TINY, 'default'=>'1','comment'=>'1：一般模式 2：推广模式'])
            ->addColumn('original_price', 'decimal',['precision' => 10, 'scale' => 2,  'default'=>'0','comment'=>'原价'])
            ->addColumn('buying_price', 'decimal',['precision' => 10, 'scale' => 2,  'default'=>'0','comment'=>'抢购价'])
            ->addColumn('rebate_commission', 'string',['limit' => 10, 'default'=>'0','comment'=>'返利佣金比例'])
            ->addColumn('promotion_commission', 'string',['limit' => 10, 'default'=>'0','comment'=>'推广人佣金比例'])
            ->addColumn('start_time', 'integer',['limit' => 11, 'default'=>'0','comment'=>'有效开始时间'])
            ->addColumn('end_time', 'integer',['limit' => 11, 'default'=>'0','comment'=>'有效结束时间'])
            ->addColumn('instructions', 'string',['limit' => 150, 'default'=>'','comment'=>'使用说明'])
            ->addColumn('total', 'integer',['limit' => 10, 'default'=>'0','comment'=>'卡券总数'])
            ->addColumn('state', 'integer',['limit' => MysqlAdapter::INT_TINY, 'default'=>'1','comment'=>'1：待审核 2：待发布 3：已发布 4：已下架 5：已拒绝'])
            ->addColumn('store_id', 'integer',['limit' => 11, 'default'=>'0','comment'=>'门店id'])
            ->create();
    }
}
