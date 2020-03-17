package models

type MarketingRecharge struct {
	Id int `json:"id"`
	Full float64 `json:"full"`
	Give float64 `json:"give"`
	CreateTime int	`json:"-"`
	UpdateTime int `json:"-"`
}