<?php
/**
 * h5
 */
namespace app\h5\controller;
use \think\Db;
use think\Session;
use think\Config;

class Wap extends Common
{
    protected static $arrConf;
    public function __construct(){
        parent::__construct();
        self::$arrConf = Config::get('wxmp');
    }

    /**
     * 微信h5首页
     */
    public function index(){
        $params = array();
        $parent_id = input('get.uid');
        if (input('?get.code')){
            $token = '';
            $code = input('get.code');
            $arrResult = json_decode(file_get_contents(sprintf(self::$arrConf['api']['token'], self::$arrConf['appID'], self::$arrConf['appSecret'], $code)));
            if ( ! empty($arrResult)){
                $this->check_openid($arrResult->openid, $parent_id);
                $url = sprintf(self::$arrConf['api']['userinfo'], $arrResult->access_token, $arrResult->openid);
                $userInfo = json_decode(file_get_contents($url));
                $token = $this->update_user($userInfo);
            }
            $url = sprintf('%s%s', url('wap/index'), "?token=$token");
            if (!empty($parent_id)) {
                $params['uid'] = $parent_id;
            }
            if (!empty($params)) {
                $url .= '&' . http_build_query($params);
            }
            Session::set('mytoken', $token);
            header("Location: $url");
        }else{
            $url = url('wap/index');
            $token = input('get.token');
            if (!empty($token)){
                if (Session::has('mytoken') && Session::get('mytoken') === $token) {
                    $this->setParentId($token,$parent_id);
                    return $this->fetch();
                }
                if (!empty($parent_id)) {
                    $url .= '?uid=' . $parent_id;
                }
                header("Location: $url");
            }else{
                $url = urlencode(url('wap/index'));
                if (!empty($parent_id)) {
                    $params['uid'] = $parent_id;
                }
                if (!empty($params)) {
                    $url .= '?' . urlencode(http_build_query($params));
                }
                $url = sprintf(self::$arrConf['api']['gzhao'], self::$arrConf['appID'], $url);
                header("Location: $url");
            }
        }
    }

    /**非强制绑定
     * @return mixed
     */
    public function index2(){
        $parent_id = input('get.uid');
        $token = input('get.token');
        $url = url('wap/index');
        if (!empty($token)) {
            if (Session::has('mytoken') && Session::get('mytoken') === $token) {
                return $this->fetch();
            }
            if (empty($parent_id)) {
                $parent_id = Db::name('user')->where('token', $token)->value('uid');
            }
            $url .= '?uid=' . $parent_id;
            header("Location: $url");
        } else {
            return $this->fetch();
        }
    }

    /**设置用户pid
     * @param $token
     * @param $parent_id
     */
    private function setParentId($token, $parent_id)
    {
        if (!empty($token) && !empty($parent_id)) {
            $user = Db::name('user')->where('token', $token)->find();
            if (!empty($user) && empty($user['parent_id']) && (time()-$user['add_time']) < 120 ) {
                Db::name('user')->where('uid', $user['uid'])->update(['parent_id' => $parent_id]);
            }
        }

    }

    /**更新用户信息
     * @param $userInfo
     * @return string
     */
    private function update_user($userInfo)
    {
        $token = '';
        if (isset($userInfo->openid)){
            $token = md5(sprintf('%s%s', $userInfo->openid, time()));
            $data = array(
                'nickname' => $userInfo->nickname,
                'u_img' => $userInfo->headimgurl,
                'area' => sprintf('%s,%s,%s', $userInfo->city, $userInfo->province, $userInfo->country),
                'token' => $token, // token
                'modify_time' => time(),
            );

            // 更新数据
            $isUser = Db::name('user')->where('gzhao', $userInfo->openid)->count();
            if (empty($isUser)){ // 新增
                $data['gzhao'] = $userInfo->openid;
                if(isset($userInfo->unionid)) $data['unionid'] = $userInfo->unionid;
                $data['add_time'] = time();
                if(Db::name('user')->insert($data)){ // 更新uid
                    Db::name('user')->where('gzhao', $userInfo->openid)->update(['uid' => ['exp','id']]);
                }
            }else{ // 更新
                unset($data['nickname'],$data['u_img']);
                Db::name('user')->where('gzhao', $userInfo->openid)->update($data);
            }
        }
        return $token;
    }

    /**检测openid是否已绑定
     * @param $openid
     * @param $parent_id
     */
    private function check_openid($openid,$parent_id)
    {
        $token = base64_encode($openid);
        if (!Db::name('user')->where('gzhao',$openid)->count()) {
            $url = url('wap/index').'?type=binding&token='. $token;
            if (!empty($parent_id)) {
                $url .= '&uid='.$parent_id;
            }
            Session::set('mytoken',$token);
            $this->redirect($url);
        }
    }
}
