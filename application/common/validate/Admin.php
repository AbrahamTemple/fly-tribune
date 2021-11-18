<?php
namespace app\common\validate;

use think\Validate;

class Admin extends Validate
{
    // 验证规则
    protected $rule = [
        'username'  =>  'require|max:25',
        'password' =>  'require',
		'encryption' => 'length:32,32',
    ];

    // 提示信息
    protected $message = [
        'username.require'  =>  '用户名必填',
        'password.require' =>  '密码必填',
        'password.length' =>  '密码加密失败',
    ];

    // 验证场景
    protected $scene = [
        'add'   =>  ['username','password','encryption'],
        'edit'  =>  ['encryption'],
    ];  
}
?>