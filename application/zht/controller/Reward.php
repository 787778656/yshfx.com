<?php
/**
* 佣金体系
* @author coder<coder@qq.com>
* 2017-12-12
*/
namespace app\zht\controller;
use app\common\controller\Admin;
use \think\Config;
use think\Db;
use \think\Session;
use app\common\controller\Money;
use app\common\controller\Common as AppCommon;
use app\common\controller\Msg as AppMsg;

class Reward extends Admin
{
    public function index()
    {
        $setting = Db::name('reward_set')->where('id', 1)->find();
        $this->assign('setting', $setting);
        return $this->fetch();
    }

    public function save()
    {
        $reward = input('reward'); //返佣比例设置
        # 这里需要对数据进行过滤
        $reward_arr = explode('|',$reward);
        if($reward_arr && count($reward_arr)){
            foreach($reward_arr as $v){
                // if(!is_numeric($v) || floatval($v)<0 || floatval($v)>1){
                if(!is_numeric($v)){
                    return $this->error('设置失败');
                    exit;
                }
            }
        }
        $data = [
            'reward' => $reward,
            'operator' => Session::get('admin.username'),
        ];
        # 先判断是否存在 如果不存在则插入
        if(Db::name('reward_set')->find(1)){
            $data['modify_time'] = time();
            Db::name('reward_set')->where('id', 1)->update($data);
        }else{
            $data['id'] = 1; # 直接指定id为1
            $data['add_time'] = time();
            $res = Db::name('reward_set')->insert($data);
            if($res){
                return $this->success('设置成功', url('index'));
            }else{
                return $this->error('设置失败');
            }
        }
        $this->success('修改成功', url('index'));
    }
    /**
    * 充值返佣
    */
    public function recharge()
    {
        // 导入分页类
        import ( 'ORG.Util.Page' );
        $keyword = input('keyword');
        $map = array();

        if ( ! empty($keyword)){
            $map['uid'] = trim($keyword);
        }
        $map['class'] = 'recharge';
        $arrReward = Db::name('user_reward')->where($map)->order('id desc')->paginate(15);
        $this->assign('arrReward', $arrReward);
        return $this->fetch();
    }

    /**
    * 审核状态
    */
    public function recharge_status(){
        if (input('?post.id')){
            $id = input('post.id');
            $uid = input('post.uid');
            $status = input('post.status');
            if(Db::name('user_reward')->where('id', $id)->update(['status' => $status, 'modify_time' => time(), 'operator' => Session::get('admin.username')])){
                if (intval($status) == 1){ // 返利
                    $arrReward = Db::name('user_reward')->where('id', $id)->find();
                    if ( ! empty($arrReward)){
                        if (Db::name('user')->where('uid', $arrReward['uid'])->setInc('imoney', $arrReward['amount'])){
                            // 写入消费日志
                            Money::record($arrReward['uid'], $arrReward['amount'], $arrReward['src_id'], 'recharge', '邀请注册充值返佣');
                        }
                    }
                }
                echo '1';
            }else{
                echo '2';
            }
        }
    }

    // /**
    // * 跟单返佣
    // */
    // public function follow()
    // {
    //     // 导入分页类
    //     import ( 'ORG.Util.Page' );
    //     $keyword = input('keyword');
    //     $map = array();

    //     if ( ! empty($keyword)){
    //         $map['uid'] = trim($keyword);
    //     }
    //     $map['class'] = 'follow';
    //     $arrReward = Db::name('user_reward')->where($map)->order('id desc')->paginate(15);
    //     $this->assign('arrReward', $arrReward);
    //     return $this->fetch();
    // }


