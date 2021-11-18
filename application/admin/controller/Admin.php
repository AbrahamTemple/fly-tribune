<?php

namespace app\admin\controller;

use app\common\controller\Backend;

class Admin extends Backend
{
    // 构造函数
    public function __construct()
    {
        parent::__construct();

        // 调用admin模型
        $this->AdminModel = model('Admin');
    }
    /**
     * 显示管理员列表
     *
     * @return \think\Response
     */
    public function list()
    {
		ini_set("error_reporting","E_ALL & ~E_NOTICE");
        // 每页要显示多少条数据
        $limit = 3;

		//模糊搜索关键词
		$keywords = $this->request->get('keywords');
		$startday = $this->request->get('startday');
		$endday = $this->request->get('endday');
		
		$placeholder = '请输入用户名';
		
		$where = array();
		if(!empty($keywords)){
			
			$placeholder = $keywords;
			
			$where['username | nickname'] = array('like',"%$keywords%");
			
			if($keywords == '启用' || $keywords == '已启用')
			{
				$where = array();
				$where['state'] = 1;
			} 
			else if($keywords == '禁用' || $keywords == '已禁用')
			{
				$where = array();
				$where['state'] = 0;
			}

		}
		
		//查询某一时段内的数据
		if($startday && $endday){
			$where['create_time'] = array('between time',[$startday,$endday]);
		}

        // 查询数据总数
        $count = $this->AdminModel->count();
        $AdminList = $this->AdminModel->where($where)->order('id','asc')->paginate($limit);

		
        
        $this->assign([
            'count' => $count,
			'placeholder' => $placeholder,
            'AdminList' => $AdminList,
        ]);

        return $this->fetch();
    }
	/**
	 * 添加管理员
	 *
	 * @return \think\Response
	 */
	public function add()
	{
	    // 是否有psot提交
	    if($this->request->isPost()){
	        // 接收所有的参数
	        $data = $this->request->param();
	        
	        // 密码盐
	        $salt = build_ranstr();
	    	
	        // 加密
	        $password = md5($data['pass'] . $salt);
	    	
	        // 把相应的参数 重新封装成一个新的数组
	        $data = [
	            'username' => $data['username'],
	            'nickname' => $data['nickname'],
	            'password' => $password,
	            'salt' => $salt,
	            'state' => $data['state']
	        ];
	    	
	        // 把数据插入数据表里
	        $result = $this->AdminModel->validate('common/Admin.add')->save($data);
	        if($result === FALSE)
	        {
	            $this->error($this->AdminModel->getError());
	            exit;
	        }else{
	            $this->success('添加成功',url('admin/admin/list'));
	            
	        }

	    }
	    return $this->fetch();
	}
	/**
	 * 更新状态
	 *
	 * @return \think\Response
	 */
	public function state(){
		 // 获取ajax提交的id
		$id = $this->request->param('id',0);
		
		// 获取该管理员的状态
		$state = $this->request->param('title','');
				
		// 如果是1 启用 0 禁用
		
		if($state == '启用'){
		    $state = 0;
		}else if($state == '禁用'){
		    // 传过来的状态 如果是禁用 然后等于启用
		    $state = 1;
		}
		// 封装一个新的数组
		$data = [
		    'id' => $id,
		    'state' => $state
		];
		
		// 更新字段
		$result = $this->AdminModel->isUpdate(true)->save($data);
				
		if($result === FALSE)
		{
		    $this->warning('修改失败');
		    exit;
		}else{
		    $this->finish('修改成功');
		    exit;
		}
	}
    /**
     * 编辑管理员
     *
     * @return \think\Response
     */
    public function edit($id)
    {
		
		$admin = $this->AdminModel->find($id);
		
		//是否有post提交
		if($this->request->isPost()){
		    // 接收所有的参数
		    $data = $this->request->param();
			
			if($data['newpass'] != $data['renewpass']){
				$this->error('两次密码不一致',null,$data);
				exit;
			}
			
			// 如果原密码验证相同才可以更改密码	
			if( md5($data['oldpass'].$admin['salt']) == $admin['password']){
				
				// 获取新的密码盐
				$salt = build_ranstr();
				
				// 加密
				$password = md5($data['newpass'].$salt);
				
				$data = [
					'id' => $data['id'],
				    'username' => $data['username'],
				    'nickname' => $data['nickname'],
				    'password' => $password,
				    'salt' => $salt,
				    'state' => $data['state']
				];
				
				// 修改操作
				$result = $this->AdminModel->validate('common/Admin.edit')->isUpdate(true)->save($data);
			
				if($result === FALSE)
				{
				    $this->error($this->AdminModel->getError());
				    exit;
				}else{
				    $this->success('修改成功',url('admin/admin/list'));
					exit;
				}
				
			} 
			//或者不想改密码，只想修改其他信息
			else if($data['newpass'] == null && $data['oldpass'] == null) {
				
				$data = [
					'id' => $data['id'],
				    'username' => $data['username'],
				    'nickname' => $data['nickname'],
					'password' => $admin['password'],
					'salt' => $admin['salt'],
				    'state' => $data['state'],
				];
				
				// 修改操作
				$result = $this->AdminModel->validate('common/Admin.edit')->isUpdate(true)->save($data);
							
				if($result === FALSE)
				{
					$this->error('修改失败',null,$data);
				    exit;
				}else{
				    $this->success('修改成功',url('admin/admin/list'));
					exit;
				}
			} 
			//否则修改失败
			else {
				
				$this->error('旧密码验证失败',null,$data);
				exit;
			}
		
		}
		
		// 渲染数据
		$this->assign([
			'username' => $admin['username'],
			'nickname' => $admin['nickname'],
			'state' => $admin['state'],
		]);
		
		return $this->fetch();
    }
	/**
	 * 逻辑删除管理员
	 *
	 * @return \think\Response
	 */
	public function del(){
		if($this->request->isAjax()){
			$result = $this->AdminModel->destroy($this->request->post('id'));
			
			if($result){
				
				$this->finish('修改成功');
				exit;
				
			} 
			
		}
		$this->warning('修改失败');
	}
	
}
