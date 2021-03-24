<?php
/**
* 站内信管理
* @author coder<coder@qq.com>
* 2017-11-29
*/
namespace app\zht\controller;
use app\common\controller\Admin;
use \think\Config;
use think\Db;
use \think\Session;
use app\common\controller\Common as AppCommon;
use app\common\controller\Msg as AppMsg;

class Msg extends Admin
{
    /**
    * 站内信列表
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
        
        $arrMsg = Db::name('user_msg')->where($map)->order('id desc')->paginate(15);
        $this->assign('arrMsg', $arrMsg);
        $this->assign('keyword', $keyword);

        return $this->fetch();
    }

    /**
    * 发送站内信
    */
    public function add()
    {

        $msgtpl = Config::get('msgtpl');
        $this->assign('msgtpl', $msgtpl);

        if (input('?post.uid')){
            // 发送信息
            $uid = trim(input('post.uid'));
            $msg = input('post.msg');
            if (strstr($uid, ',')){
                $uid = explode(',', $uid);
            }
            
            // 加入编辑人等信息
            $operator = Session::get('admin.username');

            if (AppMsg::send($uid, $msg, 0, $operator)){
                $this->success('站内信发送成功！', url('Msg/index'));
            }else{
                $this->error('站内信发送失败！');
            }
        }

        return $this->fetch();
    }
}