    /**
    * 跟单返佣
    */
    public function follow()
    {
        // 导入分页类
        import ( 'ORG.Util.Page' );
        $keyword = input('keyword');
        $this->assign('keyword',$keyword);
        $map = array();

        $start_time = trim(input('start_time')); //开始时间

        $end_time = trim(input('end_time'));   //结束时间
        $this->assign([
            'start_time'    =>  $start_time,
            'end_time'      =>  $end_time
        ]);
        if (!empty($start_time)) {
            if (empty($end_time)) {
                $end_time = date('Y-m-d');
            }

            if ($start_time > $end_time) {
                $this->error('开始时间必须小于结束时间',url('follow'));
            }
            $map['add_time'] = ['between', [strtotime($start_time.'00:00:00'),strtotime($end_time.'23:59:59')]];
        }

        if ( ! empty($keyword)){
            $map['uid'] = trim($keyword);
        }
        $map['class'] = 'money-follow-reward';
        $arrReward = Db::name('user_money_log')->where($map)->order('id desc')->paginate(15,false,['query'=>request()->param()]);
        $this->assign('arrReward', $arrReward);
        return $this->fetch();
    }


    /**
     * 充值记录
     */
    public function recharge_log(){
        $keyword = input('keyword');
        $this->assign('keyword', $keyword);

        $start_time = input('start_time'); //开始时间
        $this->assign('start_time', $start_time);

        $end_time = input('end_time');   //结束时间
        $this->assign('end_time', $end_time);

        if (!empty($start_time)) {
            if (empty($end_time)) {
                $end_time = date('Y-m-d');
            }
            if ($start_time > $end_time) {
                $this->error('开始时间必须小于结束时间',url('recharge_log'));
            }
            $where['modify_time'] = ['between', [strtotime($start_time.'00:00:00'),strtotime($end_time.'23:59:59')]];
        }
        $where['status'] = 1;
        if (!empty($keyword)) {
            $where['uid|trade_no|third_trade_no'] = ['like', trim($keyword)];
        }
        $arrOrder = Db::name('mt4_payment')->where($where)->order('id desc')->paginate(10, false,  ['query' =>request()->param()]);
        $this->assign('arrOrder', $arrOrder);
        return $this->fetch();
    }

    /**
     * 账户流水
     */
    public function water(){
        // 汇总
        $this->account_summary();

        $keyword = input('keyword');
        $this->assign('keyword', $keyword);

        $start_time = input('start_time'); //开始时间
        $this->assign('start_time', $start_time);

        $end_time = input('end_time');   //结束时间
        $this->assign('end_time', $end_time);

        if (!empty($start_time)) {
            if (empty($end_time)) {
                $end_time = date('Y-m-d');
            }
            if ($start_time > $end_time) {
                $this->error('开始时间必须小于结束时间',url('water'));
            }
            $where['add_time'] = ['between', [strtotime($start_time.'00:00:00'),strtotime($end_time.'23:59:59')]];
        }
        $where['status'] = 1;
        if (!empty($keyword)) {
            $where['uid'] = ['like', trim($keyword)];
        }
        $arrOrder = Db::name('user_money_log')->where($where)->order('id desc')->paginate(10, false,  ['query' =>request()->param()]);
        $this->assign('arrOrder', $arrOrder);
        return $this->fetch();
    }

    /**
    * 
    */
    private function account_summary(){
        $arrUser = Db::name('mt4_payment')->where(['type' => 10, 'status' => 1])->column('uid');
        $arrUser = $arrUser && count($arrUser) > 0 ? $arrUser : [0]; # 容错处理 没有用户就会报错
        $summary['vip'] = Db::name('mt4_payment')->where(['type' => 1, 'status' => 1])->sum('amount');
        $summary['recharge'] = Db::name('mt4_payment')->where('uid', 'in', $arrUser)->where(['type' => 10, 'status' => 1])->sum('amount');
        $summary['withdraw'] = Db::name('user_withdraw')->where(['status' => 1])->sum('amount');
        $summary['bail'] = Db::name('user')->where('uid', 'in', $arrUser)->sum('smoney - credited');
        $summary['imoney'] = Db::name('user')->where('uid', 'in', $arrUser)->sum('imoney');

        $this->assign('summary', $summary);
    }
    /**
     * execl导出
     */
    public function exp_excel_water()
    {
        $keyword = input('keyword');

        $start_time = input('start_time'); //开始时间

        $end_time = input('end_time');   //结束时间

        if (!empty($start_time)) {
            if (empty($end_time)) {
                $end_time = date('Y-m-d');
            }
            if ($start_time > $end_time) {
                $this->error('开始时间必须小于结束时间',url('water'));
            }
            $where['add_time'] = ['between', [strtotime($start_time.'00:00:00'),strtotime($end_time.'23:59:59')]];
        }
        $where['status'] = 1;
        if (!empty($keyword)) {
            $where['uid'] = ['like', trim($keyword)];
        }
        $arrOrder = Db::name('user_money_log')->where($where)->order('id desc')->select();
        $cellName = [['id', 'id'],['uid', '用户UID'], ['amount', '金额'],['class', '类别'],['remark', '备注'], ['operator', '来源'],['add_time', '添加时间']];  //导出的列名
        self::exportExcel('充值记录', $cellName, $arrOrder); //导出Excel
    }

