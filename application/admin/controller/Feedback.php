<?php

namespace app\admin\controller;

use app\model\FeedbackModel;

class Feedback extends Base
{
    public function index(FeedbackModel $feedbackModel)
    {
        $params = $this->request->param();
        $this->checkParams($params);
        $this->feedbacks = $feedbackModel->getList($params, $this->limit);
        return $this->fetch();
    }
}