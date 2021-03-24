<?php
/**
* 模块公用类程序
* @author efon.cheng<efon@icheng.xyz>
* 2017-08-31
*/
namespace app\common\controller;
use \think\Config; 
use \think\Controller;
use think\Cookie;
use \think\Db;
use think\Request;
use \think\Session;
use PHPExcel;
use PHPExcel_IOFactory;
use \think\cache\driver\Redis;

class Common extends Controller
{
    private static $uploadRoot = '/static.v.yshfx.com/upload/';
    private static $domain = 'http://static.v.yshfx.com/';
    private static $apisRoot = '/static.v.yshfx.com/public/static/';
    private static $apisDomain = 'https://apis.yshfx.com/';
    private static $sms_prefix = 'sms_send'; //redis短信前缀
    private static $day_expire = 86400; //1天过期时间
    private static $mobiles = [  //公司内部发送的手机号
            '18164026961', 
        ];

	public function __construct()
    {
        parent::__construct();
    }

    /**
    * 图片上传
    */
	public static function uploadImg(){
	    // 获取表单上传文件
	    $file = request()->file('imgFile');
	    // 验证上传文件
        $validate = [
	        'type' => 'image/jpeg,image/gif,image/png',
	        'ext' => 'jpeg,jpg,png,gif', 
	        'size' => 3 * 1024 * 1024
        ];

        $rootPath = dirname(dirname(dirname(ROOT_PATH))).self::$uploadRoot.'image/';
        $savePath = $rootPath;
        // 创建目录
        //if( ! is_dir($savePath)) mkdir($savePath, 0777, true);

        // 移动文件
        $info = $file->validate($validate)->rule('date')->move($savePath);
        if($info){
            // 成功上传后 获取上mkdir($path,0777,true);传信息
            // echo $info->getExtension(); 
            $fileName = $info->getPathname();
            $fileName = str_replace($rootPath, '', $fileName);

            echo json_encode(array('error'=>0,'url'=> $fileName));
        }else{
            // 上传失败获取错误信息
            echo json_encode(array('error'=>1,'message'=>$file->getError()));
        }    
    }
    
    /**
    * 图片上传
    */
	public static function uploadImg2(){
	    // 获取表单上传文件
	    $file = request()->file('imgFile');
	    // 验证上传文件
        $validate = [
	        'type' => 'image/jpeg,image/gif,image/png',
	        'ext' => 'jpeg,jpg,png,gif', 
	        'size' => 3 * 1024 * 1024
        ];

        $rootPath = ROOT_PATH.'public/static.v.yshfx.com/upload/image/';
        $savePath = $rootPath;
        // 创建目录
        //if( ! is_dir($savePath)) mkdir($savePath, 0777, true);

        // 移动文件
        $info = $file->validate($validate)->rule('date')->move($savePath);
        if($info){
            // 成功上传后 获取上mkdir($path,0777,true);传信息
            // echo $info->getExtension(); 
            $fileName = $info->getPathname();
            $fileName = str_replace($rootPath, '', $fileName);

            echo json_encode(array('error'=>0,'url'=> $fileName));
        }else{
            // 上传失败获取错误信息
            echo json_encode(array('error'=>1,'message'=>$file->getError()));
        }    
	}


    /**
    * 文件上传
    */
    public static function uploadFile(){
        // 获取表单上传文件
        $file = request()->file('imgFile');
        // 验证上传文件
        $validate = [
            'type' => 'application/vnd.ms-excel,application/octet-stream,text/plain',
            'ext' => 'xls,xlsx,csv', 
            'size' => 10 * 1024 * 1024
        ];

        $rootPath = dirname(dirname(dirname(ROOT_PATH))).self::$uploadRoot.'file/';
        $savePath = $rootPath;
        // 创建目录
        //if( ! is_dir($savePath)) mkdir($savePath, 0777, true);

        // 移动文件
        $info = $file->validate($validate)->rule('date')->move($savePath);
        if($info){
            // 成功上传后 获取上mkdir($path,0777,true);传信息
            // echo $info->getExtension(); 
            $fileName = $info->getPathname();
            $fileName = str_replace($rootPath, '', $fileName);

            echo json_encode(array('error'=>0,'url'=> $fileName));
        }else{
            // 上传失败获取错误信息
            echo json_encode(array('error'=>1,'message'=>$file->getError()));
        }    
    }

