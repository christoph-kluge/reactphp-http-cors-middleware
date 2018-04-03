<?php

namespace Sikei\React\Http\Middleware;

class CorsMiddlewareConfiguration
{

    protected $settings = [
        'server_url'            => null,
        'response_code'         => 204, // Pre-Flight Status Code
        'allow_credentials'     => false,
        'allow_origin'          => [],
        'allow_origin_callback' => null,
        'allow_methods'         => ['GET', 'POST', 'OPTIONS'],
        'allow_headers'         => [],
        'expose_headers'        => [],
        'max_age'               => 60 * 60 * 24 * 20, // preflight request is valid for 20 days
    ];

    protected $serverOrigin = [];

    public function __construct(array $settings = [])
    {
        $this->settings = array_merge($this->settings, $settings);

        if (!is_null($this->settings['server_url'])) {
            $this->serverOrigin = parse_url($this->settings['server_url']);
            if (count(array_diff_key(['scheme' => '', 'host' => ''], $this->serverOrigin)) > 0) {
                throw new \InvalidArgumentException('Option "server_url" requires at least scheme and domain');
            }
        }
    }

    public function getPreFlightResponseCode()
    {
        return (int)$this->settings['response_code'];
    }

    public function getServerOrigin()
    {
        return $this->serverOrigin;
    }

    public function getRequestCredentialsSupported()
    {
        return is_bool($this->settings['allow_credentials'])
            ? $this->settings['allow_credentials']
            : false;
    }

    public function getRequestAllowedOrigins()
    {
        if (is_callable($this->settings['allow_origin'])) {
            return [];
        }

        if (is_string($this->settings['allow_origin']) && $this->settings['allow_origin'] == '*') {
            $this->settings['allow_origin'] = ['*'];
        }

        $origins = [];
        foreach ($this->settings['allow_origin'] as $origin) {
            $origins[$origin] = true;
        }

        return $origins;
    }

    public function hasRequestAllowedOriginsCallback()
    {
        return is_callable($this->settings['allow_origin_callback']);
    }

    public function getRequestAllowedOriginsCallback()
    {
        return $this->settings['allow_origin_callback'];
    }

    public function getRequestAllowedMethods()
    {
        $methods = [];
        foreach ($this->settings['allow_methods'] as $verb) {
            $methods[$verb] = true;
        }

        return $methods;
    }

    public function getPreFlightCacheMaxAge()
    {
        return (int)$this->settings['max_age'];
    }

    public function getRequestAllowedHeaders()
    {
        $headers = [];
        foreach ($this->settings['allow_headers'] as $header) {
            $headers[$header] = true;
        }

        return $headers;
    }

    public function getResponseExposedHeaders()
    {
        $headers = [];
        foreach ($this->settings['expose_headers'] as $header) {
            $headers[$header] = true;
        }

        return $headers;
    }
}
