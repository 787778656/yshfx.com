<?php
/**
* 钱包通用方法
* @author efon.cheng<efon@icheng.xyz>
* 2017-12-27
*/
namespace app\common\controller;
use \think\Config; 
use \think\Controller;
use think\Cookie;
use \think\Db;
use \think\Session;
use \think\cache\driver\Redis;
use GeetestLib;

class Gop extends Controller
{
    /**
     * 验证
     */
    public static function check()
    {
        if (input('?post.geetest_challenge')){
            //session_start();
            vendor('gop.config.config');
            vendor('gop.lib.geetestlib');
            $GtSdk = new GeetestLib(CAPTCHA_ID, PRIVATE_KEY);
            $data = array(
                    "user_id" => $_SESSION['user_id'], # 网站用户id
                    "client_type" => "web", #web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
                    // "ip_address" => "127.0.0.1" # 请在此处传输用户请求验证时所携带的IP
                    "ip_address" => "118.190.135.159" # 请在此处传输用户请求验证时所携带的IP
                );

            if ($_SESSION['gtserver'] == 1) {   //服务器正常
                $result = $GtSdk->success_validate($_POST['geetest_challenge'], $_POST['geetest_validate'], $_POST['geetest_seccode'], $data);
                if ($result) {
                    //echo '{"status":"success"}';
                    return true;
                } else{
                    //echo '{"status":"fail"}';
                    return false;
                }
            }else{  //服务器宕机,走failback模式
                if ($GtSdk->fail_validate($_POST['geetest_challenge'],$_POST['geetest_validate'],$_POST['geetest_seccode'])) {
                    // echo '{"status":"success"}';
                    return true;
                }else{
                    // echo '{"status":"fail"}';
                    return false;
                }
            }
        }else{
            // echo '{"status":"fail."}';
            return false;
        }
    }
}