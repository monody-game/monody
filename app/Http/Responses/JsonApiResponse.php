<?php

namespace App\Http\Responses;

use App\Enums\AlertType;
use App\Enums\Status;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Cookie;

class JsonApiResponse implements Responsable
{
    public function __construct(
        public readonly ?array $data = null,
        public readonly Status $status = Status::OK,
        public ?array $alerts = null,
        public ?array $popups = null,
        public array $cookies = [],
        public array $headers = [],
        public array $cache = []
    ) {
    }

    public function toResponse($request): JsonResponse
    {
		$cache = [
			'cache' => array_key_exists('cache', $this->cache) ? $this->cache['cache'] : true,
			'until' => array_key_exists('until', $this->cache) ? $this->cache['until'] : Carbon::now()->addDay(),
			'flush' => array_key_exists('flush', $this->cache) ? $this->cache['flush'] : [],
		];

        $response = new JsonResponse(
            data: [
                'status' => $this->status->statusify(),
                'meta' => [
                    'name' => config('app.name'),
                    'version' => config('app.version'),
                    'cache' => $cache,
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

    /**
     * Create a JsonApiResponse statically, allowing easier use of helper methods (with[...])
     */
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

    /**
     * Tell the client that this route should not be cached locally, mainly because its content may change without the url changing
     */
    public function withoutCache(): self
    {
        $this->cache['cache'] = false;

        return $this;
    }

    /**
     * Tell the client that this route should be cached locally until given timestamp ($until)
     */
    public function withCache(CarbonInterface $until): self
    {
        $this->cache['cache'] = true;
        $this->cache['until'] = $until;

        return $this;
    }

    /**
     * Tell client to flush cache for specified routes
     */
    public function flushCacheFor(string ...$route): self
    {
        if (array_key_exists('flush', $this->cache)) {
            $route = array_merge($route, $this->cache);
        }

        $this->cache['flush'] = $route;

        return $this;
    }
}
