<?php
namespace YYHwxpay;
use YYHwxpay\Wxhelper;
/**
 * 微信支付
 * @author 254274509@qq.com
 */
class Wxpay {
    
    //appid
    private $app_id;
    //appsecret
    private $app_secret;
    //商户id
    private $mch_id;
    //key
    private $key;
    
    //交易类型
    private $trade_type;
    
    //日志对象
    private $log;
    
    public function __construct($config){
                
        $this->trade_type = 'NATIVE';
        if(isset($config['type'])){
            $this->trade_type = $config['type'];
        }
        
        $this->app_id = $config['app_id'];
        $this->app_secret = $config['app_secret'];
        $this->mch_id = $config['app_mchid'];
        $this->key = $config['key'];
        
    }
    
    /**
     * 统一下单
     * @param $param array 统一下单其他参数信息
     * body 商品描述  必填
     * detail 商品详情 不必填
     * out_trade_no 商户订单号 必填
     * total_fee 总金额 必填
     * notify_url 订单支付结果通知地址 必填
     * open_id 用户open_id 必填
     * spbill_create_ip 客户端ip 必填
     */
    public function create_order($param){
        
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        
        $data['appid'] = $this->app_id;
        $data['mch_id'] = $this->mch_id;
        $data['trade_type'] = $this->trade_type;
        $data['nonce_str'] = Weixinhelper::get_rand_str(32);
        //合并数组
        $data = array_merge($data, $param);
        
        $data['sign'] = Weixinhelper::get_sign($data, $this->key);
        //转换为xml
        $data_xml = Weixinhelper::array_to_xml($data);
        
        $response = Weixinhelper::postXmlCurl($data_xml, $url);
        
        //返回数据转换为数组
        $response_data = Weixinhelper::xml_to_array($response);
        
        if(strtoupper($response_data['return_code']) == 'FAIL'){
            return array(
                'error' => 1,
                'msg' => $response_data['return_msg']
            );
        }
        
        if(strtoupper($response_data['result_code']) == 'FAIL'){
            //日志
            $this->log->error($response_data['err_code_des']);
            return array(
                'error' => 1,
                'msg' => $response_data['err_code_des']
            );
        }
        
        return array(
            'error' => 0,
            'data' => $response_data
        );
    }
    
    
    /**
	 * 异步通知信息验证
	 * @return boolean|mixed
	 */
	public function verify_notify()
	{
		$xml = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : '';
		if(!$xml){
			return array('error' => 1, 'msg' => 'post数据为空');
		}
		$wx_back = Weixinhelper::xml_to_array($xml);
		if(empty($wx_back)){
			return array('error' => 1, 'msg' => 'xml数据解析错误');
		}
		if($wx_back['return_code'] == 'FAIL'){
		    return array('error' => 2, 'data' => $wx_back);
		}
		
		if($wx_back['result_code'] == 'FAIL'){
		    $this->log->error($wx_back['err_code_des']);
		    return array('error' => 3, 'data' => $wx_back);
		}
		$wx_back_sign = $wx_back['sign'];
		unset($wx_back['sign']);
		$checkSign = Weixinhelper::get_sign($wx_back, $this->key);
        if($checkSign != $wx_back_sign){
            return array('error' => 1, 'msg' => '签名失败');
        }
		return array('error' => 0, 'data' => $wx_back);
	}
    
    /**
     * weixinjsbridge调用参数
     */
    public function get_jsbridge_param($prepay_id){
        $data = array(
            'appId' => $this->app_id,
            'timeStamp' => ''.time(),
            'nonceStr' => Weixinhelper::get_rand_str(32),
            'package' => 'prepay_id='.$prepay_id,
            'signType' => 'MD5'
        );
        $data['paySign'] = Weixinhelper::get_sign($data, $this->key);
        return json_encode($data);
    }
}
