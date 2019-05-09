<?php
namespace app\jmobile\controller;
use think\Controller;
use think\Config;

/**
 * 发送短信验证码模块
 */
class SendSms extends Common {
    

    
    public function __construct(){

        parent::__construct();
    }

    /**
     * 登录首页
     */
    public function index()
    {
        
        if($this->request->isPost()){
            
            $mobile=input('mobile');
            
            if(empty($mobile)){
                print_r($this->returnJson(array(),'手机号码为空',0)); 
                die;
            }
            
            
            $preg_phone='/^1[345789][0-9]{9}$/';
            if(!preg_match($preg_phone,$mobile)){
                print_r($this->returnJson(array(),'请输入有效的手机号码',0)); 
                die;
            }
            
           
            
            $login = $this->callApi('send_sms/wxSms', array('mobile'=>$mobile), 'POST');
//            print_r($login);die;
            if($login['status'] == 0){
                print_r($this->returnJson(array(),$login['message'],0)); 
                die;

            }
            
            print_r($this->returnJson($login['data']['code'],'发送成功',1));
            
            
            
            
        }

    }
    

    
 
    
    
    
}