<?php
	namespace Peculiar\Core\Interfaces;  // not works under php 5.3 :-/
	
	interface IRequest
	{
	   public function __isset($name);
	   public function __get($name);
	   public function listDataKeys();
	   public function getHeader($name);		
	}
?>