<?php

namespace App\Controller;

use App\Attributes\Route;
use App\Dto\ApiResponseDto;
use App\Dto\SubscribeRequestDto;
use App\Http\Request;
use App\Application\SubscriptionService;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Validation;

class SubscriptionController
{
    public function __construct(private readonly SubscriptionService $service)
    {
    }

    #[Route('/api/subscribe', method: 'POST', auth: true)]
    #[OA\Post(
        path: "/api/subscribe",
        summary: "Subscribe to repository updates",
        security: [['ApiKeyAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/SubscribeRequest")
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Subscribed successfully",
                content: new OA\JsonContent(ref: "#/components/schemas/ApiResponse")
            ),
            new OA\Response(
                response: 400,
                description: "Validation error or Bad Request",
                content: new OA\JsonContent(ref: "#/components/schemas/ApiResponse")
            ),
            new OA\Response(
                response: 403,
                description: "Forbidden",
                content: new OA\JsonContent(ref: "#/components/schemas/ApiResponse")
            ),
            new OA\Response(
                response: 404,
                description: "Repository not found",
                content: new OA\JsonContent(ref: "#/components/schemas/ApiResponse")
            ),
            new OA\Response(
                response: 429,
                description: "Too many requests to external GitHub API",
                content: new OA\JsonContent(ref: "#/components/schemas/ApiResponse")
            )
        ]
    )]
    public function subscribe(Request $request): string
    {
        $dto = SubscribeRequestDto::fromArray($request->all());
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $violations = $validator->validate($dto);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
            }
            http_response_code(400);
            return (string) ApiResponseDto::error($errors);
        }

        try {
            $this->service->subscribe($dto->email, $dto->repository);
            http_response_code(200);
            return (string) ApiResponseDto::success(['message' => 'Subscribed successfully']);
        } catch (\Exception $e) {
            $code = $e->getCode();
            $status = match ($code) {
                404 => 404,
                422 => 422,
                400 => 400,
                429 => 429,
                default => 500
            };
            http_response_code($status);
            return (string) ApiResponseDto::error($e->getMessage());
        }
    }
}