    /**
     * execl导出
     */
    public function exp_excel()
    {
        $keyword = input('keyword');

        $start_time = input('start_time'); //开始时间

        $end_time = input('end_time');   //结束时间

        if (!empty($start_time)) {
            if (empty($end_time)) {
                $end_time = date('Y-m-d');
            }
            if ($start_time > $end_time) {
                $this->error('开始时间必须小于结束时间',url('recharge_log'));
            }
            $where['modify_time'] = ['between', [strtotime($start_time.'00:00:00'),strtotime($end_time.'23:59:59')]];
        }
        $where['status'] = 1;
        if (!empty($keyword)) {
            $where['uid|trade_no|third_trade_no'] = ['like', trim($keyword)];
        }
        $arrOrder = Db::name('mt4_payment')->where($where)->order('id desc')->select();
        $cellName = [['id', 'id'],['uid', '用户UID'], ['trade_no', '内部订单号'], ['third_trade_no', '第三方订单号'], ['server', '充值VIP等级'], ['amount', '充值金额'],['operator', '充值方式'],['modify_time', '充值时间']];  //导出的列名
        self::exportExcel('充值记录', $cellName, $arrOrder); //导出Excel
    }

    /**
     * execl导出提现转账数据
     */
    public function exp_excel_withdraw()
    {
        $keyword = input('keyword');
        $map = [
            'status' => 3
        ];
        $start_time = trim(input('start_time')); //开始时间

        $end_time = trim(input('end_time'));   //结束时间
        if (!empty($start_time)) {
            if (empty($end_time)) {
                $end_time = date('Y-m-d');
            }

            if ($start_time > $end_time) {
                $this->error('开始时间必须小于结束时间',url('water'));
            }
            $map['modify_time'] = ['between', [strtotime($start_time.'00:00:00'),strtotime($end_time.'23:59:59')]];
        }

        if ( ! empty($keyword)){
            $map['uid'] = trim($keyword);
        }
        $arrWithdraw = Db::name('user_withdraw')->where($map)->order('modify_time desc')->select();
        $cellName = [['id', 'id'],['uid', '用户UID'], ['amount', '金额'],['account', '提现账户'],['third_trade_no', '第三方订单号'], ['modify_time', '提现时间']];  //导出的列名
        self::exportExcel('充值记录', $cellName, $arrWithdraw); //导出Excel
    }


    /**
     * execl导出分佣记录数据
     */
    public function exp_excel_reward_follow()
    {
        $keyword = input('keyword');
        $map = array();
        $start_time = trim(input('start_time')); //开始时间

        $end_time = trim(input('end_time'));   //结束时间
        
        if (!empty($start_time)) {
            if (empty($end_time)) {
                $end_time = date('Y-m-d');
            }

            if ($start_time > $end_time) {
                $this->error('开始时间必须小于结束时间',url('follow'));
            }
            $map['add_time'] = ['between', [strtotime($start_time.'00:00:00'),strtotime($end_time.'23:59:59')]];
        }

        if ( ! empty($keyword)){
            $map['uid'] = trim($keyword);
        }
        $map['class'] = 'money-follow-reward';
        $reward_follow = Db::name('user_money_log')->field('id,uid,amount,src_id,remark,\'###\' as modify_time,FROM_UNIXTIME(`add_time`,\'%Y年%m月%d日 %H:%i:%s\') add_time,IFNULL(`operator`,\'---\') as operator')->where($map)->order('add_time desc')->select();
        $cellName = [['id', 'id'],['uid', '用户UID'], ['amount', '返佣金额'],['src_id', '相关ID'],['remark', '备注'], ['modify_time', '修改时间'], ['add_time', '添加时间'], ['operator', '操作者']];  //导出的列名
        // self::exportExcel('分佣记录', $cellName, $reward_follow); //导出Excel
        AppCommon::exportExcel('分佣记录', $cellName, $reward_follow); //导出Excel
    }


