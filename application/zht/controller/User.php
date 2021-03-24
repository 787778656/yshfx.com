<?php
/**
* 主账号分组管理
* @author coder<coder@qq.com>
* 2017-08-31
*/
namespace app\zht\controller;
use \think\Config;
use \think\Db;
use \think\Session;
use \think\Cookie;
use app\common\controller\Admin;
use app\common\controller\Common as AppCommon;
use app\common\controller\Msg as AppMsg;
use \think\cache\driver\Redis;

class User extends Admin
{
    /**
    * 分组列表
    */  
    public function index(){
        // 导入分页类
        import ( 'ORG.Util.Page' );

        // 收藏
        $admin = Session::get('admin.username');
        $arrCollect = Db::name('mt4_gms_collect')->where(['operator' => $admin, 'class' => 'user'])->column('cid');
        $this->assign('arrCollect', $arrCollect);

        // 搜索
        $map = array();
        $keyword = trim(input('keyword'));
        if ( ! empty($keyword)){
            $map['id|login|zhmt4uid|aliyun'] = $keyword;
            //$this->assign('keyword', $keyword);
        }
        $this->assign('keyword', $keyword);

        $userStatusId = input('status');
        if ( ! empty($userStatusId)){
            switch ($userStatusId) {
                case 2:
                    $map['isbuy'] = 0;
                    break;  
                case 3:
                    $map['aliyun'] = array('NEQ', '');
                    break;
                case 4:
                    $map['aliyun'] = array('EQ', '');
                    break;
                case 5:
                    if (!empty($arrCollect)){
                        $map['uid'] = array('IN', $arrCollect);
                    }else{
                        $map['uid'] = 0;
                    }                    
                    break;                
                default:
                    $map['isbuy'] = $userStatusId;
                    break;
            }
            $this->assign('userStatusId', $userStatusId);          
        }

        $serverId = input('mt4server');
        $mt4server = Config::get('options.mt4server');
        if ( ! empty($serverId)){
            $server = $mt4server[$serverId];
            $map['zhmt4server'] = array('LIKE', "$server-%");
            $this->assign('serverId', $serverId);
        }

        $typeId = input('zhttype');
        if ( ! empty($typeId)){
            $arrUid = Db::name('mt4Orderlog')->where(array('action' => $typeId))->column('uid');
            $arrUid = array_unique($arrUid);
            if ( ! empty($arrUid)){
                $map['uid'] = array('IN', $arrUid);
            }
            $this->assign('typeId', $typeId);
        }

        // 加入排序规则
        $sorder = input('sorder');
        $sorder = empty($sorder)?'modify_time desc,status,id':input('sorder');

        $this->assign('sorder', $sorder);
        $sorder = sprintf("%s desc", $sorder);

        $arrUser = Db::name('user')->where($map)->order($sorder)->paginate(10, false, ['query' =>request()->param()]);
        $this->assign('arrUser', $arrUser);

        // 记录url所传参数
        $query = request()->except(['id','uid']);
        $query['history'] = 1;
        foreach ($query as $key => $value) {
            $arrQuery[] = sprintf('%s=%s', $key, trim($value));
        }
        $this->assign('strQuery', implode('&', $arrQuery));

        // 搜索变量
        $this->assign('mt4server', $mt4server);
        $this->assign('mt4status', Config::get('options.mt4status'));
        $this->assign('zhttype', Config::get('options.zhttype'));
        $this->assign('userstatus', Config::get('options.userstatus'));

        return $this->fetch();
    }

    /**
    * 查看用户
    */

    public function look_user(){
        $map['from'] = 'iOS';
        $arrUser = Db::name('user')->where($map)->order('id desc')->paginate(20);
        $this->assign('arrUser', $arrUser);
        return $this->fetch();
    }


