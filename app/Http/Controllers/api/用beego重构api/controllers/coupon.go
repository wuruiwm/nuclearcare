package controllers

import (
	"beego_test/comm"
	"beego_test/models"
	"github.com/astaxie/beego"
	"github.com/astaxie/beego/orm"
	"strconv"
	"time"
)

type CouponController struct{
	beego.Controller
}

func (this *CouponController) Get(){
	o := orm.NewOrm()
	var coupon models.Coupon
	id,_ := this.GetInt("id")
	if id == 0{
		this.Data["json"] = comm.Error(500,"请传入正确的id")
		this.ServeJSON()
		return
	}
	o.QueryTable(coupon).Filter("id",id).One(&coupon)
	if coupon.Id == 0{
		this.Data["json"] = comm.Error(500,"优惠劵不存在")
		this.ServeJSON()
		return
	}
	if !(int(time.Now().Unix()) >= coupon.StartTime && int(time.Now().Unix()) <= coupon.EndTime){
		this.Data["json"] = comm.Error(500,"优惠券不在领取时间")
		this.ServeJSON()
		return
	}
	coupon.Full = comm.Float64TWO(coupon.Full)
	coupon.FaceValue = comm.Float64TWO(coupon.FaceValue)
	this.Data["json"] = comm.Success(200,"获取优惠券详情成功",coupon,nil)
	this.ServeJSON()
}

func (this *CouponController) Receive(){
	member_id := comm.GetToken(this.Ctx.Input.Header("token"))
	if member_id == 0{
		this.Data["json"] = comm.Error(101,"登陆态失效，或者未登录")
		this.ServeJSON()
		return
	}
	id,_ := this.GetInt("id")
	if id == 0{
		this.Data["json"] = comm.Error(500,"请传入正确的id")
		this.ServeJSON()
		return
	}
	o := orm.NewOrm()
	var coupon models.Coupon
	o.QueryTable(coupon).Filter("id",id).Filter("start_time__lte",time.Now().Unix()).Filter("end_time__gte",time.Now().Unix()).One(&coupon)
	if coupon.Id == 0{
		this.Data["json"] = comm.Error(500,"优惠劵不存在或者不在领取时间")
		this.ServeJSON()
		return
	}
	if coupon.ReceiveNum >= coupon.Total{
		this.Data["json"] = comm.Error(500,"优惠券已领完,下次请早点来喔")
		this.ServeJSON()
		return
	}
	var coupon_log models.CouponLog
	o.QueryTable(coupon_log).Filter("coupon_id",id).Filter("member_id",member_id).One(&coupon_log,"id")
	if coupon_log.Id != 0{
		this.Data["json"] = comm.Error(500,"已领取过喔，不可贪心")
		this.ServeJSON()
		return
	}
	coupon_log.MemberId = member_id
	coupon_log.Coupon_id = id
	coupon_log.FaceValue = coupon.FaceValue
	coupon_log.ValidityTime = coupon.ValidityTime
	coupon_log.Full = coupon.Full
	coupon_log.CreateTime = int(time.Now().Unix())
	coupon_log.ExpireTime = int(time.Now().Unix()) + coupon.ValidityTime*86400
	coupon_log.UpdateTime = int(time.Now().Unix())
	coupon_log.Status = 0
	o.Begin()
	if _,err := o.Insert(&coupon_log);err != nil{
		o.Rollback()
		this.Data["json"] = comm.Error(500,"领取失败")
		this.ServeJSON()
		return
	}
	_,err := o.Raw("update coupon set receive_num=receive_num+1 where id="+strconv.Itoa(id)).Exec()
	if err != nil{
		o.Rollback()
		this.Data["json"] = comm.Error(500,"领取失败")
		this.ServeJSON()
		return
	}
	o.Commit()
	this.Data["json"] = comm.Success(200,"领取成功",nil,nil)
	this.ServeJSON()
}