<?php

use Phinx\Db\Adapter\MysqlAdapter;
use think\migration\Migrator;
use think\migration\db\Column;

class Store extends Migrator
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
        $table = $this->table('store', ['engine' => 'InnoDB', 'comment' => '门店']);
        $table->addColumn('name', 'string',['limit' => 30, 'default'=>'', 'comment'=>'门店名称'])
            ->addColumn('industry_category_id', 'integer',['limit' => 11, 'default'=>'0','comment'=>'行业分类id'])
            ->addColumn('logo', 'string',['limit' => 50, 'default'=>'','comment'=>'LOGO'])
            ->addColumn('province', 'string',['limit' => 20, 'default'=>'','comment'=>'省份'])
            ->addColumn('city', 'string',['limit' => 20, 'default'=>'','comment'=>'城市'])
            ->addColumn('district', 'string',['limit' => 20, 'default'=>'','comment'=>'区域'])
            ->addColumn('address', 'string',['limit' => 50, 'default'=>'','comment'=>'地址'])
            ->addColumn('contacts', 'string',['limit' => 20, 'default'=>'','comment'=>'联系人'])
            ->addColumn('phone', 'string',['limit' => 20, 'default'=>'','comment'=>'手机号'])
            ->addColumn('apply_time', 'integer',['limit' => 11, 'default'=>'0','comment'=>'申请时间'])
            ->addColumn('entry_time', 'integer',['limit' => 11, 'default'=>'0','comment'=>'入驻时间'])
            ->addColumn('entry_fee', 'decimal',['precision' => 10, 'scale' => 2,  'default'=>'0','comment'=>'入驻费用'])
            ->addColumn('recommender_id', 'integer',['limit' => 11, 'default'=>'0','comment'=>'推荐人id'])
            ->addColumn('business_license', 'string',['limit' => 50, 'default'=>'','comment'=>'营业执照'])
            ->addColumn('id_card_positive', 'string',['limit' => 50, 'default'=>'','comment'=>'身份证正面'])
            ->addColumn('id_card_back', 'string',['limit' => 50, 'default'=>'','comment'=>'身份证反面'])
            ->addColumn('start_hours', 'time',[ 'default'=>'00:00:00','comment'=>'营业开始时间'])
            ->addColumn('end_hours', 'time',[ 'default'=>'00:00:00','comment'=>'营业结束时间'])
            ->addColumn('introduce', 'string',['limit' => 150, 'default'=>'','comment'=>'门店简介'])
            ->addColumn('exhibition', 'string',['limit' => 300, 'default'=>'','comment'=>'商家店铺图'])
            ->addColumn('extension_code', 'string',['limit' => 20, 'default'=>'','comment'=>'推广码'])
            ->addColumn('state', 'integer',['limit' => MysqlAdapter::INT_TINY, 'default'=>'1','comment'=>'1：待审核 2：审核通过 3：审核拒绝'])
            ->addColumn('reject_reason', 'string',['limit' => 200, 'default'=>'','comment'=>'拒绝原因'])
            ->create();
    }
}
