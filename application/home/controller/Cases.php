<?php
namespace app\home\controller;

use think\Controller;
use think\View;
use app\common\controller\Userpage;

class Cases extends Userpage
{
	
	public function cases()
	{
		View::share([
			'title' => '发现 Layui 2017 年度最佳案例', 
			'keywords' => 'fly,layui,前端社区',
			'description' => 'Fly社区是模块化前端UI框架Layui的官网社区，致力于为web开发提供强劲动力'
		]);
		return $this->fetch('case/case');
	}
}