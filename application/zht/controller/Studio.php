<?php
/**
* 工作室
* @author coder<coder@qq.com>
* 2017-12-18
*/
namespace app\zht\controller;
use app\common\controller\Admin;
use \think\Config;
use think\Db;
use \think\Session;
use app\common\controller\Common as AppCommon;

class Studio extends Admin
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
        
        $arrStudio = Db::name('studio')->where($map)->order('id desc')->paginate(15);

        $this->assign('keyword', $keyword);
        $this->assign('arrStudio', $arrStudio);

        return $this->fetch();
    }
}