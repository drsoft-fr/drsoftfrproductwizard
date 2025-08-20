<?php

namespace DrSoftFr\Module\ProductWizard\Normalizer;

use DrSoftFr\Module\ProductWizard\Dto\ConfiguratorDto;
use DrSoftFr\Module\ProductWizard\Dto\DisplayConditionDto;
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
        $dto->reduction = (float)($data['reduction'] ?? 0);
        $dto->reductionTax = (bool)($data['reduction_tax'] ?? true);
        $dto->reductionType = (string)($data['reduction_type'] ?? 'amount');

        foreach ($data['steps'] ?? [] as $stepData) {
            $stepDto = new StepDto();
            $stepDto->id = $stepData['id'] ?? null;
            $stepDto->label = $stepData['label'] ?? '';
            $stepDto->position = (int)($stepData['position'] ?? 0);
            $stepDto->active = (bool)($stepData['active'] ?? true);
            $stepDto->reduction = (float)($stepData['reduction'] ?? 0);
            $stepDto->reductionTax = (bool)($stepData['reduction_tax'] ?? true);
            $stepDto->reductionType = (string)($stepData['reduction_type'] ?? 'amount');

            foreach ($stepData['product_choices'] ?? [] as $choiceData) {
                $choiceDto = new ProductChoiceDto();
                $choiceDto->id = $choiceData['id'] ?? null;
                $choiceDto->label = $choiceData['label'] ?? '';
                $choiceDto->productId = false === empty($choiceData['product_id']) ? (int)$choiceData['product_id'] : null;
                $choiceDto->isDefault = (bool)($choiceData['is_default'] ?? false);
                $choiceDto->allowQuantity = (bool)($choiceData['allow_quantity'] ?? true);
                $choiceDto->forcedQuantity = false === empty($choiceData['forced_quantity']) ? (int)$choiceData['forced_quantity'] : null;
                $choiceDto->active = (bool)($choiceData['active'] ?? true);
                $choiceDto->reduction = (float)($choiceData['reduction'] ?? 0);
                $choiceDto->reductionTax = (bool)($choiceData['reduction_tax'] ?? true);
                $choiceDto->reductionType = (string)($choiceData['reduction_type'] ?? 'amount');

                foreach ($choiceData['display_conditions'] ?? [] as $dcData) {
                    $dcDto = new DisplayConditionDto();
                    $dcDto->step = $dcData['step'] ?? null;
                    $dcDto->choice = $dcData['choice'] ?? null;

                    $choiceDto->displayConditions[] = $dcDto;
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
            'active' => $dto->active,
            'reduction' => $dto->reduction,
            'reduction_tax' => $dto->reductionTax,
            'reduction_type' => $dto->reductionType,
            'steps' => array_map(fn(StepDto $s) => [
                'id' => $s->id,
                'label' => $s->label,
                'position' => $s->position,
                'active' => $s->active,
                'reduction' => $s->reduction,
                'reduction_tax' => $s->reductionTax,
                'reduction_type' => $s->reductionType,
                'product_choices' => array_map(fn(ProductChoiceDto $c) => [
                    'id' => $c->id,
                    'label' => $c->label,
                    'product_id' => $c->productId,
                    'is_default' => $c->isDefault,
                    'allow_quantity' => $c->allowQuantity,
                    'forced_quantity' => $c->forcedQuantity,
                    'active' => $c->active,
                    'reduction' => $c->reduction,
                    'reduction_tax' => $c->reductionTax,
                    'reduction_type' => $c->reductionType,
                    'display_conditions' => array_map(fn(DisplayConditionDto $dc) => [
                        'step' => $dc->step,
                        'choice' => $dc->choice,
                    ], $c->displayConditions)
                ], $s->productChoices),
            ], $dto->steps),
        ];
    }
}
