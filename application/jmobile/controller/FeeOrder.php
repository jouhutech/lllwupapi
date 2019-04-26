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
        //$params['pay_method_id'] = 1;
        $params['use_real_money'] = $total_use_real_money;
        //$params['fetch_fee_time'] = strtotime(date('Y-m-d'));
        //$params['remark'] = '缴费';
        //$params['tmp_order'] = json_encode($tmp_order);

        $openid = input('openid');
   
        if(empty($openid)){
            print_r($this->returnJson(array(),'参数错误',0)); 
            die;
        }
        $total_fee = $total_use_real_money;
        $order_id = 'HY'.date('YmdHis',time()).'-'.rand(10,99);
        
        $this->pay($total_fee,$openid,$order_id,$params);
        
//        $create_order = $this->callApi('fee_order/createFeeOrder', $params, 'POST');
//        if($create_order['status'] == 1){
//            $this->success('',null,array('fee_order_id'=>$create_order['data']['fee_order_id']));
//        }else{
//            $this->error($create_order['message']);
//        }
    }
    
    
    /* 首先在服务器端调用微信【统一下单】接口，返回prepay_id和sign签名等信息给前端，前端调用微信支付接口 */
    public function pay($total_fee,$openid,$order_id,$posts){

        if(empty($total_fee)){
            echo json_encode(array('state'=>0,'Msg'=>'金额有误'));exit;
        }
        if(empty($openid)){
            echo json_encode(array('state'=>0,'Msg'=>'登录失效，请重新登录(openid参数有误)'));exit;
        }
        if(empty($order_id)){
            echo json_encode(array('state'=>0,'Msg'=>'自定义订单有误'));exit;
        }
        $appid =        'wx0e80f191273e8063';//如果是公众号 就是公众号的appid;小程序就是小程序的appid
        $body =         '订单缴费';
        $mch_id =       '1526381861';
        $KEY = 'c5594d069baca14fd37d7383863bfe30';
        $nonce_str =    md5(uniqid(microtime(true),true));//随机字符串32
        $notify_url =   'http://xiaoshetuan.loulilouwai.net/xiao_notify_url.php';  //支付完成回调地址url,不能带参数
        $out_trade_no = $order_id;//商户订单号
        $spbill_create_ip = $_SERVER['SERVER_ADDR'];
        $trade_type = 'JSAPI';//交易类型 默认JSAPI

        $attach = implode('|', $posts);


        //这里是按照顺序的 因为下面的签名是按照(字典序)顺序 排序错误 肯定出错
        $post['appid'] = $appid;
        $post['body'] = $body;
        $post['mch_id'] = $mch_id;
        $post['attach'] = implode('-|', $posts);
        $post['nonce_str'] = $nonce_str;//随机字符串
        $post['notify_url'] = $notify_url;
        $post['openid'] = $openid;
        $post['out_trade_no'] = $out_trade_no;
        $post['spbill_create_ip'] = $spbill_create_ip;//服务器终端的ip
        $post['total_fee'] = intval($total_fee);        //总金额 最低为一分钱 必须是整数
        $post['trade_type'] = $trade_type;
        $sign = $this->MakeSign($post,$KEY);              //签名
        $this->sign = $sign;
    
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
        echo json_encode($data);
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
//        $p = xml_parser_create();
//        xml_parse_into_struct($p, $xml, $vals, $index);
//        xml_parser_free($p);
//        $data = "";
//        foreach ($index as $key=>$value) {
//            if($key == 'xml' || $key == 'XML') continue;
//            $tag = $vals[$value[0]]['tag'];
//            $value = $vals[$value[0]]['value'];
//            $data[$tag] = $value;
//        }
//        return $data;
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
        $post = post_data();    //接受POST数据XML个数
/*

function post_data(){
$receipt = $_REQUEST;
if($receipt==null){
$receipt = file_get_contents("php://input");
if($receipt == null){
$receipt = $GLOBALS['HTTP_RAW_POST_DATA'];
}
}
return $receipt;
}

*/

        $post_data = $this->xml_to_array($post);   //微信支付成功，返回回调地址url的数据：XML转数组Array
        $postSign = $post_data['sign'];
        unset($post_data['sign']);
        
        /* 微信官方提醒：
         *  商户系统对于支付结果通知的内容一定要做【签名验证】,
         *  并校验返回的【订单金额是否与商户侧的订单金额】一致，
         *  防止数据泄漏导致出现“假通知”，造成资金损失。
         */
        ksort($post_data);// 对数据进行排序
        $str = $this->ToUrlParams($post_data);//对数组数据拼接成key=value字符串
        $user_sign = strtoupper(md5($post_data));   //再次生成签名，与$postSign比较
        
        $where['crsNo'] = $post_data['out_trade_no'];
       
        
        if($post_data['return_code']=='SUCCESS'&&$postSign){
            /*
            * 首先判断，订单是否已经更新为ok，因为微信会总共发送8次回调确认
            * 其次，订单已经为ok的，直接返回SUCCESS
            * 最后，订单没有为ok的，更新状态为ok，返回SUCCESS
            */
            
                    $this->return_success();
            
            
        }else{
            echo '微信支付失败';
        }
    }
    
    /*
     * 给微信发送确认订单金额和签名正确，SUCCESS信息 -xzz0521
     */
    private function return_success(){
        $return['return_code'] = 'SUCCESS';
        $return['return_msg'] = 'OK';
        $xml_post = '<xml>
                    <return_code>'.$return['return_code'].'</return_code>
                    <return_msg>'.$return['return_msg'].'</return_msg>
                    </xml>';
        echo $xml_post;exit;
    }
  
    
    
    
    
    
}