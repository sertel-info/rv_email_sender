<?php

require_once __DIR__.'/vendor/autoload.php';

date_default_timezone_set('America/Sao_Paulo');

$mail = new PHPMailer();
$logger = new Katzgrau\KLogger\Logger(__DIR__.'/logs');

$logger->info('NEW REQUEST, METHOD:'.$_SERVER['REQUEST_METHOD']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	try{	
		$request = array("remetente"=>$_POST['remetente'],
						 "receptor"=> $_POST['receptor'],
						 "mensagem"=> $_POST['mensagem'],
						 "assunto"=>  $_POST['assunto'],
						 "gravacao"=> $_FILES['gravacao']);

		

		foreach($request as $campo=>$valor){
			if($valor === false || $valor === ''){
				echo json_encode(['status'=>0, 'err_msg'=>'campo ' .$campo. ' inválido', 'err'=>'ERR_REQUEST']);
				$logger->error('INVALID REQUEST: VALOR DE '.$campo.' INVÁLIDO ');
				die(1);
			}
		}
		
		if($request['gravacao']['error']){
			switch($request['gravacao']['error']){
				case 1:echo json_encode(['status'=>0,
							 'err_msg'=>'O arquivo excedeu o limite de tamanho para upload',
			   				 'err'=>'UPLOAD_ERR_INI_SIZE']);
				break;
				case 2:echo json_encode(['status'=>0,
							 'err_msg'=>'O arquivo excedeu o limite de tamanho para upload',
							 'err'=>'UPLOAD_ERR_FORM_SIZE']);
				break;
				case 3:echo json_encode(['status'=>0,
							 'err_msg'=>'O upload do arquivo foi feito parcialmente',
							 'err'=>'UPLOAD_ERR_PARTIAL']);
				break;
				case 4:echo json_encode(['status'=>0,
							 'err_msg'=>'Nenhum arquivo foi enviado',
							 'err'=>'UPLOAD_ERR_NO_FILE']);
				break;
				case 6:echo json_encode(['status'=>0,
							 'err_msg'=>'Pasta temporária ausênte',
							 'err'=>'UPLOAD_ERR_NO_TMP_DIR']);
				break;
				case 7:echo json_encode(['status'=>0,
							 'err_msg'=>'Uma extensão do PHP interrompeu o upload do arquivo',
							 'err'=>'UPLOAD_ERR_EXTENSION']);
				break;
			}
		}

		/*if(!move_uploaded_file($gravacao['tmp_name'], '/tmp/'.$nome_gravacao){
			echo json_encode(['status'=>0, 'err_msg'=>'Erro ao mover arquivo']);
			die(1);
		}*/
	
		$logger->info('STARTING MAIL OBJECT');
		/**Envia o email **/
		$mail->SMTPDebug = 0; 
		$mail->isSMTP();                                      
		$mail->Host = 'smtpi.sertel-info.com.br';              
		$mail->SMTPAuth = true;                               
		$mail->Username = 'contato@sertel-info.com.br'; 
		$mail->Password = 'con@2016sertel';                     
		$mail->SMTPSecure = '';                         
		$mail->Port = 587;                                    
		$mail->setLanguage('br');

		$mail->SMTPOptions = array(
	       'ssl' => array(
	           'verify_peer' => false,
	           'verify_peer_name' => false,
	           'allow_self_signed' => true
	        )
		);
		
		$mail->setFrom('correio_de_voz@sertelinfo.com.br', 'Sertel-Info');
		$mail->addAddress($request['receptor']);    
		$mail->addAttachment($request['gravacao']['tmp_name'], $request['gravacao']['name']);    						   
		$mail->isHTML(true);

		$mail->Subject = $request['assunto'];
		$mail->Body    = $request['mensagem'];
		$mail->AltBody = 'Enviado por Sertel-Info Tecnologia';
		
		if(!$mail->send()){
			$logger->error('ERROR SENDING THE EMAIL: '.$mail->ErrorInfo);
			echo json_encode(['status'=>0, 'err_msg'=>$mail->ErrorInfo]);
			die(1);
		}


		echo json_encode(['status'=>200, 'err_msg'=>'']);
	} catch (Exception $e){
		$logger->error('EXCEPTION CAUGHT:' . $e->getMessage());
		echo json_encode(['status'=>0, 'err_msg'=>$e->getMessage(), 'err'=>$e->getCode()]);
	}
}


