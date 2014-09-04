<?php
	// namespace Peculiar\Core\Classes;  // not works under php 5.3 :-/
	// use \Peculiar\Core\Interfaces as Interfaces; // not works under php 5.3 :-/
	
	class HttpRequest implements IRequest // if we use php 5.3 or above we can use namespaces so we use this: Interfaces\IRequest
	{
		private $data = array();
		private $header = array();
		
		public function __construct() 
		{
			
			// Fetch $_REQUEST to data array:
			$this->data += $_POST;
			$this->data += $_GET;			
			$this->data += $_COOKIE;
			$this->data['METHOD'] = $_SERVER['REQUEST_METHOD'];
			$this->data['URI'] = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
			
			// Fetch Headers from $_SERVER to header array:
			foreach ($_SERVER as $name => $value)
			{
				
				if (strcmp(substr($name, 0, 5) , 'HTTP_') == 0)
				{
					$this->header[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
				}
			}			
			
			// Clear Globals $_REQUEST, $_POST, $_GET, $_COOKIE arrays:
			$_REQUEST = '';
			$_POST = '';
			$_GET = '';
			$_COOKIE = '';
			
			// Strip slashes from data array:
			$this->data = array_map('stripslashes', $this->data);
		}

		private function unSlash()
		{
			
		}
		
		public function __isset($name) 
		{
			return isset($this->data[$name]);
		}

		public function __get($name) 
		{
			if( isset($this->data[$name]) ) 
			{
				return $this->data[$name];
			}
			
			return null;			
		}

		public function getData() 
		{
			if( count($this->data) > 0 ) 
			{
				return $this->data;
			}
		   
			return null;
		}

		public function listDataKeys() 
		{
			return array_keys($this->data);
		}

		public function getHeader($headerName = '') 
		{
			if ($headerName === '') return  $this->header;
			$headerName = 'HTTP_' . strtoupper( str_replace('-', '_', $headerName) );

			if ( isset($this->header[$name]) ) 
			{
				return $this->header[$name];
			}
			return null;
		}
		
	}
?>