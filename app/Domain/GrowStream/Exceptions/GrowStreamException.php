<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Exceptions;

/**
 * Base exception for all GrowStream domain errors.
 *
 * Extends \RuntimeException so existing callers that catch the generic
 * exception continue to work while gaining a type-safe catch surface.
 */
class GrowStreamException extends \RuntimeException {}
