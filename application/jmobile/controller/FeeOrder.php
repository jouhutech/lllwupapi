<?php
namespace app\jmobile\controller;

/**
 * 缴费模块
 */
class FeeOrder extends Common {
    
    public function __construct(){
        parent::__construct();
    }
    
    
    
    /**
     * 收款页面
     */
    public function check()
    {
        
        

        $room_id = input('room_id');
        
        if(empty($room_id)){
            print_r($this->returnJson(array(),'参数错误',0)); 
            die;
        }
        
        
        $res_room = $this->callApi('estate/getRoomInfo', array('room_id'=>$room_id), 'GET');
       
        //print_r($res);die;
        if($res_room['status'] != 1){
            
            print_r($this->returnJson(array(),$res_room['message'],0)); 
            die;
        }else{
            if($res_room['data']['property_fee_endtime']==0){
                $res_room['data']['property_fee_endtime'] = $res_room['data']['coming_time'];
            }
        }
     

        
        $end_time = strtotime(trim($_GET['end_time']));
        
        if(input('start_time')){
            $start_time = strtotime(trim(input('start_time')));
        }else{
            $start_time = 0;
        }

        if($start_time != $res_room['data']['property_fee_endtime']){
            print_r($this->returnJson(array(),'开始时间参数错误',0)); 
            die;
        }
        
        
        if($start_time >= $end_time){
            print_r($this->returnJson(array(),'开始时间要小于结束时间',0)); 
            die;
        }

        
        

        $params = array();
        $params['room_id'] = $room_id;
        $params['start_time'] = $start_time;
        $params['end_time'] = $end_time;
       
        $res = $this->callApi('fee_order/createTmpOrder', $params, 'POST');
        //print_r($res);die;
        $total_fee = 0;
        if($res['status'] != 1){
            print_r($this->returnJson(array(),$res['message'],0)); 
            die;
        }else{
            if(!empty($res['data']['tmp_order_list'])){
                $total_fee = array_sum(array_column($res['data']['tmp_order_list'], 'total_fee'));
            }
        }
        
        
        
        
        $res['data']['property_fee_endtime'] = $res_room['data']['property_fee_endtime'];
        $res['data']['total_fee'] = $total_fee;
        print_r($this->returnJson($res['data'],'成功',1)); 

        
    }
    
    
    
    
    
    
    
    
    /*
     * 创建订单
     */
    public function createOrder(){
        $room_id = input('room_id');
        if(empty($room_id)){
            print_r($this->returnJson(array(),'参数错误',0)); 
            die;
        }
        
        
        $total_use_real_money = trim(input('total_use_real_money'));

        if(empty($total_use_real_money)){
            print_r($this->returnJson(array(),'不可为零',0)); 
            die;
        }

        //关联费用勾选信息
        $tmp_id_arr = input('tmp_id_arr');
       
        if(empty($tmp_id_arr)){
            print_r($this->returnJson(array(),'参数错误',0)); 
            die;
        }
        $tmp_id_arr = explode(",", $tmp_id_arr);
        
        $tmp_order = array();
        if($tmp_id_arr){
            foreach($tmp_id_arr as $key=>$value){
                $value_array = explode('-', $value);
                $tmp_order[$key]['tmp_id'] = $value_array[0];
                $tmp_order[$key]['use_real_money'] = $value_array[1];;
                $tmp_order[$key]['remark'] = '';
            }
        }
        
        
        $params = array();
        $params['room_id'] = $room_id;
        $params['pay_method_id'] = 2;
        $params['use_real_money'] = $total_use_real_money;
        $params['fetch_fee_time'] = strtotime(date('Y-m-d'));
        $params['remark'] = '缴费';
        $params['tmp_order'] = json_encode($tmp_order);
        $params['status'] = 0;

        $openid = input('openid');
   
        if(empty($openid)){
            print_r($this->returnJson(array(),'参数错误',0)); 
            die;
        }
        $total_fee = $total_use_real_money*100;
  
        $create_order = $this->callApi('fee_order/createFeeOrder', $params, 'POST');
        if($create_order['status'] != 1){
            print_r($this->returnJson(array(),$create_order['message'],0)); 
            die;
        }
        
        $order_array = $create_order['data'];

        
        $this->pay($total_fee,$openid,$order_array['fee_order_id'],$order_array);
        
        
    }
    
    
    /* 首先在服务器端调用微信【统一下单】接口，返回prepay_id和sign签名等信息给前端，前端调用微信支付接口 */
    public function pay($total_fee,$openid,$order_id,$post){

        if(empty($total_fee)){
            echo json_encode(array('state'=>0,'Msg'=>'金额有误'));exit;
        }
        if(empty($openid)){
            echo json_encode(array('state'=>0,'Msg'=>'登录失效，请重新登录(openid参数有误)'));exit;
        }
        if(empty($order_id)){
            echo json_encode(array('state'=>0,'Msg'=>'自定义订单有误'));exit;
        }
        $appid =        config('wx_setting.appid');//如果是公众号 就是公众号的appid;小程序就是小程序的appid
        $body =         '物业缴费订单';
        $mch_id =       config('wx_setting.mch_id');
        $KEY = config('wx_setting.wxpay_key');
        $nonce_str =    md5(uniqid(microtime(true),true));//随机字符串32
        $notify_url =   'http://jouhu.com/lllwupapi/public/index.php/jmobile/fee_order/xiao_notify_url';  //支付完成回调地址url,不能带参数
        $out_trade_no = $post['order_number'];//商户订单号
        $spbill_create_ip = $_SERVER['SERVER_ADDR'];
        $trade_type = 'JSAPI';//交易类型 默认JSAPI

        $attach = implode('-|', $post);
        

        $post = array();
        //这里是按照顺序的 因为下面的签名是按照(字典序)顺序 排序错误 肯定出错
        $post['appid'] = $appid;
        $post['body'] = $body;
        $post['mch_id'] = $mch_id;
        $post['attach'] = $attach;
        $post['nonce_str'] = $nonce_str;//随机字符串
        $post['notify_url'] = $notify_url;
        $post['openid'] = $openid;
        $post['out_trade_no'] = $out_trade_no;
        $post['spbill_create_ip'] = $spbill_create_ip;//服务器终端的ip
        $post['total_fee'] = intval($total_fee);        //总金额 最低为一分钱 必须是整数
        $post['trade_type'] = $trade_type;
        $sign = $this->MakeSign($post,$KEY);              //签名
    
        $post_xml = '<xml>
               <appid>'.$appid.'</appid>
               <attach>'.$attach.'</attach>
               <body>'.$body.'</body>
               <mch_id>'.$mch_id.'</mch_id>
               <nonce_str>'.$nonce_str.'</nonce_str>
               <notify_url>'.$notify_url.'</notify_url>
               <openid>'.$openid.'</openid>
               <out_trade_no>'.$out_trade_no.'</out_trade_no>
               <spbill_create_ip>'.$spbill_create_ip.'</spbill_create_ip>
               <total_fee>'.$total_fee.'</total_fee>
               <trade_type>'.$trade_type.'</trade_type>
               <sign>'.$sign.'</sign>
            </xml> ';
    
        //统一下单接口prepay_id
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $xml = $this->http_request($url,$post_xml);     //POST方式请求http
       
        $array = $this->xml2array($xml);               //将【统一下单】api返回xml数据转换成数组，全要大写
        $array=array_change_key_case($array,CASE_UPPER);
  
        if($array['RETURN_CODE'] == 'SUCCESS' && $array['RESULT_CODE'] == 'SUCCESS'){
            $time = time();
            $tmp=array();                            //临时数组用于签名
            $tmp['appId'] = $appid;
            $tmp['nonceStr'] = $nonce_str;
            $tmp['package'] = 'prepay_id='.$array['PREPAY_ID'];
            $tmp['signType'] = 'MD5';
            $tmp['timeStamp'] = "$time";
    
            $data['state'] = 1;
            $data['timeStamp'] = "$time";           //时间戳
            $data['nonceStr'] = $nonce_str;         //随机字符串
            $data['signType'] = 'MD5';              //签名算法，暂支持 MD5
            $data['package'] = 'prepay_id='.$array['PREPAY_ID'];   //统一下单接口返回的 prepay_id 参数值，提交格式如：prepay_id=*
            $data['paySign'] = $this->MakeSign($tmp,$KEY);       //签名,具体签名方案参见微信公众号支付帮助文档;
            $data['out_trade_no'] = $out_trade_no;
    
        }else{
            $data['state'] = 0;
            $data['text'] = "错误";
            $data['RETURN_CODE'] = $array['RETURN_CODE'];
            $data['RETURN_MSG'] = $array['RETURN_MSG'];
        }
        
        print_r($this->returnJson($data,'获取成功',1)); 
        die;
    }
    
