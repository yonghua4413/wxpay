# wxpay
微信支付 扫码支付 jspai支付

```
$ composer require yonghua4413/wxpay
```

```php
//加载
use YYHwxpay\Wxpay;

//准备订单信息
$order_id = data('YmdHis').mt_rand(1000,9999);
$openid = ''; //自行获取用户openid
$ip = ''; //自行获取ip
$pay_param = array(
    'body' => '一分钱测试商品',
    'out_trade_no' => $order_id,
    'spbill_create_ip' => $ip,
    'total_fee' => 1,
    'openid' => $openid, 
    'notify_url' => '' //异步通知地址
);

//将订单提交到数据库（代码略）；

//自行申请
$config => array(
    'app_id'   => '',
    'app_secret'    => '',
    'app_mchid'=>'',
    'key' => ''
);

//调用统一下单
$weixin = new Wxpay($config);
$response = $weixin -> create_order($pay_param);

//发生错误
if($response['error'] == 1){
    var_dump($response['msg']);exit;
}

//扫码自付 获取支付二维码地址,自行调用相关类库生成二维码
$code_url = $response['data']['code_url'];
//扫码支付结束

//jspai支付 需要在config指定类型 'type' => "JSAPI"
$sign = $weixin -> get_jsbridge_param($response['data']['prepay_id']);

//页面端（jsapi支付）
<script type="text/javascript">
$('#pay').on('click', function(){
	function onBridgeReady(){
	    WeixinJSBridge.invoke(
		'getBrandWCPayRequest', {
		    "appId":"<?php echo $sign['appId']?>",            //公众号名称，由商户传入     
		    "timeStamp":"<?php echo $sign['timeStamp']?>",    //时间戳，自1970年以来的秒数     
		    "nonceStr":"<?php echo $sign['nonceStr']?>",      //随机串     
		    "package":"<?php echo $sign['package']?>",     
		    "signType":"<?php echo $sign['signType']?>",      //微信签名方式：     
		    "paySign":"<?php echo $sign['paySign']?>"         //微信签名 
		},
		function(res){
			  //使用以上方式判断前端返回,微信团队郑重提示：res.err_msg将在用户支付成功后返回    ok，但并不保证它绝对可靠。   
		    if(res.err_msg == "get_brand_wcpay_request:ok" ) {
			  //自行支付成功后的跳转
			  }      
		}
	    ); 
	 }
	if (typeof WeixinJSBridge == "undefined"){
	    if( document.addEventListener ){
		document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
	    }else if (document.attachEvent){
		document.attachEvent('WeixinJSBridgeReady', onBridgeReady); 
		document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
	    }
	 }else{
	    onBridgeReady();
	 } 
});
</script>



## 请选择最新的版本

