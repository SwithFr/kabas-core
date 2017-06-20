<?php 

namespace Tests;

use Kabas\App;

trait CreatesApplication {

    protected $preserveGlobalState = false;
    protected $runTestInSeparateProcess = true;
    public $app;
    public $result;

    public function createApplication(array $singletonsToBoot = null)
    {
        $this->alterGlobalServer();
        $this->app = new App(__DIR__ . '/TestTheme/public');
        $this->app->boot($singletonsToBoot);
    }

    public function visit($route)
    {
        $_SERVER['REQUEST_URI'] = $route;
        ob_start();
        $this->app->handle();
        $this->result = ob_get_clean();
        return $this;
    }

    public function see($string)
    {
        return $this->assertContains($string, $this->result);
    }

    protected function alterGlobalServer()
    {
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['HTTP_HOST'] = 'www.foo.com';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-GB,en;q=0.8,en-US;q=0.6,en;q=0.4 ';
    }
}