<?php
class Qimen {
	public $secret = '30c25d26b59bf4f1d1d2c5c02464fc38';			// 奇门提供的安全码(签名用)
	
    public $param  = array(		               // 按接口提供的逐一填写
       "format"         => 'xml',
       "app_key"        => '23253927',
       "v"              => '2.0',
       "sign_method"    => 'md5',
       "customerId"     => '014',
    );

    /**
     * 签名
     * @param $secret 	安全码
     * @param $param 	提交参数
     * @param $body     提交文档内容
     */
    private function sign($secret, $param, $body) {
        if ( empty($body) ) {
        	exit('Body can\'t empty!');
        }

        if ( empty($secret) ) {
        	exit('Secret error!');
        }

        ksort($param);
        $outputStr = '';
        foreach ($param as $k => &$v) {
        	if ( empty($v) ) {
        		exit('Param can\'t error!');
        	}
            $outputStr .= $k . $v;
        }

        $outputStr = $secret . $outputStr . $body . $secret;
        return strtoupper(md5($outputStr));
    }

    // 业务逻辑
    public function request($method, $bodyParam) {
        // 交给奇门
        require('Qimenbody.class.php');
        $qimenBody                = new \Qimenbody();
        $body                     = $qimenBody->getBody($method, $bodyParam);           // 创建发货单BODY

        $this->param['method']    = $method;                                            // 此处填写要应对应BODY，具体参考白皮书的 ‘ERP调用的奇门API名称’
        $this->param['timestamp'] = date("Y-m-d H:i:s");                                // 时间
        $this->param['sign']      = $this->sign($this->secret, $this->param , $body);   // 利用body签名

        $url    = "http://qimen.api.taobao.com/router/qimen/service?" . http_build_query($this->param);

        $return = $this->httpCurl($url, $body, 'post');

    	// -----------------------------------------------------------------------------
    	//
    	//
    	//
    	//
    	//
    	//
    	//
    	// 处理返回值信息，返回信息请查看白皮书中的出参规范
        return $return;
    	//
    	//
    	//
    	//
    	//
    	//
    	//
    	// -----------------------------------------------------------------------------
    }

    /**
     * 请求数据
     * @param $url 			请求地址
     * @param $data 		提交数据
     * @param $requestType  请求类型
     */
    public function httpCurl($url, $data, $requestType = 'get') {
        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if (strtolower($requestType) == 'post') {
            curl_setopt ( $ch, CURLOPT_POST, 1 );
            curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
        }
        $return = curl_exec($ch);
        curl_close($ch);
        return $return;
    }
}