    /**
     * 导出excel文件
     * @param $expTitle
     * @param $expCellName  导出列数组  $expCellName=[['id','id'],['mobile'，'手机号']];
     * @param $expTableData  导出的数据
     */
    private static function exportExcel($expTitle,$expCellName,$expTableData){
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
        $fileName = date('YmdHis');//文件名称
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        vendor('PHPExcel.Classes.PHPExcel');

        $objPHPExcel = new \PHPExcel();

        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex()->setCellValue($cellName[$i].'1', $expCellName[$i][1]);
        }
        // Miscellaneous glyphs, UTF-8
        for($i=0;$i<$dataNum;$i++){
            for($j=0;$j<$cellNum;$j++){
                if ($j==($cellNum-1)) {
                    $objPHPExcel->getActiveSheet()->setCellValue($cellName[$j] . ($i + 2), date('Y-m-d H:i:s',$expTableData[$i][$expCellName[$j][0]]));
                } else {
                    $objPHPExcel->getActiveSheet()->setCellValue($cellName[$j] . ($i + 2), $expTableData[$i][$expCellName[$j][0]]);
                }
            }
        }

        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    // /**
    // * 提现申请
    // */
    // public function withdraw()
    // {
    //     // 导入分页类
    //     import ( 'ORG.Util.Page' );
    //     $keyword = input('keyword');
    //     $this->assign('keyword',$keyword);
    //     $map = [
    //         'status' => ['notin', [1,3]]
    //     ];

    //     if ( ! empty($keyword)){
    //         $map['uid'] = trim($keyword);
    //     }
    //     $arrWithdraw = Db::name('user_withdraw')->where($map)->order('status asc,modify_time desc')->paginate(15, false, ['query' =>request()->param()]);
    //     $this->assign('arrWithdraw', $arrWithdraw);
    //     return $this->fetch();
    // }


    # 提现申请列表
    public function withdraw()
    {
        $keyword = input('keyword');
        // $map = [
        //     'status' => ['notin', [1,3]]
        // ];
        $map = [];

        if ( ! empty($keyword)){
            $map['uid'] = trim($keyword);
        }

        # 从index页面过来
        if($uid = input('uid')){
            $map['uid'] = $uid;
            $keyword = $uid;
        }
        $this->assign('keyword',$keyword);

        $arrWithdraw = Db::name('user_withdraw')->where($map)->order('status asc,modify_time desc,add_time desc')->paginate(15, false, ['query' =>request()->param()]);
        $this->assign('arrWithdraw', $arrWithdraw);

        return $this->fetch();
    }


    // /**
    //  * 处理提现申请
    //  */
    // public function do_withdraw()
    // {
    //     // 导入分页类
    //     import ( 'ORG.Util.Page' );
    //     $keyword = input('keyword');
    //     $this->assign('keyword',$keyword);
    //     $map = [
    //         'status' => ['in', [1,3]]
    //     ];
    //     $start_time = trim(input('start_time')); //开始时间
    //     $this->assign('start_time', $start_time);

    //     $end_time = trim(input('end_time'));   //结束时间
    //     $this->assign('end_time', $end_time);
    //     if (!empty($start_time)) {
    //         if (empty($end_time)) {
    //             $end_time = date('Y-m-d');
    //         }

    //         if ($start_time > $end_time) {
    //             $this->error('开始时间必须小于结束时间',url('water'));
    //         }
    //         $map['modify_time'] = ['between', [strtotime($start_time.'00:00:00'),strtotime($end_time.'23:59:59')]];
    //     }

