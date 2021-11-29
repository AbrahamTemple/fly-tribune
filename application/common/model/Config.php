<?php

namespace app\common\model;

use think\Model;
use traits\model\SoftDelete;

class Config extends Model
{
    // 引用软删除
    use SoftDelete;
    //表名
    protected $table = 'pre_config';

    //指定一个自动设置的时间字段
    //开启自动写入
    protected $autoWriteTimestamp = true; 

    //设置字段的名字
    protected $createTime = false; //插入的时候设置的字段名

    //禁止 写入的时间字段
    protected $updateTime = false;

    // 软删除的字段
    protected $deleteTime = 'deletetime';
}