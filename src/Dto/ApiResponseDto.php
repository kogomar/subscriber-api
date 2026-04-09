<?php

namespace App\Dto;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "ApiResponse",
    description: "Standard API response wrapper"
)]
class ApiResponseDto
{
    #[OA\Property(description: "Indicates if the operation was successful")]
    public bool $success;

    #[OA\Property(description: "Response data payload", nullable: true)]
    public mixed $data;

    #[OA\Property(description: "List of error messages", type: "array", items: new OA\Items(type: "string"))]
    public array $errors;

    public function __construct(bool $success, mixed $data = null, array $errors = [])
    {
        $this->success = $success;
        $this->data = $data;
        $this->errors = $errors;
    }

    public static function success(mixed $data = null): self
    {
        return new self(true, $data);
    }

    public static function error(string|array $errors): self
    {
        $errorList = is_array($errors) ? $errors : [$errors];
        return new self(false, null, $errorList);
    }

    public function __toString(): string
    {
        return json_encode($this, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
