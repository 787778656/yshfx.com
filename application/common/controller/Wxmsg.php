<?php
/**
* 微信模板消息
*/
namespace app\common\controller;
use \think\Config;
use \think\Db;
use \think\Controller;
use \think\Cookie;
use \think\Session;
use \think\cache\driver\Redis;
use app\common\controller\Common as AppCommon;

class Wxmsg extends Controller
{
    /**
    * 初始化
    */
    protected static $arrConf;
    public static function send($data = null, $touser = 'admin', $tplId = 'default')
    {
        self::$arrConf = Config::get('wxmp');
        // 消息模板
        if ( ! isset(self::$arrConf['touser'][$touser])) return;

        $arrTouser = self::$arrConf['touser'][$touser];

        if ( ! empty($data)){
            $result = array();
            if (empty($arrTouser)) return;
            foreach ($arrTouser as $key => $user) {
                $postData = array(
                        'touser' => $user,
                        'template_id' => self::$arrConf['tpl'][$tplId],
                        'data' => $data,
                    );
                $result[] = self::wxsend($postData);
            }
            return $result;
        }       
    }

    /**
    * 微信发送模板消息
    */
    private static function wxsend($postData = null, $token = null){
        if (empty($postData)) return;
        // 获取token
        $redis = new Redis();
        $tokenKey = sprintf('%s-%s', 'zhtEA', 'wxToken');
        if (!$redis->has($tokenKey)){
            $arrToken = json_decode(file_get_contents(sprintf(self::$arrConf['api']['access_token'], self::$arrConf['appID'], self::$arrConf['appSecret'])));
            //var_dump($arrToken);exit();
            if (isset($arrToken->access_token)){
                $redis->set($tokenKey, $arrToken->access_token, intval($arrToken->expires_in)-60);
            }
        }else{
            $token = $redis->get($tokenKey);
        }
        
        if ( ! empty($token)){
            $api = sprintf(self::$arrConf['api']['tpl_msg'], $token);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            $output = curl_exec($ch);
            curl_close($ch);
            return $output;
        }
    }
}