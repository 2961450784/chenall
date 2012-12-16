<?php
/*
	ʹ��PHP������������׵�HTTP����
	��֧����Ҫ֤�����վ��ʹ��"multipart/form-data"���ܵ�POST����(һ���ϴ��ļ�����ʹ���������).
	��;: ......
	��д: chenall
	ʱ��: 2012-11-27
	�汾: 1.1
	��ַ: http://chenall.net/post/php_http_proxy/
	�޶�:
		 
		 2012-11-28 v1.1
		   1.���write_function,�ֶ����(����������Ƶ).
		   2.Ԥ�õ���,���Կ�����¼ģʽ,��¼���з��ʺͷ��ص�����.
*/
function header_function($ch, $header){
	global $debug;
	if (stripos($header,'chunked') !== false)//��������ֶϺ�������,���Թ���һ��.�����кõķ�������֮.
		return strlen($header);
	header($header);
	empty($debug) || fwrite($debug,$header);
	return strlen($header);
}

function write_function($ch, $body){
	global $debug;
	empty($debug) || fwrite($debug,$body);
	echo $body; 
	return strlen($body);
}

function proxy()
{
	global $debug;
	$hearer = array();
	//��ȡHTTP��ص�HEADER��Ϣ
	if (function_exists('getallheaders'))
	{
		$allheader = getallheaders();
		foreach($allheader as $h=>$key)
		{
			$header[] = $h.': '.$key;
		}
	}
	else
	{
		foreach($_SERVER as $key=>$value)
		{
			if (strcasecmp(substr($key,0,4),'HTTP') == 0)
			{
				$header[] = substr($key,5).': '.$value;
			}
		}
		if (isset($_SERVER['PHP_AUTH_DIGEST'])) { 
			$header[] = 'AUTHORIZATION: '.$_SERVER['PHP_AUTH_DIGEST']; 
		} else if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
			$header[] = 'AUTHORIZATION: '.base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $_SERVER['PHP_AUTH_PW']); 
		}
		if (isset($_SERVER['CONTENT_LENGTH'])) { 
			$header[] = 'CONTENT-LENGTH: '.$_SERVER['CONTENT_LENGTH']; 
		}
		if (isset($_SERVER['CONTENT_TYPE'])) { 
			$header[] = 'CONTENT_TYPE: '.$_SERVER['CONTENT_TYPE']; 
		}
	}
	$test = explode(':',substr($_SERVER['REQUEST_URI'],0,10));
	if (count($test) > 1)
		$url = $_SERVER['REQUEST_URI'];
	else
		$url = 'http://'.$_SERVER['HTTP_HOST'].':'.$_SERVER['SERVER_PORT'].$_SERVER['REQUEST_URI'];
	$curl_opts = array(
		CURLOPT_URL => $url,
		CURLOPT_CONNECTTIMEOUT => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_AUTOREFERER => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_BINARYTRANSFER => true,
		CURLOPT_HEADERFUNCTION => 'header_function',
		CURLOPT_WRITEFUNCTION => 'write_function',
		CURLOPT_CUSTOMREQUEST =>$_SERVER['REQUEST_METHOD'],
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_HTTPHEADER => $header,
		CURLOPT_SSL_VERIFYHOST => false
	);

	if ($_SERVER['REQUEST_METHOD']=='POST')//�����POST�Ͷ�ȡPOST��Ϣ,��֧��
	{
		$curl_opts[CURLOPT_POST] = true; 
		$curl_opts[CURLOPT_POSTFIELDS] = file_get_contents('php://input'); 
	}
	$curl = curl_init();
	curl_setopt_array ($curl, $curl_opts);
	empty($debug) || fwrite($debug,"\r\n".date('Y-m-d H:i:s',time())." URL: ".$curl_opts[CURLOPT_URL]."\r\n".$curl_opts[CURLOPT_POSTFIELDS]."\r\n".implode("\r\n",$header)."\r\n\r\n");
	$ret = curl_exec ($curl);
	curl_close($curl);
	unset($curl);
}
$debug = 1;//��Ϊ1������¼.
if ($debug)
	$debug = fopen("debug/".$_SERVER['REMOTE_ADDR'].date('_ymdHis_',time()).'__'.$_SERVER['SERVER_NAME'].".log",'a');
proxy();
empty($debug) || fclose($debug);
