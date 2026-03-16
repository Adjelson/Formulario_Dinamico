<?php

class Router {
    protected $routes = [];
    protected $params = [];

    public function add($route, $params = []) {
        // Escape forward slashes
        $route = preg_replace('/\//', '\\/', $route);

        // Convert variables e.g. {id}, {slug}, {file}
        // Padrão alargado: aceita letras, números, hífens, underscores, pontos e % (nomes de ficheiro / slugs)
        $route = preg_replace('/{([a-z_-]+)}/', '(?P<\1>[a-zA-Z0-9_.%-]+)', $route);

        // Convert variables with custom regex e.g. {id:\d+}
        $route = preg_replace('/{([a-z_-]+):([^}]+)}/', '(?P<\1>\2)', $route);

        $route = '/^' . $route . '$/i';

        $this->routes[$route] = $params;
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

            $action = $this->convertToCamelCase($this->params['action']);

            if (!is_callable([$controller_object, $action])) {
                throw new Exception('Método ' . $action . ' não encontrado em ' . $controllerClass);
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
