<?php
namespace app\web\controller\api;
use \think\Config;
use \think\Db;
use \think\Session;
use think\Controller;
use \think\cache\driver\Redis;
use GeetestLib;

class Gop extends Controller
{
    public function __construct(){
        //parent::__construct();
        vendor('gop.config.config');
        vendor('gop.lib.geetestlib');
    }

    /**
    * 生成验证码
    */
    public function startCaptchaServlet(){
        $GtSdk = new GeetestLib(CAPTCHA_ID, PRIVATE_KEY);
        session_start();
        $data = array(
                "user_id" => "000001", # 网站用户id
                "client_type" => "web", #web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
                "ip_address" => "118.190.135.159" # 请在此处传输用户请求验证时所携带的IP
            );

        $status = $GtSdk->pre_process($data, 1);
        $_SESSION['gtserver'] = $status;
        $_SESSION['user_id'] = $data['user_id'];
        echo $GtSdk->get_response_str();
    }
}
