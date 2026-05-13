<?php

namespace CMS\SiteManager\Http\Middleware;

use Closure;
use CMS\SiteManager\Services\UrlRedirectService;
use Illuminate\Http\Request;

class ApplyUrlRedirects
{
    public function __construct(
        protected UrlRedirectService $redirects
    ) {}

    public function handle(Request $request, Closure $next)
    {
        if (!config('cms-kit.url_redirects.middleware_enabled', true)) {
            return $next($request);
        }

        if (!in_array($request->method(), ['GET', 'HEAD'], true)) {
            return $next($request);
        }

        if (!$this->redirects->shouldSkipPath($request)) {
            $hit = $this->redirects->tryRedirect($request);
            if ($hit instanceof \Symfony\Component\HttpFoundation\Response) {
                return $hit;
            }
        }

        $response = $next($request);

        if ($response->getStatusCode() === 404 && !$this->redirects->shouldSkipPath($request)) {
            $this->redirects->logMiss($request);
        }

        return $response;
    }
}
