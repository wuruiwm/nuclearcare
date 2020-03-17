// @APIVersion 1.0.0
// @Title beego Test API
// @Description beego has a very cool tools to autogenerate documents for your API
// @Contact astaxie@gmail.com
// @TermsOfServiceUrl http://beego.me/
// @License Apache 2.0
// @LicenseUrl http://www.apache.org/licenses/LICENSE-2.0.html
package routers

import (
	"beego_test/controllers"
	"github.com/astaxie/beego"
)

func init() {
	ns := beego.NewNamespace("/api",
		//轮播图
		beego.NSRouter("/banner", &controllers.BannerController{}),
		//wx相关
		beego.NSRouter("/wx/login", &controllers.WxController{},"post:Login"),
		beego.NSRouter("/wx/memberdecrypt", &controllers.WxController{},"post:MemberDecrypt"),
		//个人中心用户信息
		beego.NSRouter("/member", &controllers.MemberController{}),
		//充值优惠列表
		beego.NSRouter("/recharge/list", &controllers.RechargeController{},"get:List"),
		//充值
		beego.NSRouter("/recharge", &controllers.RechargeController{}),
		//服务列表
		beego.NSRouter("/service", &controllers.ServiceController{}),
		//订单获取余额和可用优惠券列表
		beego.NSRouter("/order/base", &controllers.MemberController{},"get:Base"),
		//用户端上传图片
		beego.NSRouter("/upload/image", &controllers.UploadController{},"post:Image"),
		//优惠券详情
		beego.NSRouter("/coupon/detail", &controllers.CouponController{}),
		//领取优惠券
		beego.NSRouter("/coupon/receive", &controllers.CouponController{},"post:Receive"),
		//提交订单
		beego.NSRouter("/order/create", &controllers.OrderController{},"post:Create"),
		//支付参数获取
		beego.NSRouter("/order/pay", &controllers.OrderController{},"get:Pay"),
		//订单列表
		beego.NSRouter("/order/list", &controllers.OrderController{},"get:List"),
		//订单详情
		beego.NSRouter("/order/detail", &controllers.OrderController{},"get:Detail"),
		//取消订单
		beego.NSRouter("/order/cancel", &controllers.OrderController{},"post:Cancel"),
	)
	beego.AddNamespace(ns)
}
