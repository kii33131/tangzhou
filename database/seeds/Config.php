<?php

use think\migration\Seeder;

class Config extends Seeder
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        $data = [
            'id' => '1',
            'entry_agreement' => '',
            'panic_buying_coupon_release_agreement' => '',
            'promotion_coupon_release_agreement' => ''
        ];

        $this->table('config')->insert([$data])->save();
    }
}