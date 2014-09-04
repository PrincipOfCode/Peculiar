<?php
	namespace Peculiar\Core\Interfaces;  // not works under php 5.3 :-/
	
	interface IRouter
	{
		public function setRefPath( $refPath );
		public function addRoute( array $route );
		public function generateUrl( $name, array $params = array() ) ;
		public function matchRoute( IRequest $request );
	}

?>