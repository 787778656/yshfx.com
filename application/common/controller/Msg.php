<?php
/**
* 站内消息相关类
* @author efon.cheng<efon@icheng.xyz>
* 2017-11-18
*/
namespace app\common\controller;
use \think\Config; 
use \think\Controller;
use think\Cookie;
use \think\Db;
use \think\Session;
use \think\cache\driver\Redis;

class Msg extends Controller
{
	public function __construct()
    {
        parent::__construct();
    }

    /**
    * 发站内信
    */
    public static function send($uid = 0, $msg = null, $from = 0, $operator = 'msg-api')
    {
        if ( ! empty($msg)){
            $msgtpl = Config::get('msgtpl');
            // 引用模板
            if (isset($msgtpl[$msg])) $msg = $msgtpl[$msg];
            if (is_array($uid)){
                foreach ($uid as $key => $value) {
                    $data[] = array(
                            'uid' => $value,
                            'msg' => $msg,
                            'from' => $from,
                            'operator' => $operator,
                            'add_time' => time(),
                            'modify_time' => time(),
                        );
                }
                
                // 保存信息
                if (Db::name('user_msg')->insertAll($data)){
                    return true;
                }else{
                    return false;
                }
            }else{
                $data = array(
                        'uid' => $uid,
                        'msg' => $msg,
                        'from' => $from,
                        'operator' => $operator,
                        'add_time' => time(),
                        'modify_time' => time(),
                    );

                // 保存信息
                if (Db::name('user_msg')->insert($data)){
                    return true;
                }else{
                    return false;
                }
            }
        }
    }

    /**
    * 获取站内信
    */
    public static function get($type)
    {
        $user = Session::get('zhtweb');
        $type =  input('get.type');
        $arrMsg = array();
        if ( ! empty($user)){
            $uid = isset($user['uid']) ? $user['uid'] : 0;
            if ($type == 'count'){
                $arrMsg = Db::name('user_msg')->where('uid', $uid)->where('status', 1)->count();
                // 系统群发消息
                $sysMsg = Db::name('user_msg')->where('uid', 0)->where('status', 1)->count();
                $arrMsg += $sysMsg;
            }else{
                $arrMsg = Db::name('user_msg')->where('uid', $uid)->whereOr('uid', 0)->order('id desc')->select();
            }
        }
        return $arrMsg;
    }

    /**
    * 读取站内信
    */
    public static function read($id = 0)
    {
        if (input('?get.id')){
            $id = input('get.id');
        }
        $user = Session::get('zhtweb');
        $uid = isset($user['uid']) ? $user['uid'] : 0;
        if ( ! empty($uid) &&  ! empty($uid)){
            $result = Db::name('user_msg')->where('uid', $uid)->where('id', $id)->update(['status' => 2, 'modify_time' => time()]);
            if ($result != 0){
                return 'success';
            }else{
                return 'fail';
            }
        }
    }

    /**
    * 发送短信
    */
    public static function sms($tag = null, $touser = 0, $content = null, $title = null){
        if ( ! empty($tag) && ! empty($content)){
            $redis = new Redis();
            // 消息推送记录
            $warnKey = sprintf('%s-%s-%s', 'zhtEA', 'warn', 'sms');
            $arrHasWarn = unserialize($redis->get($warnKey));

            // 一小时内未发送的消息
            if ( ! isset($arrHasWarn[$tag]) || time() - $arrHasWarn[$tag] > 3*3600){
                // 系统消息
                $key = sprintf('%s-%s', 'zhtEA', 'sms');
                $arrMsg = array();
                if ($redis->has($key)){
                    $arrMsg = unserialize($redis->get($key));
                }
                            
                // 进入列队
                if ( ! isset($arrMsg[$tag])){
                    $arrMsg[$tag] = array('touser' => $touser, 'title' => $title, 'content' => $content, 'time' => time());
                    $redis->set($key, serialize($arrMsg));

                    // 用户消息
                    if ($touser == 'pub_user'){
                        $key = sprintf('%s-%s', 'zhtEA', 'pubsms');
                        $arrMsg = array();
                        if ($redis->has($key)){
                            $arrMsg = unserialize($redis->get($key));
                        }

                        $arrMsg[$tag] = array('touser' => $touser, 'title' => $title, 'content' => $content, 'time' => time());
                        $redis->set($key, serialize($arrMsg));
                    }
                }

                // 记录推送过的消息
                $arrHasWarn[$tag] = time();
                // 系统消息
                $redis->set($warnKey, serialize($arrHasWarn));             
            }
        }
    }
}