package models

import (
	"fmt"
	"github.com/astaxie/beego/orm"
	"math/rand"
	"strconv"
	"time"
)

type RechargeOrder struct {
	Id int
	MemberId int
	Status int
	Price float64
	Ordersn string
	Give float64
	CreateTime int	`json:"-"`
	UpdateTime int `json:"-"`
}
func (this *RechargeOrder) CreateOrder(){
	fmt.Println(time.Now().Unix())
	this.Status = 0
	this.CreateOrdersn()
	this.CreateTime = int(time.Now().Unix())
	this.UpdateTime = int(time.Now().Unix())
	o := orm.NewOrm()
	var recharge MarketingRecharge
	o.QueryTable("marketing_recharge").Filter("full__lte",this.Price).OrderBy("-give").One(&recharge,"give")
	if recharge.Give > 0{
		this.Give = recharge.Give
	}
	id,_ := o.Insert(this)
	this.Id = int(id)
}
func (this *RechargeOrder) CreateOrdersn(){
	rand.Seed(time.Now().UnixNano())
	ordersn := "RE"+time.Unix(time.Now().Unix(),0).Format("20060102")+strconv.Itoa(rand.Intn(89999)+10000)
	o := orm.NewOrm()
	for{
		var recharge_order RechargeOrder
		o.QueryTable("recharge_order").Filter("ordersn",ordersn).One(&recharge_order,"id")
		if recharge_order.Id == 0{
			this.Ordersn = ordersn
			break
		}else{
			ordersn = "RE"+time.Unix(time.Now().Unix(),0).Format("20060102") + strconv.Itoa(rand.Intn(10000)+89999)
		}
	}
}