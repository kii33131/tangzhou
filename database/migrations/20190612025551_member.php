<?php

use Phinx\Db\Adapter\MysqlAdapter;
use think\migration\Migrator;
use think\migration\db\Column;

class Member extends Migrator
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
        $table = $this->table('member', ['engine' => 'InnoDB', 'comment' => '用户表']);
        $table->addColumn('openid', 'string',['limit' => 50, 'default'=>'', 'comment'=>'微信openid'])
            ->addColumn('picture', 'string',['limit' => 100, 'default'=>'', 'comment'=>'用户头像'])
            ->addColumn('name', 'string',['limit' => 50, 'default'=>'', 'comment'=>'昵称'])
            ->addColumn('create_time', 'integer',['limit' => 11, 'default'=>'0','comment'=>'注册时间'])
            ->create();
    }
}
