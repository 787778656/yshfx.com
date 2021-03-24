<?php
/**
* 接口程序
* @author efon.cheng<efon@icheng.xyz>
* 2017-12-06
*/
namespace app\web\controller\api;
use \think\Config;
use \think\Db;
use \think\Controller;
use app\common\controller\Common as AppCommon;
use \think\Cookie;
use \think\Session;
use \think\cache\driver\Redis;

class Signal extends Controller
{

    protected $userInfo;
    public function __construct(){
        parent::__construct();
        $this->userInfo = Session::get('zhtweb');
        // 未登录
        if (empty($this->userInfo)){
            exit(json_encode(array('code'=> 301, 'msg' => '请先登录!')));
        }

        $this->userInfo = Db::name('user')->where('uid', $this->userInfo['uid'])->find();
    }

    /**
    * 信号列表
    * @return string
    */
    public function get_list(){
        $arrList = Db::name('mt4_signal')->alias('a')->join('mt4_account b', 'a.mt4id = b.mt4id')->order('id desc')->field('b.id,b.id,  b.bn, b.name, b.follow, a.mt4id, a.price, a.status,a.mt4server,a.mt4pwd,b.show')->where('b.from_uid', $this->userInfo['uid'])->where('a.status', 1)->select();
        // $arrList = Db::name('mt4_account')->alias('a')->field('a.id,a.bn,a.name, a.follow, b.mt4id, b.price, b.status,b.mt4server,b.mt4pwd,a.show')->join('mt4_signal b', 'a.mt4id = b.mt4id','left')->order('a.id desc')->where(['a.from_uid'=>$this->userInfo['uid'], 'b.status'=>1])->select();
        // $arrList = Db::name('mt4_account')->order('id desc')->where(['from_uid'=>$this->userInfo['uid']])->select();
        $arrList2 = Db::name('mt4_signal')->where('status', 'in', '0,2')->where('uid', $this->userInfo['uid'])->field('sign_name as name, mt4id, price, status')->select();

        // 加入未审或未通过的
        foreach ($arrList2 as $key => $value) {
            $arrList2[$key]['id']= 0;
            $arrList2[$key]['bn']='000000';
            $arrList2[$key]['follow']=0;
            $arrList2[$key]['show']=2;
        }

        $arrList = array_merge($arrList, $arrList2);

        echo json_encode(array('code'=> 200, 'msg' => '', 'data' => $arrList));
    }

    /**
    * 编辑信号
    * @return string
    */
    public function edit($id = 0){
        if(input('?post.id')){            
            // 修改密码
            if (input('?post.password')){
                $password = input('post.password');
                $data['mt4pwd'] = $password;
            }
            // 下架            
            if (input('?post.show')){
                $show = 3;
                $data['show'] = $show;
            }
            //$show = intval(input('post.show'));
            Db::name('mt4_account')->where('from_uid', $this->userInfo['uid'])->where('id', input('post.id'))->update($data);
            echo json_encode(array('code'=> 200, 'msg' => '数据更新成功!'));
        }
    }
}