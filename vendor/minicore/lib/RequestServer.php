<?php

namespace minicore\lib;

use minicore\config\ConfigBase;
use app;

/**
 *
 * @author lixiao
 *        
 */
class RequestServer extends Base {
	
	/* url参数分隔符 */
	public static $urlDelimiter ;
	
	/* 控制器 在url参数中位置是第几个 */
	public static $actLevel ;
	public function __construct() {
	}
	
	/* 路由键值对，键名是url，值是对应的控制器 调用闭包 */
	private static $_get = array ();
	private static $_post = array ();
	
	/**
	 *
	 * @param multitype: $rule        	
	 */
	public static function get($url, \Closure $act) {
		self::$rule [$url] = $act;
	}
	public static function post($url, \Closure $act) {
	}
	public static function getRule($key) {
		if (array_key_exists ( $key, $this->rule )) {
			return $this->rule [$key];
		}
	}
	public static function initGet($array) {  
		while ( $var = array_shift ( $array ) ) {
			$_GET [$var] = array_shift ( $array );
			$_REQUEST [$var] = $_GET [$var];
		}
	}
	
	/**
	 * 生成控制器方法arr。.
	 * 
	 * @param unknown $url        	
	 * @throws \ErrorException
	 * @return string[]|mixed[]|string[]|mixed[]|\minicore\lib\the[]
	 */
	public static function generatRoute($url) {  
	       if(strpos($url, '?')) {
	           $url=substr($url, 0,strpos($url, '?')); 
	       }
			if (2 == self::$actLevel) {
				$pars = explode ( '\\', $url );
				$pars = array_filter ( $pars );
				$actArr = array_splice ( $pars, 0, self::$actLevel );
				self::initGet ( $pars );
				$act = array_pop ( $actArr );
				$controller = 'controllers\\' . array_pop ( $actArr );
				
				return array (
						'controller' => $controller,
						'act' => $act 
				);
			} else {
			    
				$pars = explode ( '\\', $url );
				$pars = array_filter ( $pars );
				$actArr = array_splice ( $pars, 0, self::$actLevel );  
				self::initGet ( $pars );
				$act = array_pop ( $actArr );
				$controller = array_pop ( $actArr );
				if (empty ( $controllerId )) {
					$controllerId = Mini::$app->getConfig ( 'defaultController' );
				}
				
				Mini::$app->setModule ( implode ( '\\', $actArr ) );
				if (! Mini::$app->getModule ()) {
					Mini::$app->setModule ( Mini::$app->getConfig ( 'defaultModule' ) );
				} else {
				}
				$controller = $controller;
				$routeArr = array (
						'module' => Mini::$app->getModule () ,
						'controller' => $controller,
						'act' => $act,
				);
				if ('' == $routeArr ['controller']) {
					$routeArr ['controller'] = Mini::$app->getConfig ( 'defaultController' );
				}
				if ($routeArr ['act'] == '') {
					
					$routeArr ['act'] = Mini::$app->getConfig ( 'defaultAct' );
				}
				$routeArr['route']=implode('/', $routeArr);
				return $routeArr;
			}
		}
	
	/**
	 * 运行程序撒......
	 */
	public static function runRout($routeArr) {
		if (array_key_exists ( static::class, Mini::$app->getConfig ( 'extentions' ) )) {
			 static::miniObjInitStatic(Mini::$app->getConfig('extentions')[static::class]);
		}
// 		echo self::$urlDelimiter,'ijiji';
		if (1 == Mini::$app->getConfig ( 'routType' )) {
			// if($config=Mini::$app->getConfig('layout')) {
			// foreach ($config as $row) {
			// self::partial($row);
			// }
			// }
			if ($routeArr ['module']) {
				$Controller = Mini::$app->getConfig ( 'appNamespace' ) . '\\' . $routeArr ['module'] . '\\controllers\\' . $routeArr ['controller'] . Mini::$app->getConfig ( 'ControllerSuffix' );
			} else {
				$Controller = Mini::$app->getConfig ( 'appNamespace' ) . '\\' . $routeArr ['controller'] . Mini::$app->getConfig ( 'ControllerSuffix' );
			}
			 
			Mini::$app->setController ( $Controller );
			Mini::$app->setAct ( Mini::$app->getConfig ( 'actPrefix' ) . $routeArr ['act'] . Mini::$app->getConfig ( 'actSuffix' ) );
			if (class_exists ( $Controller )) {
				$ControllerObj = (new \ReflectionClass($Controller))->newInstance();
				Mini::$app->setControllerStance ( $ControllerObj );
				call_user_func ( array (
						$ControllerObj,
						Mini::$app->getAct () 
				) );
			} else {
				echo ('未发现控制器，检查您的url');
			}
		}
	}
	public static function analyzeUrl($url = null) {
		if (empty ( $url )) {
			
			if (1 == Mini::$app->getConfig ( 'urlMode' )) {
				if (isset ( $_SERVER ['PATH_INFO'] )) {
					return strtr ( $_SERVER ['PATH_INFO'], array (
							'/' => '\\' 
					) );
				} else {
					$uri = $_SERVER ['REQUEST_URI']; // echo $uri,'<br>';
					$root = $_SERVER ['DOCUMENT_ROOT']; // echo $root,'<br>';
					$scriptFileName = dirname ( $_SERVER ['SCRIPT_FILENAME'] ); // echo 'scrii',$scriptFileName,'<br>';
					$str = strtr ( $scriptFileName, array (
							$root => null 
					) ); // echo $str,'<br>';
					$rs = strtr ( $uri, array (
							$str => null 
					) ); // exit;
					return strtr ( $rs, array (
							'/' => '\\',
							'index.php' => '' 
					) );
				}
			}
		} else {
			return strtr ( $url, array (
					'/' => '\\' 
			) );
		}
	}
	public static function partial($path) {
		$path = self::analyzeUrl ( $path );
		$routeArr = self::generatController ( $path );
		if ($routeArr ['module']) {
			$Controller = Mini::$app->getConfig ( 'appNamespace' ) . '\\' . $routeArr ['module'] . '\\controllers\\' . $routeArr ['controller'] . Mini::$app->getConfig ( 'ControllerSuffix' );
		} else {
			$Controller = Mini::$app->getConfig ( 'appNamespace' ) . '\\' . $routeArr ['controller'] . Mini::$app->getConfig ( 'ControllerSuffix' );
		}
		if (class_exists ( $Controller )) {
			$ControllerObj = new $Controller;
			call_user_func ( array (
					$ControllerObj,
					$routeArr ['act'] 
			) );
		} else {
			echo (' ');
		}
	}
	public static function callAct($routeArr) {
        
    }
}

