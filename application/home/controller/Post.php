<?php

namespace app\home\controller;

use app\common\controller\Userpage;
use think\View;

class Post extends Userpage
{
    public function __construct()
    {
        parent::__construct();
        // 帖子模型
        $this->PostModel = model('Post');
        // 用户模型
        $this->UserModel = model('User');
        // 消费模型
        $this->RecordModel = model('Record');
    }

    // 添加帖子
    public function add()
    {
		
		View::share([
			'title' => '发表问题 编辑问题 公用', 
			'keywords' => 'fly,layui,前端社区',
			'description' => 'Fly社区是模块化前端UI框架Layui的官网社区，致力于为web开发提供强劲动力',
			'tier' => 0
		]);
		
        if($this->request->isPost())
        {
            // 接收所有的数据
            $params = $this->request->param();

            // 接收验证码
            $vercode = $this->request->param('vercode','');

            if(!captcha_check($vercode))
            {
                $this->error('验证码错误');
                exit;
            }

            // 附加功能的值
            $state = $this->request->param('state',0);

            // 获取到附加功能相应的积分
            $statepoint = model('Config')->where('key',"Poststate{$state}")->value('value');

            // 采纳积分
            $point = $this->request->param('point',0);

            // 需要发帖人付出的积分
            $UpdatePoint = bcadd($statepoint,$point);

            // 获取用户的所有积分
            $UserPoint = cookie('LoginUser')['point'];

            // 用户的所有积分 - 发帖所需的积分
            $Point = bcsub($UserPoint,$UpdatePoint);

            // 积分不够，提示
            if($Point < 0){
                $this->error('积分不足，请充值');
                exit;
            }

            //插入帖子表 => 插入消费记录表 => 更新用户表

            //开启事务
            $this->PostModel->startTrans();
            $this->UserModel->startTrans();
            $this->RecordModel->startTrans();

            // 封装帖子的数据
            $PostData = [
                'title' => $params['title'],
                'content' => $params['content'],
                'point' => $UpdatePoint,
                'state' => $state,
                'userid' => cookie('LoginUser')['id'],
                'cateid' => $params['cateid']
            ];

            // 插入帖子表
            $PostStatus = $this->PostModel->save($PostData);

            if($PostStatus === FALSE)
            {
                $this->error($this->PostModel->getError());
                exit;
            }

            // 封装消费记录
            $RecordData = [
                'point' => $UpdatePoint,
                'state' => 1,
                'userid' => cookie('LoginUser')['id']
            ];

            //判断消费状态
            switch($state)
            {
                case 1:
                    $RecordData['content'] = "【".$PostData['title']."】采纳积分:<b>".$point."积分</b> 置顶：<b>{$statepoint}积分</b> - 发布时间：".date("Y-m-d H:i");
                    break;
                case 2:
                    $RecordData['content'] = "【".$PostData['title']."】采纳积分:<b>".$point."</b>积分 精华：<b>{$statepoint}积分</b> - 发布时间：".date("Y-m-d H:i");
                    break;
                case 3:
                    $RecordData['content'] = "【".$PostData['title']."】采纳积分:<b>".$point."</b>积分 热门：<b>{$statepoint}积分</b> - 发布时间：".date("Y-m-d H:i");
                    break;
                default:
                    $RecordData['content'] = "【".$PostData['title']."】采纳积分:<b>".$point."</b>积分 - 发布时间：".date("Y-m-d H:i");
                    break;
            }

            // 封装用户更新积分的数据
            $UserData = [
                'id' => cookie('LoginUser')['id'],
                'point' => $Point
            ];

            // 数据插入消费表
            $ReacorStatus = $this->RecordModel->save($RecordData);

            if($ReacorStatus === FALSE)
            {
                //将帖子表插入的数据回滚
                $this->PostModel->rollback();
                $this->error($this->RecordModel->getError());
                exit;
            }

            // 更新用户的积分
            $UserStatus = $this->UserModel->isUpdate(true)->save($UserData);

            if($UserStatus === FALSE)
            {
                $this->PostModel->rollback();
                $this->RecordModel->rollback();
                $this->error($this->UserModel->getError());
                exit;
            }

            // 终极判断
            if($PostStatus === FALSE || $ReacorStatus === FALSE || $UserStatus === FALSE)
            {
                $this->PostModel->rollback();
                $this->RecordModel->rollback();
                $this->UserModel->rollback();
                $this->error('帖子发布失败，请重新操作');
                exit;
            }else{
                $this->UserModel->commit();
                $this->RecordModel->commit();
                $this->PostModel->commit();
                $this->success('帖子发布成功',url('home/index/index'));
                exit;
                
            }
        }

        // 查询分类
        $CateList = model('Cate')->order('weigh','asc')->select();

        // 查询附加功能选项
        $stateList = $this->PostModel->state();
        // 赋值
        $this->assign([
            'CateList' => $CateList,
            'stateList' => $stateList
        ]);
        return $this->fetch('post/add');
    }
	
	public function index()
	{
		View::share([
			'title' => '基于 layui 的极简社区页面模版', 
			'keywords' => 'fly,layui,前端社区',
			'description' => 'Fly社区是模块化前端UI框架Layui的官网社区，致力于为web开发提供强劲动力',
			'tier' => 0
		]);
		return $this->fetch('post/index');
	}
	
	public function detail()
	{
		View::share([
			'title' => 'Fly Template v3.0，基于 layui 的极简社区页面模版', 
			'keywords' => 'fly,layui,前端社区',
			'description' => 'Fly社区是模块化前端UI框架Layui的官网社区，致力于为web开发提供强劲动力',
			'tier' => 0
		]);
		return $this->fetch('post/detail');
	}
}