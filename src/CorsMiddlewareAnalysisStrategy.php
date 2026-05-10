<?php

namespace Sikei\React\Http\Middleware;

use Neomerx\Cors\Strategies\Settings;

class CorsMiddlewareAnalysisStrategy extends Settings
{

    public function __construct(
        protected CorsMiddlewareConfiguration $config,
    ) {
        $serverOrigin = $this->config->getServerOrigin();
        if (!empty($serverOrigin)) {
            $scheme = $serverOrigin['scheme'] ?? 'http';
            $host   = $serverOrigin['host'] ?? 'localhost';
            $port   = (int) ($serverOrigin['port'] ?? ($scheme === 'https' ? 443 : 80));
            $this->init($scheme, $host, $port);
            $this->enableCheckHost();
        } else {
            $this->init('http', 'localhost', 80);
        }

        if ($this->config->getRequestCredentialsSupported()) {
            $this->setCredentialsSupported();
        }

        $allowedOrigins = $this->config->getRequestAllowedOrigins();
        $originList     = array_keys($allowedOrigins);
        if (in_array('*', $originList, true)) {
            $this->enableAllOriginsAllowed();
        } else {
            $this->setAllowedOrigins($originList);
        }

        $this->setAllowedMethods(array_keys($this->config->getRequestAllowedMethods()));
        $this->setAllowedHeaders(array_keys($this->config->getRequestAllowedHeaders()));
        $this->setExposedHeaders(array_keys($this->config->getResponseExposedHeaders()));
        $this->setPreFlightCacheMaxAge($this->config->getPreFlightCacheMaxAge());
    }

    public function isRequestOriginAllowed(string $requestOrigin): bool
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
