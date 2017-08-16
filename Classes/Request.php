<?php

class Request{

	private $data = array();
	private $files = array();

	public function fill($post, $files){
		foreach($post as $attribute=>$value){
			$this->data[$attribute] = $value;
		}

		if(is_array($files)){
			foreach($files as $attribute=>$value){
				$this->files[$attribute] = $value;
			}		
		}

	}

	public function __get($att){
		if(!isset($this->data[$att]))
			return null;

		return $this->data[$att];
	}

	public function file($file_name){
		if(!isset($this->files[$file_name]))
			return null;

		return $this->files[$att];
	}

	public function has($att){
		return isset($this->data[$att]);
	}

	public function getFiles(){
		return $this->files;
	}
}