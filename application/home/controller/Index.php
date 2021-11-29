<?php
namespace app\home\controller;

use think\View;
// use app\home\model\PrePost;
use app\common\controller\Userpage;

class Index extends Userpage
{
	
	// 构造函数
	public function __construct()
	{
	    // 继承父级
	    parent::__construct();
	    $this->PostModel = model('Post');
		$this->CateModel = model('Cate');
	}
	
    public function index()
    {
		View::share([
			'title' => '基于 layui 的极简社区页面模版', 
			'keywords' => 'fly,layui,前端社区',
			'description' => 'Fly社区是模块化前端UI框架Layui的官网社区，致力于为web开发提供强劲动力',
			'tier' => 0
		]);
		// $this->assign('data',$this->PostModel->getPost());
		
		// 接收到分类id
		$cateid = $this->request->param('cateid',0);
		// 是否采纳
		$accept = $this->request->param('accept','');
		// 接收搜索的参数
		$keyword = $this->request->param('keyword','');
				
		// 定义一个条件数组
		$where = [];
				
		// 分类id是否不为空
		if($cateid)
		{
		    $where['cateid'] = $cateid;
		}
				
		// 采纳不为空
		if($accept !== '')
		{
		    if($accept == 0)
		    {
		        // 未结
		        $where['accept'] = ['exp',Db::raw('is null')];
		    }elseif ($accept == 1) {
		        // 已结
		        $where['accept'] = ['exp',Db::raw('is not null')];
		    }
		}
				
		// 搜索
		if($keyword)
		{
		    $where['title|post.content'] = ['like',"%$keyword%"];
		}
		// 查询分类数据
		$CateList = $this->CateModel->order('weigh','asc')->select();
		
		// 查询帖子表数据
		// $PostList = $this->PostModel->with(['user','cate'])->where($where)->order('createtime','desc')->paginate(10);
		$PostList = $this->PostModel->getRelatedPost($where);
		
		// 置顶数据
		$ToppList = $this->PostModel->getPost(4);
		
		// 置顶总数
		$total = count($ToppList);
		
		$this->assign([
			'PostList' => $PostList,
		    'CateList' => $CateList,
		    'ToppList' => $ToppList,
		    'total' => $total,
		    'cateid' => $cateid,
		    'accept' => $accept
		]);

		return $this->fetch('index/index');
    }
	
	public function catalog()
	{
		return $this->fetch('index/catalog');
	}
}
