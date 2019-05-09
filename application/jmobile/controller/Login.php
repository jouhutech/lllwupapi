<?php

namespace app\jmobile\controller;
use think\Controller;
use think\Config;

class Login extends Common {

    
    public function index()
    {

        $unionid = input('unionid');
        
        if(empty($unionid)){
            print_r($this->returnJson(array(),'参数错误',0)); 
            die;
        }

        $user_login = $this->callApi('user_login/index', ['unionid'=>$unionid], 'POST'); 
        
        $info = array();
        if($user_login['status'] == 1){
            $user_login['data']['user_login']['village_id']=1;
            print_r($this->returnJson($user_login['data']['user_login'],'成功',1)); 
        }else{
            print_r($this->returnJson(array(),$user_login['message'],0)); 
        }

    }

    
    
    public function upTel()
    {

        $user_id = input('user_id');
        
        if(empty($user_id)){
            print_r($this->returnJson(array(),'参数错误',0)); 
            die;
        }
        
        $user_tel=input('user_tel');
            
        if(empty($user_tel)){
            print_r($this->returnJson(array(),'手机号码为空',0)); 
            die;
        }


        $preg_phone='/^1[345789][0-9]{9}$/';
        if(!preg_match($preg_phone,$user_tel)){
            print_r($this->returnJson(array(),'请输入有效的手机号码',0)); 
            die;
        }
        

        $user_login = $this->callApi('user_login/upTel', ['user_id'=>$user_id,'user_tel'=>$user_tel], 'POST'); 
        
        $info = array();
        if($user_login['status'] == 1){
            print_r($this->returnJson(array(),'关联成功',1)); 
        }else{
            print_r($this->returnJson(array(),$user_login['message'],0)); 
        }

    }


}
