<?php
namespace app\web\model;
use think\Model;

class Mt4Signal extends Model
{
	protected $autoWriteTimestamp = true;
	protected $createTime = 'add_time';
	protected $updateTime = 'modify_time';

}
