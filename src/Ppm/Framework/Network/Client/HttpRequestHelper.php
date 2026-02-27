<?php

namespace Ppm\Framework\Network\Client;

use Ppm\Framework\Network\Constants\RequestMethods;

class HttpRequestHelper
{
    public static function get(string $url): HttpRequest
    {
        return new HttpRequest($url);
    }

    public static function post(string $url): HttpRequest
    {
        return new HttpRequest($url, RequestMethods::POST);
    }

    public static function put(string $url): HttpRequest
    {
        return new HttpRequest($url, RequestMethods::PUT);
    }

    public static function patch(string $url): HttpRequest
    {
        return new HttpRequest($url, RequestMethods::PATCH);
    }

    public static function delete(string $url): HttpRequest
    {
        return new HttpRequest($url, RequestMethods::DELETE);
    }

    public static function head(string $url): HttpRequest
    {
        return new HttpRequest($url, RequestMethods::HEAD);
    }

    public static function options(string $url): HttpRequest
    {
        return new HttpRequest($url, RequestMethods::OPTIONS);
    }
}