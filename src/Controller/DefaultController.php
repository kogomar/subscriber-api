<?php

namespace App\Controller;

use App\Attributes\Route;
use App\Http\Request;
use App\Http\View;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use OpenApi\Generator;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    description: "API for subscribing to email notifications about new releases of selected GitHub repositories.",
    title: "GitHub Repository Release Subscriber API"
)]
#[OA\SecurityScheme(
    securityScheme: "ApiKeyAuth",
    type: "apiKey",
    name: "App-Api-Key",
    in: "header"
)]
class DefaultController
{
    private CollectorRegistry $registry;
    private View $view;

    public function __construct(CollectorRegistry $registry, View $view)
    {
        $this->registry = $registry;
        $this->view = $view;
    }

    #[Route('/', method: 'GET')]
    public function index(Request $request): string
    {
        header('Content-Type: text/html');
        return $this->view->render('index', [
            'title' => 'OctoNotify | GitHub Release Alerts'
        ]);
    }

    #[Route('/api/docs', method: 'GET')]
    public function docs(Request $request): string
    {
        header('Content-Type: text/html');
        return $this->view->render('docs', [], null);
    }

    #[Route('/openapi.json', method: 'GET')]
    public function openapi(Request $request): string
    {
        header('Content-Type: application/json');
        $openapi = Generator::scan([__DIR__ . '/../../src']);
        $openapi->servers = [
            new OA\Server(url: getenv('APP_URL') ?: 'http://localhost:8080', description: 'API Server')
        ];
        return $openapi->toJson();
    }

    #[Route('/metrics', method: 'GET')]
    public function metrics(Request $request): string
    {
        header('Content-Type: ' . RenderTextFormat::MIME_TYPE);
        $renderer = new RenderTextFormat();
        return $renderer->render($this->registry->getMetricFamilySamples());
    }
}
