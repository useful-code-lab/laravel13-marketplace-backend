<?php

namespace Domain\Shared\Exceptions;

use RuntimeException;

/**
 * Базовое исключение для всех нарушений бизнес-логики в доменах
 */
class BusinessException extends RuntimeException
{
    // Класс-маркер, инкапсулирующий логику доменных сбоев
}
