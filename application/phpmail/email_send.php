<?php
require("class.phpmailer.php");

$mail =new PHPMailer();

$mail->IsHTML(true);
$mail->CharSet ="utf-8";
$mail->Encoding ="base64";

$mail->IsSMTP();    //启用smtp
$mail->Host = "smtp.qq.com"; //SMTP服务器
$mail->SMTPAuth  =true;  //开启smtp认证
$mail->Username = "785690724";     // SMTP用户名
$mail->Password="la,.123"; //SMTP密码
$mail->Port = 25;                    // set the SMTP server port

$mail->From="fashion0015@qq.com";  //发件人地址
$mail->FromName= "Mailer";    //发件人

$name="fashion0015@sina.com";
$mail->AddAddress($name,"JOSH ADAMS"); //收件人
$mail->WordWrap =50;  //设置每行字符的长度

$mail->IsHTML(true);  //是否HTML格式邮件
$mail->Subject  ="HERE IS THE SUBJECT"; //邮件主题
$mail->Body     ="邮件的内容";  //邮件的内容
$mail->AltBody  ="This is the body in plain text for non-HTML mail clients"; //邮件正文不支持HTML的被容显示
if(!$mail->send())
{
	echo "Message could not be sent. <p>";
	echo "Mailer Error: ".$mail->ErrorInfo;
	exit;
}
echo "Message has been sent";

?>