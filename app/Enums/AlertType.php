<?php

namespace App\Enums;

enum AlertType: string
{
    case Success = 'success';
    case Info = 'info';
    case Warn = 'warn';
    case Error = 'error';
}
