<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\ValueObject\ProductChoice;

final class QuantityRule
{
    public const MODE_NONE = 'none';
    public const MODE_FIXED = 'fixed';
    public const MODE_EXPRESSION = 'expression';
    public const ROUND_NONE = 'none';
    public const ROUND_FLOOR = 'floor';
    public const ROUND_CEIL = 'ceil';
    public const ROUND_ROUND = 'round';
    public const ALLOWED_MODES = [
        self::MODE_NONE,
        self::MODE_FIXED,
        self::MODE_EXPRESSION,
    ];
    public const ALLOWED_ROUNDS = [
        self::ROUND_NONE,
        self::ROUND_FLOOR,
        self::ROUND_CEIL,
        self::ROUND_ROUND,
    ];
    private const DEFAULT_COEFF = 1.0;
    private const DEFAULT_OFFSET = 0;

    private function __construct(
        private string        $mode = self::MODE_NONE,
        private readonly bool $locked = false,
        /** @var array<int, array{step:int, choice:int, coeff:float}> */
        private array         $sources = [],
        private readonly int  $offset = 0,
        private ?int          $min = null,
        private ?int          $max = null,
        private string        $round = self::ROUND_NONE
    )
    {
        $this->mode = $this->sanitizeMode($mode);
        $this->sources = $this->parseSources($sources);
        $this->min = $this->parseNullableInt($min);
        $this->max = $this->parseNullableInt($max);
        $this->round = $this->sanitizeRound($round);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['mode'] ?? self::MODE_NONE,
            (bool)($data['locked'] ?? false),
            $data['sources'] ?? [],
            (int)($data['offset'] ?? self::DEFAULT_OFFSET),
            $data['min'] ?? null,
            $data['max'] ?? null,
            $data['round'] ?? self::ROUND_NONE
        );
    }

    public function getValue(): array
    {
        return [
            'mode' => $this->mode,
            'locked' => $this->locked,
            'sources' => array_values(array_map(
                $this->normalizeSourceForExport(...),
                $this->sources
            )),
            'offset' => $this->offset,
            'min' => $this->min,
            'max' => $this->max,
            'round' => $this->round,
        ];
    }

    /**
     * @param array<string, mixed> $s
     *
     * @return array{step:int, choice:int, coeff:float}
     */
    private function normalizeSourceForExport(array $s): array
    {
        return [
            'step' => (int)$s['step'],
            'choice' => (int)$s['choice'],
            'coeff' => (float)($s['coeff'] ?? self::DEFAULT_COEFF),
        ];
    }

    private function parseNullableInt(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        return is_numeric($value) ? (int)$value : null;
    }

    /**
     * @param array $sources
     *
     * @return array<int, array{step:int, choice:int, coeff:float}>
     */
    private function parseSources(array $sources): array
    {
        $result = [];

        foreach ($sources as $item) {
            if (!is_array($item)) {
                continue;
            }

            if (!isset($item['step'], $item['choice'])) {
                continue;
            }

            $result[] = [
                'step' => (int)$item['step'],
                'choice' => (int)$item['choice'],
                'coeff' => isset($item['coeff']) ? (float)$item['coeff'] : self::DEFAULT_COEFF,
            ];
        }

        return $result;
    }

    private function sanitizeMode(mixed $mode): string
    {
        $mode = (string)$mode;

        return in_array($mode, self::ALLOWED_MODES, true) ? $mode : self::MODE_NONE;
    }

    private function sanitizeRound($round): string
    {
        $round = (string)$round;

        return in_array($round, self::ALLOWED_ROUNDS, true) ? $round : self::ROUND_NONE;
    }
}
