package models

type Banner struct {
	Id int `json:"id"`
	Sort int `json:"-"`
	ImgPath string `json:"-"`
	CreateTime int	`json:"-"`
	UpdateTime int `json:"-"`

	Url string `json:"url" orm:"-"`
}