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
    /*
     * 获取微信openid及unionId
     */
    public function wxInfo()
    {
        
        $appid 		= 	'wx0e80f191273e8063';
        $secret	    =	'ded7c73cc2e1408d47d01a612bdb20f9';
        
        $get_result 	= 	input();
        $js_code    	=	$get_result['js_code'];
        $iv 			=	$get_result['iv'];
        $encryptedData	=	$get_result['encryptedData'];
        $url='https://api.weixin.qq.com/sns/jscode2session?appid='.$appid.'&secret='.$secret.'&js_code='.$js_code.'&grant_type=authorization_code';
 
        $x=curl($url);
        
        $curl_data=json_decode($x,true);

        $sessionKey = $curl_data['session_key'];
        
        import('wxBizDataCrypt');
        $send_class = new \WXBizDataCrypt($appid,$sessionKey);


        $errCode = $send_class->decryptData($encryptedData, $iv, $data );
        if ($errCode == 0) {
            echo $data;die;
        } else {
            print($errCode . "\n");
        }
    }
    
    public function curl($url,$data = null){
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
	if (!empty($data)){
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	}
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	$output = curl_exec($curl);
	curl_close($curl);
	return $output;
}

}
