package models

type Service struct {
	Id int `json:"id"`
	Sort int `json:"-"`
	Title string `json:"title"`
	Type int `json:"type"`
	Price float64 `json:"price"`
	CreateTime int	`json:"-"`
	UpdateTime int `json:"-"`
}