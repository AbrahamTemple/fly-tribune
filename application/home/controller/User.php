<?php
namespace app\home\controller;

use think\View;
use think\Controller;
use app\home\model\PreUser;
use think\Request;
use app\common\controller\Userpage;
use think\Cookie;


class User extends Userpage
{
	
	protected $NoLogin = ['login','logout'];
	
	protected $request;
	private $user;

	public function __construct(){
		parent::__construct();
	}
	
	public function _initialize()
    {
		$this->UserModel = model('User');
		$this->RegionModel = model('Region');
		$this->PostModel = model('Post');
		$this->CommentModel = model('Comment');
		$this->request = Request::instance();
    }

	public function activate()
	{
		$this->title('激活邮箱',1);
		$this->isLogin();
		return $this->fetch('user/activate');
	}
	
	public function forget()
	{
		$this->title('找回密码/重置密码',1);
		$this->isLogin();
		return $this->fetch('user/forget');
	}
	
	public function home()
	{
		$this->UserModel->citys(2)[0]['name'];
		
		$this->isLogin();
		$this->title('用户主页',1);
		
		$city = NULL;
		$district = NULL;
		
		foreach($this->UserModel->citys(2) as $c){
			if(!empty($c['name'])){
				$city = $c['name'];
			}
		}
		foreach($this->UserModel->districts(2) as $d){
			if(!empty($d['name'])){
				$district = $d['name'];
			}
		}
		
		$this->assign([
		    'city' => $city,
			'district' => $district
		]);
		
		return $this->fetch('user/home');
	}
	
	public function index()
	{
		$this->isLogin();
		$this->title('我的发帖',1);
		
		$limit = 10;
		$count = $this->PostModel->count();
		$PostList = $this->PostModel->where('userid',cookie('LoginUser')['id'])
				->order('createtime','desc')->paginate($limit);
		
		$total = count($PostList);
		
		$this->assign([
		    'PostList' => $PostList,
			'total' => $total
		]);
		return $this->fetch('user/index');
	}
	
	public function login()
	{
		$this->isLogin(FALSE);
		$this->title('登入',1);
		
		if($this->request->isPost()){
			$email = $this->request->post('email');
			$pwd = $this->request->post('pass');
			
			$vercode = $this->request->param('vercode','');
			if(!captcha_check($vercode)){
				$this->error('验证码错误');
				exit;
			}
			
			if($email && $pwd){
			    $auth = $this->UserModel->where('email', $email)
			            ->column('id,username,avatar,password,salt,sex,province,city,district,email,createtime,content,point,vip,auth');
			
				if(count($auth) != 1){
					$this->error('用户不存在');
					die;
				}
				
				foreach($auth as $v){
					$auth = $v;
				}
			
			    if($auth['email']==null || $auth['salt']==null){
			        $this->error('用户信息缺失');
			        die;
			    }
				
			    $check = md5($pwd.$auth['salt']);
				
			    if($auth['password']!=$check){
			        $this->error('登录密码错误');
					die;
			    }

				cookie('LoginUser',$auth,3600);
				$this->success('登录成功', './index');
			    exit;
			} 
				
			$this->error('登录失败');
			die;
		}
		return $this->fetch('user/login');
	}

	public function message()
	{
		$this->isLogin();
		$this->title('我的消息',1);
		$post = $this->CommentModel;
		// $this->CommentModel->where('id',);
		return $this->fetch('user/message');
	}
	
