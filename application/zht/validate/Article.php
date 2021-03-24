<?php
namespace app\wread\validate;
use think\Validate;

class Article extends Validate
{
    protected $rule = [
        'title'  =>  'require',
    ];

    protected $message = [
        'title.require'  =>  '文章标题必须填写！',
    ];

    protected $scene = [
        'add'   =>  ['title'],
        'edit'  =>  ['title'],
    ];
}
