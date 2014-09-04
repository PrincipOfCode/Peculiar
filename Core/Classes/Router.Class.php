<?php
	// namespace Peculiar\Core\Classes;  // not works under php 5.3 :-/
	// use \Peculiar\Core\Interfaces as Interfaces;  // not works under php 5.3 :-/
	
	class Router implements IRouter    // if we use php 5.3 or above we can use namespaces so we use this:  Interfaces\IRouter
	{
		private $refPath = "";
		private $routes = array();
		private $routeStrategy = null;
		
		// not finished
		public function __construct() 
		{
		
		}		
	
		public function getRoutesList()
		{
			return $this->routes;
		}
		
		
		public function setRouteStrategy( IRouteStrategy $routeStrategy = null )  // if we use php 5.3 or above we can use namespaces so we use this: Interfaces\IRouteStrategy
		{
			$this->routeStrategy 	= ($routeStrategy === null) ? (new DeafaultRouteStrategy()) : $routeStrategy ;
		}
		
		public function setRefPath( $refPath )
		{
			$this->refPath = $refPath;
		}
	
		public function addRoute( array $route )
		{
			if( !isset($route['name']) || $route['name'] === null ) $this->routes[] = $route; // indexed route
			elseif (!isset($this->routes[$route['name']] ))  $this->routes[$route['name']] = $route; // named route
			else {} // error!
		}
		
	
		public function generateUrl( $name, array $params = array() ) 
		{ 
			
			$route = $this->routeStrategy->generateAlgorithm( $name, $this->routes, $params );
				
			$url = ($route) ? 'http://' . $_SERVER['HTTP_HOST'] . $this->refPath . $route : "";
			return $url;
		}

		public function matchRoute( IRequest $request )  // if we use php 5.3 or above we can use namespaces so we use this: Interfaces\IRequest
		{ 
			// dirty: we add refPath as array to routes array when pass it over
			$result = $this->routeStrategy->matchAlgorithm( $request, $this->routes + array('refPath' =>  $this->refPath )  );
			return $result;			
		}		
	
	}
?>