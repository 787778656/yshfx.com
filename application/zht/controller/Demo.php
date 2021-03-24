<?php
/**
* mt4-Demo账户管理
*/
namespace app\zht\controller;
use \think\Db;
use app\common\controller\Admin;
use app\common\controller\Common as AppCommon;
use think\Session;

class Demo extends Admin
{
    public function index()
    {
        $keyword = trim(input('keyword'));
        $this->assign('keyword', $keyword);
        $status = input('status','');
        $this->assign('status', $status);
        $map = array();
        if (!empty($keyword)){
            $map['mt4id'] = array('like', "%$keyword%");
        }
        if ($status!=='' && $status !== '2'){
            $map['status'] = (int)$status;
        }
        if ($status === '2') {  //demo账号已过期
            $map['add_time'] = ['<', strtotime('-1 month')];
            $map['status'] = 0;
        }
        $data = Db::name('Mt4Demo')->where($map)->order('add_time desc')->paginate(10);
        $this->assign('mt4demo', $data);
        //在线时间
        $cache = AppCommon::get_account_lastwtime();
        $this->assign('lastwtime', $cache);
        return $this->fetch();
    }

    public function add()
    {
        return $this->fetch();
    }

    public function edit()
    {
        $id = input('id',0);
        $data = Db::name('Mt4Demo')->where('id', $id)->find();
        $this->assign('data', $data);
        return $this->fetch();
    }

    /**
     * 保存demo账号
     */
    public function save()
    {
        $id = input('id',0);
        $mt4id = input('mt4id');
        $mt4pwd = input('mt4pwd');
        $mt4lpwd = input('mt4lpwd');  //mt4观摩密码
        $mt4server = input('mt4server');
        $remark = input('remark'); //备注
        $sblfix = input('sblfix'); //后缀
        $time = time();
        $data = [
            'mt4id' => $mt4id,
            'mt4pwd' => $mt4pwd,
            'mt4lpwd' => $mt4lpwd,
            'mt4server' => $mt4server,
            'remark' => $remark,
            'sblfix' => $sblfix,
            'operator' => Session::get('admin.username')
        ];
        if ($id > 0) {
            $data['modify_time'] = $time;
            Db::name('Mt4Demo')->where('id', $id)->update($data);
        } else {
            $result = Db::name('Mt4Demo')->where($data)->find();
            if (!empty($result)) {
                $this->error('该mt4id账号已存在，请勿重复添加', url('add'));
            }
            $data['add_time'] = $time;
            $data['modify_time'] = $time;
            Db::name('Mt4Demo')->insert($data);
        }
        $this->success('数据保存成功', url('index'));
    }

    /**
     * 删除demo账号
     * @return string
     */
    public function del()
    {
        $id = input('id',0);
        Db::name('Mt4Demo')->where('id', $id)->delete();
        return json_encode(['code' => 200, 'msg' => '删除数据成功']);
    }
}
