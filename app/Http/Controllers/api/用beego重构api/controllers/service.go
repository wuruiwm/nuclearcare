package controllers

import (
	"beego_test/comm"
	"beego_test/models"
	"github.com/astaxie/beego"
	"github.com/astaxie/beego/orm"
)

type ServiceController struct{
	beego.Controller
}
func (this *ServiceController) Get(){
	o := orm.NewOrm()
	var list []*models.Service
	o.QueryTable("service").OrderBy("-sort").All(&list)
	standard := make(map[string]interface{})
	standard_data := make([]*models.Service,0)
	standard_count := 0
	additional := make(map[string]interface{})
	additional_data := make([]*models.Service,0)
	additional_count := 0
	for _,v := range list{
		if v.Type == 1{
			standard_data = append(standard_data,v)
			standard_count++
		}
		if v.Type == 2{
			additional_data = append(additional_data,v)
			additional_count++
		}
	}
	standard["data"] = standard_data
	standard["count"] = standard_count
	additional["data"] = additional_data
	additional["count"] = additional_count
	data := make(map[string]interface{})
	data["standard"] = standard
	data["additional"] = additional
	this.Data["json"] = comm.Success(200,"获取服务列表成功",data,nil)
	this.ServeJSON()
}