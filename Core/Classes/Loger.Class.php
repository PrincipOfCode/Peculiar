<?php
	// namespace Peculiar\Core\Classes;  // not works under php 5.3 :-/
	// use \Peculiar\Core\Interfaces as Interfaces;  // not works under php 5.3 :-/
	
	class Loger 
	{
		private $content = array();
		private $mode = '';
		private $target = '';

		
		public function __construct($target) 
		{
			$this->target = $target; //join(DIRECTORY_SEPARATOR, $segments);
			$this->mode = "a";
		}				
		
		public function __destruct() 
		{	
			$nl = PHP_EOL;
			$content = implode( $nl , $this->content) . $nl;
			
			/*
			$file = false;
			while(!$file)
			{
				$file = fopen($this->target, $this->mode);
				if(!$file)	echo "File Handler: " .$file;
			}
			fwrite($file, $content);
			fclose($file);
			*/
			
			echo "<br/>Log path :" . $this->target . " ";
			$res = file_put_contents($this->target , $content, FILE_APPEND);
			if($res !== 0)
			{
				echo " Log success : " . $res;
			} else echo "log not written :-(";
			//$location
			//$source = explode(PHP_EOL, $location)
			//echo "<br/> __FILE__: " . __FILE__;
		}
		
		public function log($string)
		{
			$this->content[] = "[Time: ". @date('Y-m-d H:i:s') . "] Msg: ".$string;
		}
		
		public function setMode($mode)
		{
			$this->mode = $mode;
		}

	}



?>