<?php

namespace DrSoftFr\Module\ProductWizard\Shared\Logging;

use PrestaShop\PrestaShop\Adapter\LegacyLogger;
use Throwable;

final class ErrorLogger
{
    private const PATTERN = 'drsoftfrproductwizard - %s - Throwable #%d - %s.';

    /**
     * Handles an exception by enriching the context and logging the error using the provided logger.
     *
     * @param Throwable $throwable The exception instance to be processed and logged.
     * @param LegacyLogger $logger The logger instance used to log the error details.
     * @param array $context An optional array of context values to be included in the error log.
     *
     * @return void
     */
    public static function exception(Throwable $throwable, LegacyLogger $logger, array $context = []): void
    {
        $ctx = [
            'caller' => '',
            'object_type' => ($context['object_type'] ?? null),
            'object_id' => ($context['object_id'] ?? null),
            'allow_duplicate' => ($context['allow_duplicate'] ?? false),
            'exception' => $throwable,
            'error_code' => $throwable->getCode(),
        ];
        $ctx = self::enrich($ctx);

        $logger->error(self::fromContext($ctx), $ctx);
    }

    /**
     * Enriches the provided context array with additional debugging information.
     *
     * @param array $context An array of context values to be enriched, which may contain debug metadata.
     *
     * @return array The enriched context array, including additional caller information if not already set.
     */
    private static function enrich(array $context): array
    {
        if (false === empty($context['caller'])) {
            return $context;
        }

        $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        $frame = $bt[1] ?? null;

        if (null === $frame) {
            return $context;
        }

        $fn = ($frame['class'] ?? '') . ($frame['class'] ? '::' : '') . ($frame['function'] ?? '');
        $ln = $frame['line'] ?? null;
        $context['caller'] = $ln ? "{$fn}:{$ln}" : $fn;

        return $context;
    }

    /**
     * Constructs a formatted string based on the provided context.
     *
     * @param array $context An associative array containing keys 'caller', 'error_code', and 'exception'.
     *                       'caller' is the source or origin of the call,
     *                       'error_code' is a specific code related to the error,
     *                       and 'exception' is an object implementing Throwable interface.
     *
     * @return string A formatted string derived from the context information.
     */
    private static function fromContext(array $context): string
    {
        return sprintf(self::PATTERN, $context['caller'], $context['error_code'], $context['exception']->getMessage());
    }

    /**
     * Creates an error message using the given method, line number and throwable object.
     *
     * @param string $method The name of the method where the error occurred.
     * @param int $line The line number where the error occurred.
     * @param Throwable $t The throwable object representing the error.
     *
     * @return string The formatted error message.
     */
    public static function fromThrowable(string $method, int $line, Throwable $t): string
    {
        return sprintf(self::PATTERN, "{$method}:{$line}", $t->getCode(), $t->getMessage());
    }
}