    //     if ( ! empty($keyword)){
    //         $map['uid'] = trim($keyword);
    //     }
    //     $arrWithdraw = Db::name('user_withdraw')->where($map)->order('status asc,modify_time desc')->paginate(15, false, ['query' =>request()->param()]);
    //     $this->assign('arrWithdraw', $arrWithdraw);
    //     return $this->fetch();
    // }

    /**
    * 审核状态
    */
    // public function withdraw_status(){
    //     if (input('?post.id')){
    //         $id = input('post.id');
    //         $uid = input('post.uid');
    //         $status = input('post.status');
    //         $time = time();
    //         if ($status==1) {
    //             $imoney = Db::name('user')->where('uid', $uid)->value('imoney');
    //             $withdraw_money = Db::name('user_withdraw')->where('id', $id)->value('amount');
    //             if ($imoney < $withdraw_money) {
    //                 exit('用户账户可提现余额不足');
    //             }
    //         }

    //         $count = Db::name('user_withdraw')->where('id',$id)->update(['status' => $status, 'modify_time' => $time, 'operator' => Session::get('admin.username')]);
    //         if (!empty($count)) {
    //             if ($status==1) {
    //                 Db::name('user_withdraw')->where('id', $id)->update(['modify_time' => $time]);
    //                 echo '操作成功！';
    //                 AppMsg::send($uid, '您的提现申请已通过');
    //                 AppCommon::sendmsg_czsms('15800339015', '用户提现，请及时处理！');
    //             } elseif ($status==2) {
    //                 echo '驳回操作成功！';
    //                 AppMsg::send($uid, '您的提现申请被驳回');
    //             } elseif ($status==3) {
    //                 echo '手动提现操作成功！';
    //             }
    //         }
    //     }
    // }


     # 提现处理
    public function withdraw_status()
    {
        if (input('?post.id')){
            $id = input('post.id');
            $uid = input('post.uid');
            $status = input('post.status');
            $time = time();
            
            if(!in_array($status,[1,2,3])){
                exit('状态参数不对');
            }

            $imoney = Db::name('user')->where('uid', $uid)->value('imoney');
            $withdraw_money = Db::name('user_withdraw')->where('id', $id)->value('amount');

            if(!$imoney || !$withdraw_money){
                exit('账户余额或者提现金额错误');
            }

            if ($status==1) {
                if ($imoney < $withdraw_money) {
                    exit('用户账户可提现余额不足');
                }
            }

            Db::startTrans();
            try{
                $count = Db::name('user_withdraw')->where('id',$id)->update(['status' => $status, 'modify_time' => $time, 'operator' => Session::get('admin.username')]);
                if (!empty($count)) {
                    if ($status==1) {
                        // Db::name('user_withdraw')->where('id', $id)->update(['modify_time' => $time]);
                        # user表中的imoney需要减去提现金额
                        // $imoney = $imoney - $withdraw_money;
                        // $res_imoney = Db::name('user')->where('uid', $uid)->update(['imoney'=>$imoney]);
                        $res_imoney = Db::name('user')->where('uid', $uid)->setDec('imoney',$withdraw_money);
                        if($res_imoney){
                            Db::commit(); 
                            Money::record($uid, (-1)*$withdraw_money, $id, 'withdraw', '提现');
                            AppMsg::send($uid, '您的提现申请已通过');
                            exit('操作成功！');
                        }else{
                            Db::rollback();
                            exit('操作失败，请重试！');
                        }
                        // AppCommon::sendmsg_czsms('15800339015', '用户提现，请及时处理！');
                    } elseif ($status==2) {
                        Db::commit();  
                        AppMsg::send($uid, '您的提现申请被驳回');
                        exit('驳回操作成功！');
                    } 
                    // elseif ($status==3) {
                    //     echo '手动提现操作成功！';
                    // }
                }
                Db::rollback();  
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
            }

            
        }
    }


