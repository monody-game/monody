<?php

namespace App\Providers;

use App\Enums\AlertType;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\ServiceProvider;

class ResponseMacroServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        JsonResponse::macro('addData', function (string $type, mixed $content) {
            /** @phpstan-ignore-next-line  */
            $data = $this->getData(true);

            if (array_key_exists($type, $data)) {
                $data[$type] = array_merge($data[$type], $content);
            } else {
                $data[$type] = $content;
            }

            /** @phpstan-ignore-next-line  */
            $this->setData($data);
        });

        JsonResponse::macro('withAlert', function (AlertType $type, string $content) {
            /** @phpstan-ignore-next-line  */
            $this->addData('alerts', [
                $type->value => $content,
            ]);

            return $this;
        });

        JsonResponse::macro(
            'withPopup',
            function (
                AlertType $type,
                string $content,
                ?string $note = null,
                ?string $link = null,
                ?string $linkText = null
            ) {
                $popup = [
                    'content' => $content,
                ];

				if($note !== null) {
					$popup['note'] = $note;
				}

                if ($link !== null) {
                    $popup['link'] = $link;
                    $popup['link_text'] = $linkText;
                }

                /** @phpstan-ignore-next-line  */
                $this->addData('popups', [
                    $type->value => $popup,
                ]);

                return $this;
            }
        );

        JsonResponse::macro('withContent', function (mixed $content) {
            /** @phpstan-ignore-next-line  */
            $this->addData('content', $content);

            return $this;
        });

        JsonResponse::macro('withMessage', function (string $message) {
            /** @phpstan-ignore-next-line  */
            $this->addData('message', $message);

            return $this;
        });
    }
}
