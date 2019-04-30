<?php
// +----------------------------------------------------------------------
// | TwoThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.twothink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author:
// +----------------------------------------------------------------------
namespace app\jmobile\controller;
use think\Controller;

class Common extends Controller {
    
    public function __construct(){

        parent::__construct();   
    
    }

    /* 
    * 访问接口公共方法
    */
    public function callApi($api_path, $params, $method)
    {
        $access_token = $this->getAccessToken();   //访问令牌
   
//        $access_token = 3;
        if(!$access_token){
            return 'access_token获取失败';die;
        }
        $api_path .= '?access_token=' . $access_token;
        return $this->curlCommit($api_path, $params, $method);
    }
    
    
    /* 
    * ��access_token
    */
    public function getAccessToken()
    {
        $file = file_get_contents("./access_token.json", true);
   
        $file_result = json_decode($file,true);
        
            
        if (time() > ($file_result['expires'] - 10)){          //token过期, 重新获取
            $post_data['client_id'] = config('client_auth.client_id');
            $post_data['client_secret'] = config('client_auth.client_secret');

            $res = $this->curlCommit('oauth/getAccessToken', $post_data, 'POST');

            if($res['status'] == 1){
                $data = $res['data'];
                $access_token = $data['access_token'];
                $token_info = array();
                $token_info['access_token'] = $access_token;
                $token_info['expires'] = $data['expires'];
                $jsonStr =  json_encode($token_info);
                $fp = fopen("./access_token.json", "w");
                fwrite($fp, $jsonStr);
                fclose($fp);
                return $access_token;
            }else{
                return null;
            }
        }else{
            return $file_result['access_token'];
        }
        
    }
    
    
     /* 
    * curl提交
    */
    public function curlCommit($api_path, $params, $method)
    {
        $url = config('root_path')."/api/".$api_path;
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($curl, CURLOPT_POST, 1);
//        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        switch ($method){
            case "GET" :
                foreach($params as $k=>$v){
                    $params_arr[] = sprintf('%s=%s', $k, trim($v));
                }
                $params_str = implode('&', $params_arr);
                if(strpos($url,'?') !== false){
                    $url .= '&'.$params_str;
                } else {
                    $url .= '?'.$params_str;
                }
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_HTTPGET, true);break;
            case "POST":
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                                                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($curl, CURLOPT_POST,true);
                curl_setopt($curl, CURLOPT_POSTFIELDS,json_encode($params));break;
            case "PUT" :
                curl_setopt ($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($curl, CURLOPT_POSTFIELDS,json_encode($params));break;
            case "DELETE":
                curl_setopt ($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
                curl_setopt($curl, CURLOPT_POSTFIELDS,json_encode($params));break;
            default : 
                curl_setopt($curl, CURLOPT_POST,true);
                curl_setopt($curl, CURLOPT_POSTFIELDS,$params);break;
        }
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); 
        $res = curl_exec($curl);
        curl_close($curl);
//        echo $res;die;
        $res = json_decode(trim($res),true);
        return $res;

    }
    
    /**
    * ��������ʱ����������(������ʼ�ͽ���)
    */
    function timeDiff( $begin_time, $end_time )
    {
        if ($begin_time == $end_time) {
            return array('day' => 1);
        }

        if ( $begin_time < $end_time ) {
            $starttime = $begin_time;
            $endtime = $end_time;
        } else {
            $starttime = $end_time;
            $endtime = $begin_time;
        }
        $timediff = $endtime - $starttime;
        $days = intval( $timediff / 86400 );

        if (!empty($days)) {
            $days = $days +1;
        }
        $res = array( "day" => $days);
        return $res;
    }
    
  //金额转换大写函数
    function convertAmountToCn($amount, $type = 0) {
        // 判断输出的金额是否为数字或数字字符串
        if(!is_numeric($amount)){
            return "要转换的金额只能为数字!";
        }
     
        // 金额为0,则直接输出"零元整"
        if($amount == 0) {
            return "人民币零元整";
        }
     
        // 金额不能为负数
        if($amount < 0) {
            return "要转换的金额不能为负数!";
        }
     
        // 金额不能超过万亿,即12位
        if(strlen($amount) > 12) {
            return "要转换的金额不能为万亿及更高金额!";
        }
     
        // 预定义中文转换的数组
        $digital = array('零', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖');
        // 预定义单位转换的数组
        $position = array('仟', '佰', '拾', '亿', '仟', '佰', '拾', '万', '仟', '佰', '拾', '元');
     
        // 将金额的数值字符串拆分成数组
        $amountArr = explode('.', $amount);
     
        // 将整数位的数值字符串拆分成数组
        $integerArr = str_split($amountArr[0], 1);
     
        // 将整数部分替换成大写汉字
        $result = '';
        $integerArrLength = count($integerArr);     // 整数位数组的长度
        $positionLength = count($position);         // 单位数组的长度
        for($i = 0; $i < $integerArrLength; $i++) {
            // 如果数值不为0,则正常转换
            if($integerArr[$i] != 0){
                $result = $result . $digital[$integerArr[$i]] . $position[$positionLength - $integerArrLength + $i];
            }else{
                // 如果数值为0, 且单位是亿,万,元这三个的时候,则直接显示单位
                if(($positionLength - $integerArrLength + $i + 1)%4 == 0){
                    $result = $result . $position[$positionLength - $integerArrLength + $i];
                }
            }
        }
     
        // 如果小数位也要转换
        if($type == 0) {
            // 将小数位的数值字符串拆分成数组
            $decimalArr = str_split($amountArr[1], 1);
            // 将角替换成大写汉字. 如果为0,则不替换
            if($decimalArr[0] != 0){
                $result = $result . $digital[$decimalArr[0]] . '角';
            }
            // 将分替换成大写汉字. 如果为0,则不替换
            if($decimalArr[1] != 0){
                $result = $result . $digital[$decimalArr[1]] . '分';
            }
        }else{
            $result = $result . '整';
        }
        return $result;
    }

    
    /* 
    * json����
    */
    public function returnJson($data = array(), $info='', $status = 0)
    {
        $info_array = array();
        $info_array['status'] = $status;
        $info_array['info'] = $info;
        $info_array['data'] = $data;

        return json_encode($info_array,JSON_UNESCAPED_UNICODE);
    }
    
}
