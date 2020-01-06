<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2019-12-27 10:11:07
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2020-01-06 15:48:07
 */
//返回status和msg 并exit
function msg($status = 0,$msg = ''){
	showjson(['status'=>$status,'msg'=>$msg]);
}
//封装验证函数 利用laravel自动验证
function data_check($data,$rule,$msg,$is_admin = 1){
	$validator = Illuminate\Support\Facades\Validator::make($data,$rule,$msg);
	if($validator->fails()){
		$is_admin ? msg(0,$validator->errors()->first()) : api_json(500,$validator->errors()->first());
	}
	$data = $validator->validated();
	return $data;
}
//获取页码数和条数并校验
function page($data,$is_admin = 1){
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
	$data = data_check($data,$rule,$msg,$is_admin);
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
function find_date(&$data = [],$fields = ['create_time','update_time'],$date = 'Y-m-d H:i:s'){
	foreach($fields as $field){
		$data->$field = date($date,$data->$field);
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
function api_json($code = 200,$msg = '请求成功',$data = [],$count = ''){
	$array = [
		'code'=>$code,
		'msg'=>$msg
	];
	empty($data) || $array['data'] = $data;
	(empty($count) && !is_numeric($count)) || $array['count'] = $count;
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
//随机字符串
function getRandomChar($length = 32){
    $str = null;
    $strPol = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
    $max = strlen($strPol) - 1;
    for ($i = 0; $i < $length; $i++) {
        $str .= $strPol[rand(0, $max)];
    }
    return $str;
}
//设置用户token
function set_token($user_id){
	$token = getRandomChar();
	cache([$token =>$user_id], config('common.token_time'));
	return ['token'=>$token,'token_time'=>config('common.token_time')];
}
//获取token
function get_token(){
    request()->header('token') || api_json(101,'请传入token');
    $member_id = cache(request()->header('token'));
    !empty($member_id) || api_json(101,'登陆失效,请重试');
	return $member_id;
}
//用于微信支付转换认证的信息用的
function to_url_params($data){
	$buff = "";
	foreach ($data as $k => $v){
	    if($k != "sign" && $v != "" && !is_array($v)){
	      $buff .= $k . "=" . $v . "&";
	    }
	}
	$buff = trim($buff, "&");
	return $buff;
}
//微信支付-数组转xml
function array_to_xml($arr){
	$xml = "<xml>";
	foreach ($arr as $k=>$v){
	    if (is_numeric($v)){
	        $xml.="<".$k.">".$v."</".$k.">";
	    }else{
	        $xml.="<".$k."><![CDATA[".$v."]]></".$k.">";
	    }
	}
	$xml .="</xml>";
	return $xml;
}
//xml转json
function xml_to_json($xmlstring) {
	return json_encode(xml_to_array($xmlstring),JSON_UNESCAPED_UNICODE);
}
//用户post方法请求xml信息用的
function post_xml_curl($xml, $url, $useCert = false, $second = 10){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_TIMEOUT, $second);
    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    $data = curl_exec($ch);
    if($data){
        curl_close($ch);
        return $data;
    } else {
        $error = curl_errno($ch);
        curl_close($ch);
        return $error;
    }
}
function post_url($post_data, $url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}
//把xml转换成array
function xml_to_array($xml){
    return simplexml_load_string($xml,'SimpleXMLElement',LIBXML_NOCDATA);
}
//订单里所有的服务 形成一个数组，返回，使用in查询，性能更佳
function service_in_arr($arr){
    $service = [];
    foreach ($arr as $v1) {
        !(empty($v1['standard']) || !is_numeric($v1['standard'])) || api_json(500,"请传入正确的标准服务");
        $service[] = intval($v1['standard']);
        $additional_arr = array_filter(explode(',',$v1['additional']));
        foreach ($additional_arr as $v2) {
            is_numeric($v2) || api_json(500,"请传入正确的附加服务");
            $service[] = intval($v2);
        }
    }
    return $service;
}
//get查询结果对象转二维数组
function ob_to_array($ob){
    !empty($ob) || api_json(500,"选择的服务不能为空");
    $ob->toArray();
    $data = [];
    foreach ($ob as $v) {
        $data[] = (array)$v;
    }
    return $data;
}
//获取订单总价
function order_total_price($data,$service){
    foreach ($service as $k => $v) {
        $service[$v['id']] = $v;
        unset($service[$k]);
    }
    $total_price = 0;
    foreach ($data as $v1) {
        if(!empty($service[$v1['standard']])){
            $total_price += $service[$v1['standard']]['price'];
        }else{
            api_json(500,"请传入正确的标准服务");
        }
        $additional_arr = array_filter(explode(',',$v1['additional']));
        foreach ($additional_arr as $v2) {
            empty($service[$v2]) || $total_price += $service[$v2]['price'];
        }
    }
    return $total_price;
}
//获取插入order_service的数组
function order_service_arr($data,$service,$order_id){
    foreach ($service as $k => $v) {
        $service[$v['id']] = $v;
        unset($service[$k]);
    }
    $array = [];
    foreach ($data as $v1) {
        $order_service = [];
        $order_service['standard_service_id'] = $service[$v1['standard']]['id'];
        $order_service['standard_service_title'] = $service[$v1['standard']]['title'];
        $order_service['standard_service_price'] = $service[$v1['standard']]['price'];
        $additional_arr = array_filter(explode(',',$v1['additional']));
        $additional = [];
        foreach ($additional_arr as $v2) {
            $additional_v = [];
            if(!empty($service[$v2])){
                $additional_v['id'] = $service[$v2]['id'];
                $additional_v['title'] = $service[$v2]['title'];
                $additional_v['price'] = $service[$v2]['price'];
            }
            $additional[] = $additional_v; 
        }
        $order_service['additional'] = json_encode($additional);
        $order_service['order_id'] = $order_id;
        $order_service['create_time'] = time();
        $order_service['update_time'] = time();
        $array[] = $order_service;
    }
    return $array;
}
//将二维数组的id作为值，形成一个新的索引数组return
function array_in($arr = []){
    $id_arr = [];
    foreach ($arr as $v) {
        $id_arr[] = $v['id'];
    }
    return $id_arr;
}
//用户端订单列表 计算每个鞋子的服务价格 和按前端要求 拼接title
function order_service_list_price_or_title($order,$order_service){
    $order_service_tmp = [];
    foreach ($order_service as $k => $v) {
        $v->price = $v->standard_service_price;
        $additional = json_decode($v->additional,true);
        $v->additional_title = '';
        foreach ($additional as $k2 => $v2) {
            $v->price += $v2['price'];
            $v->additional_title .= $v2['title'] . ' ';
        }
        $order_id = $v->order_id;
        unset($v->order_id);
        unset($v->additional);
        unset($v->standard_service_price);
        $order_service_tmp[$order_id][] = (array)$v;
    }
    foreach ($order as $k => $v) {
        $order[$k]['service'] = $order_service_tmp[$v['id']]; 
    }
    return $order;
}
//图片路径的数组转成url数组返回
function img_path_url_arr($arr){
    foreach ($arr as $k => $v) {
        $arr[$k] = img_path_url($v);
    }
    return $arr;
}
function additional_json_to_arr(&$order_service){
    foreach ($order_service as $k => $v) {
        $order_service[$k]->additional = json_decode($v->additional,true);
    }
}
?>