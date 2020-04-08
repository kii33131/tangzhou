<?php

use think\migration\Migrator;
use think\migration\db\Column;

class IndustryCategory extends Migrator
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
        $table = $this->table('industry_category', ['engine' => 'InnoDB', 'comment' => '行业分类']);
        $table->addColumn('name', 'string',['limit' => 50, 'default'=>'', 'comment'=>'分类名称'])
            ->addColumn('pid', 'integer',['limit' => 11, 'default'=>'0','comment'=>'父级分类ID'])
            ->addColumn('created_at', 'timestamp', [ 'default' => 'CURRENT_TIMESTAMP','comment' => '创建时间'])
            ->create();
    }
}
