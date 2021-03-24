<?php
/**
* 排位赛
* @author efon.cheng<efon@icheng.xyz>
* 2017-12-26
*/
namespace app\web\controller;
use \think\Config;
use \think\Db;
use \think\Controller;
use app\common\controller\Common as AppCommon;
use \think\Cookie;
use \think\Session;
use \think\cache\driver\Redis;

class Rank extends Common
{

    protected $userInfo;
    public function __construct(){
        parent::__construct();
        $this->userInfo = Session::get('zhtweb');
        $this->userInfo = Db::name('user')->where('uid', $this->userInfo['uid'])->find();        
    }
    
    /**
    * pk
    */
    public function pk()
    {
        $from_uid = input('uid',0);  //分享者的uid
        $this->assign('from_uid', $from_uid);
        $arrPk = Db::name('pk_list')->where(['status' => 0])->order('session desc')->select();
        $this->assign('arrPk', $arrPk);
        // 历史战绩
        $arrSession = Db::name('pk_list')->where('uid', $this->userInfo['uid'])->column('session');
        $arrHPK = array();
        if ( ! empty($arrSession)){
          $hpk = Db::name('pk_list')->where('session', 'in', $arrSession)->where('status', 1)->order('session desc')->select();
          if ( ! empty($hpk)){
            $i = 0;
            foreach ($hpk as $key => $value) {
                if ($value['uid'] == $this->userInfo['uid']){
                    $arrHPK[$value['session']] = $value;
                }else{
                    $arrHPK[$value['session']]['pk'] = $value['remark'];
                }
                $arrHPK[$value['session']]['key'] = $i;
                $i++;
            }
          }
        }
        
        $this->assign('arrHPK', $arrHPK);
        $this->assign('userInfo', $this->userInfo);
        return $this->fetch();
    }


    /**
    * robots数据导入，暂时停用
    */
    /*
    public function robots(){

        exit();
        for ($i=0; $i < 30; $i++) {
            $robots[] = array(
                    'name' => sprintf('robots-%d', $i+1),
                    'level' => $i+1,
                    'balance' => 0,
                    'profit' => 0,
                    'time' => 0,
                    'operator' => 'rank-web-api',
                    'add_time' => time()
                );
        }


        $balance = -100;
        $profit = -100;
        $time = 5;

        for ($i=0; $i < 30; $i++) {
            $robots[] = array(
                    'name' => sprintf('robots-%d', $i+1),
                    'level' => $i+1,
                    'balance' => ($i < 10)?$balance+($i)*10:(-10)+($i-9)*20,
                    'profit' => ($i < 10)?$profit+($i)*10:(-10)+($i-9)*20,
                    'time' => $time*60,
                    'operator' => 'rank-web-api',
                    'add_time' => time()
                );
        }


        $balance = -190;
        $profit = -210;
        $time = 15;

        for ($i=0; $i < 30; $i++) {
            $robots[] = array(
                    'name' => sprintf('robots-%d', $i+1),
                    'level' => $i+1,
                    'balance' => ($i < 10)?$balance+($i)*10:(-100)+($i-9)*20,
                    'profit' => ($i < 10)?$profit+($i)*10:(-100)+($i-9)*20,
                    'time' => $time*60,
                    'operator' => 'rank-web-api',
                    'add_time' => time()
                );
        } 

        $balance = -190;
        $profit = -120;
        $time = 30;

        for ($i=0; $i < 30; $i++) {
            $robots[] = array(
                    'name' => sprintf('robots-%d', $i+1),
                    'level' => $i+1,
                    'balance' => ($i < 10)?$balance+($i)*10:(-100)+($i-9)*20,
                    'profit' => ($i < 10)?$profit+($i)*10:(-30)+($i-9)*20,
                    'time' => $time*60,
                    'operator' => 'rank-web-api',
                    'add_time' => time()
                );
        } 


        $balance = -190;
        $profit = -230;
        $time = 60;

        for ($i=0; $i < 30; $i++) {
            $robots[] = array(
                    'name' => sprintf('robots-%d', $i+1),
                    'level' => $i+1,
                    'balance' => ($i < 10)?$balance+($i)*10:(-100)+($i-9)*20,
                    'profit' => ($i < 10)?$profit+($i)*10:(-140)+($i-9)*20,
                    'time' => $time*60,
                    'operator' => 'rank-web-api',
                    'add_time' => time()
                );
        } 


        $balance = -190;
        $profit = -150;
        $time = 120;

        for ($i=0; $i < 30; $i++) {
            $robots[] = array(
                    'name' => sprintf('robots-%d', $i+1),
                    'level' => $i+1,
                    'balance' => ($i < 10)?$balance+($i)*10:(-100)+($i-9)*20,
                    'profit' => ($i < 10)?$profit+($i)*10:(-60)+($i-9)*20,
                    'time' => $time*60,
                    'operator' => 'rank-web-api',
                    'add_time' => time()
                );
        }

        $balance = -300;
        $profit = -20;
        $time = 180;

        for ($i=0; $i < 30; $i++) {
            $robots[] = array(
                    'name' => sprintf('robots-%d', $i+1),
                    'level' => $i+1,
                    'balance' => ($i < 10)?$balance+($i)*10:(-210)+($i-9)*20,
                    'profit' => ($i < 10)?$profit+($i)*10:(70)+($i-9)*20,
                    'time' => $time*60,
                    'operator' => 'rank-web-api',
                    'add_time' => time()
                );
        } 


        $balance = -250;
        $profit = -100;
        $time = 240;

        for ($i=0; $i < 30; $i++) {
            $robots[] = array(
                    'name' => sprintf('robots-%d', $i+1),
                    'level' => $i+1,
                    'balance' => ($i < 10)?$balance+($i)*10:(-160)+($i-9)*20,
                    'profit' => ($i < 10)?$profit+($i)*10:(-10)+($i-9)*20,
                    'time' => $time*60,
                    'operator' => 'rank-web-api',
                    'add_time' => time()
                );
        } 

        $balance = -300;
        $profit = -10;
        $time = 300;

        for ($i=0; $i < 30; $i++) {
            $robots[] = array(
                    'name' => sprintf('robots-%d', $i+1),
                    'level' => $i+1,
                    'balance' => ($i < 10)?$balance+($i)*10:(-210)+($i-9)*20,
                    'profit' => ($i < 10)?$profit+($i)*10:(80)+($i-9)*20,
                    'time' => $time*60,
                    'operator' => 'rank-web-api',
                    'add_time' => time()
                );
        }            

        var_dump($robots);
        Db::name('pk_robots')->insertAll($robots);
    }
    */

    /**
     * 获取分享者的相关信息
     * @return string
     */
    public function getUserInfo()
    {
        $from_uid = input('from_uid');
        $from_userinfo = Db::name('user')->field('nickname, login, u_img')->where('uid', $from_uid)->find();
        if (!empty($from_userinfo)) {
            return json_encode(['code' => 200, 'msg' => '查询成功', 'data' => $from_userinfo]);
        }
        return json_encode(['code' => 302, 'msg' => '查询失败']);
    }
}