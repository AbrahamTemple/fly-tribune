<?php
namespace app\common\model;

use think\Db;
use think\Model;

class Post extends Model
{
    // protected $pk = 'id';//主键是id不用定义

	// 设置当前模型对应的完整数据表名称
	protected $table = 'pre_post';


    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        //TODO:自定义的初始化
    }

	public function user(){
        return $this->belongsTo('User','userid','id',[],'LEFT')->setEagerlyType(0);
    }

	public function cate(){
        return $this->belongsTo('Cate','cateid','id',[],'LEFT')->setEagerlyType(0);
    }

	//得到所有
    public function getPost($count){

        return Db::name('post')
					->alias('p')
					->join('pre_user u','p.userid = u.id')
					->join('pre_cate c','p.cateid = c.id')
					->where('p.state',1)
                    ->limit($count)
					->field('p.*,u.avatar,u.username,u.vip,c.name,c.weigh')
					->select();

    }
	
	public function getOne($id){
	
	    return Db::name('post')
					->alias('p')
					->join('pre_user u','p.userid = u.id')
					->join('pre_cate c','p.cateid = c.id')
					->where('p.state',1)
					->field('p.*,u.id as uid,u.avatar,u.username,u.vip,c.name,c.weigh')
					->find($id);
	
	}
	
	public function getRelatedPost($where){
		
		return Db::name('post')
					->alias('p')
					->join('pre_user u','p.userid = u.id')
					->join('pre_cate c','p.cateid = c.id')
					->where($where)
					->field('p.*,u.avatar,u.username,u.vip,c.name,c.weigh')
					->select();
					
	}
	
	//  获取器
	public function getCommentAttr($value,$data)
	{
	    $count = model('Comment')->where('postid',$data['id'])->count();
	    return $count;
	}
	
	// 附加功能
	public function state()
	{
	    return [
	        '1' => '置顶',
	        '2' => '精华',
	        '3' => '热门'
	    ];
	}
}