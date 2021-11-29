<?php
// 命名空间
namespace app\common\controller;

// 继承tp的控制器
use think\Controller;
use think\View;

// 后台的公共控制器
class Userpage extends Controller
{
	
	// 设置全局属性 不需要登录
	protected $NoLogin = [];
	
	// 构造函数
	public function __construct()
	{
	    // 继承父级
	    parent::__construct();
	    $this->PostModel = model('Post');
		$this->UserModel = model('User');
	}
	
	// 验证登录
	public function isLogin($go = TRUE)
	{
		// 获取cookie
		$LoginUser = cookie('LoginUser');
		
		if($go){
			
			// 如果cookie不为空
			if(!empty($LoginUser)){
			    $id = isset($LoginUser['id']) ? $LoginUser['id'] : 0;
				
			    // 拿到cookie的id去查询数据库是否有该管理员
			    $User = $this->UserModel->find($id);
				
			    // 查询不到该管理员
			    if(!$User){
			        $this->error('非法登录',url('home/user/login'));
			        exit;
			    }
				
			    // 验证是否禁用
			    // if($User['auth'] != 1){
			    //     $this->success('你已经登录，不需要重复登录！',url('home/index/index'));
			    //     exit;
			    // }
				
			}else{
			    $this->error('请登录',url('home/user/login'));
			    exit;
			}
		} else{
			
			// 如果cookie不为空
			if(!empty($LoginUser)){
			    $id = isset($LoginUser['id']) ? $LoginUser['id'] : 0;
				
			    // 拿到cookie的id去查询数据库是否有该管理员
			    $User = $this->UserModel->find($id);
				
			    // 查询不到该管理员
			    if(!$User){
			        $this->error('非法登录',url('home/user/login'));
			        exit;
			    }
				
			    // 验证是否禁用
			    if($User['auth'] != 1){
			        $this->success('你已经登录，不需要重复登录！',url('home/index/index'));
			        exit;
			    }
				
			}
		}
	    
	
	}

	
	function title($what,$mode){
		View::share([
			'title' => $what, 
			'keywords' => 'fly,layui,前端社区',
			'description' => 'Fly社区是模块化前端UI框架Layui的官网社区，致力于为web开发提供强劲动力',
			'tier' => $mode
		]);
	}
	
	//空白页面
	public function _empty(){
		$this->title("404 - Fly社区",1);
		return $this->fetch('other/404');
	}
	
	
	/**
	     * 返回数据的方法
	     * @access protected
	     * @param mixed  $msg    提示信息
	     * @param mixed  $data   返回的数据
	     * @param mixed  $code   返回的状态码
	     * @return void
	     */
	    public function back($msg = '未知信息',$data = null,$code = 1)
	    {
	        // 一般接口的数据类型是json xml 把返回的数据封装一个数组
	        $result = [
	            'msg' => $msg,
	            'data' => $data,
	            'code' => $code
	        ];
	
	        // 数组转json
	        echo json_encode($result);
	        exit;
	    }
	
	    /**
	     * 操作失败跳转的快捷方法
	     * @access protected
	     * @param mixed  $msg    提示信息
	     * @param mixed  $data   返回的数据
	     * @param mixed  $code   返回的状态码
	     * @return void
	     */
	
	    public function warning($msg = '未知信息',$data = null,$code = 0)
	    {
	        $this->back($msg,$data,$code);
	        exit;
	    }
	
	    /**
	     * 操作成功跳转的快捷方法
	     * @access protected
	     * @param mixed  $msg    提示信息
	     * @param mixed  $data   返回的数据
	     * @param mixed  $code   返回的状态码
	     * @return void
	     */
	
	    public function finish($msg = '未知信息',$data = null,$code = 1)
	    {
	        $this->back($msg,$data,$code);
	        exit;
	    }
}