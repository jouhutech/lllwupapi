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
            $post_data['grant_type'] = 'client_credentials';
            $post_data['client_id'] = 'test';
            $post_data['client_secret'] = 'test';

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
    
    
    //����
    function convertAmountToCn($amount, $type = 0) {
        // �ж�����Ľ���Ƿ�Ϊ���ֻ������ַ���
        if(!is_numeric($amount)){
            return "Ҫת���Ľ��ֻ��Ϊ����!";
        }
     
        // ���Ϊ0,��ֱ�����"��Ԫ��"
        if($amount == 0) {
            return "�������Ԫ��";
        }
     
        // ����Ϊ����
        if($amount < 0) {
            return "Ҫת���Ľ���Ϊ����!";
        }
     
        // ���ܳ�������,��12λ
        if(strlen($amount) > 12) {
            return "Ҫת���Ľ���Ϊ���ڼ����߽��!";
        }
     
        // Ԥ��������ת��������
        $digital = array('��', 'Ҽ', '��', '��', '��', '��', '½', '��', '��', '��');
        // Ԥ���嵥λת��������
        $position = array('Ǫ', '��', 'ʰ', '��', 'Ǫ', '��', 'ʰ', '��', 'Ǫ', '��', 'ʰ', 'Ԫ');
     
        // ��������ֵ�ַ�����ֳ�����
        $amountArr = explode('.', $amount);
     
        // ������λ����ֵ�ַ�����ֳ�����
        $integerArr = str_split($amountArr[0], 1);
     
        // �����������滻�ɴ�д����
        $result = '';
        $integerArrLength = count($integerArr);     // ����λ����ĳ���
        $positionLength = count($position);         // ��λ����ĳ���
        for($i = 0; $i < $integerArrLength; $i++) {
            // �����ֵ��Ϊ0,������ת��
            if($integerArr[$i] != 0){
                $result = $result . $digital[$integerArr[$i]] . $position[$positionLength - $integerArrLength + $i];
            }else{
                // �����ֵΪ0, �ҵ�λ����,��,Ԫ��������ʱ��,��ֱ����ʾ��λ
                if(($positionLength - $integerArrLength + $i + 1)%4 == 0){
                    $result = $result . $position[$positionLength - $integerArrLength + $i];
                }
            }
        }
     
        // ���С��λҲҪת��
        if($type == 0) {
            // ��С��λ����ֵ�ַ�����ֳ�����
            $decimalArr = str_split($amountArr[1], 1);
            // �����滻�ɴ�д����. ���Ϊ0,���滻
            if($decimalArr[0] != 0){
                $result = $result . $digital[$decimalArr[0]] . '��';
            }
            // �����滻�ɴ�д����. ���Ϊ0,���滻
            if($decimalArr[1] != 0){
                $result = $result . $digital[$decimalArr[1]] . '��';
            }
        }else{
            $result = $result . '��';
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