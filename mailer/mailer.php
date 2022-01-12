<?php
require 'phpmailer/PHPMailerAutoload.php';
function sendEmail($recipients, $subject, $body) {
	$mail = new PHPMailer; 
	
	$mail->isSMTP();                      		// Set mailer to use SMTP 
	$mail->Host = 'gator4137.hostgator.com';   	// Specify main and backup SMTP servers 
	$mail->SMTPAuth = true;               		// Enable SMTP authentication 
	$mail->Username = '';   	// SMTP username 
	$mail->Password = '';   		// SMTP password 
	$mail->SMTPSecure = 'tls';            		// Enable TLS encryption, `ssl` also accepted 
	$mail->Port = 587;                    		// TCP port to connect to 
	 
	// Sender info 
	$mail->setFrom('alarm@yeltech.com', 'Yeltech Alarm'); 
	// Add a recipient 
	$mail->addAddress('alarm@yeltech.com', 'Yeltech Alarm');

	$mail->addReplyTo('support@yeltech.com', 'Yeltech Support');

	foreach ($recipients as $email) {
		$mail->addBCC($email);
	}
	 
	// Set email format to HTML 
	$mail->isHTML(true); 
	 
	// Mail subject 
	$mail->Subject = $subject;
	 
	// Mail body content 
	// $bodyContent = 'Greetings!<br><br>';
	$bodyContent = $body;
	$bodyContent .= '<br><br>Log into <a href="https://yelcloud.com/">YelCloud</a> to monitor your devices!<br>'; 
	$bodyContent .= '<br>Many thanks,<br>Yeltech Team<br><br>'; 
	$bodyContent .= '<sub>If you have any questions, respond to this email or get in touch with us at support@yeltech.com</sub>'; 
	$mail->Body    = $bodyContent; 
	 
	// Send email 
	if(!$mail->send()) { 
		echo 'Message could not be sent. Mailer Error: '.$mail->ErrorInfo; 
	} else { 
		echo 'Message has been sent.'; 
	} 
}

?>