    /**
    * 文件上传
    */
    public static function uploadFile2(){
        // 获取表单上传文件
        $file = request()->file('imgFile');
        // 验证上传文件
        $validate = [
            'type' => 'application/vnd.ms-excel,application/octet-stream,text/plain',
            'ext' => 'xls,xlsx,csv', 
            'size' => 10 * 1024 * 1024
        ];

        $rootPath = ROOT_PATH.'public/static.v.yshfx.com/upload/file/';
        $savePath = $rootPath;
        // 创建目录
        //if( ! is_dir($savePath)) mkdir($savePath, 0777, true);

        // 移动文件
        $info = $file->validate($validate)->rule('date')->move($savePath);
        if($info){
            // 成功上传后 获取上mkdir($path,0777,true);传信息
            // echo $info->getExtension(); 
            $fileName = $info->getPathname();
            $fileName = str_replace($rootPath, '', $fileName);

            echo json_encode(array('error'=>0,'url'=> $fileName));
        }else{
            // 上传失败获取错误信息
            echo json_encode(array('error'=>1,'message'=>$file->getError()));
        }    
    }


    /**
    * base64保存为图片
    */
    public static function base64img($content = null, $imgName = null, $path = null, $ext = 'jpg'){
        $rootPath = dirname(dirname(dirname(ROOT_PATH))).self::$uploadRoot.'image/';

        if (empty($path)) $path = date('Ymd');
        if (empty($imgName)) $imgName = time();

        $savePath = sprintf('%s%s', $rootPath, $path);
        if( ! is_dir($savePath)) mkdir($savePath, 0777, true); // 创建目录

        $fileName = sprintf('%s/%s.%s', $savePath, $imgName, $ext);
        $file = fopen($fileName, "w+");        
        $img = base64_decode($content);
        fputs($file, $img);
        fclose($file);
        return sprintf('%s/%s.%s', $path, $imgName, $ext);
    }

    // /**
    //  * 发送短信
    //  * @param $mobile
    //  * @param $msg
    //  * @param bool $timelimit 限制发送短信频率
    //  * @return string
    //  */
    public static function sendmsg_czsms($mobile, $msg, $timelimit = false){
        header("Content-Type: text/html; charset=UTF-8");

        $redis = new Redis();
        $redis_key = self::$sms_prefix.'_'.$mobile;

        $con = '1';
        if(!$redis->has($redis_key)) {
            $con= self::yshfx_send_code($mobile, $msg);
            if ($con === '0' && !in_array($mobile,self::$mobiles)) {
                $redis->set($redis_key,['mobile' => $mobile,'times' => 1,'wtime' => time(),'expire' => time()+self::$day_expire,'ip' => request()->ip()],600);
            }
        } else {
            $result = $redis->get($redis_key);  //当前发送短信手机号，redis中存的数据
            if (!in_array($mobile, self::$mobiles)) {
                if ($result['expire'] > time() && $result['times']+1 > 10) {
                    exit(json_encode(['code' => 503, 'msg' => date('Y-m-d H:i:s', $result['expire']) .'后重试']));
                }
                if(($result['wtime']+60) >= time()) {  //1分钟过期时间
                    exit(json_encode(['code' => 502, 'msg' => '请在' . date('Y-m-d H:i:s', $result['wtime']) . '后再使用短信接口']));
                }
            }

            $con = self::yshfx_send_code($mobile, $msg);  //获取信息发送后的状态
            if (!in_array($result['mobile'],self::$mobiles)) {
                if ($con === '0') {
                    $result['times'] += 1;
                    $result['wtime'] = time();
                    if ($result['expire'] < time()) {
                        $result['expire'] = time()+self::$day_expire;
                        $result['times'] = 1;
                    }
                    $redis->set($redis_key, $result, self::$day_expire);
                }
            }

        }

        return $con;
    }

    /**短信发送验证规则
     * @param $mobile
     * @param $params
     * @param $url
     * @return string
     */
    // private static function check($mobile, $params, $url)
    // {
    //     $con = '';
    //     $url .=$params; //提交的url地址
    //     $redis = new Redis();
    //     $redis_key = self::$sms_prefix.'_'.$mobile;

