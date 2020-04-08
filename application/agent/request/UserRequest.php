<?php
namespace app\agent\request;

use app\agent\validates\UserValidate;

class UserRequest extends FormRequest
{
	public function validate()
	{
		return (new UserValidate())->getErrors($this->post());
	}
}