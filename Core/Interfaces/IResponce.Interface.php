<?php
	namespace Peculiar\Core\Interfaces;  // not works under php 5.3 :-/
	
	interface IResponce
	{
	   
	   public function setStatus
	   
	   public function setHeader($name, $value);
	   public function unSetHeader($name);
	   
	   public function getContent(); // /??
	   
	   
	}
?>