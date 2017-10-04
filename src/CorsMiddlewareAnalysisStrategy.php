<?php

namespace Sikei\React\Http\Middleware;

use Neomerx\Cors\Contracts\Http\ParsedUrlInterface;
use Neomerx\Cors\Strategies\Settings;

class CorsMiddlewareAnalysisStrategy extends Settings
{

    protected $config;

    public function __construct(CorsMiddlewareConfiguration $config = null)
    {
        parent::__construct();

        $this->config = $config;
        $this
//            ->setCheckHost(true)
//            ->setServerOrigin($this->config->getServerOrigin())
            ->setRequestCredentialsSupported($this->config->getRequestCredentialsSupported())
            ->setRequestAllowedOrigins($this->config->getRequestAllowedOrigins())
            ->setRequestAllowedMethods($this->config->getRequestAllowedMethods())
            ->setRequestAllowedHeaders($this->config->getRequestAllowedHeaders())
            ->setResponseExposedHeaders($this->config->getResponseExposedHeaders())
            ->setPreFlightCacheMaxAge($this->config->getPreFlightCacheMaxAge());
    }

    public function isRequestOriginAllowed(ParsedUrlInterface $requestOrigin)
    {
        if ($this->config->hasRequestAllowedOriginsCallback()) {
            $callback = $this->config->getRequestAllowedOriginsCallback();

            $return = $callback($requestOrigin);
            if (is_bool($return)) {
                return $return;
            }
            return false;
        }

        return parent::isRequestOriginAllowed($requestOrigin);
    }

}
