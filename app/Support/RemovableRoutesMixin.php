<?php

namespace App\Support;

use Closure;
use Illuminate\Routing\Router;

class RemovableRoutesMixin
{
    /**
     * Remove a Get route using the provided uri
     *
     * @return closure|\Closure
     */
    public function removeGet()
    {
        return function (string $uri) {
            $this->setRoutes(
                RemovableRouteCollection::cloneFrom($this->routes)
                    ->remove('GET', $uri)
            );
        };
    }

    /**
     * Remove a route using the provided method and uri
     *
     * @return closure|\Closure
     */
    public function remove()
    {
        return function (string $method, string $uri) {
            $this->setRoutes(
                RemovableRouteCollection::cloneFrom($this->routes)
                    ->remove(strtoupper($method), $uri)
            );
        };
    }

    /**
     * Remove a Post route using the provided uri
     *
     * @return closure|\Closure
     */
    public function removePost()
    {
        return function (string $uri) {
            $this->setRoutes(
                RemovableRouteCollection::cloneFrom($this->routes)
                    ->remove('POST', $uri)
            );
        };
    }

    /**
     * Remove a Put route using the provided uri
     *
     * @return closure|\Closure
     */
    public function removePut()
    {
        return function (string $uri) {
            $this->setRoutes(
                RemovableRouteCollection::cloneFrom($this->routes)
                    ->remove('PUT', $uri)
            );
        };
    }

    /**
     * Remove a Patch route using the provided uri
     *
     * @return closure|\Closure
     */
    public function removePatch()
    {
        return function (string $uri) {
            $this->setRoutes(
                RemovableRouteCollection::cloneFrom($this->routes)
                    ->remove('PATCH', $uri)
            );
        };
    }

    /**
     * Remove a Delete route using the provided uri
     *
     * @return closure|\Closure
     */
    public function removeDelete()
    {
        return function (string $uri) {
            $this->setRoutes(
                RemovableRouteCollection::cloneFrom($this->routes)
                    ->remove('DELETE', $uri)
            );
        };
    }

    /**
     * Remove a Options route using the provided uri
     *
     * @return closure|\Closure
     */
    public function removeOptions()
    {
        return function (string $uri) {
            $this->setRoutes(
                RemovableRouteCollection::cloneFrom($this->routes)
                    ->remove('OPTIONS', $uri)
            );
        };
    }

    /**
     * Remove routes using any method that matches the provided uri
     *
     * @return closure|\Closure
     */
    public function removeAny(): Closure
    {
        return function ($uri) {
            $this->setRoutes(
                RemovableRouteCollection::cloneFrom($this->routes)
                    ->remove(Router::$verbs, $uri)
            );
        };
    }
}
