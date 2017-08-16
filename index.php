<?php

require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/Classes/Request.php';
require_once __DIR__.'/Classes/RequestValidator.php';

date_default_timezone_set('America/Sao_Paulo');

$mail = new PHPMailer();
$logger = new Katzgrau\KLogger\Logger(__DIR__.'/logs');

$logger->info('NOVA REQUEST');

if(!$_SERVER['REQUEST_METHOD'] === 'POST'){
	$logger->error('MÉTODO NÃO AUTORIZADO: '.$_SERVER['REQUEST_METHOD']);
	$logger->error('FINALIZANDO APLICAÇÃO COM ERRO 401: '.$_SERVER['REQUEST_METHOD']);

	header('HTTP/1.0 401 Unauthorized');
	return;
}

try{
	$logger->info('CRIANDO OBJETO REQUEST');
	$request = new Request;
	$request->fill($_POST, $_FILES);	
	$validator = new RequestValidator();
	$validator->validate($request);

	if($validator->hasErrors()){
		$logger->error('ERROS NA REQUEST :');
		foreach($validator->getErrors() as $error){
			$logger->debug($error);
		}
		echo json_encode((Object)['status'=>0, 'err_msgs'=>array($validator->getErrors())]);
		return;
	}

	$logger->info('CRIANDO OBJETO MAIL');
	/**Envia o email **/
	$mail->SMTPDebug = 0; 
	$mail->isSMTP();
	$mail->Host = 'smtp.sertel-info.com.br';
	$mail->SMTPAuth = true;
	$mail->Username = 'contato@sertel-info.com.br';
	$mail->Password = 'con@2016sertel';
	$mail->SMTPSecure = '';
	$mail->Port = 587;
	$mail->setLanguage('br');
	$mail->CharSet = 'UTF-8';

	$mail->SMTPOptions = array(
	      'ssl' => array(
	          'verify_peer' => false,
	          'verify_peer_name' => false,
	          'allow_self_signed' => true
	       )
	);
		
	$mail->setFrom($request->remetente, 'Sertel-Info');
	$mail->addAddress($request->receptor);    
	
	$mail->addAttachment($request->file('gravacao')['tmp_name'], 
						 $request->file('gravacao')['name']);

	$mail->isHTML(true);

	$mail->Subject = $request->assunto;
	$mail->Body    = $request->mensagem;
	$mail->AltBody = 'Enviado por Sertel-Info Tecnologia';
	
	$logger->info('ENVIANDO EMAIL');

	if(!$mail->send()){
		$logger->error('ERRO AO ENVIAR O EMAIL: '.$mail->ErrorInfo);
		echo json_encode((Object)['status'=>0, 'err_msg'=>array($mail->ErrorInfo)]);
		return;
	}

	$logger->info('EMAIL ENVIADO COM SUCESSO');

	echo json_encode((Object)['status'=>1]);

} catch (Exception $e){
	$logger->error('EXCEÇÃO NÃO TRATADA :' . $e->getMessage());
	echo json_encode((Object)['status'=>0,'err_msg'=>array($e->getMessage())]);
}