    //     if(!$redis->has($redis_key)) {
    //         $con= strstr(file_get_contents($url),',',true);  //获取信息发送后的状态
    //         if ($con === '0' && !in_array($mobile,self::$mobiles)) {
    //             $redis->set($redis_key,['mobile' => $mobile,'times' => 1,'wtime' => time(),'expire' => time()+self::$day_expire,'ip' => request()->ip()],600);
    //         }
    //     } else {
    //         $result = $redis->get($redis_key);  //当前发送短信手机号，redis中存的数据
    //         if (!in_array($mobile, self::$mobiles)) {
    //             if ($result['expire'] > time() && $result['times']+1 > 10) {
    //                 exit(json_encode(['code' => 503, 'msg' => date('Y-m-d H:i:s', $result['expire']) .'后重试']));
    //             }
    //             if(($result['wtime']+60) >= time()) {  //1分钟过期时间
    //                 exit(json_encode(['code' => 502, 'msg' => '请在' . date('Y-m-d H:i:s', $result['wtime']) . '后再使用短信接口']));
    //             }
    //         }

    //         $con = strstr(file_get_contents($url),',',true);  //获取信息发送后的状态
    //         if (!in_array($result['mobile'],self::$mobiles)) {
    //             if ($con === '0') {
    //                 $result['times'] += 1;
    //                 $result['wtime'] = time();
    //                 if ($result['expire'] < time()) {
    //                     $result['expire'] = time()+self::$day_expire;
    //                     $result['times'] = 1;
    //                 }
    //                 $redis->set($redis_key, $result, self::$day_expire);
    //             }
    //         }

    //     }
    //     return $con;
    // }

    /**短信发送
     * @param $mobile
     * @param $msg
     * @return string
     */
    public static function yshfx_send_code($mobile, $msg, $timelimit = false){
        
	if(!is_numeric($msg)) return '1';

	header("Content-Type: text/html; charset=UTF-8");
        // 发送数据
        $username = 'yisihui';
        $id = '5550';
        $password = '123456';
        $time_str = date("YmdHis");
        $sign = md5(sprintf("%s%s%s", $username, $password, $time_str));
        $url = "http://39.104.28.149:8888/v2sms.aspx?action=send";

        $postData = [
            'action'    =>  'send',
            'userid'    =>  $id,
            // 'account'   =>  $username,
            // 'pwd'       =>  $password,
            'timestamp' =>  $time_str,
            'sign'      =>  $sign,
            'mobile'    =>  $mobile,
            // 'content'   =>  is_numeric($msg)?sprintf("【易思汇】短信验证码是：%s，请勿将验证码提供给他人。", $msg):sprintf("【易思汇】%s", $msg),
            'content'   =>  sprintf("【亿思汇】短信验证码是：%s，请勿将验证码提供给他人。", $msg),
            'sendTime'  =>  '',
            'extno'     =>  ''
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $output = curl_exec($ch);
        curl_close($ch);
        // 输出表单
        # 判断返回值
        if(!$output){
            exit(json_encode(['code'=>400,'msg'=>'发送失败']));
        }
        $result = simplexml_load_string($output, 'SimpleXMLElement', LIBXML_NOCDATA);
        if($result && isset($result->returnstatus) && isset($result->message) && $result->returnstatus=='Success' && $result->message=='ok'){
            return '0';
        }else{
            return '1';
        }
    }

    // /**短信发送
    //  * @param $mobile
    //  * @param $msg
    //  * @return string
    //  */
    // public static function yshfx_send_code($mobile, $msg, $timelimit = false){
    //     header("Content-Type: text/html; charset=UTF-8");
    //     // 发送数据
    //     $postData = array(
    //         'accesskey' => 'jfPvKchahhKHQwys',
    //         'secret' => 'JjG6YiDwTo1wfYOBDDXt8jlSewZjd0YC',
    //         'sign' => '【亿思汇】',
    //         'templateId' => '9965',
    //         'mobile' => $mobile,
    //         'content' => $msg,
    //     );
    //     $ch = curl_init();
    //     curl_setopt($ch, CURLOPT_URL, 'http://api.1cloudsp.com/api/v2/single_send');
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //     curl_setopt($ch, CURLOPT_POST, 1);
    //     curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    //     $output = curl_exec($ch);
    //     curl_close($ch);
    //     // 输出表单
    //     # 判断返回值
    //     if(!$output){
    //         exit(json_encode(['code'=>400,'msg'=>'发送失败']));
    //     }
    //     $res_send = json_decode($output,true);
    //     if($res_send && isset($res_send['code']) && isset($res_send['msg']) && ($res_send['code'] == 0) && ($res_send['msg'] == 'SUCCESS')){
    //         return '0';
    //     }else{
    //         return '1';
    //     }
    // }


    /**
     * 导出excel文件
     * @param $expTitle
     * @param $expCellName  导出列数组  $expCellName=[['id','id'],['mobile'，'手机号']];
     * @param $expTableData  导出的数据
     */
    public static function exportExcel($expTitle,$expCellName,$expTableData){
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
        $fileName = date('YmdHis');//文件名称
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        vendor('PHPExcel.Classes.PHPExcel');

        $objPHPExcel = new PHPExcel();

        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex()->setCellValue($cellName[$i].'1', $expCellName[$i][1]);
        }
        // Miscellaneous glyphs, UTF-8
        for($i=0;$i<$dataNum;$i++){
            for($j=0;$j<$cellNum;$j++){
                $objPHPExcel->getActiveSheet()->setCellValue($cellName[$j].($i+2), $expTableData[$i][$expCellName[$j][0]]);
            }
        }

        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }



