<?php

namespace App\Http;

use App\Enums\AlertType;
use Illuminate\Http\JsonResponse as BaseResponse;

class JsonResponse extends BaseResponse
{
    public function __construct($data = null, $status = 204, $headers = [], $options = 0, $json = false)
    {
        parent::__construct($data, $status, $headers, $options, $json);
    }

    public function withAlert(AlertType $type, string $content): self
    {
        $this->addData('alerts', [
            $type->value => $content,
        ]);

        return $this;
    }

    public function withMessage(string $content): self
    {
        $this->addData('message', $content);

        return $this;
    }

    public function withContent(mixed $content): self
    {
        $this->addData('content', $content);

        return $this;
    }

    public function withPopup(
        AlertType $type,
        string $content,
        ?string $note = null,
        ?string $link = null,
        ?string $linkText = null
    ): self {
        $popup = [
            'content' => $content,
            'note' => $note,
        ];

        if ($link !== null) {
            $popup['link'] = $link;
            $popup['link_text'] = $linkText;
        }

        $this->addData('popup', [
            $type->value => $popup,
        ]);

        return $this;
    }

    private function addData(string $type, mixed $content): void
    {
        $data = $this->getData(true);

        if (array_key_exists($type, $data)) {
            $data[$type] = array_merge($data[$type], $content);
        } else {
            $data[$type] = $content;
        }

        $this->setData($data);
    }
}
