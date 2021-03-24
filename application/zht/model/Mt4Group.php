<?php
namespace app\zht\model;
use think\Model;

class Mt4Group extends Model
{
	protected $autoWriteTimestamp = true;
	protected $createTime = 'add_time';
	protected $updateTime = 'modify_time';
}