    /**
     * 生成签名, $KEY就是支付key
     * @return 签名
     */
    public function MakeSign( $params,$KEY){
        //签名步骤一：按字典序排序数组参数
        ksort($params);
        $string = $this->ToUrlParams($params);  //参数进行拼接key=value&k=v
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".$KEY;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }
    /**
     * 将参数拼接为url: key=value&key=value
     * @param $params
     * @return string
     */
    public function ToUrlParams( $params ){
        $string = '';
        if( !empty($params) ){
            $array = array();
            foreach( $params as $key => $value ){
                $array[] = $key.'='.$value;
            }
            $string = implode("&",$array);
        }
        return $string;
    }
    /**
     * 调用接口， $data是数组参数
     * @return 签名
     */
    public function http_request($url,$data = null,$headers=array())
    {
        $curl = curl_init();
        if( count($headers) >= 1 ){
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($curl, CURLOPT_URL, $url);
    
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
    //获取xml里面数据，转换成array
    private function xml2array($xml){
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $data;
    }
    /**
     * 将xml转为array
     * @param string $xml
     * return array
     */
    public function xml_to_array($xml){
        if(!$xml){
            return false;
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $data;
    }
    
    /* 微信支付完成，回调地址url方法  xiao_notify_url() */
    public function xiao_notify_url(){
        //获取接口数据，如果$_REQUEST拿不到数据，则使用file_get_contents函数获取
        $post = $_REQUEST;
        if (empty($post)) {
            $post = file_get_contents("php://input");
        }

        if (empty($post)) {
            $post = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : '';
        }
        
        if (empty($post) || $post == null || $post == '') {
            //阻止微信接口反复回调接口  文档地址 https://pay.weixin.qq.com/wiki/doc/api/H5.php?chapter=9_7&index=7，下面这句非常重要!!!
            $str='<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';  
            echo $str;
            exit('Notify 非法回调');
        }
        /*****************微信回调返回数据样例*******************
        $post = '<xml>
           <return_code><![CDATA[SUCCESS]]></return_code>
           <return_msg><![CDATA[OK]]></return_msg>
           <appid><![CDATA[wx2421b1c4370ec43b]]></appid>
           <mch_id><![CDATA[10000100]]></mch_id>
           <nonce_str><![CDATA[IITRi8Iabbblz1Jc]]></nonce_str>
           <sign><![CDATA[7921E432F65EB8ED0CE9755F0E86D72F]]></sign>
           <result_code><![CDATA[SUCCESS]]></result_code>
           <prepay_id><![CDATA[wx201411101639507cbf6ffd8b0779950874]]></prepay_id>
           <trade_type><![CDATA[APP]]></trade_type>
           </xml>';
        *************************微信回调返回*****************/

       libxml_disable_entity_loader(true); //禁止引用外部xml实体

       $xml = simplexml_load_string($post, 'SimpleXMLElement', LIBXML_NOCDATA);//XML转数组

       $post_data = (array)$xml;
       
       /** 解析出来的数组
        *Array
        * (
        * [appid] => wx1c870c0145984d30
        * [bank_type] => CFT
        * [cash_fee] => 100
        * [fee_type] => CNY
        * [is_subscribe] => N
        * [mch_id] => 1297210301
        * [nonce_str] => gkq1x5fxejqo5lz5eua50gg4c4la18vy
        * [openid] => olSGW5BBvfep9UhlU40VFIQlcvZ0
        * [out_trade_no] => fangchan_588796
        * [result_code] => SUCCESS
        * [return_code] => SUCCESS
        * [sign] => F6890323B0A6A3765510D152D9420EAC
        * [time_end] => 20180626170839
        * [total_fee] => 100
        * [trade_type] => JSAPI
        * [transaction_id] => 4200000134201806265483331660
        * )
        *
        */
       //平台支付key
        $wxpay_key = config('wx_setting.wxpay_key');

        //接收到的签名
        $post_sign = $post_data['sign'];
        unset($post_data['sign']);

        //重新生成签名
        $newSign = $this->MakeSign($post_data,$wxpay_key);

        //签名统一，则更新数据库
        if($post_sign == $newSign){
            $attach = explode(',', $post_data['attach']);
            $data = array();
            $data['fee_order_id'] = $attach['0'];
            $data['out_trade_no'] = $post_data['out_trade_no'];
            $data['wechat_transaction_id'] = $post_data['wechat_transaction_id'];
            $wxUpOrder = $this->callApi('fee_order/wxUpOrder', $data, 'PUT');
            //print_r($res);die;
            if($wxUpOrder['status'] == 1){
                //阻止微信接口反复回调接口  文档地址 https://pay.weixin.qq.com/wiki/doc/api/H5.php?chapter=9_7&index=7，下面这句非常重要!!!
                $str='<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';  
                echo $str;
                die;
            }
     
            
           


        }
    
   
    
    }
    
    


    
}