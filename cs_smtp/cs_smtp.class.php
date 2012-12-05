<?php
/*
	���׵�SMTP�����ʼ��࣬����Ƚ��٣�������ѧϰSMTPЭ�飬
	���Դ�������֧����Ҫ��֤��SMTP��������Ŀǰ��SMTP��������Ҫ��֤��
	��д: chenall
	ʱ��: 2012-12-04
	��ַ: http://chenall.net/post/cs_smtp/
	�޶���¼:
		2012-12-04
		   ��һ���汾

	ʹ�÷���:
		
		1. ��ʼ�������ӵ���������Ĭ����QQ���䣩
		   $mail = new cs_smtp('smtp.qq.com',25)
		   if ($mail->errstr) //������ӳ���
			   die($mail->errstr;
		2. ��¼����������֤,���ʧ�ܷ���FALSE;
		   if (!$mail->login('USERNAME','PASSWORD'))
				die($mail->errstr;
		3. ��Ӹ��������ָ��name�Զ���ָ�����ļ���ȡ�ļ���
		   $mail->AddFile($file,$name) //�������ϵ��ļ�������ָ���ļ���;
		4. �����ʼ�
			$mail->send($to,$subject,$body)
			$to �ռ��ˣ����ʹ��','�ָ�
			$subject �ʼ����⣬��ѡ��
			$body  �ʼ��������ݣ���ѡ
*/
class cs_smtp
{
	private $CRLF = "\r\n";
	private $from;
	private $smtp = null;
	private $attach = array();
	public $debug = false;
	public $errstr = '';

	function __construct($host='smtp.qq.com',$port = 25) {
		if (empty($host))
			die('SMTP������δָ��!');
		$this->smtp = fsockopen($host,$port,$errno,$errstr,5);
		if (empty($this->smtp))
		{
			$this->errstr = '����'.$errno.':'.$errstr;
			return;
		}
		$this->smtp_log(fread($this->smtp, 515));
		if (intval($this->smtp_cmd('EHLO LOCALHOST')) != 250 && intval($this->smtp_cmd('HELO LOCALHOST')))
			$this->errstr = '��������֧�֣�';
	}

	function __destruct()
	{
		if ($this->smtp)
			$this->smtp_cmd('QUIT');//�����˳�����
	}

	private function smtp_log($msg)//��ʱ�������ʹ��
	{
		if ($this->debug == false)
			return;
		echo $msg."\r\n";
		ob_flush();
		flush();
	}

	function smtp_cmd($msg)//SMTP����ͺ�����
	{
		fputs($this->smtp,$msg.$this->CRLF);
		$this->smtp_log('SEND:'. substr($msg,0,80));
		$res = fread($this->smtp, 515);
		$this->smtp_log($res);
		return $res;
	}

	function AddFile($file,$name = '')//����ļ�����
	{
		if (file_exists($file))
		{
			if (!empty($name))
				return $this->attach[$name] = $file;
			$fn = pathinfo($file);
			return $this->attach[$fn['basename']] = $file;
		}
		return false;
	}

	private function attachment($file,$name)
	{
		$msg = "Content-Type: application/octet-stream; name=".$name."\n";
		$msg .= "Content-Disposition: attachment; filename=".$name."\n";
		$msg .= "Content-transfer-encoding: base64\n\n";
		$msg .= chunk_split(base64_encode(file_get_contents($file)));//ʹ��BASE64���룬����chunk_split��ж�˿飨ÿ��76���ַ���
		return $msg;
	}

	function send($to,$subject='',$body = '')
	{
		$this->smtp_cmd("MAIL FROM: ".$this->from);
		$mailto = explode(',',$to);
		foreach($mailto as $email_to)
			$this->smtp_cmd("RCPT TO: $email_to");
		$boundary = '--BY_CHENALL_'.uniqid("");
		$headers = "MIME-Version: 1.0".$this->CRLF;
		$headers .= "From: <".$this->from.">".$this->CRLF;
		$headers .= "Content-type: multipart/mixed; boundary= $boundary".$this->CRLF;
		$message = "--$boundary\nContent-Type: text/html;charset=\"ISO-8859-1\"\nContent-Transfer-Encoding: base64\n\n";
		$message .= chunk_split(base64_encode($body));
		$files = '';
		foreach($this->attach as $name=>$file)
		{
			$files .= $name;
			$message .= "--$boundary\n--$boundary\n".$this->attachment($file,$name);
		}
		empty($subject) && $subject = $files;
		$message .= "--$boundary--\n";
		$this->smtp_cmd("DATA");
		$this->smtp_cmd("To:$to\nFrom: ".$this->from."\nSubject: $subject\n$headers\n\n$message\r\n.");
	}

	function login($su,$sp)
	{
		if (empty($this->smtp))
			return false;
		$res = $this->smtp_cmd("AUTH LOGIN");
		if (intval($res)>400)
			return !$this->errstr = $res;
		$res = $this->smtp_cmd(base64_encode($su));
		if (intval($res)>400)
			return !$this->errstr = $res;
		$res = $this->smtp_cmd(base64_encode($sp));
		if (intval($res)>400)
			return !$this->errstr = $res;
		$this->from = $su;
		return true;
	}
}
?>