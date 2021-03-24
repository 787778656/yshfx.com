<?php
/**
* iOS和Android接口程序 跟单
*/
namespace app\api\controller\v1;
use think\cache\driver\Redis;
use think\Config;
use \think\Db;
use app\common\controller\Common as AppCommon;

class Order extends Common
{
    /**
     * 主信号页面用户跟单数据
     * @return string
     */
    public function get_master_signal()
    {
        $device = input('post.device',0);   //设备号
        $slave_mt4id = input('slave_mt4id'); //从mt4id
        $join = [
            ['mt4_account a', 'diy.mt4id = a.mt4id']
        ];
        $arrFollow = [];

        $map['diy.uid'] = $this->client_uid;
        if (!empty($slave_mt4id)) {
            $arrFollow = Db::name('user_mt4')->where(['mt4id' => $slave_mt4id])->field('uid,mt4id,is_default')->find();
            //$map['diy.slave_mt4id'] = $slave_mt4id;
        }
        // 多mt4启用, 兼容单mt4账号没有slave_mt4id的情况
        if (!empty($arrFollow) && $arrFollow['is_default'] == 1){
            $map['diy.slave_mt4id'] = array(['EXP', 'IS NULL'], ['eq', $slave_mt4id], 'or');
        }else{
            $map['diy.slave_mt4id'] = ['eq', $slave_mt4id];
        }
        // 国家配置
        $mt4country = array_flip(Config::get('options.mt4country'));
        // 服务商
        $mt4service = array_flip(Config::get('options.mt4service'));
        //$user_info = Db::name('User')->where('uid', $this->client_uid)->find();
        $data = Db::name('Mt4DiyAccount')->alias('diy')->join($join,'','left')->where($map)->field('diy.*, a.img as u_img, a.country, a.mt4server')->order('diy.add_time desc')->limit(100)->select();
        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $data[$k]['u_img'] =  $this->domain['__BASIC__'].'upload/image/'.$v['u_img'];
                if (isset($mt4country[$v['country']]) && !empty($mt4country[$v['country']])) {
                    $country_num = $mt4country[$v['country']];
                } else {
                    $country_num = $mt4country['中国'];
                }
                if (isset($mt4service[$v['mt4server']]) && !empty($mt4service[$v['mt4server']])) {
                    $service_num = $mt4service[$v['mt4server']];
                } else {
                    $service_num = $mt4service['forex'];
                }
                $data[$k]['country_img'] = $this->domain['__BASIC__'].'app/countryflag/'.$country_num.'.png';
                $data[$k]['service_img'] = $this->domain['__BASIC__'].'app/broker/'.$service_num.'.png';

            }
        }
        $redis = new Redis();
        $order_img = [];
        if ($redis->has($device)) {
            $order_img = $redis->get($device);   //获取用户组合跟单临时数据
        }
        if (!empty($order_img)) {
            foreach ($order_img as $key => $order) {
                foreach ($data as $value) {
                    if ($order['mt4id'] == $value['mt4id']) {
                        $this->del_signal($device,$value['mt4id']);
                        unset($order_img[$key]);
                    }
                }

            }
            $order_img = array_values($order_img);
        }
        return json_encode(['code' => 200, 'msg' => '查询成功', 'result' => ['data' => array_merge($order_img,$data)]]);
    }

    /**
     * 确认跟单
     * @return string
     */
    public function confirm()
    {
        $data = json_decode(input('data'),true);
        $device = input('post.device'); // 设备号
        $slave_mt4id = input('slave_mt4id'); //用户跟单的mt4id
        if (!$slave_mt4id) {
            $userinfo = Db::name('user')->where('uid', $this->client_uid)->find();
            if ($userinfo['isbuy']==0) {
                return json_encode(['code' => 302, 'msg' => 'mt4账号审核中,暂无法跟单!']);
            }
            $count_mam = Db::name('mam_follow')->where(['slave_mt4id' => $userinfo['zhmt4uid'], 'status' => 1])->count(); //计算该mt4id是否有正在运行的mam交易
            if ($count_mam) {
                return json_encode(['code' => 302, 'msg' => '请先停止mam交易跟单']);
            }
        } else {
            $count = Db::name('user_mt4')->where(['uid' => $this->client_uid, 'mt4id' => $slave_mt4id])->count();
            if (!$count) {
                return json_encode(['code' => 302, 'msg' => 'mt4账号审核中,暂无法跟单!']);
            }
            $count_mam = Db::name('mam_follow')->where(['slave_mt4id' => $slave_mt4id, 'status' => 1])->count(); //计算该mt4id是否有正在运行的mam交易
            if ($count_mam) {
                return json_encode(['code' => 302, 'msg' => '请先停止mam交易跟单']);
            }
        }
        $user_count = Db::name('Mt4DiyAccount')->where(['uid' => $this->client_uid, 'slave_mt4id' => $slave_mt4id, 'status' => 1])->count();
        if ($user_count && empty($this->userInfo['server'])) {
            return json_encode(['code' => 302, 'msg' => '您暂未购买VIP服务']);
        }
        $time = time();
        $count = 0;
        if(!empty($data)) {
            foreach ($data as $k => $d) {
                $map = array('mt4id' => $d['mt4id'], 'uid' => $this->client_uid);
                // 多mt4账号
                if (!empty($slave_mt4id)){
                    $map['slave_mt4id'] = $slave_mt4id;
                    $d['slave_mt4id'] = $slave_mt4id;
                }
                $data[$k]['uid'] = $this->client_uid;
                $data[$k]['operator'] = 'mobile-api';
                $d['status'] =1;
                $d['operator'] ='mobile-api';
                $info = Db::name('Mt4DiyAccount')->where($map)->find();
                if (!empty($info)) {
                    $result = Db::name('Mt4DiyAccount')->where($map)->update(array_merge($d,['modify_time' => $time,'uid' => $this->client_uid]));
                }else {
                    $result = Db::name('Mt4DiyAccount')->insert(array_merge($d,['add_time' => $time,'modify_time' => $time,'uid' => $this->client_uid]));
                }
                if ($result) {
                    $count += Db::name('UserMt4')->where(['mt4id' => $slave_mt4id, 'uid' => $this->client_uid])->update(['status' => 1]);
                }
            }

            $redis = new Redis();
            $redis->rm($device);
            /*$status = Db::name('User')->where('uid', $this->client_uid)->update(['status' => 1]);
            db('Mt4DiyAccount')->where('uid',$this->client_uid)->update(['status' => 1]);*/

            if ($count > 0) {
                $this->add_log($data,'批量修改或更新跟单信号'); //添加组合跟单的日志记录
                return json_encode(['code' => 200, 'msg' => '数据添加成功']);
            }

            return json_encode(['code' => 302, 'msg' => '暂无任何修改']);
        }

        return json_encode(['code' => 302, 'msg' => '数据添加失败']);


    }

    /**
     * 组合信号中用户停止跟单
     * @return string
     */
    public function update_order_status()
    {
        $slave_mt4id = input('slave_mt4id'); //用户从mt4id
        $umt4['uid'] = $diy['uid'] = $this->client_uid;
        if (!empty($slave_mt4id)) {
            $umt4['mt4id'] = $slave_mt4id;
            $diy['slave_mt4id'] = $slave_mt4id;
        }
        $count = db('UserMt4')->where($umt4)->update(['status' => 2, 'modify_time' => time()]);
        Db::name('Mt4DiyAccount')->where($diy)->update(['status' => 0]);
        if ($count > 0) {
            $this->add_log("['status' => 2]", 'mobile-api停止跟单');
        }
        return json_encode(['code' => 200, 'msg' => '停止跟单成功']);
    }

    /**
     * 修改单个跟单信号数据
     * @return string
     */
    public function update_order()
    {
        $data = json_decode(input('data'),true);
        $device = input('post.device');
        $data['operator'] = 'mobile-api';
        $time = time();
        $data['uid'] = $this->client_uid;
        $data['status'] = 1;
        $map = array('uid' => $this->client_uid, 'mt4id' => $data['mt4id']);
        // 多mt4账号
        if (isset($data['slave_mt4id'])){
            $map['slave_mt4id'] = $data['slave_mt4id'];
        }
        $user_count = Db::name('Mt4DiyAccount')->where(['uid' => $this->client_uid, 'slave_mt4id' => $data['slave_mt4id'], 'status' => 1, 'mt4id' => ['NEQ', $data['mt4id']]])->count();
        if ($user_count && empty($this->userInfo['server'])) {
            return json_encode(['code' => 302, 'msg' => '您暂未购买VIP服务']);
        }
        $info = Db::name('Mt4DiyAccount')->where($map)->find();

        if (!empty($info)) {
            $data['modify_time'] = $time;
            Db::name('Mt4DiyAccount')->where($map)->update($data);
        } else {
            $data['add_time'] = $time;
            $data['modify_time'] = $time;
            Db::name('Mt4DiyAccount')->insert($data);
            $this->del_signal($device,$data['mt4id']);
        }
        return json_encode(['code' => 200, 'msg' => '单个跟单信号修改成功!']);
    }

    /**
     * 获取用户跟单状态
     * @return string
     */
    public function get_status()
    {
        $slave_mt4id = input('slave_mt4id'); //从mt4id
        $map = [
            'uid' => $this->client_uid,
        ];
        if (!empty($slave_mt4id)) {
            $map['mt4id'] = $slave_mt4id;
        }
        if (!empty($this->client_uid)) {
            $info = Db::name('UserMt4')->where($map)->find();
            if (!empty($info)) {
                return json_encode(['code' => 200, 'result' => ['status' => $info['status']], 'msg' => '查询成功']);
            }
            return json_encode(['code' => 302, 'status' => 2, 'msg' => '暂无该数据']);
        }
        return json_encode(['code' => 302, 'result' => ['status' => 2], 'msg' => '用户暂未登录']);

    }

    /**
     * 根据设备号redis中存储用户图像，并返回客户端
     * @return string
     */
    public function order_car()
    {
        $device_num = input('device',''); //手机设备号
        $picture = input('picture','');   //图片地址
        $flag = input('flag','');   //国旗
        $service_img = input('service_img','');   //经纪商图片
        $name = input('name',''); //名称
        $mt4id = input('mt4id',0);   //mt4id
        $bn = input('bn',0);   //主信号编号

        if (empty($device_num)) {
            return json_encode(['code' => 302, 'msg' => '请输入手机设备号']);
        }
        $arr_picture=[
            'u_img' => $picture,
            'country_img' => $flag,
            'service_img' => $service_img,
            'mt4id' => $mt4id,
            'name' => $name,
            'bn' => $bn,
            'weight' => '10%',  //权重(手数)
            'maxloss' => 100,    //最大浮亏
            'maxhold' => 5,    //最大持仓
            'status' => 0,
            'status_u' => 1,    //待修改数据
        ];
        $mt4id_arr = [];
        $redis = new Redis();
        if (!$redis->has($device_num)) {
            $redis->set($device_num,[$arr_picture], $this->expire); //redis保留3小时
        } else {
            $order_old = $redis->get($device_num);  //根据设备号获取用户的组合图片
            foreach ($order_old as $old) {
                $mt4id_arr[] = $old['mt4id'];
            }
            if (!in_array($mt4id, $mt4id_arr)) {
                $order_new = array_merge($order_old,[$arr_picture]);
                $redis->set($device_num,$order_new, $this->expire);
                return json_encode(['code' => 200, 'msg' => '添加成功']);
            }

            return json_encode(['code' => 302, 'msg' => '请勿重复添加']);

        }
        return json_encode(['code' => 200, 'msg' => '添加成功']);
    }

    /**
     *删除redis中暂存的单个组合信号
     */
    public function del()
    {
        $redis = new Redis();
        $device = input('post.device', '');  //设备号
        $mt4id = input('post.mt4id', '');  //mt4id
        if (!empty($device) && $redis->has($device)) {
            $data = $redis->get($device);
            foreach ($data as $key => $signal) {
                if ($signal['mt4id'] === $mt4id) {
                    unset($data[$key]);
                }
            }
            $redis->set($device,array_values($data),$this->expire);
            return json_encode(['code' => 200, 'msg' => '删除成功']);
        }
        return json_encode(['code' => 302, 'msg' => '暂无该数据']);
    }

    /**
     *删除redis中暂存的单个组合信号
     * @param string $device 设备号
     * @param int $mt4id mt4id
     * @return string
     */
    private function del_signal($device='', $mt4id=0)
    {
        $redis = new Redis();
        if (!empty($device) && $redis->has($device)) {
            $data = $redis->get($device);
            foreach ($data as $key => $signal) {
                if ($signal['mt4id'] === $mt4id) {
                    unset($data[$key]);
                }
            }
            $redis->set($device,array_values($data),$this->expire);
            return json_encode(['code' => 200, 'msg' => '删除成功']);
        }
        return json_encode(['code' => 302, 'msg' => '暂无该数据']);
    }

    /**
     *获取redis中暂存的组合信号数据
     */
    public function get_history()
    {
        $redis = new Redis();
        $device = input('post.device', '');  //设备号
        if (!empty($device) && $redis->has($device)) {
            $data = $redis->get($device);
            return json_encode(['code' => 200, 'msg' => '查询成功', 'result' => $data]);
        }
        return json_encode(['code' => 302, 'msg' => '暂无该数据']);
    }
    /**
     * 添加组合跟单日志记录
     * @param $data
     * @return bool
     */
    public function add_log($data,$remark)
    {
        $time = time();
        $conf = [
            'uid' => $this->client_uid,
            'content' => json_encode($data),
            'remark' => $remark,
            'add_time' => $time,
            'modify_time' => $time
        ];
        Db::name('Mt4DiyAccountLog')->insert($conf);
        return true;
    }

    /**
     * 删除单个跟单信号
     * @return string
     */
    public function del_order()
    {
        $mt4id = input('mt4id');
        $delMap = array('uid' => $this->client_uid, 'mt4id' => $mt4id);
        // 多mt4账号
        if (input('?post.slave_mt4id')){
            $isDefault = Db::name('UserMt4')->where('mt4id', input('post.slave_mt4id'))->value('is_default');
            if($isDefault!=1) {
                $delMap['slave_mt4id'] = input('post.slave_mt4id');
            } else {
                $delMap['slave_mt4id'] = array(['EXP', 'IS NULL'], ['eq',  input('post.slave_mt4id')], 'or');
            }
        } else {
            $delMap['slave_mt4id'] = ['EXP', 'IS NULL'];
        }

        $count = Db::name('Mt4DiyAccount')->where($delMap)->delete();
        if ($count) {
            unset($delMap['mt4id']);
            $diyaccount = Db::name('Mt4DiyAccount')->where($delMap)->count();
            if (empty($diyaccount)) {
                $update_count = Db::name('user_mt4')->where(['mt4id' => input('post.slave_mt4id')])->update(['status' => 2]);
                if ($update_count) {
                    $this->add_log("['status' => 2]", '用户平仓');
                }
            }
        }
        return json_encode(['code' => 200, 'msg' => '删除成功']);
    }

}