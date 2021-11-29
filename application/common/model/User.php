<?php
namespace app\common\model;

use think\Model;
use traits\model\SoftDelete;

class User extends Model
{
    // protected $pk = 'id';//主键是id不用定义

	protected $table = 'pre_user';

	use SoftDelete;
	protected $deleteTime = 'delete_time';

	// 定义省份关联模型
	public function provinces($id)
	{
	    // 第一个关联模型名
	    // 第二个 关联外键
	    // 第三个 关联主键
	    // 第四个 关联别名(已废弃)
	    // 第五个 关联方法->关联类型
	   return $this->alias('u')
	   			->join('pre_region r','u.province = r.code')
	   			->field('r.*')->where('u.id',$id)->select();
	}
		
	// 城市关联模型
	public function citys($id)
	{
	    // return $this->belongsTo('app\common\model\Region','city','code',[],'LEFT');
		return $this->alias('u')
					->join('pre_region r','u.city = r.code')
					->field('r.*')->where('u.id',$id)->select();
	}
		
	// 区的关联模型
	public function districts($id)
	{
	    // return $this->belongsTo('app\common\model\Region','district','code',[],'LEFT')->setEagerlyType(0);
		return $this->alias('u')
					->join('pre_region r','u.district = r.code')
					->field('r.*')->where('u.id',$id)->select();
	}
}