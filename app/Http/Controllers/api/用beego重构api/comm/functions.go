package comm

import (
	"crypto/md5"
	"encoding/hex"
	"encoding/json"
	"errors"
	"fmt"
	"github.com/astaxie/beego"
	"github.com/garyburd/redigo/redis"
	"io/ioutil"
	"math/rand"
	"net/http"
	"os"
	"os/exec"
	"path/filepath"
	"strconv"
	"strings"
	"time"
)
//接口成功返回
func Success(code int,msg string,data interface{},count interface{})map[string]interface{}{
	m := make(map[string]interface{})
	m["code"] = code
	m["msg"] = msg
	if data != nil{
		m["data"] = data
	}
	if count != nil{
		m["count"] = count
	}
	return m
}
//接口失败返回
func Error(code int,msg string)map[string]interface{}{
	m := make(map[string]interface{})
	m["code"] = code
	m["msg"] = msg
	return m
}
//图片路径转url
func ImgpathToUrl(img_path string)string{
	return DomainName() + img_path
}
//获取当前带协议头的地址
func DomainName()string{
	domain_name := beego.AppConfig.String("domain_name")
	is_https,_ := beego.AppConfig.Bool("is_https")
	var agreement string
	if is_https {
		agreement = "https://"
	}else{
		agreement = "http://"
	}
	return agreement + domain_name
}
//wx登陆用  请求过程中因为编码原因+变成了空格  将空格转换为+
func DefineStrReplace(code string)string{
	return strings.Replace(code," ","+",-1)
}
//get请求 返回map
func CurlGet(url string)map[string]interface{}{
	resp,_ := http.Get(url)
	bytes,_ := ioutil.ReadAll(resp.Body)
	m := make(map[string]interface{})
	json.Unmarshal(bytes,&m)
	return m
}
//生成随机字符串
func RandStr(lens int)string{
	rand.Seed(time.Now().UnixNano())
	var str string
	strpol := "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"
	for i:=0;i<lens;i++ {
		num := rand.Intn(len(strpol)-1)
		str = str + strpol[num:num+1]
	}
	return str
}
//设置token
func SetToken(member_id int)string{
	conn := Redis.Get()
	defer conn.Close()
	str := RandStr(32)
	conn.Do("set",str,member_id)
	return str
}
//根据token，返回id
func GetToken(token string)int{
	conn := Redis.Get()
	defer conn.Close()
	id,_ := redis.Int(conn.Do("get",token))
	return id
}
//微信支付 签名
func WxPaySign(data map[string]interface{})string{
	var str string
	str = "appId="+ data["appId"].(string) + "&nonceStr=" + data["nonceStr"].(string) + "&package=" + data["package"].(string) + "&signType=" + data["signType"].(string) + "&timeStamp=" + strconv.Itoa(int(data["timeStamp"].(int64))) + "&key=" + beego.AppConfig.String("key")
	h := md5.New()
	h.Write([]byte(str))
	return hex.EncodeToString(h.Sum(nil))
}
//获取订单模板id
func TemplateIdList()[]string{
	template_id_list := beego.AppConfig.String("template_id_list")
	data := make([]string,0)
	json.Unmarshal([]byte(template_id_list),&data)
	return data
}
//判断文件夹是否存在
func PathExists(path string) (bool, error) {
	_, err := os.Stat(path)
	if err == nil {
		return true, nil
	}
	if os.IsNotExist(err) {
		return false, nil
	}
	return false, err
}
//利用md5生成随机文件名
func Md5FileName()string{
	h := md5.New()
	h.Write([]byte(strconv.Itoa(rand.Intn(99) + int(time.Now().Unix())) + RandStr(10)))
	return hex.EncodeToString(h.Sum(nil))
}
//获取go当前二进制文件运行目录
func GetCurrentRunPath() (string, error) {
	file, err := exec.LookPath(os.Args[0])
	if err != nil {
		return "", err
	}
	path, err := filepath.Abs(file)
	if err != nil {
		return "", err
	}
	i := strings.LastIndex(path, "/")
	if i < 0 {
		i = strings.LastIndex(path, "\\")
	}
	if i < 0 {
		return "", errors.New(`error: Can't find "/" or "\".`)
	}
	return string(path[0 : i+1]), nil
}
func Float64TWO(num float64)float64{
	f,_ := strconv.ParseFloat(fmt.Sprintf("%.2f", num), 64)
	return f
}
//判断是否为json
func IsJson(s string) bool {
	var js map[string]interface{}
	return json.Unmarshal([]byte(s), &js) == nil
}