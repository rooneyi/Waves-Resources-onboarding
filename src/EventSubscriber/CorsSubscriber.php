<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class CorsSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 100],
            KernelEvents::RESPONSE => ['onKernelResponse', 0],
        ];
    }

    private function getAllowedOrigins(): array
    {
        $env = getenv('FRONTEND_URL') ?: 'http://localhost:3000';
        $origins = array_map('trim', explode(',', $env));
        return array_values(array_filter($origins));
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        // Handle preflight
        if ($request->getMethod() === 'OPTIONS') {
            $response = new Response();
            $this->addCorsHeaders($request, $response);
            $event->setResponse($response);
        }
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        $response = $event->getResponse();
        $this->addCorsHeaders($request, $response);
    }

    private function addCorsHeaders(Request $request, Response $response): void
    {
        $allowedOrigins = $this->getAllowedOrigins();
        $origin = $request->headers->get('Origin');

        $allowOrigin = null;

        if ($origin) {
            if (in_array('*', $allowedOrigins, true) || in_array($origin, $allowedOrigins, true)) {
                $allowOrigin = $origin;
            } else {
                // Allow any localhost origin (different ports) during local development
                $parts = parse_url($origin);
                $host = $parts['host'] ?? '';
                if ($host === 'localhost' || $host === '127.0.0.1') {
                    $allowOrigin = $origin;
                }
            }
        }

        if ($allowOrigin) {
            $response->headers->set('Access-Control-Allow-Origin', $allowOrigin);
        } else {
            // fallback to first allowed origin when no Origin header present
            $response->headers->set('Access-Control-Allow-Origin', $allowedOrigins[0] ?? '*');
        }

        $response->headers->set('Access-Control-Allow-Methods', 'GET,POST,PUT,PATCH,DELETE,OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept');
        $response->headers->set('Access-Control-Max-Age', '3600');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Vary', 'Origin');
    }
}
