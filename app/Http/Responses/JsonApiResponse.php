<?php

namespace App\Http\Responses;

use App\Enums\AlertType;
use App\Enums\Status;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Cookie;

class JsonApiResponse implements Responsable
{
    public function __construct(
        public readonly ?array $data = null,
        public readonly Status $status = Status::OK,
        public ?array $alerts = null,
        public ?array $popups = null,
        public array $cookies = [],
        public array $headers = []
    ) {
    }

    public function toResponse($request): JsonResponse
    {
        $response = new JsonResponse(
            data: [
                'status' => $this->status->statusify(),
                'meta' => [
                    'name' => config('app.name'),
                    'version' => config('app.version'),
                ],
                'data' => $this->data,
                'alerts' => $this->alerts,
                'popups' => $this->popups,
            ],
            status: $this->status->value
        );

        if (count($this->cookies) > 0) {
            foreach ($this->cookies as $cookie) {
                $response = $response->withCookie($cookie);
            }
        }

        if (count($this->headers) > 0) {
            $response->withHeaders($this->headers);
        }

        return $response;
    }

    public static function make(?array $data = null, Status $status = Status::OK, array $headers = []): self
    {
        return new self($data, $status, headers: $headers);
    }

    public function withAlert(AlertType $type, string $content): self
    {
        $this->alerts[$type->value] = $content;

        return $this;
    }

    public function withPopup(AlertType $type, string $content, ?string $note = null, ?string $link = null, ?string $linkText = null): self
    {
        $this->popups[$type->value] = [
            'content' => $content,
            'note' => $note,
            'link' => $link,
            'link_text' => $linkText,
        ];

        return $this;
    }

    public function withCookie(Cookie $cookie): self
    {
        $this->cookies[] = $cookie;

        return $this;
    }
}
