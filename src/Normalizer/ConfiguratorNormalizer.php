<?php

namespace DrSoftFr\Module\ProductWizard\Normalizer;

use DrSoftFr\Module\ProductWizard\Dto\ConfiguratorDto;
use DrSoftFr\Module\ProductWizard\Dto\StepDto;
use DrSoftFr\Module\ProductWizard\Dto\ProductChoiceDto;
use DrSoftFr\Module\ProductWizard\Exception\ProductChoice\ProductChoiceConstraintException;
use DrSoftFr\Module\ProductWizard\Exception\Step\StepConstraintException;
use DrSoftFr\Module\ProductWizard\ValueObject\ProductChoice\DisplayCondition;
use DrSoftFr\Module\ProductWizard\ValueObject\ProductChoice\QuantityRule;

final class ConfiguratorNormalizer
{
    /**
     * @throws ProductChoiceConstraintException
     * @throws StepConstraintException
     */
    public function denormalize(array $data): ConfiguratorDto
    {
        $dto = new ConfiguratorDto();
        $dto->id = $data['id'] ?? null;
        $dto->name = $data['name'] ?? '';
        $dto->description = false === empty($data['description']) ? $this->sanitizeHtml($data['description']) : null;
        $dto->active = (bool)($data['active'] ?? true);
        $dto->reduction = (float)($data['reduction'] ?? 0);
        $dto->reductionTax = (bool)($data['reduction_tax'] ?? true);
        $dto->reductionType = (string)($data['reduction_type'] ?? 'amount');

        foreach ($data['steps'] ?? [] as $stepData) {
            $stepDto = new StepDto();
            $stepDto->id = $stepData['id'] ?? null;
            $stepDto->label = $stepData['label'] ?? '';
            $stepDto->description = false === empty($stepData['description']) ? $this->sanitizeHtml($stepData['description']) : null;
            $stepDto->position = (int)($stepData['position'] ?? 0);
            $stepDto->active = (bool)($stepData['active'] ?? true);
            $stepDto->reduction = (float)($stepData['reduction'] ?? 0);
            $stepDto->reductionTax = (bool)($stepData['reduction_tax'] ?? true);
            $stepDto->reductionType = (string)($stepData['reduction_type'] ?? 'amount');

            foreach ($stepData['product_choices'] ?? [] as $choiceData) {
                $choiceDto = new ProductChoiceDto();
                $choiceDto->id = $choiceData['id'] ?? null;
                $choiceDto->label = $choiceData['label'] ?? '';
                $choiceDto->description = false === empty($choiceData['description']) ? $this->sanitizeHtml($choiceData['description']) : null;
                $choiceDto->productId = false === empty($choiceData['product_id']) ? (int)$choiceData['product_id'] : null;
                $choiceDto->isDefault = (bool)($choiceData['is_default'] ?? false);
                $choiceDto->active = (bool)($choiceData['active'] ?? true);
                $choiceDto->reduction = (float)($choiceData['reduction'] ?? 0);
                $choiceDto->reductionTax = (bool)($choiceData['reduction_tax'] ?? true);
                $choiceDto->reductionType = (string)($choiceData['reduction_type'] ?? 'amount');
                $choiceDto->quantityRule = QuantityRule::fromArray(
                    isset($choiceData['quantity_rule']) && is_array($choiceData['quantity_rule'])
                        ? $choiceData['quantity_rule']
                        : []
                )
                    ->getValue();

                foreach ($choiceData['display_conditions'] ?? [] as $dcData) {
                    $choiceDto->displayConditions[] = DisplayCondition::fromArray($dcData)->getValue();
                }

                $stepDto->productChoices[] = $choiceDto;
            }

            $dto->steps[] = $stepDto;
        }

        return $dto;
    }

    public function normalize(ConfiguratorDto $dto): array
    {
        return [
            'id' => $dto->id,
            'name' => $dto->name,
            'description' => $dto->description,
            'active' => $dto->active,
            'reduction' => $dto->reduction,
            'reduction_tax' => $dto->reductionTax,
            'reduction_type' => $dto->reductionType,
            'steps' => array_map(fn(StepDto $s) => [
                'id' => $s->id,
                'label' => $s->label,
                'description' => $s->description,
                'position' => $s->position,
                'active' => $s->active,
                'reduction' => $s->reduction,
                'reduction_tax' => $s->reductionTax,
                'reduction_type' => $s->reductionType,
                'product_choices' => array_map(fn(ProductChoiceDto $c) => [
                    'id' => $c->id,
                    'label' => $c->label,
                    'description' => $c->description,
                    'product_id' => $c->productId,
                    'is_default' => $c->isDefault,
                    'active' => $c->active,
                    'reduction' => $c->reduction,
                    'reduction_tax' => $c->reductionTax,
                    'reduction_type' => $c->reductionType,
                    'quantity_rule' => $c->quantityRule,
                    'display_conditions' => $c->displayConditions,
                ], $s->productChoices),
            ], $dto->steps),
        ];
    }

    /**
     * Sanitizes the given HTML input by removing or neutralizing potentially harmful content.
     *
     * @param string|null $value The HTML input to sanitize. Can be null or an empty string.
     *
     * @return string|null The sanitized HTML string, or null if the input was null or an empty string.
     */
    private function sanitizeHtml(?string $value): ?string
    {
        if (null === $value) {
            return null;
        }

        $raw = trim((string)$value);

        // Considers content that does not contain visible text (tags alone, spaces, &nbsp;, etc.) to be “empty.”
        if ($this->isHtmlVisuallyEmpty($raw)) {
            return null;
        }

        $clean = \Tools::purifyHTML($raw, null, true);

        // Revalidate after purification to handle cases such as <p><br></p>, &nbsp;, etc.
        if ($this->isHtmlVisuallyEmpty($clean)) {
            return null;
        }

        return $clean;
    }

    /**
     * Indicates whether HTML is visually empty (only tags, spaces, NBSP, or zero-width characters).
     */
    private function isHtmlVisuallyEmpty(string $html): bool
    {
        if ('' === $html) {
            return true;
        }

        // Normalize NBSPs and zero-width characters
        $normalized = preg_replace('/\x{00A0}|\x{200B}|\x{200C}|\x{200D}|\x{FEFF}|&nbsp;/u', ' ', $html);

        // Remove tags and decode HTML entities to keep only visible text
        $text = trim(html_entity_decode(strip_tags((string)$normalized), ENT_QUOTES | ENT_HTML5));

        return '' === $text;
    }
}
