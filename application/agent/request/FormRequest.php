<?php
namespace app\agent\request;

use think\exception\HttpResponseException;
use think\Request;

abstract class FormRequest extends Request
{

    /**
     * FormRequest constructor.
     */
	public function __construct()
	{
		parent::__construct();

		if ($this->withServer($_SERVER)->isAjax(true) && $err = $this->validate()) {
            throw new HttpResponseException(json([
                'code' => 0,
                'msg'  => $err,
                'wait' => 3,
            ]));
        }
	}
}