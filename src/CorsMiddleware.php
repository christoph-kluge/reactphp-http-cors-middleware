<?php

namespace Sikei\React\Http\Middleware;

use Neomerx\Cors\Analyzer;
use Neomerx\Cors\Contracts\AnalysisResultInterface;
use Neomerx\Cors\Contracts\AnalyzerInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response;
use Sikei\React\Http\Middleware\CorsMiddlewareAnalysisStrategy as Strategy;
use Sikei\React\Http\Middleware\CorsMiddlewareConfiguration as Config;

final class CorsMiddleware
{

    /**
     * @var AnalyzerInterface
     */
    private $analyzer;

    /**
     * @var Config
     */
    private $config;

    public function __construct(array $settings = [])
    {
        $this->config = new Config($settings);
        $this->analyzer = Analyzer::instance(new Strategy($this->config));
    }

    public function __invoke(ServerRequestInterface $request, $next)
    {
        $cors = $this->analyzer->analyze($request);
        switch ($cors->getRequestType()) {
            case AnalysisResultInterface::TYPE_REQUEST_OUT_OF_CORS_SCOPE:
                return $next($request);
            case AnalysisResultInterface::TYPE_PRE_FLIGHT_REQUEST:
                return new Response($this->config->getPreFlightResponseCode(), $cors->getResponseHeaders());
            case AnalysisResultInterface::ERR_NO_HOST_HEADER:
                return new Response(400, [], 'No host header present');
            case AnalysisResultInterface::ERR_HEADERS_NOT_SUPPORTED:
                return new Response(401, [], 'Headers not supported');
            case AnalysisResultInterface::ERR_ORIGIN_NOT_ALLOWED:
                return new Response(403, [], 'Origin not allowed');
            case AnalysisResultInterface::ERR_METHOD_NOT_SUPPORTED:
                return new Response(405, [], 'Method not supported');
        }

        return $next($request)->then(function (Response $response) use ($cors) {
            foreach ($cors->getResponseHeaders() as $header => $value) {
                $response = $response->withHeader($header, $value);
            }
            return $response;
        });
    }
}
