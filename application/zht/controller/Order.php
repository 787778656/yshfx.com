<?php
/**
* zht组合信号管理
*/
namespace app\zht\controller;
use app\common\controller\Admin;
use think\Db;
use \think\Session;
use app\common\controller\Common as AppCommon;

class Order extends Admin
{
    public function index()
    {
        // 导入分页类
        import ( 'ORG.Util.Page' );
        $where = [];
        $status = input('status');
        $this->assign('status', $status);
        $type = input('type');
        $this->assign('type', $type);  //查询类别 1：用户UID 2：mt4id

        $keyword = trim(input('keyword'));
        $this->assign('keyword', $keyword);
        if (!empty($keyword)){
            $key = '';
            switch ($type) {
                case 1 :
                    $key = 'uid';
                    break;
                case 2 :
                    $key = 'mt4id';
                    break;
                case 3 :
                    $key = 'slave_mt4id';
                    break;
            }
            $where[$key] = ['like', "%$keyword%"];
        }
        if ($status !== NULL && $status !== '') {
            $where['status'] = $status;
        }
        $data = Db::name('Mt4DiyAccount')->where($where)->order('id desc')->paginate(10, false, ['query' =>request()->param()]);
        $this->assign('data', $data);

        //在线时间
        $cache = AppCommon::get_account_lastwtime();
        $this->assign('lastwtime', $cache);

        return $this->fetch();
    }


    /**
     * 删除签约信号
     */
    public function delete()
    {
        $id = input('id');
        Db::name('Mt4DiyAccount')->where('id', $id)->delete();
    }
}
