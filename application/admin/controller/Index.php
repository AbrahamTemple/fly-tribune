<?php
namespace app\admin\controller;

use app\common\controller\Backend;

class Index extends Backend
{
    // 不需要登录的方法的属性
    protected $NoLogin = ['login','logout'];
    // 构造函数
    public function __construct()
    {
        // 继承父级
        parent::__construct();

        // 使用公共模型
        $this->AdminModel = model('Admin');
    }
    // 后台首页
    public function index()
    {
        return $this->fetch();
    }

    // 后台main页面
    public function main()
    {
        return $this->fetch();
    }

    // 后台登录页面
    public function login()
    {
        // 如果有session
        $LoginAdmin = session('LoginAdmin');

        if(!empty($LoginAdmin)){
            $admin = $this->AdminModel->where('id',$LoginAdmin['id'])->find();
            // halt($admin);
            // 该管理员不存在
            if(!$admin){
                $this->error('非法登录',url('admin/index/login'));
                exit;
            }

            // 是否被禁用
            if($admin['state'] !=1){
                $this->error('该管理员已禁用，请重新登录',url('admin/index/login'));
            }

            $this->success('你已经成功登录了，请不要重复登录',url('admin/index/index'));
            exit;
        }

        // 如果post提交的代码就会执行里面的代码
        if($this->request->isPost()){
            // 接收用户名
            $username = $this->request->param('username','');

            // 去数据库里面查询
            $admin = $this->AdminModel->where('username',$username)->find();

            // 该管理员不存在
            if(!$admin){
                $this->error('该管理员不存在',url('admin/index/login'));
                exit;
            }
            // 密码盐
            $salt = $admin['salt'];
			// echo $salt."<br>";
            // 接收密码
            $pass = $this->request->param('password','');
			// echo $pass."<br>";
            // 加密
            $password = md5($pass.$salt);
			// echo $password."<br>";
            
            if($admin['password'] != $password)
            {
                $this->error('用户名或密码错误',url('admin/index/login'));
				// echo $admin['password'];
                exit;
            }

            // 该管理员是否禁用
            if($admin['state'] != 1){
                $this->error('该管理员已禁用');
                exit;
            }

            // 封装session的数组
            $data = [
                'id' => $admin['id'],
                'nickname' => $admin['nickname']
            ];
            // 设置session
            session('LoginAdmin',$data);

            $this->success('登录成功',url('admin/index/index'));
        }
        return $this->fetch();
    }

    // 退出
    public function logout()
    {
        // 清空session
        session('LoginAdmin',null);
        $this->success('退出成功',url('admin/index/login'));
    }
	
	
}
