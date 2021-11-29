<?php
namespace app\index\controller;

use think\Controller;
use think\Cache;

class Index extends Controller
{
	
	// 不需要登录的方法的属性
	protected $NoLogin = ['login','logout'];
	// 构造函数
	public function __construct()
	{
	    // 继承父级
	    parent::__construct();
	
	}
	
    public function index()
    {
        // 切换到redis操作
    	Cache::store('redis')->set('key','hello');
        Cache::store('redis')->set('name','中文');
        echo Cache::get('name');
    }
}
