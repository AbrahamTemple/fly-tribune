<?php

namespace app\admin\controller;

use app\common\controller\Backend;

class User extends Backend
{
    // 构造函数
    public function __construct()
    {
        parent::__construct();

        // 调用User模型
        $this->UserModel = model('User');
    }
    /**
     * 显示用户列表
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
			
			$where['username'] = array('like',"%$keywords%");
			
			if($keywords == '启用' || $keywords == '已启用')
			{
				$where = array();
				$where['auth'] = 1;
			} 
			else if($keywords == '禁用' || $keywords == '已禁用')
			{
				$where = array();
				$where['auth'] = 0;
			}

		}
		
		//查询某一时段内的数据
		if($startday && $endday){
			$where['create_time'] = array('between time',[$startday,$endday]);
		}

        // 查询数据总数
        $count = $this->UserModel->count();
        $UserList = $this->UserModel->where($where)->order('id','asc')->paginate($limit);

		
        
        $this->assign([
            'count' => $count,
			'placeholder' => $placeholder,
            'UserList' => $UserList,
        ]);

        return $this->fetch();
    }
	/**
	 * 编辑管理员
	 *
	 * @return \think\Response
	 */
	public function edit($id)
	{
		
		$user = $this->UserModel->find($id);
		
		//是否有post提交
		if($this->request->isPost()){
			$this->error('无权修改用户信息',null,$data);
			exit;
		}
		
		// 渲染数据
		$this->assign([
			'username' => $user['username'],
			'salt' => $user['salt'],
			'avatar' => $user['avatar'],
			'email' => $user['email'],
			'content' => $user['content'],
			'point' => $user['point'],
			'vip' => $user['vip'],
			'createtime' => $user['createtime'],
			'sex' => $user['sex'],
		]);
		
		return $this->fetch();
	}
	/**
	 * 逻辑删除用户
	 *
	 * @return \think\Response
	 */
	public function del(){
		if($this->request->isAjax()){
			$result = $this->UserModel->destroy($this->request->post('id'));
			
			if($result){
				
				$this->finish('修改成功');
				exit;
				
			} 
			
		}
		$this->warning('修改失败');
	}
}