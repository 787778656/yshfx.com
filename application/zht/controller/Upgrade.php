<?php
/**
* 升级管理(vip自动延期之类)
* @author coder<coder@qq.com>
* 2017-12-28
*/
namespace app\zht\controller;
use app\common\controller\Admin;
use \think\Config;
use think\Db;
use \think\Session;
use app\common\controller\Common as AppCommon;
use app\common\controller\Upgrade as AppUpgrade;
use \think\cache\driver\Redis;

class Upgrade extends Admin
{    
    /**
    * 截屏列表
    */
    public function screen($uid = 0)
    {
        // 导入分页类
        import ( 'ORG.Util.Page' );
        $keyword = input('keyword');
        $map = array();

        if ( ! empty($keyword)){
            $map['uid'] = trim($keyword);
        }

        $arrScreen = Db::name('user_screen')->order('id desc')->paginate(15);
        $this->assign('arrScreen', $arrScreen);
        return $this->fetch();
    }

    /**
    * 审核状态
    */
    public function status(){
        if (input('?post.id')){
            $id = input('post.id');
            $uid = input('post.uid');
            $status = input('post.status');
            if(Db::name('user_screen')->where('id', $id)->update(['status' => $status, 'modify_time' => time(), 'operator' => Session::get('admin.username')])){
                if (intval($status) == 1){ // 加vip7天
                    AppUpgrade::updateVip(7, $uid, '上传文章截图获取', Session::get('admin.username'));
                }
                echo '1';
            }else{
                echo '2';
            }
        }
    }
}