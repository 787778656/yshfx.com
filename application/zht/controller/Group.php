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
use app\common\controller\Admin;

class Group extends Admin
{
    /**
    * 分组列表
    */  
    public function index(){
        // 导入分页类
        import ( 'ORG.Util.Page' );
        $keyword = input('keyword');
        $map = array();
        if ( ! empty($keyword)){
            $map['bn|name|mt4id'] = array('like', "%$keyword%");
        }

        $status = input('status');
        if ( ! empty($status)){
            $map['status'] = $status;
        }
        
        $arrGroup = Db::name('mt4Group')->where($map)->order('id desc')->paginate(10);
        // 加载Mt4账号
        $arrAccount = Db::name('mt4Account')->column('id,bn,name,mt4id');
        $this->assign('arrAccount', $arrAccount);   

        $this->assign('arrGroup', $arrGroup);
        $this->assign('status', $status);
        $this->assign('keyword', $keyword);
        return $this->fetch();
    }

    /**
    * 添加分组
    */
    public function add(){
        // 加载Mt4账号
        $arrAccount = Db::name('mt4Account')->column('id,bn,name,mt4id');
        $this->assign('arrAccount', $arrAccount);

        if (input('?post.bn')){
            $Group = model('Mt4Group');
            $data = [
                'bn' => input('post.bn'),
                'name' => input('post.name'),
                'mt4id' => input('post.arrmt4id'),
                'remark' => input('post.remark'),
            ];
            // 加入编辑人等信息
            $data['operator'] = Session::get('admin.username');
            if ($Group->save($data)){
                $this->success('账号添加成功！', url('Group/index'));
            }else{
                $this->error('账号添加失败！');
            }
        }
        return $this->fetch();
    }

    /**
    * 编辑分组
    */
    public function edit($id = 0){
        if ( ! empty($id)){
            // 加载Mt4账号
            $arrAccount = Db::name('mt4Account')->column('id,bn,name,mt4id');
            $this->assign('arrAccount', $arrAccount);

            $Group = model('Mt4Group');
            if ( ! input('?post.id')){
                $arrGroup = $Group::get($id);
                $this->assign('group', $arrGroup);
            }else{
                $data = [
                    'bn' => input('post.bn'),
                    'name' => input('post.name'),
                    'mt4id' => input('post.arrmt4id'),
                    'remark' => input('post.remark'),
                ];
                // 加入编辑人等信息
                $data['operator'] = Session::get('admin.username');

                if (empty($data['mt4id'])){
                    $this->error('组合修改失败！');
                }else{
                    if ($Group->save($data, ['id' => input('post.id')])){
                        $this->success('组合修改成功！', url('Group/index'));
                    }else{
                        $this->error('组合修改失败！');
                    }
                }
            }
            return $this->fetch();
        }
    }

    /**
    * 更新状态
    */
    public function status(){
        if (input('?post.aid')){
            $id = input('post.aid');
            $status = input('post.status');
            $status = ($status == 1) ? 2 : 1;
            $Group = model('Mt4Group');
            $Group->save(['status' => $status], ['id' => $id]);
        }
    }

    /**
    * 删除分组
    */
    public function del(){
        if (input('?post.aid')){
            $id = input('post.aid');
            $Group = model('Mt4Group');
            $Group::destroy($id);
        }
    }
}