    /**
    * 读取excel
    */
    public static function read_excel($filePath = null){
        if ( ! empty($filePath)){
            vendor('PHPExcel.Classes.PHPExcel');
            $arrPath = explode('.', $filePath);
            $type = isset($arrPath[count($arrPath)-1]) ? $arrPath[count($arrPath)-1] : null;
            switch ($type) {
                case 'xlsx':
                case 'xls':
                    $phpReader = PHPExcel_IOFactory::createReader('Excel2007'); // 读取 excel 文档
                    break;
                case 'csv':
                    $phpReader = PHPExcel_IOFactory::createReader('CSV'); // 读取 excel 文档
                    break;                
                default:
                    die('Not supported file types!');
                    break;
            }
            // 开始读取数据
            $phpExcel = $phpReader->load($filePath); 
            $currentSheet = $phpExcel->getSheet(0);  //读取excel文件中的第一个工作表
            $allColumn = $currentSheet->getHighestColumn(); //取得最大的列号
            $allRow = $currentSheet->getHighestRow(); //取得一共有多少行
            $strOrder = array();

            /**从第二行开始输出，因为excel表中第一行为列名*/ 
            for($currentRow = 1; $currentRow <= $allRow; $currentRow++){
                //if ($currentRow == 1) continue; // 跳过标题行
                /**从第A列开始输出*/ 
                for($currentColumn= 'A'; $currentColumn<= $allColumn; $currentColumn++){                
                    $val = $currentSheet->getCellByColumnAndRow(ord($currentColumn) - 65,$currentRow)->getValue();/**ord()将字符转为十进制数*/
                    if($val!=''){
                        $strOrder[] = $val; 
                    }
                  /**如果输出汉字有乱码，则需将输出内容用iconv函数进行编码转换，如下将gb2312编码转为utf-8编码输出*/ 
                  //echo iconv('utf-8','gb2312', $val)."\t"; 
                  
                } 
            } 
            return $strOrder;
        }
    }

    /**
    * 生成随机ip
    */
    public static function getRankIp(){
      $arr = array("218","218","66","66","218","218","60","60","202","204","66","66","66","59","61","60","222","221","66","59","60","60","66","218","218","62","63","64","66","66","122","211");
      $key= rand(0, count($arr)-1);
      $ip1 = $arr[$key];
      $ip2 = round(rand(600000, 2550000)/10000);
      $ip3 = round(rand(600000, 2550000)/10000);
      $ip4 = round(rand(600000, 2550000)/10000);

      return sprintf('%s.%s.%s.%s', $ip1, $ip2, $ip3, $ip4);
    }

