<?php
namespace app\wread\validate;
use think\Validate;

class Gongzh extends Validate
{
    protected $rule = [
        'gh_name'  =>  'require|max:25',
    ];

    protected $message = [
        'gh_name.require'  =>  '公号名称必须填写！',
    ];

    protected $scene = [
        'add'   =>  ['gh_name'],
        'edit'  =>  ['gh_name'],
    ];
}
