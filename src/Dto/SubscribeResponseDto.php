<?php

namespace App\Dto;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "SubscribeResponse",
    description: "Response when subscription is processed successfully"
)]
class SubscribeResponseDto
{
    #[OA\Property(description: "Status message", example: "Subscribed successfully")]
    public string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }
}
