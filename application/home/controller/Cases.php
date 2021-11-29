<?php
namespace app\home\controller;

use think\Controller;
use think\View;
use app\common\controller\Userpage;

class Cases extends Userpage
{
	
	public function __construct()
	{
	    parent::__construct();
		// 标签模型
		$this->CateModel = model('Cate');
		// 帖子模型
		$this->PostModel = model('Post');
	}
	
	public function cases()
	{
		$this->title('发现 Layui 2017 年度最佳案例',1);
		$CateList = $this->CateModel->order('weigh','asc')->select();
		
		$limit = 8;
		$count = $this->PostModel->count();
		
		//获取当前月份
		$month = intval(date('m'));
		
		//获取今年元旦的时间戳
		$timestamp = strtotime("-0 year -{$month} month -0 day");
		
		$PostList = $this->PostModel
			->with('user','cate')
			// ->where("createtime",">",intval($timestamp))
			->order('createtime','desc')
			->paginate($limit);
		
		
		
		$total = count($PostList);
		
		$this->assign([
			'CateList' => $CateList,
			'cateid' => 0,
			'PostList' => $PostList,
			'total' => $total
		]);
		return $this->fetch('case/case');
	}
}