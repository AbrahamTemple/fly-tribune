<?php
namespace app\common\validate;

use think\Validate;

class Cate extends Validate
{
    // 验证规则
    protected $rule = [
        'name'  =>  'require|max:25',
        'weigh' =>  'require',
    ];

    // 提示信息
    protected $message = [
        'username.require'  =>  '用户名必填',
        'password.require' =>  '权重必需是数字',
    ];

    // 验证场景
    protected $scene = [
        'edit'  =>  ['name','weigh'],
    ];  
}
?>