package models

type OrderService struct {
	Id int
	OrderId int
	status int
	StandardServiceId int
	StandardServiceTitle string
	StandardServicePrice float64
	Additional string
	CreateTime int
	UpdateTime int
}

