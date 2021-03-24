<?php
/**
* 信用金管理
* @author coder<coder@qq.com>
* 2017-11-29
*/
namespace app\zht\controller;
use app\common\controller\Admin;
use \think\Config;
use think\Db;
use \think\Session;
use app\common\controller\Common as AppCommon;
use app\common\controller\Money;

class Credit extends Admin
{
    /**
    * 信用金列表
    */
    public function index()
    {
        // 导入分页类
        import ( 'ORG.Util.Page' );
        $keyword = input('keyword');
        $map = array();
        if ( ! empty($keyword)){
            $map['uid'] = trim($keyword);
        }        
        $arrLog = Db::name('user_credit_log')->where($map)->order('id desc')->paginate(15, false, ['query' =>request()->param()]);
        $this->assign('arrLog', $arrLog);
        $this->assign('keyword', $keyword);

        // 记录url所传参数
        $query = request()->param();
        $query['history'] = 1;
        foreach ($query as $key => $value) {
            $arrQuery[] = sprintf('%s=%s', $key, trim($value));
        }
        $this->assign('strQuery', implode('&', $arrQuery));

        return $this->fetch();
    }

    /**
    * 添加信用金
    */
    public function add()
    {
        if (input('?post.uid')){
            // 发送信息
            $uid = trim(input('post.uid'));
            $amount = (float)trim(input('post.credit_limit_new'));

            if (Db::name('user')->where('uid', $uid)->setField('credit_limit', $amount)){
                // 加入编辑人等信息
                $operator = Session::get('admin.username');
                Money::record_credit($uid, $amount, 0, 'credit-add', '管理员修改信用金额度', $operator);
                $this->success('信用金额度修改成功！', url('Credit/index'));
            }else{
                $this->error('信用金额度没有更新！');
            }
        }else{
            return $this->fetch();
        }        
    }

    /**
    * 获取信用额度
    */
    public function getInfo(){
        if (input('?post.uid')){
            $uid = input('post.uid');
            $arrAccount = Db::name('user')->where('uid', $uid)->find();
            return json_encode($arrAccount);
        }
    }
}