	public function reg()
	{
		$this->isLogin(FALSE);
		$this->title('注册',1);
		
		if($this->request->isPost()){
			
			$vercode = $this->request->param('vercode','');
			if(!captcha_check($vercode)){
				$this->error('验证码错误');
				exit;
			}
			
			$data = [
				'username'  =>  $this->request->post('username'),
				'avatar' => '/static/home/images/avatar/00.jpg',
				'email' =>  $this->request->post('email'),
				'password' => $this->request->post('pass'),
				'salt' => build_ranstr(),
				'createtime' => \time()
			];
			
			$data['password'] = md5($data['salt'].$data['password']);
			
			if($this->UserModel->save($data)){
				cookie('LoginUser',$data['username'],3600);
				cookie('LoginAvatar','/static/home/images/avatar/00.jpg',3600);
				$this->success('注册成功', './index');
			}else{
				$this->error('注册失败');
			}
			exit;
		}
		
		return $this->fetch('user/reg');
	}
	// 退出
	public function logout()
	{
	    // 清空cookie
		cookie('LoginUser', null);
		if(empty(cookie('LoginUser')) && empty(cookie('LoginAvatar'))){
			$this->success('注销成功',url('home/user/login'));
		}else{
			$this->success('注销失败',url('home/user/login'));
		}
	    
	}
	//信息设置
	public function set()
	{
		$this->isLogin();
		$this->title('帐号设置',1);
		
		if($this->request->isPost()){
			// 接收隐藏域的值
			$action = $this->request->param('action','');
						
			// 接收所有的数据
			$params = $this->request->param();
						
			// 判断是否修改我的资料
			if($action == 'profile')
			{
						
			    // 封装数据
			    $data = [
			        'id' => cookie('LoginUser')['id'],
			        'email' => $params['email'],
			        'username' => $params['username'],
			        'sex' => $params['sex'],
			        'province' => $params['province'],
			        'city' => $params['city'],
			        'district' => $params['districts'],
			        'content' => $params['content']
			    ];
						
			    if($params['email'] != cookie('LoginUser')['email']){
			        $data['auth'] = 0;
			    }
						
			    $result = $this->UserModel->isUpdate(true)->save($data);
						
			    if($result === FALSE)
			    {
			        $this->error($this->UserModel->getError());
			        exit;
			    }else{
			        // 封装一个cookie数据
			        $data = [
			            'id'=>cookie('LoginUser')['id'],
			            'email' => $params['email'],
			            'username' => $params['username'],
			            'vip' => cookie('LoginUser')['vip'],
			            'auth' => cookie('LoginUser')['auth'],
						'point' => cookie('LoginUser')['point'],
						'sex' => $params['sex'],
						'province' => $params['province'],
						'city' => $params['city'],
						'district' => $params['districts'],
						'content' => $params['content'],
						'createtime' => cookie('LoginUser')['createtime'],
			            'avatar' => cookie('LoginUser')['avatar'],
			        ];
			        if($params['email'] != cookie('LoginUser')['email']){
			            $data['auth'] = 0;
			        }
			        // 设置cookie
			        Cookie::set('LoginUser',$data);
			        $this->success('个人资料更新成功',url('home/user/home'));
			        exit;
			    }
						
			}
						
			// 上传封面图
			if($action == 'avatar')
			{
			    $avatar = upload('avatar');
						
			    // halt($avatar);
			    if($avatar === FALSE)
			    {
			        $this->error('上传失败');
			        exit;
			    }
						
			    $data = [
			        'id' => cookie('LoginUser')['id'],
			        'avatar' => $avatar
			    ];
						
			    $result = $this->UserModel->isUpdate(true)->save($data);
						
			    // halt($result);
						
			    if($result ===FALSE)
			    {
			        //上传上来的图片删除了
			        @is_file($data['avatar']) && @unlink($data['avatar']);
			        $this->error('封面图更新失败');
			        exit;
			    }else{
			        //修改成功后，将原来的图片删除
			        // halt($data['avatar']);
			        $avatar = ltrim(cookie('LoginUser')['avatar'],"/");
			        @is_file($avatar) && @unlink($avatar);
						
			        // halt(is_file($data['avatar']) ? $data['avatar'] : '/static/home/res/images/back.jpg');
			        //设置一个保存cookie信息
			        $avatar = ltrim($data['avatar'],"/");
			        $cookie = [
			            'id'=>cookie('LoginUser')['id'],
			            'username'=>cookie('LoginUser')['username'],
						'email' => cookie('LoginUser')['email'],
			            'vip'=>cookie('LoginUser')['vip'],
			            'auth'=>cookie('LoginUser')['auth'],
						'point' => cookie('LoginUser')['point'],
						'sex' => cookie('LoginUser')['sex'],
						'province' => cookie('LoginUser')['province'],
						'city' => cookie('LoginUser')['city'],
						'district' => cookie('LoginUser')['districts'],
						'content' => cookie('LoginUser')['content'],
						'createtime' => cookie('LoginUser')['createtime'],
			            'avatar' => is_file($avatar) ?  $data['avatar'] : '/static/home/images/avatar/00.jpg'
			        ];
						
			        //将用户信息设置到cookie里面
			        Cookie::set('LoginUser', $cookie);
						
			        // echo $data['avatar'];
			        // halt(is_file($data['avatar']));
			        $this->success('封面图更新成功');
			        exit;
			    }
			}
		}
		
		// 查询省
		$province = $this->RegionModel->where(['grade' => 1])->select();
		$city = $this->RegionModel->where(['grade' => 2])->select();
		$district = $this->RegionModel->where(['grade' => 3])->select();
		
		// 赋值
		$this->assign([
		    'province' => $province,
			'city' => $city,
			'district' => $district
		]);
		return $this->fetch('user/set');
	}
	
