<?php

namespace app\home\controller;

use app\common\controller\Userpage;
use think\Cache;

class Comment extends Userpage
{
    public function __construct()
    {
        parent::__construct();
		// 评论模型
		$this->CommentModel = model('Comment');
    }
	
	//增加评论点赞量
	public function like($id){
		if($this->request->isAjax()){
			
			//获取当前登录id并加密
			$one = md5(cookie('LoginUser')['id']);
			
			//如果这个人1个小时内没点过赞
			if(Cache::get($one) == FALSE){
				
				$comment = $this->CommentModel->where('id',$id)->toAarray();
				
				$comment['like']++;
				
				if($this->CommentModel->isUpdate(true)->save($comment))
				{
					
					Cache::set($one,true,3600);
				    $this->finish('点赞成功',$result);
				    exit;
				}
			}
			
		}
		$this->warning('点赞失败');
	}
}