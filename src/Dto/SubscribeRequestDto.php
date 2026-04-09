<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "SubscribeRequest",
    description: "Data needed to subscribe to repository updates",
    required: ["email", "repository"]
)]
class SubscribeRequestDto
{
    #[OA\Property(description: "Subscriber email address", example: "user@example.com")]
    #[Assert\NotBlank(message: "Email is required")]
    #[Assert\Email(message: "Invalid email format")]
    public ?string $email = null;

    #[OA\Property(description: "GitHub repository in owner/repo format", example: "golang/go")]
    #[Assert\NotBlank(message: "Repository is required")]
    #[Assert\Regex(pattern: "/^[A-Za-z0-9_.-]+\/[A-Za-z0-9_.-]+$/", message: "Repository format must be owner/repo")]
    public ?string $repository = null;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->email = $data['email'] ?? null;
        $dto->repository = $data['repository'] ?? null;
        return $dto;
    }
}
