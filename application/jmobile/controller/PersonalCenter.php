<?php
namespace app\jmobile\controller;

/**
 * 缴费模块
 */
class PersonalCenter extends Common {
    
    public function __construct(){
        parent::__construct();
    }


    /**
     * @title 我的中心主页面
     * @author ssm
     * @url /api/personal_center/index
     * @method GET
     * @return list:列表@
     * @return is_default:默认选中房产@
     * @list user_room_id:表id village_id:小区id building_id:楼栋id unit_id:单元id room_id:房间id room_type_id:房屋类型id full_room_name:完整房屋名称 is_default:是否为默认 1默认
     * 
     */
    public function index(){
        $member_id=input('member_id');
        $res_room = $this->callApi('village/getUserRoomList', array('member_id'=>$member_id),'GET');
        if($res_room['status']==1){
            print_r($this->returnJson($res_room['data'],'成功',1)); 
        }else{
            print_r($this->returnJson($res_room['data'],'成功',1)); 
        }
       
    }
    
    /**
     * @title 切换为我的默认房产
     * @author ssm
     * @url /api/personal_center/set_default
     * @method PUT

     * @param name:user_room_id type:int require:0 default: other: desc:业主用户关联房间id
     * @return 结果@
     */
    public function set_default(){
       
        $user_room_id   =   input('user_room_id');
        $member_id      =   input('member_id');
        
        $res_room = $this->callApi('village/SetDefaultUserRoom', array('member_id'=>$member_id,'user_room_id'=>$user_room_id),'PUT');
        if($res_room['status']==1){
            print_r($this->returnJson(array(),'设置成功',1)); 
        }else{
            print_r($this->returnJson(array(),'设置失败',2)); 
        }
    }
    
/**
     * @title 获取楼栋列表
     * @author ssm
     * @url /api/personal_center/getBuildingList
     * @method GET
     * @description 可分页可不分页；
     *
     *
     * @param name:page_size type:string require:0 default: other: desc:每页记录数
     * @param name:page type:string require:0 default: other: desc:第几页, 从1开始，不传表示不分页获取所有
     * 
     * @return page_size:每页记录数
     * @return page:第几页
     * @return total:总记录数
     * @return building_list:楼栋列表@
     * @building_list building_id:楼栋id building_code:楼栋编号 building_name:楼栋名称 remark:备注 create_time:创建时间
     */
    public function getBuildingList(){

        $res_room = $this->callApi('estate/getBuildingList', array(),'GET');
        if($res_room['status']==1){
            print_r($this->returnJson($res_room['data'],'成功',1)); 
        }else{
            print_r($this->returnJson($res_room['data'],'失败',2)); 
        }
    }
    
    
        /**
     * @title 获取住户列表
     * @author ssm
     * @url /api/personal_center/getRoomList
     * @method GET
     * @description 可查询单元、楼栋、小区下的房屋，building_id、unit_id中可以只传一个或者都不传，如果都不传就是查小区下的所有房屋；可分页可不分页；
     *
     * @param name:building_id type:string require:0 default: other: desc:楼栋id，用于查楼栋下的所有房屋
     * @param name:unit_id type:string require:0 default: other: desc:单元id，用于查单元下的房屋
     * @param name:room_type_id type:string require:0 default: other: desc:房屋类型id
     * @param name:tel type:string require:0 default: other: desc:手机号
     * @param name:keywords type:string require:0 default: other: desc:关键字（房间号/业主/业主电话/车牌号）
     * @param name:property_fee_status type:string require:0 default: other: desc:物业费缴纳状态1：未到期2：已到期3：即将到期
     * @param name:property_fee_leftdays type:string require:0 default: other: desc:物业费剩余天数（property_fee_status为3时必传，代表几日内到期）
     * @param name:page_size type:string require:0 default: other: desc:每页记录数
     * @param name:page type:string require:0 default: other: desc:第几页, 从1开始，不传表示不分页获取所有
     * 
     * @return page_size:每页记录数
     * @return page:第几页
     * @return total:总记录数
     * @return area_sum:房屋总面积
     * @return owner_total:业主总数
     * @return room_list:房屋列表@
     * @room_list room_id:房屋id room_code:房屋编号 room_name:房屋名称 full_room_code:完整房屋编号 full_room_name:完整房屋名称 room_type_id:房屋类型id room_type_name:房屋类型名称 area:建筑面积 building_id:楼栋id building_code:楼栋编号 building_name:楼栋名称 unit_id:单元id unit_code:单元编号 unit_name:单元名称 room_remark:房屋信息备注 create_time:房屋创建时间 owner_name:业主姓名 idcard:业主身份证 company:业主工作单位 owner_tel:业主手机号 owner_tel2:业主备用手机号 car_num:车牌号1 car_num2:车牌号2 owner_remark:业主信息备注 coming_time:入住日期 property_fee_endtime:物业费到期时间（0表示该房屋没有关联物业费或者没有业主）
     */
    public function getRoomList(){
        $building_id=input('building_id');
        
        $keywords=input('keywords');
        $res_room = $this->callApi('estate/getRoomList', array('building_id'=>$building_id,'keywords'=>$keywords),'GET');
        
        if($res_room['status']==1){
            if(!empty($res_room['data']['room_list'])){
                foreach($res_room['data']['room_list'] as $k=>$v){
                    if(empty($v['owner_name'])){
                        unset($res_room['data']['room_list'][$k]);
                    }
                }
            }
            print_r($this->returnJson($res_room['data'],'成功',1)); 
        }else{
            print_r($this->returnJson($res_room['data'],'失败',2)); 
        }
        
        
    }
    
    
     /**
     * @title 绑定新房产
     * @author ssm
     * @url /api/personal_center/add_user_room
     * @method POST

     * @param name:village_id type:int require:0 default: other: desc:小区id
     * @param name:building_id type:int require:0 default: other: desc:楼栋id
     * @param name:unit_id type:int require:0 default: other: desc:单元id 
     * @param name:room_id type:int require:0 default: other: desc:房间id 
     * @param name:room_type_id type:int require:0 default: other: desc:房屋类型id
     * @param name:full_room_name type:string require:0 default: other: desc:完整房屋名称  
     * @return 结果@
     */
    public function add_user_room(){

        $data['member_id']        =   input('post.member_id');
        
        $data['building_id']    =   input('post.building_id');
        $data['unit_id']        =   input('post.unit_id');
        $data['room_id']        =   input('post.room_id');
        $data['room_type_id']   =   input('post.room_type_id');
        $data['full_room_name'] =   input('post.full_room_name');
        $data['create_time']    =   time();
        
        $res_room = $this->callApi('village/addUserRoom', $data,'POST');
        if($res_room['status']==1){
            print_r($this->returnJson(array(),'成功',1)); 
        }else{
            print_r($this->returnJson(array(),'失败',2)); 
        }
       
    }
    
    
    /**
     * @title 获取缴费订单列表
     * @author ssm
     * @url /api/personal_center/getFeeOrderList
     * @method GET
     *
     * @param name:village_id type:int require:0 default: other: desc:小区id
     * @param name:building_id type:string require:0 default: other: desc:楼栋id
     * @param name:unit_id type:string require:0 default: other: desc:单元id
     * @param name:room_id type:string require:0 default: other: desc:房屋id
     * @param name:room_code type:string require:0 default: other: desc:房屋编号
     * @param name:start_time type:string require:0 default: other: desc:缴费开始时间
     * @param name:end_time type:string require:0 default: other: desc:缴费结束时间
     * @param name:fee_type_id type:string require:0 default: other: desc:收费项目id
     * @param name:pay_method_id type:string require:0 default: other: desc:支付方式id
     * @param name:room_type_id type:string require:0 default: other: desc:房屋类型id
     * @param name:bills_code type:string require:0 default: other: desc:票据编号
     * @param name:create_member_name type:string require:0 default: other: desc:操作人姓名
     * @param name:keywords type:string require:0 default: other: desc:关键字（房间号/业主/业主电话）
     * @param name:status type:string require:0 default: other: desc:订单状态:-1:废除;1:正常,默认是1
     * @param name:del_start_time type:string require:0 default: other: desc:作废开始时间
     * @param name:del_end_time type:string require:0 default: other: desc:作废结束时间
     * @param name:del_member_name type:string require:0 default: other: desc:作废人姓名
     * @param name:has_refund type:string require:0 default: other: desc:是否含退款:1含退款0全部，默认是0
     * @param name:page_size type:string require:0 default: other: desc:每页记录数，最多500
     * @param name:page type:string require:0 default: other: desc:第几页, 从1开始
     *
     * @return page_size:每页记录数
     * @return page:第几页
     * @return total:总记录数
     * @return total_fee:应缴总额
     * @return total_use_real_money:实缴总额
     * @return total_refund_money:退款总额
     * @return fee_type_totals:各收费项目总额信息@
     * @fee_type_totals fee_type_id:收费项目id total_fee:应交金额 use_real_money:实际花费金额 fee_type_name:收费项目名称 refund_money:退款金额
     * @return fee_order_list:订单列表@
     * @fee_order_list fee_order_id:订单id order_number:订单号 bills_code:票据编号 status:订单状态:-1:删除;1:正常 total_fee:应交费用 use_real_money:实交金额 fetch_fee_time:收款日期 owner_name:业主名字 owner_tel:业主电话 building_id:楼栋id building_code:楼栋编号 building_name:楼栋名称 unit_id:单元id unit_code:单元编号 unit_name:单元名称 room_id:房间id room_code:房屋编号 room_name:房屋名称 room_area:房屋面积 pay_method_id:支付方式id pay_method_name:支付方式名称 remark:备注 create_time:创建时间 create_member_name:后台订单创建人姓名 import_id:导入记录表id del_time:删除时间 del_member_name:删除人姓名 del_reason:删除原因 full_room_code:完整房屋编号 full_room_name:完整房屋名称 refund_money:退款金额 details:订单详情@
     * @details fee_order_detail_id:订单详情id fee_id:收费标准id fee_name:收费标准名称 cate:收费类型1：走表费用2：周期费用 3：一次性费用 fee_type_id:收费项目id fee_type_name:收费项目名称 calc_type:计算方式1:按面积2：按金额 price:单价/金额 decimal:保留小数位数 distance_type:计算周期2:按月3:按年 other_type:特殊类别标识0:不是特殊类别1：物业费 total_fee:应交费用 use_real_money:实交金额 start_time:开始时间 end_time:结束时间 is_new_add:是否是新增0：否1：是 meter_record_id:走表费用记录id is_diff:补交差额1是0否 remark:备注 refund_money:退款金额
     */
    public function getFeeOrderList(){
        $start_time =input('start_time');
        $end_time   =input('end_time');
        $member_id   =input('member_id');
        $page_size   =input('page_size');
        $page   =input('page');
        $data=array();
        if(!empty($start_time)){
            $data['start_time']=strpos($start_time,'-')===false ? $start_time : strtotime($start_time);
        }
        if(!empty($end_time)){
            $data['end_time']=strpos($end_time,'-')===false ? $end_time : strtotime($end_time);
        }
        $data['member_id']	=$member_id;
        $data['page']		=$page;
        $data['page_size']	=$page_size;
        //dump($data);die;
        $res_room = $this->callApi('fee_order_info/getFeeOrderList',$data,'GET');
        if($res_room['status']==1){
            print_r($this->returnJson($res_room['data'],'成功',1)); 
        }else{
            print_r($this->returnJson(array(),'失败',2)); 
        }
    }
    
    
    /**
     * @title 获取缴费订单详情
     * @author lhy
     * @url /api/personal_center/getFeeOrderInfo
     * @method GET
     * @description 提供按订单id和票据编号两种查询 以订单id为主
     * @param name:village_id type:string require:0 default: other: desc:小区id
     * @param name:fee_order_id type:string require:0 default: other: desc:订单id
     * @param name:bills_code type:string require:0 default: other: desc:票据编号
     *
     * @return fee_order_id:订单id 
     * @return order_number:订单号 
     * @return bills_code:票据编号 
     * @return status:订单状态:-1:删除;1:正常 
     * @return total_fee:应交费用 
     * @return use_real_money:实交金额 
     * @return fetch_fee_time:收款日期 
     * @return owner_name:业主名字 
     * @return owner_tel:业主电话 
     * @return building_id:楼栋id 
     * @return building_code:楼栋编号 
     * @return building_name:楼栋名称 
     * @return unit_id:单元id 
     * @return unit_code:单元编号 
     * @return unit_name:单元名称 
     * @return room_id:房间id 
     * @return room_code:房屋编号 
     * @return room_name:房屋名称 
     * @return room_area:房屋面积 
     * @return pay_method_id:支付方式id 
     * @return pay_method_name:支付方式名称 
     * @return remark:备注 
     * @return create_time:创建时间 
     * @return create_member_name:后台订单创建人姓名 
     * @return import_id:导入记录表id 
     * @return del_time:删除时间 
     * @return del_member_name:删除人姓名 
     * @return del_reason:删除原因 
     * @return full_room_code:完整房屋编号 
     * @return full_room_name:完整房屋名称 
     * @return unit:走表费用计量单位 
     * @return refund_money:退款金额 
     * @return details:订单详情@
     * @details fee_order_detail_id:订单详情id fee_id:收费标准id fee_name:收费标准名称 cate:收费类型1：走表费用2：周期费用 3：一次性费用 fee_type_id:收费项目id fee_type_name:收费项目名称 calc_type:计算方式1:按面积2：按金额 price:单价/金额 decimal:保留小数位数 distance_type:计算周期2:按月3:按年 other_type:特殊类别标识0:不是特殊类别1：物业费 total_fee:应交费用 use_real_money:实交金额 start_time:开始时间 end_time:结束时间 is_new_add:是否是新增0：否1：是 meter_record_id:走表费用记录id is_diff:补交差额1是0否 remark:备注 refund_money:退款金额 refund_list:退款列表@
     * @refund_list fee_order_refund_id:退款id refund_money:退款金额 refund_time:退款日期 refund_bills_code:退款票据号 refund_method_id:退款方式id refund_method_name:退款方式名称 remark:备注 create_member_name:操作人 create_time:操作时间
     */
    public function getFeeOrderInfo(){
        $member_id   =input('member_id');
        $data=array();
        $data['fee_order_id']=input('fee_order_id');
        $data['member_id']=$member_id;
        $res_room = $this->callApi('fee_order_info/getFeeOrderInfo',$data,'GET');
        if($res_room['status']==1){
            print_r($this->returnJson($res_room['data'],'成功',1)); 
        }else{
            print_r($this->returnJson(array(),'失败',2)); 
        }
    }
    
    

    
}
