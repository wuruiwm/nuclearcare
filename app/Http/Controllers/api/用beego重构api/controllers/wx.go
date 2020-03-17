package controllers

import (
	"beego_test/comm"
	"beego_test/models"
	"fmt"
	"github.com/astaxie/beego"
	"github.com/astaxie/beego/orm"
	"github.com/xlstudio/wxbizdatacrypt"
)

//定义wx请求的struct
type WxController struct{
	beego.Controller
}
//微信用户信息解密接口 表单验证
type MemberDecryptForm struct {
	SessionKey string `form:"session_key"`
	EncryptedData string `form:"encrypteData"`
	Iv string `form:"iv" valid:"Required"`
}
func (this *WxController) Login() {
	code := this.GetString("code")
	if code == ""{
		this.Data["json"] = comm.Error(500,"code不能为空")
		this.ServeJSON()
		return
	}
	appid := beego.AppConfig.String("appid")
	secret := beego.AppConfig.String("appsecret")
	grant_type := "authorization_code"
	js_code := comm.DefineStrReplace(code)
	url := "https://api.weixin.qq.com/sns/jscode2session?appid="+appid+"&secret="+secret+"&grant_type="+grant_type+"&js_code="+js_code
	m := comm.CurlGet(url)
	_,ok := m["session_key"]
	if !ok {
		this.Data["json"] = comm.Error(500,"登陆出错,请重试")
		this.ServeJSON()
		return
	}
	this.Data["json"] = comm.Success(200,"获取session_key成功",m,0)
	this.ServeJSON()
}
func (this *WxController) MemberDecrypt(){
	var input MemberDecryptForm
	this.ParseForm(&input)
	if input.Iv == ""{
		this.Data["json"] = comm.Error(500,"iv不能为空")
		this.ServeJSON()
		return
	}
	if input.SessionKey == ""{
		this.Data["json"] = comm.Error(500,"SessionKey不能为空")
		this.ServeJSON()
		return
	}
	if input.EncryptedData == ""{
		this.Data["json"] = comm.Error(500,"EncryptedData不能为空")
		this.ServeJSON()
		return
	}
	input.EncryptedData = comm.DefineStrReplace(input.EncryptedData)
	input.Iv = comm.DefineStrReplace(input.Iv)
	appID := beego.AppConfig.String("appid")
	wx := wxbizdatacrypt.WxBizDataCrypt{AppID: appID, SessionKey: input.SessionKey}
	result,_ := wx.Decrypt(input.EncryptedData, input.Iv, false) //第三个参数解释 true返回json false返回map
	data := result.(map[string]interface{})
	openid,ok := data["openId"]
	if !ok{
		this.Data["json"] = comm.Error(500,"登陆出错,请重试")
		this.ServeJSON()
		return
	}
	o := orm.NewOrm()
	var member models.Member
	o.QueryTable("member").Filter("openid",openid).One(&member)
	fmt.Println(member)
	if member.Id == 0{
		//用户不存在  执行插入操作
		member.Openid = openid.(string)
		member.Nickname = data["nickName"].(string)
		member.AvatarUrl = data["avatarUrl"].(string)
		id,err := o.Insert(&member)
		if err != nil{
			this.Data["json"] = comm.Error(500,"登陆出错,请重试")
			this.ServeJSON()
			return
		}
		member.Id = int(id)
	}
	member.Token = comm.SetToken(member.Id)
	member.CouponTotal = 0
	this.Data["json"] = comm.Success(200,"登陆成功",member,nil)
	this.ServeJSON()
}