	public function set_loc(){
		
		if($this->request->isAjax()){
			$code = $this->request->post('code');
			
			$result = $this->RegionModel->where('parentid',$code)->select();
			
			if($result === FALSE)
			{
			    $this->warning('查询失败');
			    exit;
			}else{
			    $this->finish('查询成功',$result);
			    exit;
			}
		}
	}
	
	//发送邮箱
	public function set_email(){
		
		 // 引用邮箱类
		 include_once(APP_PATH .'phpmail/class.phpmailer.php');
		 // 实例化
		 $mail = new \PHPMailer(true);
		 		
		 // 开启SMTP
		 $mail->IsSMTP();
		 		
		 //SMTP服务器
		 $mail->Host = "smtp.qq.com";
		 		
		 //开启smtp认证
		 $mail->SMTPAuth = true;
		 
		 //ssl配置
		 $mail->SMTPOptions = array(
		    'ssl' => array(
		        'verify_peer' => false,
		        'verify_peer_name' => false,
		        'allow_self_signed' => true
		    )
		);
		 
		 // 设置smtp的邮箱用户
		 $mail->Username = "1179011410@qq.com";
		 // 在qq邮箱生成的授权码
		 $mail->Password = "lzuzgtnupnirjjdc";
		 // 端口
		 $mail->Port = 25;
		 		
		 // 发件人的邮箱
		 $mail->From = '1179011410@qq.com';
		 // 发件人的名字
		 $mail->FromName = 'ask社区';
		 		
		 // 收件人的邮箱
		 $mail->AddAddress('1135530168@qq.com',cookie('LoginUser')['username']);
		 // $mail->AddAddress(cookie('LoginUser')['email'],cookie('LoginUser')['username']);
		 		
		 //邮件主题
		 $mail->Subject  ="ask社区认证";
		 // qq邮箱生产授权码
		 // lzuzgtnupnirjjdc
		 // 接收请求参数
		 $email = $this->request->param('email','');
		 		
		 $email = md5($email);
		 // 允许发送html的内容
		 $mail->IsHTML(true);
		 // 封装的内容
		 $msg = "<a href='http://q/home/user/email?email=$email'>免费激活邮件</a>";
		 //邮件的内容
		 $mail->Body = $msg;  
		 
		 $result = $mail->send();
		 		
		 if($result === FALSE)
		 {
		     $this->warning('发送邮件失败');
		     exit;
		 }else{
		     $this->finish('发送邮件成功');
		     exit;
		 }
	}
	
	// 用户验证邮箱
	public function email()
	{
	    // 接收的参数 邮箱加密后的值
	    $auth = $this->request->get('email');
		
	    // 查询出所有的用户邮箱以及id
	    $User = $this->UserModel->field('id,email')->select();
		
		
	    // 循环User
	    foreach($User as $v)
	    {
	        // 把查询出的用户邮箱全部md5加密
	        $email = md5($v['email']);
			
	        // 如果接收的跟循环的相等
	        if($auth == $email)
	        {
				// 封装更新认证的数据
				$data = [
				    'id' => $v['id'],
				    'auth' => 1
				];
				
				$result = $this->UserModel->isUpdate(true)->save($data);
				
				if($result) 
				{
					$this->success('邮箱认证成功',url('home/user/home'));
					exit;
				}
				
	        }
			
			break;
	    }
	    
	   $this->error('认证失败，请检查邮箱是否正确',url('home/user/set'));
	   exit;
	    
	}
}