package comm

import (
	"github.com/astaxie/beego"
	"github.com/garyburd/redigo/redis"
)

var Redis *redis.Pool
func init(){
	Redis = &redis.Pool{
		MaxIdle:   3,
		MaxActive: 5,
		Dial: func() (redis.Conn, error) {
			c, err := redis.Dial("tcp", beego.AppConfig.String("redishost")+":"+beego.AppConfig.String("redisport"))
			if err != nil {
				return nil, err
			}
			c.Do("select",1)//切换到redis数据库1
			return c, err
		},
	}
}
