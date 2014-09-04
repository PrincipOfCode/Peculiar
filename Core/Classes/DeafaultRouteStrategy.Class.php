<?php
	namespace Peculiar\Core\Classes; // not works under php 5.3 :-/
	use \Peculiar\Core\Interfaces as Interfaces;  // not works under php 5.3 :-/
	
	class DeafaultRouteStrategy implements Interfaces\IRouteStrategy // IRouteStrategy // if we use php 5.3 or above we can use namespaces so we use this: Interfaces\IRouteStrategy
	{
		private $reqParams = array();
		private $queryString = '';
		private $pattern = '`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`';
		private $microRegxs = array( 
			'i'  	=> '[0-9]++',
			'a' 	=> '[0-9A-Za-z]++',
			'h'  	=> '[0-9A-Fa-f]++',
			'*'  	=> '.+?',
			'**' 	=> '.++',
			''   	=> '[^/\.]++'											
		);
		
		
		public function __construct() { }
		
		public function __destruct() {}

		/**
		 * generateAlgorithm generate the URL from stored named routes
		 * and replace regexes with supplied parameters!
		 *
		 * @param string $routeName The name of the route.
		 * @param array $routes - array of stored routes.
		 * @param array $params Associative array of parameters to replace 'placeholders' with.
		 * @return string Generated URL of the route with supplied parameters in place.
		 */		
		public function generateAlgorithm( $routeName, array $routes, array $params)
		{
			if(!isset($routes[$routeName])) return false;
			$route = $routes[$routeName]['url'];
			if (preg_match_all( $this->pattern, $route, $matches, PREG_SET_ORDER)) 
			{
				foreach($matches as $match) 
				{
					list($block, $pre, $type, $param, $extra) = $match;

					if ($pre) $block = substr($block, 1);
					
					if (isset($params[$param])) $route = str_replace($block, $params[$param], $route);
					elseif ($extra)  $route = str_replace($pre . $block, '', $route);
					
				}
			}
			return $route;
		}		
		
		/**
		 * matchAlgorithm check request Url against stored routes
		 *
		 * @param IRequest $request - the request object.
		 * @param array $routes - array of stored routes.
		 * @return string|boolean, on success return handler 'name', on failure return false (no match).
		 */		
		public function matchAlgorithm( Interfaces\IRequest $request, array $routes ) // if we use php 5.3 or above we can use namespaces so we use this: Interfaces\IRequest
		{
			
			// extract passed $refPath from the routes array: 
			$refPath = $routes['refPath'];
			unset($routes['refPath']);

			// strip refPath from request URI
			$requestURI = substr($request->URI, strlen($refPath));
			
			// Strip query string (?a=b) from Request Url
			if (($strpos = strpos($requestURI, '?')) !== false) 
			{
				$this->queryString = substr($requestURI, $strpos + 1);
				$requestURI = substr($requestURI, 0, $strpos);				
			}			
			
			// explode requestURI to segments
			$reqSegments = explode('/', $requestURI); 			
			
			$foundHandler = array();
			// iterate over routes array: 
			foreach($routes as $route) 
			{

				list($method, $url, $handler) = array_values($route);
				
				// Check if request method matches. If not, continue to next route. 
				if(!$this->matchMethods( $request->METHOD, $method)) continue;
				
				// explode route url to segments
				$routeSegments = explode('/', $url); 						
				
				// match request segment via route segment:  
				if($this->matchSegments($reqSegments, $routeSegments)) 
				{
					if($this->reqParams) 
						foreach($this->reqParams as $key => $value) 
							if(is_numeric($key)) unset($this->reqParams[$key]);					
					return array( 	'handler' 		=> $handler, 
										'parameters' 	=> $this->reqParams, 
										'queryString' 	=> $this->queryString );
				}
				else continue; // try next route		 		
			}

			return false; // no matches, return false
		}

		/**
		 * matchMethods check request method against route methods
		 * this is a HELPER function for matchAlgorithm function!
		 *
		 * @param string $requestMethod - the request method.
		 * @param string $routeMethods - the route methods.
		 * @return boolean - return true if found match, on failure return false (no match).
		 */			
		private function matchMethods( $requestMethod, $routeMethods )
		{
			$methods = explode('|', $routeMethods);
			// Check if request method match to route methods. If not, skip. (SIMPLE)
			foreach($methods as $method) 
			{
				if (strcasecmp( $requestMethod, $method) === 0)  return true; // method match
			}
			return false; // methods not match
		}
		
		/**
		 * matchSegments match request parts against route parts
		 * this is a HELPER function for matchAlgorithm function!
		 *
		 * @param array $reqSegments - the request segments.
		 * @param array $routeSegments - the route segments.
		 * @return boolean - if segments match return true, if no match return false!  
		 */		
		private function matchSegments( $reqSegments, $routeSegments )
		{

			// check for a wildcard (match all):
			if($routeSegments[0] === '*') return true;
						
			$reqSize = count($reqSegments);
			$routeSize = count($routeSegments);
			
			// compare number of segments
			if( $reqSize != $routeSize ) return false;
			
			// iterate over segments
			for($i=0; $i < $reqSize; $i++)
			{
				// iterate over segment characters
				for($c = 0; ; $c++ )
				{
					$reqSCExist = isset($reqSegments[$i][$c]);
					$routSCExist = isset($routeSegments[$i][$c]);

			
					if(!$reqSCExist && !$routSCExist) break; // end of segments, go to next segments pair
					if(!$reqSCExist || !$routSCExist) return false; // not match for this rout, return false	
				
					// if the chars are the same continue to next char
					if($reqSegments[$i][$c] === $routeSegments[$i][$c]) continue;
					else // can be regEx part:
					{
						// check for beginning of regEx
						if(($routeSegments[$i][$c] === '[' ) || ($routeSegments[$i][$c] === '(' ) || ($routeSegments[$i][$c] === '.' ) )  	
						{
							$reqSegPart = substr($reqSegments[$i], $c);
							$routeSegPart = substr($routeSegments[$i], $c);
							
							if($this->matchParams($reqSegPart, $routeSegPart)) break; // go to next segments pair
							else $this->reqParams = array(); // no match, erase all stored params						
						}
						return false;  // not match for this rout, return false								
					}				
				}								
			}
			
			return true;
			
		}
		
		/**
		 * matchParams match string agains stored reqex and extract the params  
		 * this is a HELPER function for matchAlgorithm function!
		 *
		 * @param string $reqSegments - the request segments.
		 * @param string $routeSegments - the route segments.
		 * @return boolean - if segments match return true, if no match return false!  
		 */				
		private function matchParams( $reqSegPart, $routeSegPart )
		{	
			// operate route segment part  
			if (preg_match_all( $this->pattern, $routeSegPart, $matches, PREG_SET_ORDER))
			{
				foreach ($matches as $match)
				{
					list($block, $pre, $type, $param, $extra) = $match;
					
					if (isset($this->microRegxs[$type])) $type = $this->microRegxs[$type];
					if ($pre === '.') $pre = '\.';	
						
					//Older versions of PCRE require the 'P' in (?P<named>)
					$actualPattern =  	'(?:'
												. ($pre !== '' ? $pre : null)
												. '('
												. ($param !== '' ? "?P<$param>" : null)
												. $type
												. '))'
												. ($extra !== '' ? '?' : null);
												
					$routeSegPart =  str_replace($block, $actualPattern, $routeSegPart) ;					
				}
				$routeSegPart = "`^" . $routeSegPart . "`"; // prepare for use
			}					
			else return false; // bad route segment part, return false
		
			// match string agains pattern and extract params
			if(preg_match($routeSegPart, $reqSegPart, $paramList )) 
			{
				$this->reqParams += $paramList; // append rout params 
				return true; // match, return true
			}
			else return false; // bad route segment part, return false
								
		}
		
	}
?>