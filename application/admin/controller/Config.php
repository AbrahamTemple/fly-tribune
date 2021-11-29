<?php

namespace app\admin\controller;

use app\common\controller\Backend;

class Config extends Backend
{

    // 构造函数
    public function __construct()
    {
        parent::__construct();

        // 使用config模型
        $this->ConfigModel = model('Config');
    }
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function list()
    {
		$limit = 3;
		
		//模糊搜索关键词
		$keywords = $this->request->get('keywords');
		$startday = $this->request->get('startday');
		$endday = $this->request->get('endday');
		
		$placeholder = '请输入用户名';
		
		$where = array();
		if(!empty($keywords)){
			
			$placeholder = $keywords;
			
			$where['title | key'] = array('like',"%$keywords%");
		
		}
		
		if($startday && $endday){
			$where['create_time'] = array('between time',[$startday,$endday]);
		}
		
		$count = $this->ConfigModel->count();
		$ConfigList = $this->ConfigModel->where($where)->order('id','asc')->paginate($limit);
		
		$this->assign([
		    'count' => $count,
			'placeholder' => $placeholder,
		    'ConfigList' => $ConfigList,
		]);
		
        return $this->fetch();
    }

    

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function add()
    {
        if($this->request->isPost()){
            $config = $this->request->param();

            // 封装一个新的数组
            $data = [
                'title' => $config['title'],
                'key' => $config['key'],
                'type' => $config['type'],
            ];

            // 判断配置类型
            if($config['type'] == 'text'){
				
                $data['value'] = $config['value'];
				
            } elseif ($config['type'] == 'file') {
                // 处理上传
				$file = request()->file('file');
				// \halt($file);
				// die;
				if($file){
					$path = ROOT_PATH . 'public' . DS . 'upload/config';
				    $info = $file
							->validate(['ext'=>'jpg,png,gif,jpeg']) //验证格式
							->rule('date') //以时间命名
							->move($path, $savename = true, $replace = true); //保存到punlic/upload/config
					if($info){
						//路径是（日期）/（生成的保存名）.jpg
					   $data['value'] = DS.'upload/config'. DS .$info->getSaveName();
				    }
				}
            } else {
				// 上传失败获取错误信息
				// echo $file->getError();
				$this->error($file->getError());
				die;
			}
			
			$result = $this->ConfigModel->validate('common/Config.add')->save($data);
			
			if($result){
				$this->success('添加成功',url('admin/config/list'));
			} else {
				$this->error("添加成功");
			}
			
			die;
        }
        return $this->fetch();
    }

    

    /**
     * 显示编辑资源表单页.
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $config = $this->ConfigModel->find($id);
		if($this->request->isPost()){
			
			$data = $this->request->param();
			
			$data = [
				'id' => $data['id'],
				'title' => $data['title'],
				'key' => $data['key'],
				'value' => $data['value'],
				'type' => $data['type'],
			];
			
			// 判断配置类型
			if($config['type'] == 'text'){
				
			    $data['value'] = $config['value'];
				
			} elseif ($config['type'] == 'file') {
				
				//项目根目录
				$path = ROOT_PATH . 'public';
				
				//先删除原来的文件
				$old = $path.$config['value'];
				
				if(is_file($old)){
					unlink($old);
				}
				
			    // 处理上传
				$file = request()->file('file');
				// \halt($file);
				// die;
				if($file){
					//存放目录
					$dir = DS . 'upload'. DS .'config' . DS; 
				    $info = $file
							->validate(['ext'=>'jpg,png,gif,jpeg']) //验证格式
							->rule('date') //以时间命名
							->move($path.$dir , $savename = true, $replace = true); //保存到public/upload/config
					if($info){
						//路径是（日期）/（生成的保存名）.jpg
					   $data['value'] = $dir .$info->getSaveName();
				    }
				}
			} else {
				// 上传失败获取错误信息
				// echo $file->getError();
				$this->error($file->getError());
				die;
			}
			
			//更新操作返回bool
			if($this->ConfigModel->isUpdate(true)->save($data))
			{
				$this->success('更新成功',url('admin/config/list'));
			}else{
			    $this->error('更新失败',null,$data);
			}
			
			exit;
		}
		// 渲染数据
		$this->assign('config',$config);
		
		return $this->fetch();
    }
	/**
	 * 逻辑删除管理员
	 *
	 * @return \think\Response
	 */
	public function del(){
		if($this->request->isAjax()){
			$result = $this->ConfigModel->destroy($this->request->post('id'));
			
			if($result){
				
				$this->finish('修改成功');
				exit;
				
			} 
			
		}
		$this->warning('修改失败');
	}
}