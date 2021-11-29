<?php

namespace app\common\model;

use think\Db;
use think\Model;

class Comment extends Model
{
    // 数据表
    protected $table = 'pre_comment';
	
	public function posts($uid){
	    return $this->belongsTo('Post','postid','id',[],'LEFT')
				->where('postid',$postid)
				->setEagerlyType(0);
	}
	
	//得到所有
	public function getComment($id){
	
	    return Db::name('comment')
					->alias('c')
					->join('pre_user u','c.userid = u.id')
					->join('pre_post p','c.postid = p.id')
					->where('p.id',$id)
	                // ->limit($count)
					->field('c.*,u.id as uid,u.avatar,u.username,u.vip,p.accept')
					->select();
	
	}
	
	//得到一个
	public function getOne($id){
	
	    return Db::name('comment')
					->alias('c')
					->join('pre_user u','c.userid = u.id')
					->join('pre_post p','c.postid = p.id')
					->where('c.id',$id)
	                // ->limit($count)
					->field('c.*,u.id as uid,u.avatar,u.username,u.vip,p.accept')
					->find();
	
	}
}