<?php

namespace app\common\model;

use think\Model;
use traits\model\SoftDelete;

class Admin extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = 'pre_admin';
	
	use SoftDelete;
	protected $deleteTime = 'delete_time';
}
