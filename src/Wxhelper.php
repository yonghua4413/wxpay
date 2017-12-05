<?php
namespace YYHwxpay;
/**
 * 微信接口调用帮助类
 * @author 254274509@qq.com
 */
class Wxhelper {
    
    /**
     * 获取签名
     */
    public static function get_sign($parameters, $api_key){
        //签名步骤一：按字典序排序参数
        ksort($parameters);
        $String = self::formatBizQueryParaMap($parameters, false);
        //签名步骤二：在string后加入KEY
        $String = $String."&key=".$api_key;
        //签名步骤三：MD5加密
        $result = strtoupper(md5($String));
        return $result;
    }
    
    /**
     * 获取随机字符串
     */
    public static function get_rand_str($length = 10){
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol)-1;
        for($i=0;$i<$length;$i++){
            $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }
        return $str;
    }
    
    /**
     * 数组转xml
     * @param unknown $arr
     */
    public static function array_to_xml($arr){
        
        $xml = "<xml>";
    	foreach ($arr as $key=>$val)
    	{
    		if (is_numeric($val)){
    			$xml.="<".$key.">".$val."</".$key.">";
    		}else{
    			$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
    		}
        }
        $xml.="</xml>";
        return $xml; 
    }
    
    /**
     * xml转成数组
     * @param unknown $xml
     */
    public static function xml_to_array($xml){
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array_data;
    }
    
    /**
     * 将数组转成uri字符串
     * @param array $paraMap
     * @param bool $urlencode
     * @return string
     */
    private static function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v)
        {
            if($urlencode)
            {
                $v = urlencode($v);
            }
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar;
        if (strlen($buff) > 0)
        {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }
    
    /**
     * 解析错误
     */
    public static function parse_error($error_code){
        
        $error_arr = array(
            'NOAUTH' => '商户无此接口权限',
            'NOTENOUGH' => '账户余额不足',
            'ORDERPAID' => '订单已支付',
            'ORDERCLOSED' => '订单已关闭',
            'SYSTEMERROR' => '系统超时',
            'APPID_NOT_EXIST' => '参数中缺少APPID',
            'MCHID_NOT_EXIST' => 'MCHID不存在',
            'APPID_MCHID_NOT_MATCH' => 'appid和mch_id不匹配',
            'LACK_PARAMS' => '缺少必要的请求参数',
            'OUT_TRADE_NO_USED' => '商户订单号重复',
            'SIGNERROR' => '签名错误',
            'XML_FORMAT_ERROR' => 'XML格式错误',
            'REQUIRE_POST_METHOD' => '未使用post传递参数 ',
            'POST_DATA_EMPTY' => 'post数据为空',
            'NOT_UTF8' => '编码格式错误',
        );
        return isset($error_arr[$error_code]) ? $error_arr[$error_code] : '未知错误';
    }
    
    /**
     * 以post方式提交xml到对应的接口url
     *
     * @param string $xml  需要post的xml数据
     * @param string $url  url
     * @param bool $useCert 是否需要证书，默认不需要
     * @param int $second   url执行超时时间，默认30s
     * @throws WxPayException
     */
    public static function postXmlCurl($xml, $url, $second=30, $useCert=false, $sslcert_path='', $sslkey_path='')
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch,CURLOPT_URL, $url);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
    
        if($useCert == true){
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
            curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLCERT, $sslcert_path);
            curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLKEY, $sslkey_path);
        }
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        	
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
    
            curl_close($ch);
            return false;
        }
    }
}
