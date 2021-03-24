<?php
/**
* 账户余额管理
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

class Imoney extends Admin
{
    /**
    * 账户余额修正
    */
    public function index()
    {
        // 导入分页类
        import ( 'ORG.Util.Page' );
        $keyword = input('keyword');
        $map['class'] = 'gms-correct-api';
        if ( ! empty($keyword)){
            $map['uid'] = trim($keyword);
        }        
        $arrLog = Db::name('user_money_log')->where($map)->order('id desc')->paginate(15, false, ['query' =>request()->param()]);
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
    * 添加账户余额
    */
    public function add()
    {
        if (input('?post.uid')){
            // 发送信息
            $uid = trim(input('post.uid'));
            $amount = (float)trim(input('post.imoney_add'));
            $remark = trim(input('post.remark')); // 后台备注
            if ( ! empty($amount)){
                if (Db::name('user')->where('uid', $uid)->update(['imoney' => ['exp', "imoney+($amount)"]]) == true){
                    // 加入编辑人等信息
                    $operator = Session::get('admin.username');
                    Money::record($uid, $amount, 0, 'gms-correct-api', '冲正', $operator, $remark);
                    $this->success('账户余额修改成功！', url('Imoney/index'));
                }else{
                    $this->error('账户余额没有更新！');
                }
            }else{
                $this->error('账户余额没有更新！');
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
