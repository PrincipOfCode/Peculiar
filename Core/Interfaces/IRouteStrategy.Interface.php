<?php
	namespace Peculiar\Core\Interfaces;  // not works under php 5.3 :-/
	
	interface IRouteStrategy
	{
		public function generateAlgorithm( $routeName, array $routes, array $params);
		public function matchAlgorithm( IRequest $request, array $routes);
		
	}
?>