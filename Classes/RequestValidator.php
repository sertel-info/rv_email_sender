<?php

class RequestValidator{
	private $required_fields =  array("remetente",
										"receptor",
										"mensagem",
										"assunto");

	private $errors = array();

	public function validate(Request $request){
		return $this->validateData($request) && $this->validateFiles($request);
	}

	public function validateData($request){
		$hasErrors = false;
		foreach($this->required_fields as $field){
			if(!$request->has($field)){
				$hasErrors = true;
				array_push($this->errors, "Campo ".$field." não encontrado na request");
			}
		}
		
		return $hasErrors;

	}

	public function validateFiles($request){
		$hasErrors = false;
		
		foreach($request->getFiles() as $file){
			if($file['error']){
				switch($file['error']){
					case 1:
						$error = 'O arquivo excedeu o limite de tamanho para upload';
					break;
					case 2:
						$error = 'O arquivo excedeu o limite de tamanho para upload';
					break;
					case 3:
						$error = 'O upload do arquivo foi feito parcialmente';
					break;
					case 4:
						$error = 'Nenhum arquivo foi enviado';
					break;
					case 6:
						$error = 'Pasta temporária ausente';		 
					break;
					case 7:
						$error = 'Uma extensão do PHP interrompeu o upload do arquivo';
					break;
				}
				array_push($this->errors, $error);
				$hasErrors = true;
			}
		}

		return $hasErrors;
	}

	public function hasErrors(){
		return (count($this->errors)) > 0;
	}

	public function errorsToConsoleString(){
		$string = "";

		foreach($this->errors as $error){
			$string .= $error."\\n";
		}

		return $string;
	}

	public function errorsToJsonResponse(){
		$response = array();

		foreach($this->errors as $error){
			array_push($response, (Object)['err_msg'=>$error]);
		}

		return json_encode($response);
	}

	public function getErrors(){
		return $this->errors;
	}
}