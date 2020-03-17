package models

import (
	"beego_test/comm"
	"github.com/astaxie/beego"
	"github.com/liyoung1992/wechatpay"
	"time"
)

type WxPay struct {
	Ordersn string
	Price float64
	Openid string
}

func (this *WxPay) Pay()interface{}{
	//前人栽树，后人乘凉，感谢https://github.com/liyoung1992/wechatpay
	wechat_client := wechatpay.New(beego.AppConfig.String("appid"),beego.AppConfig.String("mchid"),beego.AppConfig.String("key"),nil,nil)
	var pay_data wechatpay.UnitOrder
	pay_data.NotifyUrl = comm.DomainName() + "/api/wx/notify"
	pay_data.TradeType = "JSAPI"
	pay_data.Body = beego.AppConfig.String("body")
	pay_data.SpbillCreateIp = "8.8.8.8"
	pay_data.TotalFee = int(this.Price*100)
	pay_data.OutTradeNo = this.Ordersn
	pay_data.Openid = this.Openid
	result,err := wechat_client.Pay(pay_data)
	if err != nil{
		return false
	}
	data := make(map[string]interface{})
	data["appId"] = result.AppId
	data["nonceStr"] = result.NonceStr
	data["package"] = "prepay_id=" + result.PrepayId
	data["signType"] = "MD5"
	data["timeStamp"] = time.Now().Unix()
	data["paySign"] = comm.WxPaySign(data)
	return data
}