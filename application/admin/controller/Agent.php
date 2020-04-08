<?php

namespace app\admin\controller;


use app\admin\request\AgentRequest;
use app\enum\AgentEnum;
use app\model\AgentModel;
use app\model\MemberModel;
use think\Db;

class Agent extends Base
{
    public function index(AgentModel $AgentModel)
    {
        $this->assign('agentLevel',AgentEnum::LEVEL);
        $params = $this->request->param();
        $this->checkParams($params);
        $this->agents  = $AgentModel->getAllList($params,$this->limit);
        return $this->fetch();
    }

    /**
     * 充值积分给代理点
     * @param $id int 代理点id
     * @param $integral int 充值的积分
     */
    public function rechargeIntegral($id,$integral){
        if(!preg_match("/^[1-9][0-9]*$/",$integral)){
            $this->error('请输入正确的数值');
        }
        Db::startTrans();
        try{
            AgentModel::changeIntegral($id,$integral,1,1);
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success('充值成功', url('Agent/index'));
    }


    public function create(AgentModel $agentModel, AgentRequest $request){
        if ($request->isPost()) {
            $data = $request->post();
            if(empty($data['password']) || empty($data['relpassword'])){
                $this->error('密码不得为空');
            }
            if($data['password']!=$data['relpassword']){
                $this->error('两次密码输入不一致');
            }
            $agent=AgentModel::where([
                "account"=>$data['account'],
                'is_delete' => 0
            ])->find();
            if($agent){
                $this->error('账号重复');
            }
            //代理级别
            $data['level'] = 1;
            if(!empty($data['city'])){
                $data['level'] = 2;
                if(!empty($data['district'])){
                    $data['level'] = 3;
                }else{
                    $data['district'] = '';
                }
            }else{
                $data['city'] = '';
                $data['district'] = '';
            }
            if(AgentModel::regionIsExist($data)){
                $this->error('同一个区域只能设置一个代理');
            }
            $data['password'] =md5($data['password']);
            $data['user_id'] = session('user.id');
            $agentModel->store($data) ? $this->success('添加成功', url('Agent/create')) : $this->error('添加失败');
        }
        return $this->fetch();
    }

    public function edit(AgentModel $agentModel, AgentRequest $request){
        $agent_id= $this->request->param('id');
        if (!$agent_id) {
            $this->error('不存在的数据');
        }
        $result=$agentModel->findBy($agent_id);
        if (!$result) {
            $this->error('不存在的数据');
        }
        if ($request->isPost()) {
            $data = $request->post();
            if(!empty($data['password'])){
                if($data['password']!=$data['relpassword']){
                    $this->error('两次密码输入不一致');
                }
                $data['password'] =md5($data['password']);
            }else{
                unset($data['password']);
            }
            $agent=$agentModel::where([
                ["account",'=',$data['account']],
                ['id','<>',$agent_id],
                ['is_delete','=',0]
            ])->find();
            if($agent){
                $this->error('账号重复');
            }
            if(AgentModel::regionIsExist([
                'province' => $data['province'],
                'city' => $data['city'],
                'district' => $data['district'],
                'id' => $agent_id
            ])){
                $this->error('同一个区域只能设置一个代理');
            }
            //代理级别
            $data['level'] = 1;
            if(!empty($data['city'])){
                $data['level'] = 2;
                if(!empty($data['district'])){
                    $data['level'] = 3;
                }else{
                    $data['district'] = '';
                }
            }else{
                $data['city'] = '';
                $data['district'] = '';
            }
            $data['user_id'] = session('user.id');

            if($agentModel->updateBy($data['id'], $data) !== false){
                $this->success('编辑成功', url('Agent/index'));
            }else{
                $this->error('');
            }
        }
        $this->assign('agent',$result);
        return $this->fetch();
    }

    public function delete(AgentModel $agentModel){
        $agentId = $this->request->post('id');
        if (!$agentId) {
            $this->error('不存在数据');
        }
        $agentModel->where('id',$agentId)->update(['is_delete'=>1]);
        $this->success('删除成功', url('Agent/index'));
    }

    public function chooseWechat(){
        $params = $this->request->param();
        $this->checkParams($params);
        $memberModel = new MemberModel();
        $this->assign('members',$memberModel->getList($params,$this->limit));
        return $this->fetch();
    }

}