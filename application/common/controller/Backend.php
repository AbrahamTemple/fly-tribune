<?php
// 命名空间
namespace app\common\controller;

// 继承tp的控制器
use think\Controller;

// 后台的公共控制器
class Backend extends Controller
{

    // 设置全局属性 不需要登录
    protected $NoLogin = [];

    // 构造函数
    public function __construct()
    {
        // 继承父级
        parent::__construct();
        $this->AdminModel = model('Admin');

        // 首先去除数组中为空元素
        $this->NoLogin = array_filter($this->NoLogin);

        // 如果NoLogin为空
        if(empty($this->NoLogin))
        {
            // 需要登录
            $this->isLogin();
        }else {
            
            // 所有的方法
            $all = '*';

            // 获取当前访问的方法
            $action = $this->request->action();

            if(!in_array($all,$this->NoLogin) && !in_array($action,$this->NoLogin)){
                // 需要登录
                $this->isLogin();
            }
        }
    }
    // 验证登录
    public function isLogin()
    {
        // 获取session
        $LoginAdmin = session('LoginAdmin');

        // 如果session不为空
        if(!empty($LoginAdmin)){
            $id = isset($LoginAdmin['id']) ? $LoginAdmin['id'] : 0;

            // 拿到session的id去查询数据库是否有该管理员
            $admin = $this->AdminModel->find($id);

            // 查询不到该管理员
            if(!$admin){
                $this->error('非法登录',url('admin/index/login'));
                exit;
            }

            // 验证是否禁用
            if($admin['state'] != 1){
                $this->error('该管理员已禁用，请重新登录',url('admin/index/login'));
                exit;
            }

            // 设置所有的控制器都能使用
            $this->LoginAdmin = $admin;

            // 全局赋值
            $this->assign('LoginAdmin',$admin);

        }else{
            $this->error('请登录',url('admin/index/login'));
            exit;
        }

    }
	
	//空白页面
	public function _empty(){
		return '<h1>&nbsp;&nbsp;该页面已丢失 … ☚ ☹ ☚</h1>';
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
		
	function build_ranstr($len = 8, $special=false)
	{
	    $chars = array(
	        "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
	        "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
	        "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
	        "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
	        "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
	        "3", "4", "5", "6", "7", "8", "9"
	    );
	    	    
	    if($special){
	        $chars = array_merge($chars, array(
	            "!", "@", "#", "$", "?", "|", "{", "/", ":", ";",
	            "%", "^", "&", "*", "(", ")", "-", "_", "[", "]",
	            "}", "<", ">", "~", "+", "=", ",", "."
	        ));
	    }
	    	    
	    $charsLen = count($chars) - 1;
	    shuffle($chars);                            //打乱数组顺序
	    $str = '';
	    for($i=0; $i<$len; $i++)
	    {
	        $str .= $chars[mt_rand(0, $charsLen)];    //随机取出一位
	    }
	    return $str;
	}
}
