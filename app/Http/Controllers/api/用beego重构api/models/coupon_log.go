package models

type CouponLog struct {
	Id int `json:"id,omitempty"`
	MemberId int `json:"member_id,omitempty"`
	Coupon_id int `json:"coupon_id,omitempty"`
	FaceValue float64 `json:"face_value,omitempty"`
	ValidityTime int `json:"validity_time,omitempty"`
	Full float64 `json:"full,omitempty"`
	Status int `json:"status,omitempty"`
	ExpireTime int `json:"expire_time,omitempty"`
	CreateTime int `json:"-"`
	UpdateTime int `json:"-"`
}