# 物业费微信小程序接口示例
## 功能介绍
> 用于小程序和app端数据交互 小程序和app端github地址 https://github.com/jouhutech/lllwupuniapp
## 使用场景
> 需要自行开发缴费业务逻辑的用户
## 代码架构
> 代码基于Thinkphp5.0.x版本开发,需要熟练使用Thinkphp框架,Thinkphp官网 http://www.thinkphp.cn/
## 使用流程
> 需要修改application文件夹下的config.php文件一部分内容
```php
    //目录
    'root_path'=> 'https://n.loulilouwai.net',//请求的接口域名
    //oauth数据凭证
    'client_auth' =>  [
        'grant_type'      => 'client_credentials',
        'client_id'  => '',//客户端id
        'client_secret' => '',//客户端secret
    ],
    //微信相关数据
    'wx_setting' =>  [
        'appid'      => '',
        'secret'  => '',
        'mch_id' => '',
        'wxpay_key' => '',
    ],
```
