package controllers

import (
	"beego_test/comm"
	"beego_test/models"
	"github.com/astaxie/beego"
	"github.com/astaxie/beego/orm"
	"github.com/garyburd/redigo/redis"
	"github.com/wulijun/go-php-serialize/phpserialize"
)

//定义banner请求的struct
type BannerController struct{
	beego.Controller
}
//轮播图 /api/banner
func (this *BannerController) Get() {
	//创建orm对象
	o := orm.NewOrm()
	//准备一个切片 用于存储sql查询返回的结果
	var banner []models.Banner
	//select id,img_path from banner order by sort desc
	o.QueryTable("banner").OrderBy("-Sort").All(&banner,"Id", "ImgPath")
	count,_ := o.QueryTable("banner").Count()
	for k,v := range banner{
		//循环将 图片路径转为可访问的url
		banner[k].Url = comm.ImgpathToUrl(v.ImgPath)
	}
	//从redis连接池中取出连接
	conn := comm.Redis.Get()
	defer conn.Close()
	long_img_path_value,_ := redis.String(conn.Do("GET", "laravel_database_laravel_cache:long_img_path"))
	//按照php反序列化回来
	long_img_path,_ := phpserialize.Decode(long_img_path_value)
	data := comm.Success(200,"获取轮播图列表成功",banner,count)
	data["long_img_url"] = comm.ImgpathToUrl(long_img_path.(string))
	this.Data["json"] = &data
	this.ServeJSON()
}