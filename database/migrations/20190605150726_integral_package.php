<?php

use think\migration\Migrator;
use think\migration\db\Column;
use Phinx\Db\Adapter\MysqlAdapter;

class IntegralPackage extends Migrator
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

        $table = $this->table('integral_package', ['engine' => 'InnoDB', 'comment' => '积分充值套餐']);
        $table->addColumn('integral', 'integer',['limit' => 11, 'default'=>'0', 'comment'=>'积分'])
            ->addColumn('amount', 'decimal',['precision' => 10, 'scale' => 2,  'default'=>'0','comment'=>'金额'])
            ->create();
    }
}
