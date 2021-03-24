<?php
/**
* zht-web登录通用方法
*/
namespace app\web\controller;
use \think\Config; 
use \think\Controller;
use \think\Db;
use think\Model;
use \think\Session;
use app\common\controller\Action;

class Common extends Controller
{
    /**
    * 初始化
    */
    protected $userInfo;
    public function __construct(){
        parent::__construct();
        $user = Session::get('zhtweb');
        $uid = isset($user['uid']) ? $user['uid'] : 0;

        $info = Db::name('User')->where('uid', $uid)->find();

        // 加载多mt4账号
        if (!empty($info)){
            // mt4账号
            $info['arrMt4'] = Db::name('user_mt4')->where('uid', $info['uid'])->order('id desc')->select();

            // 将待审账号也加到列表显示中
            if ($info['isbuy'] == 0 && !empty($info['zhmt4uid'])){
                $info['arrMt4'][] = array('id' => 0, 'uid' => $info['uid'], 'mt4id' => $info['zhmt4uid'], 'mt4server' => $info['zhmt4server'], 'mt4pwd' => $info['zhmt4pwd'], 'sh' => $info['isbuy']);            
            }

            // vip过期判断
            if ($info['server_expire'] < time()){
               $info['server'] = ''; 
            }

            // // 是否开通工作室
            // $arrStudio = Db::name('studio')->where('uid', $info['uid'])->find();
            // if (!empty($arrStudio)){
            //     $info['studio_id'] = $arrStudio['id'];
            // }else{
            //     $info['studio_id'] = 0;
            // }

            // 可用信用金
            $info['credit'] = $info['credit_limit'] - $info['credited'];
        }
        $this->userInfo = $info;
        $this->assign('info', $info);
        $this->assign('uid', $uid);
        $this->assign('user', $user);

        $pagefrom = input('?get.server')?input('get.server', '', 'htmlspecialchars'):'yshfx';
        $this->assign('pagefrom', $pagefrom);
    }

    /**
     * 空方法
     */
    public function _empty(){
        return $this->fetch();      
    }

    /**
     * 登录检测
     */
    public function verifyLogin()
    {
        $user = Session::get('zhtweb');
        if (empty($user)) {
            $this->redirect('index/index');
        }
    }

    /**
     * 登录
     * @param $mobile
     * @param $password
     */
    public function login($mobile, $password)
    {
        if (empty($mobile) || empty($password)) {
            return false;
        }

        return $this->logining($mobile, $password);

    }

    public function logining($mobile, $password)
    {
        $user = Db::name('User')->field('login, uid')->where(array('login' =>$mobile, 'password' => md5($password)))->find();
        if (!empty($user)){
            Session::set('zhtweb', $user);
            Db::name('User')->update(['sign_time' => time(), 'id' => $user['uid']]);
            return true;
        }else{
            return false;
        }
    }

}