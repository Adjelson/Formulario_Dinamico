<?php

class Router {
    protected $routes = [];
    protected $params = [];

    public function add($route, $params = []) {
        // 1. Escapar barras literais
        $route = str_replace('/', '\\/', $route);

        // 2. Converter {param:regex} -> regex customizado
        $route = preg_replace_callback(
            '/\{([a-z_-]+):([^}]+)\}/',
            function($m) { return '(?P<' . $m[1] . '>' . $m[2] . ')'; },
            $route
        );

        // 3. Converter {param} -> aceitar letras, números, hífen, underscore, ponto, %
        $route = preg_replace_callback(
            '/\{([a-z_-]+)\}/',
            function($m) { return '(?P<' . $m[1] . '>[a-zA-Z0-9_.%\-]+)'; },
            $route
        );

        $this->routes['/^' . $route . '$/i'] = $params;
    }

    public function getRoutes() {
        return $this->routes;
    }

    public function match($url) {
        foreach ($this->routes as $route => $params) {
            if (preg_match($route, $url, $matches)) {
                foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        $params[$key] = $match;
                    }
                }
                $this->params = $params;
                return true;
            }
        }
        return false;
    }

    public function dispatch($url) {
        if ($this->match($url)) {
            $controller      = $this->convertToStudlyCaps($this->params['controller']);
            $controllerClass = $controller . 'Controller';
            $controllerFile  = '../app/controllers/' . $controllerClass . '.php';

            if (!file_exists($controllerFile)) {
                throw new Exception('Ficheiro de controller não encontrado: ' . $controllerClass);
            }

            require_once $controllerFile;

            if (!class_exists($controllerClass)) {
                throw new Exception('Classe de controller não encontrada: ' . $controllerClass);
            }

            $controller_object = new $controllerClass($this->params);

            // Tentar o nome exacto da action primeiro (ex: deleteOwn, exportZip)
            // depois a versão camelCase (ex: delete-own -> deleteOwn)
            $actionRaw   = $this->params['action'];
            $actionCamel = $this->convertToCamelCase($actionRaw);

            if (is_callable([$controller_object, $actionRaw])) {
                $action = $actionRaw;
            } elseif (is_callable([$controller_object, $actionCamel])) {
                $action = $actionCamel;
            } else {
                throw new Exception('Método ' . $actionRaw . ' não encontrado em ' . $controllerClass);
            }

            $controller_object->$action();

        } else {
            throw new Exception('Nenhuma rota correspondeu.', 404);
        }
    }

    protected function convertToStudlyCaps($string) {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
    }

    protected function convertToCamelCase($string) {
        return lcfirst($this->convertToStudlyCaps($string));
    }
}
