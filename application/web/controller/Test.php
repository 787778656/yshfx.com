<?php
/**
 * 测试代码 
 */
namespace app\web\controller;
use think\Db;

Class Test
{
    public function amysql()
    {
        $res = Db::name('user')->order('id desc')->limit(10)->column('login');
        print_r($res);
    }

    public function amysql1()
    {
        
    }
}