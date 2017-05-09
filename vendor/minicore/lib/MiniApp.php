<?php
namespace minicore\lib;

use minicore\config\ConfigBase;
use minicore\traits\SingleInstance;
use minicore\helper\Db;
use Composer\Autoload\ComposerStaticInit344e82d8c2bfce44cf961e58b48d128c;
use minicore\helper\DbContainer;
use minicore\interfaces\MiniInterface;
use minicore\config\Configer;
use app\run\RunClass;

/**
 *
 * @author Administrator
 * @property
 *
 */
class MiniApp extends MiniBase
{

    private $controller;

    private $act;

    private $baseDir;

    private $controllerStance;

    private $viewPath;

    private $module;

    private $component;

    public static $params;

    /**
     *
     * @return the $params
     */
    public static function getParams($key)
    {
        return MiniApp::$params[$key];
    }

    /**
     *
     * @param field_type $params            
     */
    public static function setParams($params)
    {
        MiniApp::$params = $params;
    }

    public function __get($name = NULL)
    {
        var_dump($name);
        if (array_key_exists($name, $this->component)) {
            return $this->component[$name];
        }
    }

    public function __set($name, $component)
    {
        $this->component[$name] = $component;
    }

    /**
     *
     * @return the $module
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     *
     * @param field_type $module            
     */
    public function setModule($module)
    {
        $this->module = $module;
    }

    /**
     *
     * @return the $viewPath
     */
    public function getViewPath()
    {
        return $this->viewPath;
    }

    /**
     *
     * @param field_type $viewPath            
     */
    public function setViewPath($viewPath)
    {
        $this->viewPath = $viewPath;
    }

    /**
     *
     * @return the $controllerStance
     */
    public function getControllerStance()
    {
        return $this->controllerStance;
    }

    public $appPath;

    /**
     *
     * @return the $appPath
     */
    public function getAppPath()
    {
        return $this->appPath;
    }

    /**
     *
     * @param field_type $appPath            
     */
    public function setAppPath($appPath)
    {
        $this->appPath = $appPath;
    }

    /**
     *
     * @param field_type $controllerStance            
     */
    public function setControllerStance($controllerStance)
    {
        $this->controllerStance = $controllerStance;
    }

    /**
     *
     * @return the $controller
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     *
     * @return the $act
     */
    public function getAct()
    {
        return $this->act;
    }

    /**
     *
     * @param field_type $controller            
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    /**
     *
     * @param field_type $act            
     */
    public function setAct($act)
    {
        $this->act = $act;
    }

    /**
     *
     * @return the $baseDir
     */
    public function getBaseDir()
    {
        return $this->baseDir;
    }

    /**
     *
     * @param field_type $baseDir            
     */
    public function setBaseDir($baseDir)
    {
        $this->baseDir = $baseDir;
    }
    
    use SingleInstance;

    /* 导入单例模式trait */
    function __clone()
    {}

    public function getExtention($key = null)
    { // var_dump($key,$this->getConfig('extentions')[$key]);
        if ($key) {
            return $this->getConfig('extentions')[$key] ?: null;
        }
    }

    public function run()
    {
        if (1 == $this->getConfig('RunMode')) {
            RequestServer::miniObjInitStatic();
            RequestServer::miniObjInitStatic();
            $path = RequestServer::analyzeUrl();
            $routArr = RequestServer::generatRoute($path);
            $_SESSION['miniroute'] = $routArr;
            call_uwser_func(Configer::getConfig('app.runClass'));
            // RequestServer::runRout($routArr);
        }
    }

    public function __construct($config = null)
    {
        if (is_null($config)) {
            $this->config = include dirname(__FILE__) . '/../config/Config.php';
        } else {
            $this->setConfig($config);
            Configer::setConfig($config);
        }
        Mini::$app = $this;
        self::setParams($config['params']);
        if (PHP_SESSION_DISABLED  === session_status()) {
            session_start();
        }
    }
}

