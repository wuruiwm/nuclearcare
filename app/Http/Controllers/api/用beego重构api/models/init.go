package models

import (
	"github.com/astaxie/beego"
	"github.com/astaxie/beego/orm"
	_ "github.com/go-sql-driver/mysql"
)

func init(){
	mysql_conn_str := beego.AppConfig.String("dbuser")+":"+beego.AppConfig.String("dbpasswd")+"@tcp("+beego.AppConfig.String("dbhost")+":"+beego.AppConfig.String("dbport")+")/"+beego.AppConfig.String("dbname")+"?charset="+beego.AppConfig.String("dbcharset")
	// 设置数据库基本信息，
	orm.RegisterDataBase("default","mysql",mysql_conn_str)
	// 映射model数据库
	orm.RegisterModel(new(Banner),new(Member),new(MarketingRecharge),new(Service),new(RechargeOrder),new(CouponLog),new(Coupon),new(Order),new(OrderService))
}