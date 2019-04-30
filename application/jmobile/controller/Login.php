<?php

namespace app\jmobile\controller;
use think\Controller;
use think\Config;

class Login extends Common {

    
    public function index()
    {


        $user_tel = input('user_tel');
        if(empty($user_tel)){
            print_r($this->returnJson(array(),'电话号不可为空',0)); 
            die;
        }

        $unionid = input('unionid');
        
        if(empty($unionid)){
            print_r($this->returnJson(array(),'参数错误',0)); 
            die;
        }

        $user_login = $this->callApi('user_login/index', ['user_tel'=>$user_tel,'unionid'=>$unionid], 'POST'); 
        
        $info = array();
        if($user_login['status'] == 1){
            $user_login['data']['user_login']['village_id']=1;
            print_r($this->returnJson($user_login['data']['user_login'],'成功',1)); 
        }else{
            print_r($this->returnJson(array(),$user_login['message'],0)); 
        }

    }



}
