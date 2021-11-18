<?php

namespace app\common\validate;

// 引用tp的验证器
use think\Validate;

class Config extends Validate
{
    // 验证规则
    protected $rule = [
        'title'  =>  'require',
        'key' => 'require|unique:config',
        'type' =>  'require|in:text,file',
        'value' => 'require'
    ];

    // 提示信息
    protected $message = [
        'title.require'  =>  '配置标题必填',
        'key.require' =>  '配置名称必填',
        'key.unique' =>  '配置名称已存在，请重新输入',
        'type.require' =>  '配置类型必选',
        'value.require' => '配置的值必填'
    ];

    // 验证场景
    protected $scene = [
    ];  
}