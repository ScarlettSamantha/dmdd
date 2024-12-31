<?php
declare(strict_types=1);

namespace Scarlett\DMDD\GUI\Services;

class BackendIntegrationServiceResponse
{
    public mixed $data;
    public int $status_code;
    public ?string $message;

    public function __construct(mixed $data = null, int $status_code = 200, ?string $message = null)
    {
        $this->data = $data;
        $this->status_code = $status_code;
        $this->message = $message;
    }

    public function toArray(): array
    {
        return [
            'data' => $this->data,
            'status_code' => $this->status_code,
            'message' => $this->message,
        ];
    }
}
