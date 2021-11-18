<?php
/**
* Simple example script using PHPMailer with exceptions enabled
* @package phpmailer
* @version $Id$
*/

require '../class.phpmailer.php';

try {
	$mail = new PHPMailer(true); //New instance, with exceptions enabled

	$body             = file_get_contents('contents.html');
	$body             = preg_replace('/\\\\/','', $body); //Strip backslashes

	$mail->IsSMTP();                           // tell the class to use SMTP
	$mail->SMTPAuth   = true;                  // enable SMTP authentication
	$mail->Port       = 25;                    // set the SMTP server port
	$mail->Host       = "smtp.qq.com"; // SMTP server  SMTP 服务
	$mail->Username   = "";     // SMTP server username   服务账号  
	$mail->Password   = "";            // SMTP server password

	//$mail->IsSendmail();  // tell the class to use Sendmail

	$mail->AddReplyTo("435281639@qq.com","Mr.Lee");     //"邮件回复人地址","邮件回复人名称

	$mail->From       = "435281639@qq.com";  			//发件人地址
	$mail->FromName   = "Mr.Lee";						//发件人名称

	$to = "779767724@qq.com";							//收件人地址

	$mail->AddAddress($to);

	$mail->Subject  = "First PHPMailer Message";		//邮件主题

	$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
	$mail->WordWrap   = 80; // set word wrap

	$mail->MsgHTML($body);    //发送内容

	$mail->IsHTML(true); // send as HTML

	$mail->Send();
	echo 'Message has been sent.';
} catch (phpmailerException $e) {
	echo $e->errorMessage();
}
?>