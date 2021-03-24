<?php
/**
* zht签约信号管理
*/
namespace app\web\controller;
use app\common\controller\Admin;
use think\Db;
use think\Model;
use think\Session;
use app\common\controller\Common as AppCommon;
use app\common\controller\Msg as AppMsg;

class Signal extends Admin
{
    private $area_arr = [  //用户信息中的区域对应的国家
        'China,中国' => '中国',
        'Russia' => '俄罗斯',
        'Singapore' => '新加坡',
        'Australia' => '澳大利亚',
        'Korea' => '韩国',
        'New Zealand' => '新西兰',
        'Canada' => '加拿大',
        'Germany' => '德国',
        'Switzerland' => '瑞士',
        'Japan' => '日本',
        'France' => '法国',
        'England' => '英国',
        'America' => '美国'
    ];
    public function index()
    {
        // 导入分页类
        import ( 'ORG.Util.Page' );
        $where = [];
        $status = input('status');
        $keyword = input('keyword');
        if (!empty($keyword)){
            $where['uid|mt4id'] = array('like', "%$keyword%");
        }
        if ($status !== NULL && $status !== '') {
            $where['status'] = $status;
        }
        $data = Db::name('Mt4Signal')->where($where)->order('id desc')->paginate(10, false, ['query' =>request()->param()]);
        $this->assign('data', $data);

        return $this->fetch();
    }


    /**
     * 签约信号审核
     */
    public function check()
    {
        $model = model('Mt4Signal');
        $id = input('id');
        $status = input('status');
        $operator =Session::get('admin.username');
        $data = [
            'status' => $status,
            'operator' => $operator
        ];

        if($model->save($data, ['id' => $id])) {
            $info = $model->get($id)->toArray();
            $syn=false;
            if (!empty($info)) {
                if($status == 1) {
                    $syn = $this->synchronize($info);  //同步此信号到主信号列表
                } elseif ($status == 2) {
                    $user = Db::name('User')->where('uid',$info['uid'])->find();
                    $msg = '尊敬的用户您好，您上传的信号账号或密码有误或信号评分过低，暂无法通过，请您重新上传。如有疑问,您可以联系我们的官方邮箱yshfx888@163.com';
                    // AppCommon::sendmsg_czsms($user['login'], $msg, 'signal_check_'.$info['uid']);
                    AppMsg::send($info['uid'], 'upload_accout_fail');
                }
                if ($syn) {
                   return json_encode(['code' => 'ok', 'msg' => '审核成功,并已同步该信号至主信号中']);
                }
                return json_encode(['code' => 200, 'msg' => '操作成功']);

            }
            return json_encode(['code' => 200, 'msg' => '操作成功']);
        }
        return json_encode(['code' => 302, 'msg' => '操作失败']);

    }

    /**
     * 删除签约信号
     */
    public function delete()
    {
        $id = input('id');
        Db::name('Mt4Signal')->where('id', $id)->delete();
    }

    //签约审核通过后将此信号相关内容同步至zht主信号中
    public function synchronize($info)
    {
        $time = time();
        $nickname = $u_img = $area = '';
        $result = 0;
        if (!empty($info)) {
            $user = Db::name('User')->where('uid', $info['uid'])->find();
            $account = Db::name('Mt4Account')->where('mt4id', $info['mt4id'])->find();
            if (!empty($account)) {
                return false;
            }
            // 自动编号
            $maxId = Db::name('Mt4Account')->max('id');
            $autoBn = sprintf('0008%s', $maxId+1);
            if (!empty($user)) {
                if (!empty($info['sign_name'])) {
                    $nickname = $info['sign_name'];
                }else {
                    if (!empty($user['nickname'])) {
                        $nickname = $user['nickname'];
                    } else {
                        $nickname = $user['login'];
                    }
                }
                $u_img = $user['u_img'];  //用户图像
                if (isset($this->area_arr[$user['area']])) {
                    $area = $this->area_arr[$user['area']];  //区域
                }
            }
            $operator =Session::get('admin.username');
            $data['img'] = $u_img;
            $data['country'] = $area;
            $data['name'] = $nickname;
            $data['bn'] = $autoBn;
            $data['show'] = 2;
            $data['mt4id'] = $info['mt4id'];
            $data['mt4pwd'] = $info['mt4pwd'];
            $data['mt4server'] = $info['mt4server'];
            $data['operator'] = $operator;
            $data['add_time'] = $time;
            $data['modify_time'] = $time;
            $data['detail'] = $info['detail']?$info['detail']:'';
            // $data['studio_id'] = $info['studio_id'];
            $data['from_uid'] = $info['uid'];
            $result = Db::name('Mt4Account')->insert($data);
        }
        return $result > 0;
    }
}
