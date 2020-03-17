package controllers

import (
	"beego_test/comm"
	"beego_test/models"
	"encoding/json"
	"fmt"
	"github.com/astaxie/beego"
	"github.com/astaxie/beego/orm"
	"regexp"
	"strconv"
	"strings"
	"time"
)

type OrderController struct {
	beego.Controller
}
//创建订单
func (this *OrderController) Create(){
	var order models.Order
	member_id := comm.GetToken(this.Ctx.Input.Header("token"))
	if member_id == 0{
		this.Data["json"] = comm.Error(101,"登陆态失效，或者未登录")
		this.ServeJSON()
		return
	}
	types,_ := this.GetInt("type")
	if !(types == 1 || types == 2){
		this.Data["json"] = comm.Error(500,"请输入正确的类型")
		this.ServeJSON()
		return
	}
	name := this.GetString("name")
	if name == ""{
		this.Data["json"] = comm.Error(500,"姓名不能为空")
		this.ServeJSON()
		return
	}
	phone := this.GetString("phone")
	if phone == ""{
		this.Data["json"] = comm.Error(500,"手机号不能为空")
		this.ServeJSON()
		return
	}
	service_json := this.GetString("service")
	if service_json == ""{
		this.Data["json"] = comm.Error(500,"服务不能为空")
		this.ServeJSON()
		return
	}
	is_send,_ := this.GetInt("is_send")
	if !(is_send == 0 || is_send == 1){
		this.Data["json"] = comm.Error(500,"请输入正确的是否寄出值")
		this.ServeJSON()
		return
	}
	service := make([]map[string]interface{},0)
	err := json.Unmarshal([]byte(service_json),&service)
	if err != nil{
		this.Data["json"] = comm.Error(500,"服务不能为空,请选择服务")
		this.ServeJSON()
		return
	}
	reg := `^1[3456789]\d{9}$`
	rgx := regexp.MustCompile(reg)
	if !rgx.MatchString(phone){
		this.Data["json"] = comm.Error(500,"请输入正确的手机号")
		this.ServeJSON()
		return
	}
	var service_list []models.Service
	o := orm.NewOrm()
	service_in_ids := make([]int,0)
	for _,v := range service{
		 standard,ok := v["standard"]
		 if !ok{
			 this.Data["json"] = comm.Error(500,"请输入正确的标准服务")
			 this.ServeJSON()
			 return
		 }
		 standard_id,_ := standard.(int)
		 if err != nil{
			 this.Data["json"] = comm.Error(500,"请输入正确的标准服务")
			 this.ServeJSON()
			 return
		 }
		 service_in_ids = append(service_in_ids,standard_id)
		 additional,ok := v["additional"]
		 if !ok{
			 this.Data["json"] = comm.Error(500,"请输入正确的附加服务1")
			 this.ServeJSON()
			 return
		 }
		additional_arr := strings.Split(additional.(string),",")
		for _,v := range additional_arr {
			if v != ""{
				id,err := strconv.Atoi(v)
				if err != nil{
					fmt.Println(err)
					this.Data["json"] = comm.Error(500,"请输入正确的附加服务2")
					this.ServeJSON()
					return
				}
				service_in_ids = append(service_in_ids,id)
			}
		}
	}
	o.QueryTable("service").Filter("id__in",service_in_ids).All(&service_list)
	if len(service_list) == 0{
		this.Data["json"] = comm.Error(500,"服务不能为空,请选择服务")
		this.ServeJSON()
		return
	}
	//计算订单总价
	service_price := make(map[int]float64,0)
	var total_price float64
	for _,v := range service_list {
		service_price[v.Id] = v.Price
	}
	fmt.Println(service_price)
	for _,v := range service{
		standard_id,_ := v["standard"].(int)
		total_price += service_price[standard_id]
		additional_arr := strings.Split(v["additional"].(string),",")
		for _,v := range additional_arr {
			additional_id,_ := strconv.Atoi(v)
			total_price += service_price[additional_id]
		}
	}
	//构造入库数据
	order.Type = types
	order.Name = name
	order.Phone = phone
	order.IsSend = is_send
	order.TotalPrice = total_price
	order.MemberId = member_id
	order.CreateOrdersn()
	if this.GetString("remark") != ""{
		order.Remark = this.GetString("remark")
	}
	order.PayablePrice = order.TotalPrice
	order.UpdateTime = int(time.Now().Unix())
	order.CreateTime = int(time.Now().Unix())
	if comm.IsJson(this.GetString("photos")) {
		order.Photos = 	this.GetString("photos")
	}else{
		order.Photos = "[]"
	}
	//如果是邮寄
	if types == 2{
		if address := this.GetString("photos");address == ""{
			this.Data["json"] = comm.Error(500,"地址不能为空")
			this.ServeJSON()
			return
		}else{
			order.Address = address
		}
		if is_send == 1{
			order.ExpressName = this.GetString("express_name")
			order.ExpressNumber = this.GetString("express_number")
		}
	}
	//开启事务
	o.Begin()
	//优惠券
	if coupon_log_id,_ := this.GetInt("express_name");order.PayablePrice > 0 && coupon_log_id !=0{
		var coupon_log models.CouponLog
		o.QueryTable(coupon_log).Filter("id",coupon_log_id).One(&coupon_log)
		if coupon_log.Id == 0{
			o.Rollback()
			this.Data["json"] = comm.Error(500,"选择的优惠券不存在,请重新选择")
			this.ServeJSON()
			return
		}
		if order.TotalPrice < coupon_log.Full {
			o.Rollback()
			this.Data["json"] = comm.Error(500,"优惠券不满足使用条件,请重新选择")
			this.ServeJSON()
			return
		}
		order.PayablePrice = order.TotalPrice - coupon_log.FaceValue
		if order.PayablePrice < 0{
			order.PayablePrice = 0
		}
		order.CouponLogId = coupon_log_id
		order.CouponPrice = coupon_log.FaceValue
		coupon_log.Status = 1
		coupon_log.UpdateTime = int(time.Now().Unix())
		if _,err := o.Update(&coupon_log); err != nil{
			o.Rollback()
			this.Data["json"] = comm.Error(500,"提交订单失败,优惠券状态异常,请选择取消优惠券后重试")
			this.ServeJSON()
			return
		}
		//余额抵扣
		if is_balance,_ := this.GetInt("express_name");order.PayablePrice > 0 && is_balance != 0{
			var member models.Member
			o.QueryTable(member).Filter("id",member_id).One(&member)
			payable_price := order.PayablePrice
			order.PayablePrice = order.PayablePrice - member.Balance
			if order.PayablePrice >=0{
				if member.Balance != 0{
					member.Balance = 0
					member.UpdateTime = int(time.Now().Unix())
					o.Update(&member)
				}
			}else{
				order.PayablePrice = 0;
				order.Balance = payable_price
				if payable_price != 0{
					member.Balance = member.Balance - payable_price
					member.UpdateTime = int(time.Now().Unix())
					o.Update(&member)
				}
			}
		}
	}
	if order.PayablePrice == 0{
		order.Status = 1
		order.PayType = 1
		order.PayTime = int(time.Now().Unix())
	}else{
		order.PayType = 2
	}
	id,err := o.Insert(&order)
	if err != nil{
		o.Rollback()
		this.Data["json"] = comm.Error(500,"提交订单失败,请重试")
		this.ServeJSON()
		return
	}
	for _,v := range service{
		var standard_order models.OrderService
		standard_order.OrderId = int(id)
		standard_id,_ := v["standard"].(int)
		standard_order.StandardServiceId = standard_id
		for _,v2 := range service_list{
			if v2.Id == standard_order.StandardServiceId {
				standard_order.StandardServiceTitle = v2.Title
				standard_order.StandardServicePrice = v2.Price
			}
		}
		additional := make([]map[string]interface{},0)
		additional_arr := strings.Split(v["additional"].(string),",")
		for _,v2 := range additional_arr{
			tmp := make(map[string]interface{},0)
			additional_id,_ := strconv.Atoi(v2)
			for _,v3 := range service_list{
				if v3.Id == additional_id {
					tmp["id"] = v3.Id
					tmp["title"] = v3.Title
					tmp["price"] = v3.Price
				}
			}
			additional = append(additional,tmp)
		}
		additional_json,_ := json.Marshal(additional)
		standard_order.Additional = string(additional_json)
		o.Insert(&standard_order)
	}
	o.Commit()
	data := make(map[string]int,0)
	data["order_id"] = int(id)
	if order.PayType == 1{
		data["status"] = 0
		this.Data["json"] = comm.Success(200,"提交订单成功,余额足够抵扣,无需支付",data,nil)
		this.ServeJSON()
		return
	}else{
		data["status"] = 1
		this.Data["json"] = comm.Success(200,"提交订单成功",data,nil)
		this.ServeJSON()
		return
	}
}
//获取订单支付参数
func (this *OrderController) Pay(){
	member_id := comm.GetToken(this.Ctx.Input.Header("token"))
	if member_id == 0{
		this.Data["json"] = comm.Error(101,"登陆态失效，或者未登录")
		this.ServeJSON()
		return
	}
	var member models.Member
	o := orm.NewOrm()
	o.QueryTable(member).Filter("id",member_id).One(&member,"openid")
	if member.Openid == ""{
		this.Data["json"] = comm.Error(500,"订单异常请重试")
		this.ServeJSON()
		return
	}
	id,_ := this.GetInt("id")
	var order models.Order
	o.QueryTable(order).Filter("id",id).One(&order,"ordersn","payable_price")
	if order.Ordersn == ""{
		this.Data["json"] = comm.Error(500,"订单异常请重试")
		this.ServeJSON()
		return
	}
	var wx models.WxPay
	wx.Price = order.PayablePrice
	wx.Ordersn = order.Ordersn
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
//订单列表
func (this *OrderController) List(){
	member_id := comm.GetToken(this.Ctx.Input.Header("token"))
	if member_id == 0{
		this.Data["json"] = comm.Error(101,"登陆态失效，或者未登录")
		this.ServeJSON()
		return
	}
}
//订单详情
func (this *OrderController) Detail(){
	member_id := comm.GetToken(this.Ctx.Input.Header("token"))
	if member_id == 0{
		this.Data["json"] = comm.Error(101,"登陆态失效，或者未登录")
		this.ServeJSON()
		return
	}
	o := orm.NewOrm()
	id,_ := this.GetInt("id")
	var order models.Order
	o.QueryTable(order).Filter("id",id).One(&order)
	this.Data["json"] = comm.Success(200,"获取支付参数成功",order,nil)
	this.ServeJSON()
}
//取消订单
func (this *OrderController) Cancel(){
	member_id := comm.GetToken(this.Ctx.Input.Header("token"))
	if member_id == 0{
		this.Data["json"] = comm.Error(101,"登陆态失效，或者未登录")
		this.ServeJSON()
		return
	}
}