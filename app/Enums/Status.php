<?php

namespace App\Enums;

enum Status: int
{
    case OK = 200;
    case CREATED = 201;
    case NO_CONTENT = 204;

    case MOVED_PERMANENTLY = 301;
    case TEMPORARY_REDIRECT = 307;
    case PERMANTENTELY_REDIRECT = 308;

    case BAD_REQUEST = 400;
    case UNAUTHORIZED = 401;
    case FORBIDDEN = 403;
    case NOT_FOUND = 404;
    case UNPROCESSABLE_ENTITY = 422;

    public function statusify(): string
    {
        return match ($this) {
            self::OK, self::CREATED, self::NO_CONTENT, self::MOVED_PERMANENTLY, self::TEMPORARY_REDIRECT, self::PERMANTENTELY_REDIRECT => 'success',
            self::BAD_REQUEST, self::UNAUTHORIZED, self::FORBIDDEN, self::NOT_FOUND, self::UNPROCESSABLE_ENTITY => 'fail'
        };
    }
}
