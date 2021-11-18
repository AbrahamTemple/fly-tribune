<?php

namespace app\admin\controller;

use app\common\controller\Backend;

class Cate extends Backend
{
	//构造函数
	public function __construct(){
		
		parent::__construct();
		
		// 调用cate模型
		$this->CateModel = model('Cate');
	}
	
	/**
	 * 类目列表
	 *
	 * @return \think\Response
	 */
	public function list(){
		
		// 每页要显示多少条数据
		$limit = 3;
		
		// 查询数据总数
		$count = $this->CateModel->count();
		$CateList = $this->CateModel->order('id','asc')->paginate($limit);
		
		
		$this->assign([
		    'count' => $count,
		    'CateList' => $CateList
		]);
		
		return $this->fetch();
	}
	/**
	 * 增加一个类目
	 *
	 * @return \think\Response
	 */
	public function add(){
		if($this->request->isPost()){
			
			$cate_name = $this->request->post('cate_name');
			
			$data = [
				'name' => $cate_name,
				'weigh' => 0,
			];
		
			if($result = $this->CateModel->save($data)){
				
				$this->success('添加成功',url('admin/cate/list'));
				exit;
				
			} 
			
		}
		$this->error('添加失败',null,$data);
	}
	/**
	 * 类目编辑
	 *
	 * @return \think\Response
	 */
	public function edit($id){
		
		$cate = $this->CateModel->find($id);
		
		if($this->request->isPost()){
			
			$data = $this->request->param();
			
			$data = [
				'id' => $data['id'],
				'name' => $data['name'],
				'weigh' => $data['weigh'],
			];
			
			//更新操作返回bool
			if($this->CateModel->validate('common/Cate.edit')->isUpdate(true)->save($data))
			{
				$this->success('更新成功',url('admin/cate/list'));
			}else{
			    $this->error('更新失败',null,$data);
			}
			
			exit;
		}
		
		// 渲染数据
		$this->assign('cate',$cate);
		
		return $this->fetch();
	}
	
	/**
	 * 逻辑删除类目
	 *
	 * @return \think\Response
	 */
	public function del(){
		if($this->request->isAjax()){
			
			$result = $this->CateModel->destroy($this->request->post('id'));
			
			if($result){
				$this->finish('修改成功');
				exit;
			} 
		}
		$this->warning('修改失败');
	}
	
}