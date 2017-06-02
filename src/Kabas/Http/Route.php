<?php

namespace Kabas\Http;

use \Kabas\App;

class Route
{
      /**
       * The string representation of this route.
       * ex: '/news/{id}'
       * @var string
       */
      public $string;

      /**
       * The regular expression to match this route's string.
       * @var string
       */
      protected $regex;

      /**
       * List of parameters in the route.
       * @var array
       */
      public $parameters = [];


      /**
       * Target page's identifier
       * @var object
       */
      public $page;

      public function __construct($page)
      {
            $this->page = $page->id;
            $this->string = $page->route;
            $this->regex = $this->generateRegex();
      }

      /**
       * Generate a regex to match this route.
       * @return string
       */
      protected function generateRegex()
      {
            if($this->string === '') return '/^\s*$/';
            $regex = trim(strtolower($this->string), '/');
            $regex = $this->upgradeParamsToRegex($regex);
            $regex = preg_replace('/([^\\\])\//', '$1\/', $regex);
            $regex = strlen($regex) ? '/^\/' . $regex . '\/?$/' : '/^\/?$/';
            return $regex;
      }

      protected function upgradeParamsToRegex($regex)
      {
            preg_match_all('/\{([a-zA-Z0-9]*)(?:::)?(\/.[^\/]+\/+)?(\?)?\}/', $regex, $a);
            foreach ($a[0] as $i => $param) {
                  $param = $this->makeParameter($param, $a[1][$i], $a[2][$i], $a[3][$i]);
                  $regex = str_replace($param->string, $param->regex, $regex);
                  $this->parameters[] = $param;
            }
            return $regex;
      }

      protected function makeParameter($string, $variable, $regex, $optional)
      {
            $o = new \stdClass();
            $o->string = $string;
            $o->variable = $variable;
            $o->isRequired = $optional === '?' ? false : true;
            $o->regex = strlen($regex) ? '(' . trim($regex,'/') . ')' : '(.[^\/]*)';
            if(!$o->isRequired) $o->regex .= '?';
            $o->value = null;
            return $o;
      }

      /**
       * Check if this route matches the specified route.
       * @param  string $route
       * @return bool
       */
      public function matches($route)
      {
            return !!preg_match($this->regex, $route);
      }

      /**
       * Retrieves the parameters for the current route.
       * @return void
       */
      public function gatherParameters($route = null)
      {
            $route = App::router()->getRoute();
            preg_match($this->regex, $route, $matches);
            array_shift($matches);
            foreach ($matches as $i => $value) {
                  $this->parameters[$i]->value = urldecode($value);
            }
      }

      /**
       * Get this route's parameters in a key => value format
       * @return array
       */
      public function getParameters()
      {
            $params = [];
            foreach ($this->parameters as $param) {
                  $params[$param->variable] = $param->value;
            }
            return $params;
      }

}