<?php

namespace app\jmobile\controller;
use think\Controller;
use think\Config;

class Village extends Common {

    
    public function index()
    {


        $user_id = input('user_id');
        if(empty($user_id)){
            print_r($this->returnJson(array(),'参数错误',0)); 
            die;
        }
        $user_village = $this->callApi('user_village/index', ['user_id'=>$user_id], 'GET'); 
    
        $info = array();
        if($user_village['status'] == 1){
            
            $room_id = $user_village['data']['user_village']['room_id'];

            $res_room = $this->callApi('estate/getRoomInfo', array('room_id'=>$room_id), 'GET');
     
            if($res_room['status'] != 1){

                print_r($this->returnJson(array(),$res['message'],0)); 
                die;
            }else{
                if($res_room['data']['property_fee_endtime']==0){
                    $user_village['data']['user_village']['property_fee_endtime'] = date('Y-m-d',$res_room['data']['coming_time']);
                }else{
                    $user_village['data']['user_village']['property_fee_endtime'] = date('Y-m-d',$res_room['data']['property_fee_endtime']);
                }
            }

            print_r($this->returnJson($user_village['data']['user_village'],'成功',1)); 
            die;
        }else{
            print_r($this->returnJson(array(),$user_village['message'],0)); 

        }

    }
    
    

}
