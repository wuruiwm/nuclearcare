<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2019-12-27 10:11:07
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2019-12-28 09:33:22
 */
//返回status和msg 并exit
function msg($status = 0,$msg = ''){
	showjson(['status'=>$status,'msg'=>$msg]);
}
//封装验证函数 利用laravel自动验证
function data_check($data,$rule,$msg,$is_admin = 1){
	$validator = Illuminate\Support\Facades\Validator::make($data,$rule,$msg);
	if($validator->fails()){
		$is_admin ? msg(0,$validator->errors()->first()) : api_json(0,$validator->errors()->first());
	}
	$data = $validator->validated();
	return $data;
}
//获取页码数和条数并校验
function page($data){
	$rule = [
		'page' => 'required|integer',
		'limit' => 'required|integer',
	];
	$msg = [
		'page.required'=>'页码数不能为空',
		'page.integer'=>'请传入正确的页码数',
		'limit.required'=>'每页条数不能为空',
		'limit.integer'=>'请传入正确的每页条数',
	];
	$data = data_check($data,$rule,$msg);
	$data = ['number'=>($data['page'] - 1) * $data['limit'],'limit'=>$data['limit']];
	return $data;
}
//获取用户ip地址
function get_client_ip(){
	if (getenv('HTTP_CLIENT_IP')) {
		$ip = getenv('HTTP_CLIENT_IP');
	}
	if (getenv('HTTP_X_REAL_IP')) {
		$ip = getenv('HTTP_X_REAL_IP');
	} elseif (getenv('HTTP_X_FORWARDED_FOR')) {
		$ip = getenv('HTTP_X_FORWARDED_FOR');
		$ips = explode(',', $ip);
		$ip = $ips[0];
	} elseif (getenv('REMOTE_ADDR')) {
		$ip = getenv('REMOTE_ADDR');
	} else {
		$ip = '0.0.0.0';
	}
	return $ip;
}
function array_date(&$data = [],$fields = ['create_time','update_time'],$date = 'Y-m-d H:i:s'){
    foreach ($data as $k => $v) {
		foreach($fields as $field){
			$data[$k]->$field = date($date,$v->$field);
		}
	}
}
//获取id，通常用于删除
function delete_id($id){
	if (empty($id) || !is_numeric($id)) {
		msg(0,'请传入正确的id');
	}
	$id = intval($id);
	return $id;
}
//获取id
function get_id($id){
    if (empty($id) || !is_numeric($id)) {
		$id = 0;
	}
	$id = intval($id);
	return $id;
}
//输出json并exit
function showjson($data = []){
    exit(json_encode($data,JSON_UNESCAPED_UNICODE));
}
//获取当前请求带协议头的域名  例如https://www.baidu.com
function domain_name(){
	return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
}
//301重定向
function http301($url = ''){
	header('HTTP/1.1 301 Moved Permanently');
	header("location:$url");
	exit();
}
//api接口返回
function api_json($code = 200,$msg = '请求成功',$data = [],$count = 0){
	$array = [
		'code'=>$code,
		'msg'=>$msg
	];
	empty($data) || $array['data'] = $data;
	empty($count) || $array['count'] = $count;
	showjson($array);
}
//将图片路径转换成前端可以访问的url
function img_path_url($path){
	return domain_name() . $path;
}
//请求过程中因为编码原因+号变成了空格
//需要用下面的方法转换回来
function define_str_replace($data){
	return str_replace(' ','+',$data);
}
/**
 * 发送HTTP请求方法
 * @param  string $url    请求URL
 * @param  array  $params 请求参数
 * @param  string $method 请求方法GET/POST
 * @return array  $data   响应数据
 */
function httpCurl($url, $params, $method = 'POST', $header = array(), $multi = false){
    date_default_timezone_set('PRC');
    $opts = array(
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER     => $header,
        CURLOPT_COOKIESESSION  => true,
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_COOKIE         =>session_name().'='.session_id(),
    );
    /* 根据请求类型设置特定参数 */
    switch(strtoupper($method)){
        case 'GET':
            // $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
            // 链接后拼接参数  &  非？
            $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
            break;
        case 'POST':
            //判断是否传输文件
            $params = $multi ? $params : http_build_query($params);
            $opts[CURLOPT_URL] = $url;
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = $params;
            break;
        default:
            throw new Exception('不支持的请求方式！');
    }
    /* 初始化并执行curl请求 */
    $ch = curl_init();
    curl_setopt_array($ch, $opts);
    $data  = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if($error) throw new Exception('请求发生错误：' . $error);
    return $data;
}
/**
 * 微信信息解密
 * @param  string  $appid  小程序id
 * @param  string  $sessionKey 小程序密钥
 * @param  string  $encryptedData 在小程序中获取的encryptedData
 * @param  string  $iv 在小程序中获取的iv
 * @return array 解密后的数组
 */
function decryptData( $appid , $sessionKey, $encryptedData, $iv ){
    $OK = 0;
    $IllegalAesKey = -41001;
    $IllegalIv = -41002;
    $IllegalBuffer = -41003;
    $DecodeBase64Error = -41004;
 
    if (strlen($sessionKey) != 24) {
        return $IllegalAesKey;
    }
    $aesKey=base64_decode($sessionKey);
 
    if (strlen($iv) != 24) {
        return $IllegalIv;
    }
    $aesIV=base64_decode($iv);
 
    $aesCipher=base64_decode($encryptedData);
 
    $result=openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
    $dataObj=json_decode( $result );
    if( $dataObj  == NULL )
    {
        return $IllegalBuffer;
    }
    if( $dataObj->watermark->appid != $appid )
    {
        return $DecodeBase64Error;
    }
    $data = json_decode($result,true);
 
    return $data;
}
?>