    /**
    * 查看所有下级用户
    */
    public function all_next_user($uid = 0)
    {
        if($uid){
            $this->assign('uid',$uid);
            // 收藏
            $admin = Session::get('admin.username');
            $arrCollect = Db::name('mt4_gms_collect')->where(['operator' => $admin, 'class' => 'user'])->column('cid');
            $this->assign('arrCollect', $arrCollect);

            // 搜索
            $map = array();
            $keyword = trim(input('keyword'));
            if ( ! empty($keyword)){
                $map['id|login|zhmt4uid|aliyun'] = $keyword;
                //$this->assign('keyword', $keyword);
            }
            $this->assign('keyword', $keyword);

            // 级别 level 0：所有的人员  >0： uid为level_id的这个用户的直接下级
            $level = input('level',0);
            $level_id = input('level_id',0);  // 被点击的用户的uid
            if(is_numeric($level) && $level > 0 && is_numeric($level_id)){
                $map['parent_id'] = $level_id;
            }

            $level++;
            $this->assign('level',$level);

            $userStatusId = input('status');
            if ( ! empty($userStatusId)){
                switch ($userStatusId) {
                    case 2:
                        $map['isbuy'] = 0;
                        break;  
                    case 3:
                        $map['aliyun'] = array('NEQ', '');
                        break;
                    case 4:
                        $map['aliyun'] = array('EQ', '');
                        break;
                    case 5:
                        if (!empty($arrCollect)){
                            $map['uid'] = array('IN', $arrCollect);
                        }else{
                            $map['uid'] = 0;
                        }                    
                        break;                
                    default:
                        $map['isbuy'] = $userStatusId;
                        break;
                }
                $this->assign('userStatusId', $userStatusId);          
            }

            $serverId = input('mt4server');
            $mt4server = Config::get('options.mt4server');
            if ( ! empty($serverId)){
                $server = $mt4server[$serverId];
                $map['zhmt4server'] = array('LIKE', "$server-%");
                $this->assign('serverId', $serverId);
            }

            $typeId = input('zhttype');
            if ( ! empty($typeId)){
                $arrUid = Db::name('mt4Orderlog')->where(array('action' => $typeId))->column('uid');
                $arrUid = array_unique($arrUid);
                if ( ! empty($arrUid)){
                    $map['uid'] = array('IN', $arrUid);
                }
                $this->assign('typeId', $typeId);
            }

            // 加入排序规则
            $sorder = input('sorder');
            $sorder = empty($sorder)?'modify_time desc,status,id':input('sorder');

            $this->assign('sorder', $sorder);
            $sorder = sprintf("%s desc", $sorder);

            $user1_id_arr = $user1 = $user2_id_arr = $user2 = $user3_id_arr = $user3 = [];
            # 直接下级
            $user1_id_arr = Db::name('user')->where($map)->where('parent_id',$uid)->column('id'); # 直接下级的id
            if($user1_id_arr){
                $user1 = Db::name('user')->where('id','in',$user1_id_arr)->order($sorder)->select(); # 直接下级的详情
                $user2_id_arr = Db::name('user')->where('parent_id','in',$user1_id_arr)->column('id'); # 二级下级的id
                if($user2_id_arr){
                    $user2 = Db::name('user')->where('id','in',$user2_id_arr)->order($sorder)->select(); # 二级下级的详情
                    $user3 = Db::name('user')->where('parent_id','in',$user2_id_arr)->order($sorder)->select(); # 三级下级的详情
                }
            }

            # 数据处理
            $data = []; $i = 0;
            // if($user1){
            //     $data = $user1;
            //     foreach($user1 as $k1=>$v1){
            //         if($user2){
            //             foreach($user2 as $k2=>$v2){
            //                 if($v1['id'] == $v2['parent_id']){
            //                     $data[$k1]['child'][$v2['id']] = $v2;
            //                     if($user3){
            //                         foreach($user3 as $k3=>$v3){
            //                             if($v2['id'] == $v3['parent_id']){
            //                                 $data[$k1]['child'][$v2['id']]['child'][$v3['id']] = $v3;
            //                             }
            //                         }
            //                     }
            //                 }
            //             }
            //         }
            //     }
            // }

            if($user1){
                foreach($user1 as $k1=>$v1){
                    $data[$i] = $v1;
                    $data[$i]['child_level'] = 1;
                    $i++;
                    if($user2){
                        foreach($user2 as $k2=>$v2){
                            if($v1['id'] == $v2['parent_id']){
                                $data[$i] = $v2;
                                $data[$i]['child_level'] = 2;
                                $i++;
                                if($user3){
                                    foreach($user3 as $k3=>$v3){
                                        if($v2['id'] == $v3['parent_id']){
                                            $data[$i] = $v3;
                                            $data[$i]['child_level'] = 3;
                                            $i++;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $this->assign('arrUser', $data);

            // 记录url所传参数
            $query = request()->except(['id','uid']);
            $query['history'] = 1;
            foreach ($query as $key => $value) {
                $arrQuery[] = sprintf('%s=%s', $key, trim($value));
            }
            $this->assign('strQuery', implode('&', $arrQuery));

            // 搜索变量
            $this->assign('mt4server', $mt4server);
            $this->assign('mt4status', Config::get('options.mt4status'));
            $this->assign('zhttype', Config::get('options.zhttype'));
            $this->assign('userstatus', Config::get('options.userstatus'));

            return $this->fetch();
        }else{
            $this->redirect('index');
        }
    }


    /**
    * 导出所有下级用户
    */
    public function exp_excel_all_next($uid = 0)
    {
        if($uid){
            // 搜索
            $map = array();
            $keyword = trim(input('keyword'));
            if ( ! empty($keyword)){
                $map['id|login|zhmt4uid|aliyun'] = $keyword;
            }

            $userStatusId = input('status');
            if ( ! empty($userStatusId)){
                switch ($userStatusId) {
                    case 2:
                        $map['isbuy'] = 0;
                        break;  
                    case 3:
                        $map['aliyun'] = array('NEQ', '');
                        break;
                    case 4:
                        $map['aliyun'] = array('EQ', '');
                        break;
                    case 5:
                        if (!empty($arrCollect)){
                            $map['uid'] = array('IN', $arrCollect);
                        }else{
                            $map['uid'] = 0;
                        }                    
                        break;                
                    default:
                        $map['isbuy'] = $userStatusId;
                        break;
                }     
            }

            $serverId = input('mt4server');
            $mt4server = Config::get('options.mt4server');
            if ( ! empty($serverId)){
                $server = $mt4server[$serverId];
                $map['zhmt4server'] = array('LIKE', "$server-%");
            }

            $typeId = input('zhttype');
            if ( ! empty($typeId)){
                $arrUid = Db::name('mt4Orderlog')->where(array('action' => $typeId))->column('uid');
                $arrUid = array_unique($arrUid);
                if ( ! empty($arrUid)){
                    $map['uid'] = array('IN', $arrUid);
                }
            }

            // 加入排序规则
            $sorder = input('sorder');
            $sorder = empty($sorder)?'modify_time desc,status,id':input('sorder');
            $sorder = sprintf("%s desc", $sorder);

            $user1_id_arr = $user1 = $user2_id_arr = $user2 = $user3_id_arr = $user3 = [];
            # 直接下级
            $user1_id_arr = Db::name('user')->where($map)->where('parent_id',$uid)->column('id'); # 直接下级的id
            if($user1_id_arr){
                $user1 = Db::name('user')->field('*,`credit_limit`-`credited` credit_limit_credited')->where('id','in',$user1_id_arr)->order($sorder)->select(); # 直接下级的详情
                $user2_id_arr = Db::name('user')->where('parent_id','in',$user1_id_arr)->column('id'); # 二级下级的id
                if($user2_id_arr){
                    $user2 = Db::name('user')->field('*,`credit_limit`-`credited` credit_limit_credited')->where('id','in',$user2_id_arr)->order($sorder)->select(); # 二级下级的详情
                    $user3 = Db::name('user')->field('*,`credit_limit`-`credited` credit_limit_credited')->where('parent_id','in',$user2_id_arr)->order($sorder)->select(); # 三级下级的详情
                }
            }

            # 数据处理
            $data = []; $i = 0;
            // if($user1){
            //     $data = $user1;
            //     foreach($user1 as $k1=>$v1){
            //         if($user2){
            //             foreach($user2 as $k2=>$v2){
            //                 if($v1['id'] == $v2['parent_id']){
            //                     $data[$k1]['child'][$v2['id']] = $v2;
            //                     if($user3){
            //                         foreach($user3 as $k3=>$v3){
            //                             if($v2['id'] == $v3['parent_id']){
            //                                 $data[$k1]['child'][$v2['id']]['child'][$v3['id']] = $v3;
            //                             }
            //                         }
            //                     }
            //                 }
            //             }
            //         }
            //     }
            // }

            if($user1){
                foreach($user1 as $k1=>$v1){
                    $data[$i] = $v1;
                    $data[$i]['child_level'] = 1;
                    $i++;
                    if($user2){
                        foreach($user2 as $k2=>$v2){
                            if($v1['id'] == $v2['parent_id']){
                                $data[$i] = $v2;
                                $data[$i]['child_level'] = 2;
                                $i++;
                                if($user3){
                                    foreach($user3 as $k3=>$v3){
                                        if($v2['id'] == $v3['parent_id']){
                                            $data[$i] = $v3;
                                            $data[$i]['child_level'] = 3;
                                            $i++;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if($data){
                $cellName = [['child_level','下级级别'],['id', 'id'],['uid', 'uid'],['login', '手机号'],['nickname','昵称'],['server','vip等级'],['imoney','余额'],['smoney','保证金'],['credit_limit_credited','可用信用金'],['credit_limit','额度'],['parent_id','上级ID'],['zhmt4uid','mt4账号'],['zhmt4pwd','mt4主密码'], ['zhmt4server', 'mt4服务器'], ['isbuy', '审核状态（1：已审核 0：未审核）'], ['sblfix', '后缀'], ['aliyun', '备注'], ['time', '加入时间']];  //导出的列名
                AppCommon::exportExcel('查看用户'.$uid.'的所有下级（三级）', $cellName, $data); //导出Excel
            }
        }
    }


    /**
     * 更新vip
     */
    public function update_vip()
    {
        // 记录所传参数
        $query = request()->param();
        
        if (empty($query['expire'])) $query['expire'] = 0;

        $id = $query['id'];
        if (input('?post.id')){
            $time = time();
            unset($query['id']);
            $operator = Session::get('admin.username');
            // vip续费手动处理
            $userInfo = Db::name('user')->where('id',$id)->field('server,server_expire')->find();
            $expire_day = $time+$query['expire']*86400;
            if (!empty($userInfo) && !empty($userInfo['server'])) {
                if ($userInfo['server'] === $query['server']) {
                    if ($userInfo['server_expire'] > $time) {
                        $expire_day = $userInfo['server_expire'] + $query['expire']*86400;
                    }
                }
            }
            $result = Db::name('user')->where('uid', $id)->update(['server' => $query['server'], 'server_expire' => $expire_day, 'modify_time' => $time]);
            if ($result){
                // 保存日志
                $logData = $query;
                $logData['operator'] = $operator;
                $logData['server_expire'] = $expire_day;
                $logData['days'] = $query['expire'];
                $logData['add_time'] = $time;
                $logData['modify_time'] = $time;
                unset($logData['expire']);

                if(Db::name('Mt4VipLog')->insert($logData)){
                    $this->success('服务器信息修改成功!', url('user/vip_list'));
                }else{
                    $this->error('服务器信息修改失败!');
                }
            }else{
                $this->success('服务器信息未修改!', url('user/vip_list'));
            }
        }else{
            $arrUser = Db::name('user')->find(array('uid' => $id));
            $this->assign('arrUser', $arrUser);

            // 修改日志
            $arrLog = Db::name('Mt4VipLog')->where(array('uid'=>$id))->order('id desc')->paginate(10);
            $this->assign('arrLog', $arrLog);
        }
        return $this->fetch();
    }

    public function vip_list()
    {
        $map = [];
        $keyword = input('keyword'); //关键词
        if ( ! empty($keyword)){
            $map['id|login'] = $keyword;
        }
        $this->assign('keyword', $keyword);
        $arrUser = Db::name('User')->where($map)->order('modify_time desc, id desc')->paginate(10);
        $this->assign('arrUser', $arrUser);
        return $this->fetch();
    }

    /**
     * 导出execl文件
     */
    public function add_collect(){
        $arrAid = $_POST['aid'];
        if ( ! empty($arrAid)){
            $admin = Session::get('admin.username');
            $action = input('post.action');

            if ( ! empty($action)){
                if ($action == 'remove'){
                    $map['operator'] = $admin;
                    $map['class'] = 'user';
                    $map['cid'] = array('in', $arrAid);
                    Db::name('mt4_gms_collect')->where($map)->delete();
                }else{
                    foreach ($arrAid as $key => $value) {
                        $data[] = array(
                            'operator' => $admin, 
                            'class' => 'user',
                            'cid' => $value,
                            'modify_time' => time(),
                            'add_time' => time(),
                            );
                    }
                    Db::name('mt4_gms_collect')->insertAll($data);
                }
            }            
        }
    }

    /**
     * 导出execl文件
     */
    public function exp_excel(){
        // 搜索
        $map = array();
        $keyword = input('keyword');
        if ( ! empty($keyword)){
            $map['id|login|zhmt4uid'] = $keyword;
        }

        $userStatusId = input('status');
        if ( ! empty($userStatusId)){
            switch ($userStatusId) {
                case 2:
                    $map['isbuy'] = 0;
                    break;
                case 3:
                    $map['aliyun'] = array('NEQ', '');
                    break;
                case 4:
                    $map['aliyun'] = array('EQ', '');
                    break;
                default:
                    $map['isbuy'] = $userStatusId;
                    break;
            }
        }

        $serverId = input('mt4server');
        $mt4server = Config::get('options.mt4server');
        if ( ! empty($serverId)){
            $server = $mt4server[$serverId];
            $map['zhmt4server'] = array('LIKE', "$server-%");
        }

        $statusId = input('mt4status');
        if ( ! empty($statusId)){
            if($statusId == 1){ // 在线
                $map['lastwtime'] = array('GT', time()-180);
            }else{
                $map['lastwtime'] = array('ELT', time()-180);
            }
        }

        $typeId = input('zhttype');
        if ( ! empty($typeId)){
            $arrUid = Db::name('mt4Orderlog')->where(array('action' => $typeId))->column('uid');
            $arrUid = array_unique($arrUid);
            if ( ! empty($arrUid)){
                $map['uid'] = array('IN', $arrUid);
            }
        }

        // 加入排序规则
        $sorder = input('sorder');
        $sorder = empty($sorder)?'id':input('sorder');

        $sorder = sprintf("%s desc", $sorder);
        $excel_user = Db::name('user')->where($map)->order($sorder)->column('id,login,zhmt4server,isbuy,time,aliyun');   //要导出的数据
        $excel_user = array_values($excel_user);
        $cellName = [['id', 'id'],['login', '手机号'], ['zhmt4server', 'mt4服务器'], ['isbuy', '审核状态（1：已审核 0：未审核）'], ['time', '加入时间'], ['aliyun', '备注']];  //导出的列名
        AppCommon::exportExcel('用户管理', $cellName, $excel_user); //导出Excel
    }

    /**
    * 修改服务器信息
    */
    public function edit_server($id = 0){
        if (!empty($id)){
            // 记录所传参数
            $query = request()->param();
            foreach ($query as $key => $value) {
                $arrQuery[] = sprintf('%s=%s', $key, trim($value));
            }
            $this->assign('strQuery', implode('&', $arrQuery));

            if (input("?post.id")){
                Db::transaction(function() use($id){
                    // 保存日志
                    $mt4Data = array(
                        'mt4id' => trim(input('post.zhmt4uid')),
                        'mt4pwd' => trim(input('post.zhmt4pwd')),
                        'mt4server' => trim(input('post.zhmt4server')),
                        'sblfix' => trim(input('post.sblfix')),
                        'is_demo' => input('post.is_demo'),
                        'remark' => input('post.aliyun'),
                        'modify_time' => time(),
                        'add_time' => time(),
                        'operator' => Session::get('admin.username'),
                    );                    

                    // 更新数据
                    $count = Db::name('user_mt4')->where('mt4id', $mt4Data['mt4id'])->where('id', 'neq', $id)->count();
                    if ($count != 0){
                        $this->error('该mt4账号已存在!');
                        exit();
                    }

                    if(Db::name('user_mt4')->where('id', $id)->update($mt4Data)){
                        // 更新user表
                        $uid = input('post.uid');
                        $arrUser = Db::name('user')->where('uid', $uid)->find();
                        $mt4id = input('post.mt4id');
                        if ($arrUser['isbuy'] == 1){
                            $userData = array(
                                'zhmt4uid' => input('post.zhmt4uid'),
                                'zhmt4pwd' => input('post.zhmt4pwd'),
                                'zhmt4server' => input('post.zhmt4server'),
                                'sblfix' => input('post.sblfix'),
                                'modify_time' => time(),
                                'isbuy' => 1,
                                'aliyun' => input('post.aliyun'),
                            );
                            Db::name('user')->where('uid', $uid)->update($userData);
                            //echo Db::name('user')->getLastSql();
                        }

                        // 保存日志
                        $reward = input('post.is_demo')==0?'是':'否';
                        $logData = array(
                            'uid' => input('post.uid'),
                            'zhmt4uid' => input('post.zhmt4uid'),
                            'zhmt4pwd' => input('post.zhmt4pwd'),
                            'zhmt4server' => input('post.zhmt4server'),
                            'aliyun' => sprintf('备：%s；分佣：%s', input('post.aliyun'), $reward),
                            'add_time' => time(),
                            'wadmin' => Session::get('admin.username'),
                        );
                        Db::name('usersevers')->insert($logData);
                    }
                });
                
                $this->success('mt4账号修改成功!', url('user/mt4_list', ['uid'=>input('post.uid')]));
            }else{
                $arrMt4 = Db::name('user_mt4')->where('id', $id)->find();
                $this->assign('arrMt4', $arrMt4);
                // 修改日志
                $arrLog = Db::name('usersevers')->where('uid', $arrMt4['uid'])->where('zhmt4uid', $arrMt4['mt4id'])->order('id desc')->paginate(10, false, ['var_page' => 'mt4logpage']);
                $this->assign('arrLog', $arrLog);
                return $this->fetch();
            }
        }
    }

    /**
    * 下单记录
    */
    public function mt4_list($uid = 0){
        //$uid = input('?get.id')?input('get.id'):$uid;
        $cache = AppCommon::get_account_lastwtime();
        $this->assign('lastwtime', $cache);
        $status = 0;
        if ($uid != 0){
            $map['uid'] = $uid;
        }else{
            // 搜索
            $map = array();
            $keyword = trim(input('keyword'));
            if ( ! empty($keyword)){
                $map['uid|mt4id'] = trim($keyword);
            }
            $this->assign('keyword', $keyword);

            $status = input('mt4status');  // 1:在线  2：离线  3:全部

            if ($status == 1 || $status == 2 ) {
                if (!empty($cache)) {
                    $cache_in = $cache_out = [];
                    foreach ($cache as $key => $value) {
                        if ($value > (time() - 180)) {
                            $cache_in[] = $key;  //在线的mt4数组集合
                        } else {
                            $cache_out[] = $key;  //离线的mt4数组集合
                        }
                    }

                    if ($status == 1) {
                        $map['mt4id'] = ['in', $cache_in];
                    } elseif ($status == 2) {
                        $map['mt4id'] = ['in', $cache_out];
                    }
                } else {
                    $status = 3;
                }
            }
            $this->assign('status', $status);
        }

        
        $arrMt4 = Db::name('user_mt4')->where($map)->order('id desc')->paginate(10, false, ['var_page' => 'mt4page', 'query' => ['mt4status' => $status]]);
        $this->assign('arrMt4', $arrMt4);
        $this->assign('uid', $uid);

        // 记录所传参数
        $query = request()->param();
        $arrQuery = array();
        foreach ($query as $key => $value) {
            $arrQuery[] = sprintf('%s=%s', $key, trim($value));
        }
        $this->assign('strQuery', implode('&', $arrQuery));

        // redis
        $redis = new Redis();
        $this->assign('redis', $redis);
        return $this->fetch();
    }

    /**
    * 获取mt4账号信息
    */
    public function mt4_info($id = 0){
        $id = input('post.id');
        if ( ! empty($id)){
            $arrMt4 = Db::name('user_mt4')->where('id', $id)->order('id desc')->find();
            echo json_encode($arrMt4);
        }
    }

    /**
    * 下单记录
    */
    public function order($account = 0){
        if (!empty($account)){
            // 导入分页类
            import ( 'ORG.Util.Page' );
            $arrOrder = Db::name('mt4_slave_order')->where(array('account'=>$account))->order('open_time desc')->paginate(10, false, ['var_page' => 'opage']);
            $this->assign('arrOrder', $arrOrder);

            $this->assign('zhttype', Config::get('options.zhttype'));
            // 记录所传参数
            $query = request()->param();
            foreach ($query as $key => $value) {
                $arrQuery[] = sprintf('%s=%s', $key, trim($value));
            }
            $this->assign('strQuery', implode('&', $arrQuery));
            $this->assign('account', $account);
            return $this->fetch();
        }
    }


    /**
    * 历史记录
    */
    public function order_history($account = 0){
        if (!empty($account)){
            // 导入分页类
            import ( 'ORG.Util.Page' );
            $table = AppCommon::tableName('mt4_history_order', $account);
            $arrOrder = Db::name($table)->where(array('account'=>$account))->order('close_time desc')->paginate(10, false, ['var_page' => 'opage']);
            $this->assign('arrOrder', $arrOrder);

            $this->assign('zhttype', Config::get('options.zhttype'));
            // 记录所传参数
            $query = request()->param();
            foreach ($query as $key => $value) {
                $arrQuery[] = sprintf('%s=%s', $key, trim($value));
            }

            $this->assign('account', $account);
            $this->assign('strQuery', implode('&', $arrQuery));
            return $this->fetch();
        }
    }

    /**
    * 充值记录
    */
    public function money($id = 0){
        if (!empty($id)){
            // 导入分页类
            import ( 'ORG.Util.Page' );
            $arrOrder = Db::name('mt4_payment')->where(array('uid'=>$id, 'status' => 1))->order('id desc')->paginate(10, false, ['var_page' => 'mpage']);
            $this->assign('arrOrder', $arrOrder);

            $this->assign('zhttype', Config::get('options.zhttype'));

            // 记录所传参数
            $query = request()->param();
            foreach ($query as $key => $value) {
                $arrQuery[] = sprintf('%s=%s', $key, trim($value));
            }
            $this->assign('strQuery', implode('&', $arrQuery));
            return $this->fetch();
        }
    }


    /**
    * 收支记录
    */
    public function money_log($id = 0){
        if (!empty($id)){
            // 导入分页类
            import ( 'ORG.Util.Page' );
            $arrLog = Db::name('user_money_log')->where('uid', $id)->order('id desc')->paginate(10, false, ['var_page' => 'mlpage']);
            $this->assign('arrLog', $arrLog);

            // 记录所传参数
            $query = request()->param();
            foreach ($query as $key => $value) {
                $arrQuery[] = sprintf('%s=%s', $key, trim($value));
            }
            $this->assign('strQuery', implode('&', $arrQuery));
            return $this->fetch();
        }
    }

    /**
    * 解绑mt4账号
    */
    public function reset($id = 0){
        if ( ! empty($arrAid)){
            $data = array('isbuy' => 0, 'zhmt4uid' => '', 'zhmt4pwd' => '', 'zhmt4server' => '', 'status' => 2, 'modify_time' => time());
            Db::transaction(function() use($data){
                $arrAid = input('post.aid');
                $arrMt4 = Db::name('user')->where('id', 'in', $arrAid)->column('zhmt4uid');
                Db::name('user')->where('id', 'in', $arrAid)->update($data);
                // 删除相对应的mt4
                Db::name('user_mt4')->where('mt4id', 'in', $arrMt4)->delete();
            });            
        }
    }

    /**
    * 审核账号
    */
    public function sysuser()
    {
        if (input('?post.id') && input('?post.status')){
            $id = input('post.id');
            if (input('post.status') == 1){
                $arrUser = Db::name('user')->where(['id' => $id, 'isbuy' => 0])->find();
                if (!empty($arrUser)){
                    $tel = $arrUser['login'];                    
                    Db::transaction(function() use($arrUser){
                        if (Db::name('user')->where('id', $arrUser['uid'])->update(['isbuy' => 1,'modify_time' => time()])){
                            if ( ! empty($arrUser['zhmt4uid'])){
                                // 添加到多mt4账号列表
                                $count = Db::name('user_mt4')->where(['uid' => $arrUser['uid'], 'mt4id' => $arrUser['zhmt4uid']])->count();
                                if ($count == 0){
                                    $mt4Data = array(
                                            'uid' => $arrUser['uid'],
                                            'mt4id' => $arrUser['zhmt4uid'],
                                            'mt4pwd' => $arrUser['zhmt4pwd'],
                                            'mt4server' => $arrUser['zhmt4server'],
                                            'sblfix' => $arrUser['sblfix'],
                                            'remark' => $arrUser['aliyun'],
                                            'modify_time' => time(),
                                            'add_time' => time(),
                                            'operator' => Session::get('admin.username'),
                                        );
                                    // 保存到列表中
                                    $mt4Num = Db::name('user_mt4')->where('uid', $arrUser['uid'])->count();
                                    // 作为默认账号
                                    if ($mt4Num == 0) $mt4Data['is_default'] = 1;

                                    Db::name('user_mt4')->insert($mt4Data);
                                }
                            }
                            $msg ='恭喜您，您的MT4账号已关联成功，您可以随时进入管理页面启动跟单。如有疑问,您可以联系我们的官方邮箱yshfx888@163.com';                 
                            AppCommon::sendmsg_czsms($arrUser['login'], $msg);
                            AppMsg::send($arrUser['uid'], 'bind');
                            echo "操作成功!";
                        }
                    });
                }else{
                    echo "该用户已经审核过了!";
                }
            }else{
                $arrUser = Db::name('user')->where(['id' => $id])->find();
                if ( ! empty($arrUser)){
                    Db::transaction(function() use($arrUser){
                        $tel = $arrUser['login'];
                        $msg ='尊敬的用户您好，系统检测到您绑定的MT4账户有误，请登录亿思汇重新绑定。如有疑问,您可以联系我们的官方邮箱yshfx888@163.com';

                        // 不通过则删除当前mt4,并更新user中的mt4信息
                        Db::name('user_mt4')->where('uid', $arrUser['uid'])->where('mt4id', $arrUser['zhmt4uid'])->delete();
                        $arrMt4 = Db::name('user_mt4')->where('uid', $arrUser['uid'])->order('id desc')->find();
                        $isbuy = 1;
                        if (empty($arrMt4)){
                            $arrMt4 = array('mt4id' => '', 'mt4pwd' => '', 'mt4server' => '', 'remark' => '');
                            $isbuy = 0;
                        }

                        if (Db::name('user')->where('uid', $arrUser['uid'])->update(array('isbuy' => $isbuy, 'zhmt4uid' => $arrMt4['mt4id'], 'zhmt4pwd' => $arrMt4['mt4pwd'], 'zhmt4server' => $arrMt4['mt4server'], 'aliyun' => $arrMt4['remark'], 'modify_time' => time()))){
                            // 删除相对应的mt4
                            AppCommon::sendmsg_czsms($tel, $msg);
                            AppMsg::send($arrUser['uid'], 'bind-fail');
                            echo "操作成功!";
                        } 
                    });
                }else{
                    echo "用户不存在!";
                }

            }
        }
    }

    /**
     * 设置用户上级
     */
    public function setlevel(){
        $uid = input('post.id');
        $level = input('post.level');
        if ( ! empty($uid)){
            Db::name('user')->where('id', $uid)->setField('parent_id', $level);
        }
    }

    //用户申请修改下级列表
    public function sid_list()
    {
        $where = [];
        $keyword = input('keyword');
        $this->assign('keyword', $keyword);
        if (!empty($keyword)) {
            $where['name|phone'] = ['like', "%$keyword%"];
        }
        $status = input('status');
        $this->assign('status', $status);
        if ($status === 0 || $status === '0' || !empty($status)) {
            $where['status'] = $status;
        }
        $lists = Db::name('sid_manage')->where($where)->order('modify_time desc')->paginate(10,false,['query' => request()->param()]);
        $this->assign('lists', $lists);
        return $this->fetch();
    }

    /**
     * 用户审核
     * @return string
     */
    public function sid_check()
    {
        $id = input('id');
        $status = input('status');
        $operator =Session::get('admin.username');
        $time = time();
        $where = [
            'status' => $status,
            'operator' => $operator,
            'modify_time' => $time
        ];
        Db::name('sid_manage')->where('id', $id)->update($where);
    }

    /**下级用户列表
     * @return mixed
     */
    public function sid_user()
    {
        // 搜索
        $map = $arrQuery = array();
        $keyword = trim(input('keyword'));
        if ( ! empty($keyword)){
            $map['id|login|nickname'] = $keyword;
        }
        $this->assign('keyword', $keyword);
        $sid_users = [];

        $pid = input('pid');
        $this->assign('pid', $pid);

        if (!empty($pid)) {
            $map['parent_id'] = $pid;
            $sid_users = Db::name('user')->where($map)->paginate(10, false, ['var_page' => 'spage','query' => request()->param()]);
        }
        $this->assign('sid_users', $sid_users);

        // 记录所传参数
        $query = request()->param();
        foreach ($query as $key => $value) {
            $arrQuery[] = sprintf('%s=%s', $key, trim($value));
        }
        $this->assign('strQuery', implode('&', $arrQuery));
        return $this->fetch();
    }
}
