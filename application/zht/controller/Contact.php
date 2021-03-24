<?php
/**
* zht用户留言管理
*/
namespace app\zht\controller;
use app\common\controller\Admin;
use think\Db;
use think\Session;

class Contact extends Admin
{
    public function index()
    {
        // 导入分页类
        import ( 'ORG.Util.Page' );
        $where = [];
        $status = input('status');
        $keyword = input('keyword');
        $this->assign('keyword', $keyword);
        if (!empty($keyword)){
            $where['name|mobile'] = array('like', "%$keyword%");
        }
        if ($status !== NULL && $status !== '') {
            $where['status'] = $status;
        }
        $data = Db::name('Mt4Content')->where($where)->order('id desc')->paginate(10, false, ['query' =>request()->param()]);
        $this->assign('data', $data);

        return $this->fetch();
    }

    /**
     * 联系用户
     * @return string
     */
    public function check()
    {
        $id = input('id');
        $status = input('status');
        $operator =Session::get('admin.username');
        $time = time();
        $where = [
            'status' => $status,
            'operator' => $operator,
            'modify_time' => $time
        ];
        $data = Db::name('Mt4Content')->where('id', $id)->update($where);
        if ($data > 0) {
            $this->success('审核成功', url('contact/index'));
        } else {
            $this->error('审核失败');
        }
    }



    /**
     * 删除签约信号
     */
    public function delete()
    {
        $id = input('id');
        Db::name('Mt4Content')->where('id', $id)->delete();
    }

    //招募大师
    public function recruit()
    {
        $where = [];
        $keyword = input('keyword');
        $this->assign('keyword', $keyword);
        if (!empty($keyword)) {
            $where['name|phone'] = ['like', "%$keyword%"];
        }
        $status = input('status');
        $this->assign('status', $status);
        if ($status === 0 || $status === '0' || !empty($status)) {
            $where['status'] = $status;
        }
        $recruits = Db::name('recruit')->where($where)->order('modify_time desc')->paginate(10,false,['query' => request()->param()]);
        $this->assign('recruits', $recruits);
        return $this->fetch();
    }

    /**
     * 联系用户
     * @return string
     */
    public function recruit_check()
    {
        $id = input('id');
        $status = input('status');
        $operator =Session::get('admin.username');
        $time = time();
        $where = [
            'status' => $status,
            'operator' => $operator,
            'modify_time' => $time
        ];
        Db::name('recruit')->where('id', $id)->update($where);
    }
}
