<?php

namespace app\jmobile\controller;
use think\Controller;
use think\Config;
use think\Loader;


class WxUserInfoDecrypt extends Common {


    public function __construct(){
        parent::__construct();   
       
    }

	/**
	 * 检验数据的真实性，并且获取解密后的明文.
	 * @param encryptedData string 加密的用户数据
	 * @param iv string 与用户数据一同返回的初始向量
	 * @param js_code  用户允许登录后，回调内容会带上 code（有效期五分钟），开发者需要将 code 发送到开发者服务器后台，使用code 换取 session_key api，将 code 换成 openid 和 session_key
	 * 
	 */
    public function dateCrypt()
    {
        
                $appid	= 	config('wx_setting.appid');
                $secret	=	config('wx_setting.secret');

		$get_result 	= 	file_get_contents("php://input");
		$get_result		=	json_decode($get_result,true);
             
		$js_code    	=	$get_result['js_code'];
		$iv 			=	$get_result['iv'];
		$encryptedData	=	$get_result['encryptedData'];
		//$js_code    	=	'071rJy6i08NSsw1SJ1ai0rBB6i0rJy6z';
		//$iv 			=	'TKk2Are3Gvg1lKTMvQmfsA==';
		//$encryptedData	=	'qtCl4p2axF5Vy80JQp77v/tNaBWb/6ponr2d5faBWbgUOKAterdcw8YFWzb3kKaU7QE7TqElz1XGnQ2XP2A5k/e/UR72BV0TyaTZhicBooqeEUV3Xmi0mQHrkFZTes4p2bKhU2H0DPP5IDCF/3uLscDWCl0bICbviSmEVzN+iKEN5RgS2vR6QUML/MvFumvPMukc7pPyw6KAmJGRTDIHfskQXSOkmKyCErlWgaSCc4/1iQksuDZ8OUa6LSHqd38JQZl9dPKW5zwevUsMJJGhRJZOeNAkYri1K9nGIZSl0Tqb9i/r4GhvqpAQHYH5mU8QKn4h8ZEsr58L0J5MwAii64fwqHn67vXIVWfomdMBWnc53xfxIUoaH1xPAtkGTCgK/TDZMhJ6WnbPzx/1FQ2kJQIhWZmj5e8S875NlWOHqeIaaVFqae57uqx9EzlIO1LiuaTnuWsxnRVlZj2qqc8ZAmsg0VHdDHQpgHWVJMyvWqE=';


		$url='https://api.weixin.qq.com/sns/jscode2session?appid='.$appid.'&secret='.$secret.'&js_code='.$js_code.'&grant_type=authorization_code';
          
		$get_sessionkey=$this->curl($url);


		$curl_data=json_decode($get_sessionkey,true);
		

		$sessionKey = $curl_data['session_key'];// string 用户在小程序登录后获取的会话密钥
                import('wxBizDataCrypt.wxBizDataCrypt');
		$pc = new \wxBizDataCrypt($appid,$sessionKey);

		$errCode = $pc->decryptData($encryptedData, $iv, $data );

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
