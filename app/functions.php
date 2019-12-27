<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2019-12-27 10:11:07
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2019-12-27 15:27:35
 */
//返回status和msg 并exit
function msg($status = 0,$msg = ''){
	exit(json_encode(['status'=>$status,'msg'=>$msg],JSON_UNESCAPED_UNICODE));
}
//封装验证函数 利用laravel自动验证
function data_check($data,$rule,$msg){
	$validator = Illuminate\Support\Facades\Validator::make($data,$rule,$msg);
	if($validator->fails()){
		msg(0,$validator->errors()->first());
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
?>