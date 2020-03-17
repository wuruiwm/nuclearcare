package controllers

import (
	"beego_test/comm"
	"beego_test/models"
	"github.com/astaxie/beego"
	"github.com/astaxie/beego/orm"
	"time"
)

type MemberController struct{
	beego.Controller
}

func (this *MemberController) Get() {
	id := comm.GetToken(this.Ctx.Input.Header("token"))
	o := orm.NewOrm()
	var member models.Member
	o.QueryTable("member").Filter("id",id).One(&member)
	if member.Id == 0{
		this.Data["json"] = comm.Error(101,"登陆态失效，或者未登录")
		this.ServeJSON()
		return
	}
	member.CouponTotal = 0
	this.Data["json"] = comm.Success(200,"登陆成功",member,nil)
	this.ServeJSON()
}
func(this *MemberController) Base(){
	id := comm.GetToken(this.Ctx.Input.Header("token"))
	o := orm.NewOrm()
	var member models.Member
	o.QueryTable("member").Filter("id",id).One(&member,"balance")
	data := make(map[string]interface{})
	data["balance"] = member.Balance
	data["template"] = comm.TemplateIdList()
	var coupon_log []models.CouponLog
	o.QueryTable("coupon_log").Filter("member_id",id).Filter("status",0).Filter("expire_time__gte",time.Now().Unix()).All(&coupon_log,"id","face_value","full","expire_time")
	if len(coupon_log) == 0{
		data["is_coupon"] = 0
	}else{
		data["is_coupon"] = 1
	}
	data["coupon_log_list"] = coupon_log
	this.Data["json"] = comm.Success(200,"获取余额和可用优惠券列表成功",data,nil)
	this.ServeJSON()
}