    /**
     * 后台提现
     * @param $id
     * @param $uid
     * @param $status
     * @param $amount
     * @param $payee_account //收款方账户
     * @param string $alipay_logonid
     * @param $remark
     * @return string
     */
    // public function auto_withdraw($id,$uid,$status,$amount,$payee_account,$alipay_logonid='ALIPAY_LOGONID',$remark)
    // {
    //     vendor('alipay-sdk-PHP-3.AopSdk');
    //     $config = Config::get('alipay.withdraw');
    //     // 获取数据
    //     $tradeNo = date('YmdHis').substr(microtime(),0,4)*100;
    //     $aop = new \AopClient();
    //     $aop->appId = $config['appid']; //appid
    //     //开发者私钥去头去尾去回车，一行字符串
    //     $aop->rsaPrivateKey = $config['merchant_private_key'];
    //     //支付宝公钥，一行字符串
    //     $aop->alipayrsaPublicKey = $config['alipay_public_key'];
    //     $aop->signType = $config['sign_type'];

    //     $request = new \AlipayFundTransToaccountTransferRequest();
    //     if ($amount < 0.1) {
    //         return json_encode(['code' => 302, 'msg' => '转账金额必须大于0.1']);
    //     }
    //     $imoney = Db::name('user')->where('uid', $uid)->value('imoney');
    //     $withdraw_money = Db::name('user_withdraw')->where('id', $id)->value('amount');

    //     if ($imoney < $amount) {
    //         return json_encode(['code' => 302, 'msg' => '用户账户可提现余额不足']);
    //     }
    //     if ($imoney < $withdraw_money) {
    //         return json_encode(['code' => 302, 'msg' => '用户账户可提现余额不足']);
    //     }
    //     $withdraw_status = Db::name('user_withdraw')->where('id', $id)->value('status');
    //     if ($withdraw_status==3) {
    //         return json_encode(['code' => 302, 'msg' => '该笔提现已处理，请勿重复操作！']);
    //     }

    //     $params = [
    //         'out_biz_no' => $tradeNo, //商户转账唯一订单号。发起转账来源方定义的转账单据ID，用于将转账回执通知给来源方。
    //         'payee_type' => $alipay_logonid, //收款方账户类型。1：ALIPAY_USERID：支付宝账号对应的支付宝唯一用户号。以2088开头的16位纯数字组成。2、ALIPAY_LOGONID：支付宝登录号，支持邮箱和手机号格式。
    //         'payee_account' => trim($payee_account),  //收款方账户
    //         'amount' => $amount,  //转账金额
    //         'payer_show_name' => '铂盛集团科技有限公司', //付款方名称（可选）
    //         //'payee_real_name' => '', //收款方真实姓名（可选）
    //         'remark' => $remark //转账备注（可选）
    //     ];
    //     $request->setBizContent(json_encode($params));

    //     $result = $aop->execute ($request);

    //     $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
    //     $resultCode = $result->$responseNode->code;
    //     if(!empty($resultCode)&&$resultCode == 10000){
    //         $this->setMoneyLog($id,$uid,$status,$amount,$withdraw_money);
    //         Db::name('user_withdraw')->where('id', $id)->update(['status' => 3, 'dealer' => Session::get('admin.username'),'trade_no' => $tradeNo, 'third_trade_no' => $result->$responseNode->order_id, 'modify_time' => strtotime($result->$responseNode->pay_date), 'real_amount' => $amount]);
    //         return json_encode(['code' => 200, 'msg' => '转账成功']);
    //     }
    //     return json_encode(['code' => $result->$responseNode->code, 'msg' => $result->$responseNode->msg]);
    // }

    public function setMoneyLog($id,$uid,$status,$amount,$withdraw_money)
    {
        if (!empty($id) && !empty($uid)) {
            if (intval($status) == 1) {
                $imoney = Db::name('user')->where('uid', $uid)->value('imoney');
                $max = max($amount,$withdraw_money);
                if ($imoney >= $max) {
                    if (Db::name('user')->where('uid', $uid)->setDec('imoney', $max)) {
                        // 写入消费日志
                        Money::record($uid, $max * (-1), $id, 'withdraw', '提现');
                        // 发送站内信
                        AppMsg::send($uid, 'withdraw_sh_success');
                        return '操作成功！';
                    }
                        return '操作失败！';
                }
                return '账户余额不足，无法完成提现！';
            }
        } else {
            return '参数错误！';
        }
    }
}
