package models

import (
	"github.com/astaxie/beego/orm"
	"math/rand"
	"strconv"
	"time"
)

type Order struct {
	Id int `json:"id"`
	MemberId int `json:"memberId"`
	Ordersn string `json:"ordersn"`
	Status int `json:"status"`
	Name string `json:"name"`
	Remark string `json:"remark"`
	Phone string `json:"phone"`
	Address string `json:"address"`
	Type int `json:"type"`
	IsSend int `json:"is_send"`
	ExpressName string `json:"express_name"`
	ExpressNumber string `json:"express_number"`
	Photos string `json:"photos"`
	TotalPrice float64 `json:"total_price"`
	PayablePrice float64 `json:"payable_price"`
	CouponLogId int `json:"coupon_log_id"`
	CouponPrice float64 `json:"coupon_price"`
	PayTime int `json:"pay_time"`
	Balance float64 `json:"balance"`
	PayType int `json:"pay_type"`
	WxTransactionId string `json:"wx_transaction_id"`
	CreateTime int `json:"create_time"`
	UpdateTime int `json:"update_time"`
	IsNotice int `json:"is_notice"`
}
func (this *Order) CreateOrdersn(){
	rand.Seed(time.Now().UnixNano())
	ordersn := "SE"+time.Unix(time.Now().Unix(),0).Format("20060102")+strconv.Itoa(rand.Intn(89999)+10000)
	o := orm.NewOrm()
	for{
		var order Order
		o.QueryTable("order").Filter("ordersn",ordersn).One(&order,"id")
		if order.Id == 0{
			this.Ordersn = ordersn
			break
		}else{
			ordersn = "SE"+time.Unix(time.Now().Unix(),0).Format("20060102") + strconv.Itoa(rand.Intn(10000)+89999)
		}
	}
}