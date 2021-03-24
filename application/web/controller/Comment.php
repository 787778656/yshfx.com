<?php
/**
* zht评论管理
*/
namespace app\web\controller;
use app\common\controller\Admin;
use think\Db;
use think\Model;
use think\Session;

class Comment extends Admin
{
    public function index()
    {
        // 导入分页类
        import ( 'ORG.Util.Page' );
        $where = [];
        $where['uid'] = ['>=', 0];
        $status = input('status');
        $keyword = input('keyword');
        $this->assign('keyword', $keyword);
        if (!empty($keyword)){
            $where['mt4id|username|comment'] = array('like', "%$keyword%");
        }
        if ($status !== NULL && $status !== '') {
            $where['status'] = $status;
        }
        $data = Db::name('mt4Comment')->where($where)->order('id desc')->paginate(10, false, ['query' =>request()->param()]);
        $this->assign('data', $data);

        return $this->fetch();
    }

    /**
     * 评论审核
     */
    public function check()
    {
        $model = Model('Mt4Comment');
        $id = input('id');
        $status = input('status');
        $operator =Session::get('admin.username');
        $data = [
            'status' => $status,
            'operator' => $operator
        ];

        if($model->save($data, ['id' => $id])) {
            $this->success('评论审核成功', url('comment/index'));
        } else {
            $this->error('评论审核失败');
        }

    }

    /**
     * 添加评论
     */
    public function add()
    {
        return $this->fetch();

    }

    /**
     * 编辑评论
     * @return mixed
     */
    public function edit()
    {
        $id = input('id', 0);
        $data = Db::name('Mt4Comment')->where('id', $id)->find();
        $this->assign('data', $data);
        return $this->fetch();
    }

    /**
     * 保存编辑评论
     */
    public function save()
    {
        $id = input('id', 0);
        $uid = input('uid');
        $login = input('mobile');  //手机号
        $mt4id = input('mt4id');  //mt4id
        $comment = htmlspecialchars(input('comment'));  //评论内容
        $operator =Session::get('admin.username');
        $datetime = input('time'); //显示日期
        $datetime = strtotime($datetime);
        $time = time();

        $data = [
            'uid' => $uid,
            'login' => $login,
            'mt4id' => $mt4id,
            'comment' => $comment,
            'status' => 1,
            'operator' => $operator
        ];
        if ($id > 0) {
            $data['modify_time'] = $datetime;
            Db::name('Mt4Comment')->where('id', $id)->update($data);
            $this->success('评论修改成功', url('comment/index'));
        } else {
            $data['add_time'] = $time;
            $data['modify_time'] = $datetime;
            Db::name('Mt4Comment')->insert($data);
            $this->success('评论添加成功', url('comment/index'));
        }
    }

    /**
     * 删除评论
     */
    public function delete()
    {
        $model = Model('Mt4Comment');
        $id = input('id');
        $model::destroy($id);
    }

    # 在线预约
    # uid = -1
    public function yuyue_index()
    {
        $where['uid'] = -1;
        $data = Db::name('mt4Comment')->where($where)->order('id desc')->paginate(10, false, ['query' =>request()->param()]);
        $this->assign('data', $data);

        return $this->fetch('yuyue_index');
    }
}