    /**
    * 合成图片
    */
    public static function copyImg($newPic, $bg, $wxcode, $userInfo = null, $bg_type){
        // 获取背景
        $rootPath = dirname(dirname(dirname(ROOT_PATH))).self::$uploadRoot.'wxadv'.$bg_type.'/';
        $apisRootPath = dirname(dirname(dirname(ROOT_PATH))).self::$apisRoot.'wxadv'.$bg_type.'/';
        $image_bg = imagecreatefrompng($bg);

        if (imagepng($image_bg,$rootPath.$newPic)){
            if ( ! empty($userInfo)){
                // 添加用户头像
                $bg = sprintf('%supload/wxadv'.$bg_type.'/%s?v=%s', self::$domain, $newPic, time());
                $image_bg = imagecreatefrompng($bg);
                // 添加昵称
                $black = imagecolorallocate($image_bg, 28, 28, 28);
                imagettftext($image_bg, 20, 0, 196, 95, $black, dirname(dirname(dirname(ROOT_PATH))).self::$uploadRoot.'font/'.'msyh.ttf', $userInfo['name']);
                /*
                $image_photo = imagecreatefromstring(file_get_contents($userInfo['img']));
                $target_photo = imagecreatetruecolor(110, 110); 
                // 去掉背景色
                $white = imagecolorallocate($target_photo,0,0,0);
                imagecolortransparent($target_photo, $white);


                imagecopyresampled($target_photo,$image_photo, 0, 0, 0, 0, 110, 110, imagesx($image_photo), imagesy($image_photo));

                imagecopy($image_bg, $target_photo, 56, 60, 0, 0, imagesx($image_photo), imagesy($image_photo));
                */
                // 合并小程序码
                $image_wxcode = imagecreatefrompng($wxcode);
                //imagedestroy($wxcode);
                imagecopyresized($image_bg, $image_wxcode, 502, 720, 0, 0, 220, 220, imagesx($image_wxcode), imagesy($image_wxcode));

                if (!is_dir($apisRootPath)) {
                    if (!mkdir($apisRootPath) && !is_dir($apisRootPath)) {
                        return false;
                    }
                }
                // 输出合成图片
                //imagepng($image[,$filename]) — 以 PNG 格式将图像输出到浏览器或文件
                if (imagepng($image_bg,$apisRootPath.$newPic)){
                    // 释放
                    imagedestroy($image_bg);
                    return sprintf('%sstatic/wxadv'.$bg_type.'/%s?v=%s', self::$apisDomain, $newPic, time());
                }
            }
        }
    }

    /**
    * 分表表名
    */
    public static function tableName($prefix = null, $id = 0){
        if ( ! empty($prefix)){
            if (!is_numeric($id)) return 'mt4_history_order_0';
            switch ($prefix) {
                case 'mt4_history_order':
                    $suffix = intval($id%10);
                    $table = sprintf('mt4_history_order_%d', $suffix);
                    return $table;
                    break;
            }
        }
    }

    /**
     * 最新写入时间
     * @return mixed
     */
    public static function get_account_lastwtime()
    {
        $key = sprintf('%s-%s', 'zhtEA', 'hash_online_time');
        $redis = new Redis();
        if ($redis->has($key)){
            return $redis->hgetall($key);
        }else{
            return array();
        }        
    }

    /**
     * 获取账号缓存信息
     * @return mixed
     */
    public static function get_account_detail($account = 0)
    {
        // 获取可用预付款
        if ( ! empty($account)){
            $key = sprintf('%s-%s-%s', 'zhtEA', 'mt4_account_detail', $account);
            $detail = array();
            $redis = new Redis();
            if ($redis->has($key)){
                $detail = unserialize($redis->get($key));
            }
            return $detail;
        }
    }

    /**
     * 用户绑定手机号
     * @param $login
     * @param $password
     * @param $verify
     * @param $uid
     * @param $type //wxcode ：标识是小程序中的功能
     * @return string
     */
    public static function mobile_binding($login,$password,$verify,$uid)
    {
        $time = time();
        if ($login == '') {
            return json_encode(['code' => 302, 'msg' => '请填写手机号']);
        } else {
            $user = Db::name('user')->where('login', $login)->count();
            if ($user) {
                return json_encode(['code' => 302, 'msg' => '该手机号已被占用，请使用其他手机号']);
            }
        }
        $sms_verify = Db::name('Mt4Smslog')->where(['login' => $login, 'verify' => $verify])->find();
        if (empty($sms_verify)) {
            return json_encode(['code' => 302, 'msg' => '短信验证码错误!']);
        }

        $user_info = [
            'login' => $login,
            'sign_ip' => request()->ip(),
            'modify_time' => $time,
            'password' => md5($password)
        ];

        $result = Db::name('User')->where('uid', $uid)->update($user_info);
        if ($result <= 0) {
            return json_encode(['code' => 302, 'msg' => '绑定失败']);
        }
        return json_encode(['code' => 200, 'msg' => '绑定成功']);
    }

}
