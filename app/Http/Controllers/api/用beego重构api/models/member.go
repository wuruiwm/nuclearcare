package models

type Member struct {
	Id int `json:"id"`
	Openid string `json:"openid"`
	Nickname string `json:"nickname"`
	Phone string `json:"-"`
	AvatarUrl string `json:"avatar_url"`
	Balance float64 `json:"balance"`
	CreateTime int	`json:"-"`
	UpdateTime int `json:"-"`

	Token string `json:"token,omitempty" orm:"-"`
	CouponTotal int `json:"coupon_total" orm:"-"`
}