<?php
namespace app\admin\controller;

use app\admin\request\ConfigRequest;
use app\model\ConfigModel;

class Config extends Base
{
    public function index(ConfigModel $config,ConfigRequest $request)
    {
        if ($request->isPost()) {
            $data = $request->post();
            if(empty($data['entry_audit']) || $data['entry_audit'] != 1){
                $data['entry_audit'] = 0;
            }
            if(empty($data['coupon_audit']) || $data['coupon_audit'] != 1){
                $data['coupon_audit'] = 0;
            }
            if(empty($data['cash_audit']) || $data['cash_audit'] != 1){
                $data['cash_audit'] = 0;
            }
            if($config->updateBy(1, $data) !== false){
                $this->success('保存成功', url('Config/index'));
            }else{
                $this->error('');
            }
        }
        $configInfo = $config->findBy(1);
        $this->config = $configInfo;
        return $this->fetch();
    }
}