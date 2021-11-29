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
		// 标签模型
		$this->CateModel = model('Cate');
        // 用户模型
        $this->UserModel = model('User');
        // 消费模型
        $this->RecordModel = model('Record');
		// 评论模型
		$this->CommentModel = model('Comment');
    }

    // 添加帖子
    public function add()
    {

		$this->title('发表问题 编辑问题 公用',0);
		
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
        $CateList = $this->CateModel->order('weigh','asc')->select();

        // 查询附加功能选项
        $stateList = $this->PostModel->state();
        // 赋值
        $this->assign([
            'CateList' => $CateList,
            'stateList' => $stateList,
			'cateid' => 0,
        ]);
        return $this->fetch('post/add');
    }
	
	public function index()
	{
		$this->title('基于 layui 的极简社区页面模版',0);
		$CateList = $this->CateModel->order('weigh','asc')->select();
		
		$limit = 6;
		$count = $this->PostModel->count();
		$PostList = $this->PostModel
			->with('user','cate')
			->order('createtime','desc')->paginate($limit);
		
		$total = count($PostList);
		
		// 赋值
		$this->assign([
		    'CateList' => $CateList,
			'PostList' => $PostList,
			'total' => $total
		]);
		return $this->fetch('post/index');
	}
	
	//帖子详情页
	public function detail($id)
	{
		$this->title('Fly Template v3.0，基于 layui 的极简社区页面模版',0);
		
		$post = $this->PostModel->getOne($id);
		
		// 找不到帖子
		if($post==null){
			$this->error('该贴已丢失');
			die;
		}
		
		// 新的帖子（为了免去一次查询用已有数据）
		$newpost = [
			'id' => $id,
			'title' => $post['title'],
			'content' => $post['content'],
			'point' => $post['point'],
			'createtime' => $post['createtime'],
			'state' => $post['state'],
			'ask' => $post['ask'],
			'visit' => $post['visit'],
			'userid' => $post['uid'],
			'accept' => $post['accept'],
			'cateid' => $post['cateid'],
		];
		
		//增加浏览量
		$newpost['visit']++;
		$addvisit = $this->PostModel->isUpdate(true)->save($newpost);
		
		if($addvisit === FALSE){
			$this->error('访问该贴时出错');
			die;
		}
		
		$CateList = $this->CateModel->order('weigh','asc')->select();
		$CommentList = $this->CommentModel->getComment($id);
		
		if($this->request->isPost()) {
			//所有评论发表参数
			$params = $this->request->param();
			
			$data = [
			    'pid' =>  $params['pid']!=null?$params['pid']:0,
			    'content' => $params['content'],
			    'createtime' => time(),
			    'like' => 0,
			    'userid' => cookie('LoginUser')['id'],
			    'postid' => $id,
			];
			
			$result = $this->CommentModel->save($data);
			
			//增加回答数量
			$newpost['ask']++;
			$addask = $this->PostModel->isUpdate(true)->save($newpost);
			
			if($result === FALSE && $addask)
			{
				$this->error('发表评论失败');
				exit;
			} else {
				$this->success('发表评论成功',url('home/post/detail',['id' => $id]));
				exit;
			}
		}
		
		$this->assign([
			'CateList' => $CateList,
			'CommentList' => $CommentList,
		    'post' => $post,
		]);
		
		return $this->fetch('post/detail');
	}
	
	//悬赏采纳人积分
	public function accept($id){
		
		$comment = $this->CommentModel->getOne($id);
		
		$post = $this->PostModel->find($comment['postid'])->toArray();
		
		//当前登录用户是发表这篇文章的作者
		if(cookie('LoginUser')['id'] == $post['userid']){
			
			//如果采纳的人是第一个人
			if($post['accept'] == null){
				
				//添加文章的采纳人
				$post['accept'] = $comment['userid'];
				
				//更新采纳人（重复更新是FALSE）
				$result = $this->PostModel->allowField(true)->isUpdate(true)->save($post);
						
				//获取作者
				$master = $this->UserModel->find($post['userid'])->toArray();
				
				//获取采纳人
				$guest = $this->UserModel->find($comment['userid'])->toArray();
				
				//扣掉作者对应文章悬赏积分
				$master['point'] = bcsub($master['point'],$post['point']);
				
				//采纳人增加积分
				$guest['point'] = bcadd($master['point'],$post['point']);
				
				//更新作者积分
				$pointTo = $this->UserModel->allowField(true)->isUpdate(true)->save($master);
				
				//更新采纳人积分
				$pointFrom = $this->UserModel->allowField(true)->isUpdate(true)->save($guest);
				
			}
			//如果之前采纳过人
			else{
				
				//获取之前的采纳人
				$master = $this->UserModel->find($post['accept'])->toArray();
				
				//更换文章的采纳人
				$post['accept'] = $comment['userid'];
				
				//更新采纳人（重复更新是FALSE）
				$result = $this->PostModel->allowField(true)->isUpdate(true)->save($post);

				//获取新的采纳人
				$guest = $this->UserModel->find($comment['userid'])->toArray();
				
				//扣掉旧采纳人对应文章悬赏积分
				$master['point'] = bcsub($master['point'],$post['point']);
				
				//新采纳人增加积分
				$guest['point'] = bcadd($master['point'],$post['point']);
				
				//更新旧采纳人积分
				$pointTo = $this->UserModel->allowField(true)->isUpdate(true)->save($master);
				
				//更新新采纳人积分
				$pointFrom = $this->UserModel->allowField(true)->isUpdate(true)->save($guest);
				
			}
			
			if($result && $pointTo && $pointFrom)
			{
				//如果给钱的人是作者
				if(cookie('LoginUser')['id'] == $master['id']){
					//更新作者cookie
					$loginUser = cookie('LoginUser');
					$loginUser['point'] = $master['point'];
					cookie('LoginUser',$loginUser);
				}
				$this->success('你成功采纳了'.$comment['username'].'的评论',url('home/post/detail',['id' => $post['id']]));
				exit;
				
			} 
		}
		
		//回滚
		$this->UserModel->rollback();
		$this->PostModel->rollback();
		
		$this->error('登录用户权限不够');
	}
	
	//作者删除评论
	public function del($id){
		
		$comment = $this->CommentModel->find($id);
		
		$post = $this->PostModel->find($comment['postid']);
		
		//当前登录用户是发表这篇文章的作者
		if(cookie('LoginUser')['id'] == $post['userid']){
			
			$result = $this->CommentModel->destroy($id);
			
			if($result){
			
				$this->success('删除评论成功',url('home/post/index',['id' => $comment['postid']]));
				exit;
				
			} 
			
			$this->error('你无权删除该评论');
			die;
		}

		$this->error('删除评论失败');
	}
}