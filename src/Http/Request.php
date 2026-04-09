<?php

namespace App\Http;

class Request
{
    private array $data;
    private array $headers;

    public function __construct()
    {
        $input = file_get_contents('php://input');
        $this->data = json_decode($input, true) ?? [];
        $this->headers = $this->extractHeaders();
    }

    public function all(): array
    {
        return $this->data;
    }

    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function header(string $key, $default = null)
    {
        $key = strtolower($key);
        return $this->headers[$key] ?? $default;
    }

    private function extractHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (str_starts_with($name, 'HTTP_')) {
                $headers[strtolower(str_replace('_', '-', substr($name, 5)))] = $value;
            }
        }
        return $headers;
    }
}
