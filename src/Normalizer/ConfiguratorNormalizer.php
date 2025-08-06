<?php

namespace DrSoftFr\Module\ProductWizard\Normalizer;

use DrSoftFr\Module\ProductWizard\Dto\ConfiguratorDto;
use DrSoftFr\Module\ProductWizard\Dto\StepDto;
use DrSoftFr\Module\ProductWizard\Dto\ProductChoiceDto;

final class ConfiguratorNormalizer
{
    public function denormalize(array $data): ConfiguratorDto
    {
        $dto = new ConfiguratorDto();
        $dto->id = $data['id'] ?? null;
        $dto->name = $data['name'] ?? '';
        $dto->active = (bool)($data['active'] ?? true);

        foreach ($data['steps'] ?? [] as $stepData) {
            $stepDto = new StepDto();
            $stepDto->id = $stepData['id'] ?? null;
            $stepDto->label = $stepData['label'] ?? '';
            $stepDto->position = (int)($stepData['position'] ?? 0);
            $stepDto->active = (bool)($stepData['active'] ?? true);

            foreach ($stepData['product_choices'] ?? [] as $choiceData) {
                $choiceDto = new ProductChoiceDto();
                $choiceDto->id = $choiceData['id'] ?? null;
                $choiceDto->label = $choiceData['label'] ?? '';
                $choiceDto->productId = (int)($choiceData['product_id'] ?? null);
                $choiceDto->isDefault = (bool)($choiceData['is_default'] ?? false);
                $choiceDto->allowQuantity = (bool)($choiceData['allow_quantity'] ?? true);
                $choiceDto->forcedQuantity = isset($choiceData['forced_quantity']) ? (int)$choiceData['forced_quantity'] : null;
                $choiceDto->active = (bool)($choiceData['active'] ?? true);
                $choiceDto->displayConditions = $choiceData['display_conditions'] ?? [];

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
            'active' => $dto->active,
            'steps' => array_map(fn(StepDto $s) => [
                'id' => $s->id,
                'label' => $s->label,
                'position' => $s->position,
                'active' => $s->active,
                'product_choices' => array_map(fn(ProductChoiceDto $c) => [
                    'id' => $c->id,
                    'label' => $c->label,
                    'product_id' => $c->productId,
                    'is_default' => $c->isDefault,
                    'allow_quantity' => $c->allowQuantity,
                    'forced_quantity' => $c->forcedQuantity,
                    'active' => $c->active,
                    'display_conditions' => $c->displayConditions,
                ], $s->productChoices),
            ], $dto->steps),
        ];
    }
}
