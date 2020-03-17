package controllers

import (
	"beego_test/comm"
	"beego_test/models"
	"github.com/astaxie/beego"
	"github.com/astaxie/beego/orm"
)

type RechargeController struct{
	beego.Controller
}

func (this *RechargeController) List(){
	o := orm.NewOrm()
	model := o.QueryTable("marketing_recharge").OrderBy("-id")
	count,_ := model.Count()
	var list []models.MarketingRecharge
	model.All(&list)
	this.Data["json"] = comm.Success(200,"获取充值优惠列表成功",list,count)
	this.ServeJSON()
}
func (this *RechargeController) Post(){
	id := comm.GetToken(this.Ctx.Input.Header("token"))
	if id == 0{
		this.Data["json"] = comm.Error(101,"登陆态失效，或者未登录")
		this.ServeJSON()
		return
	}
	price,_ := this.GetFloat("price")
	if price == 0{
		this.Data["json"] = comm.Error(500,"请输入正确的充值金额")
		this.ServeJSON()
		return
	}
	price = comm.Float64TWO(price)
	o := orm.NewOrm()
	var member models.Member
	o.QueryTable("member").Filter("id",id).One(&member,"openid")
	if member.Openid == ""{
		this.Data["json"] = comm.Error(500,"用户数据错误")
		this.ServeJSON()
		return
	}
	var recharge_order models.RechargeOrder
	recharge_order.MemberId = id
	recharge_order.Price = price
	recharge_order.CreateOrder()
	var wx models.WxPay
	wx.Price = recharge_order.Price
	wx.Ordersn = recharge_order.Ordersn
	wx.Openid = member.Openid
	data := wx.Pay()
	if data == false{
		this.Data["json"] = comm.Error(500,"调起支付失败,请重试")
		this.ServeJSON()
		return
	}
	this.Data["json"] = comm.Success(200,"获取支付参数成功",data,nil)
	this.ServeJSON()
}