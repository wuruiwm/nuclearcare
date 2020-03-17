package models

type Coupon struct {
	Id int `json:"id"`
	FaceValue float64 `json:"face_value"`
	ValidityTime int `json:"validity_time"`
	Full float64 `json:"full"`
	Total int `json:"total"`
	ReceiveNum int `json:"receive_num"`
	StartTime int `json:"-"`
	EndTime int	`json:"-"`
	CreateTime int	`json:"-"`
	UpdateTime int `json:"-"`
}