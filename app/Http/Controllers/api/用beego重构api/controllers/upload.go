package controllers

import (
	"beego_test/comm"
	"fmt"
	"github.com/astaxie/beego"
	"os"
	"path"
	"strings"
	"time"
)

type UploadController struct {
	beego.Controller
}

func (this *UploadController) Image(){
	f, h, err := this.GetFile("file")
	if err != nil {
		this.Data["json"] = comm.Error(500,"请上传图片")
		this.ServeJSON()
		return
	}
	defer f.Close()
	ext := path.Ext(h.Filename)
	var AllowExtMap map[string]bool = map[string]bool{
		".jpg":true,
		".jpeg":true,
		".png":true,
	}
	if _,ok:=AllowExtMap[ext];!ok{
		this.Data["json"] = comm.Error(500,"允许上传的图片类型为jpg,jpeg,png")
		this.ServeJSON()
		return
	}
	//创建文件夹
	runpath,_ := comm.GetCurrentRunPath()
	fmt.Println(runpath)
	upload_dir := runpath + "/public/upload/" + time.Now().Format("2006/01/02/")
	if ok,_ := comm.PathExists(upload_dir);!ok{
		if err := os.MkdirAll(upload_dir,777);err != nil {
			this.Data["json"] = comm.Error(500,"上传文件失败,请检查权限")
			this.ServeJSON()
			return
		}
	}
	//构造文件名和路径
	file_name := comm.Md5FileName() + ext
	path := upload_dir + file_name
	//保存文件
	err = this.SaveToFile("file",path)
	if err != nil{
		this.Data["json"] = comm.Error(500,"上传文件失败,请检查权限")
		this.ServeJSON()
		return
	}
	//构造返回值
	path = strings.Replace(path,runpath,"",1)
	data := make(map[string]string)
	data["path"] = path
	data["url"] = comm.ImgpathToUrl(path)
	this.Data["json"] = comm.Success(200,"上传成功",data,nil)
	this.ServeJSON()
}