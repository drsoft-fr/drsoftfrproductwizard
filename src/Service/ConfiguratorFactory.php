<?php

namespace DrSoftFr\Module\ProductWizard\Service;

use Doctrine\ORM\EntityManagerInterface;
use DrSoftFr\Module\ProductWizard\Application\Dto\ConfiguratorDto;
use DrSoftFr\Module\ProductWizard\Application\Dto\ProductChoiceDto;
use DrSoftFr\Module\ProductWizard\Application\Dto\StepDto;
use DrSoftFr\Module\ProductWizard\Application\Exception\Configurator\ConfiguratorNotFoundException;
use DrSoftFr\Module\ProductWizard\Domain\Entity\Configurator;
use DrSoftFr\Module\ProductWizard\Domain\Entity\ProductChoice;
use DrSoftFr\Module\ProductWizard\Domain\Entity\Step;
use DrSoftFr\Module\ProductWizard\Domain\Exception\ProductChoice\ProductChoiceConstraintException;
use DrSoftFr\Module\ProductWizard\Domain\Exception\Step\StepConstraintException;
use DrSoftFr\Module\ProductWizard\Domain\Repository\ConfiguratorRepositoryInterface;
use DrSoftFr\Module\ProductWizard\Domain\ValueObject\ProductChoice\DisplayCondition;
use DrSoftFr\Module\ProductWizard\Domain\ValueObject\ProductChoice\QuantityRule;
use Doctrine\Common\Collections\ArrayCollection;

final class ConfiguratorFactory
{
    public function __construct(
        private readonly EntityManagerInterface          $em,
        private readonly ConfiguratorRepositoryInterface $configuratorRepository
    )
    {
    }

    /**
     * @throws ConfiguratorNotFoundException
     */
    public function createOrUpdateFromDto(ConfiguratorDto $dto): Configurator
    {
        $isNew = empty($dto->id);

        if (true === $isNew) {
            $configurator = new Configurator();
        } else {
            $configurator = $this->configuratorRepository->find($dto->id);
        }

        if (null === $configurator) {
            throw new ConfiguratorNotFoundException('Configurator not found');
        }

        $configurator->setName($dto->name);
        $configurator->setActive($dto->active);
        $configurator->setDescription($dto->description);
        $configurator->setReduction($dto->reduction);
        $configurator->setReductionTax($dto->reductionTax);
        $configurator->setReductionType($dto->reductionType);

        if (true === $isNew) {
            $this->em->persist($configurator);
        }

        $this->mapSteps($configurator, $dto->steps);

        return $configurator;
    }

    /**
     * @param Configurator $configurator
     * @param StepDto[] $stepsDto
     *
     * @return void
     */
    private function mapSteps(Configurator $configurator, array $stepsDto): void
    {
        $stepsCollection = $configurator->getSteps();
        $processedSteps = new ArrayCollection();

        foreach ($stepsDto as $stepDto) {
            $isNew = !isset($stepDto->id) || str_starts_with($stepDto->id, 'virtual-');

            if (true === $isNew) {
                $step = new Step();
            } else {
                $step = $stepsCollection->filter(fn(Step $s) => (string)$s->getId() === (string)$stepDto->id)->first() ?: null;
            }

            if (null === $step) {
                continue;
            }

            $step->setConfigurator($configurator);
            $step->setLabel($stepDto->label);
            $step->setDescription($stepDto->description);
            $step->setPosition($stepDto->position);
            $step->setActive($stepDto->active);
            $step->setReduction($stepDto->reduction);
            $step->setReductionTax($stepDto->reductionTax);
            $step->setReductionType($stepDto->reductionType);

            if (true === $isNew) {
                $stepsCollection->add($step);
            }

            $processedSteps->add($step);
            $this->mapProductChoices($step, $stepDto->productChoices);
        }

        foreach ($stepsCollection as $step) {
            if (true === $processedSteps->contains($step)) {
                continue;
            }

            $stepsCollection->removeElement($step);
        }
    }

    /**
     * @param Step $step
     * @param ProductChoiceDto[] $choicesDto
     *
     * @return void
     *
     * @throws ProductChoiceConstraintException
     * @throws StepConstraintException
     */
    private function mapProductChoices(Step $step, array $choicesDto): void
    {
        $choicesCollection = $step->getProductChoices();
        $processedChoices = new ArrayCollection();

        foreach ($choicesDto as $choiceDto) {
            $isNew = !isset($choiceDto->id) || str_starts_with($choiceDto->id, 'virtual-');

            if (true === $isNew) {
                $choice = new ProductChoice();
            } else {
                $choice = $choicesCollection->filter(fn(ProductChoice $c) => (string)$c->getId() === (string)$choiceDto->id)->first() ?: null;
            }

            if (null === $choice) {
                continue;
            }

            $choice->setStep($step);
            $choice->setLabel($choiceDto->label);
            $choice->setDescription($choiceDto->description);
            $choice->setProductId($choiceDto->productId);
            $choice->setIsDefault($choiceDto->isDefault);
            $choice->setActive($choiceDto->active);
            $choice->setReduction($choiceDto->reduction);
            $choice->setReductionTax($choiceDto->reductionTax);
            $choice->setReductionType($choiceDto->reductionType);
            $choice->setDisplayConditions(array_map(
                DisplayCondition::fromArray(...),
                $choiceDto->displayConditions
            ));
            $choice->setQuantityRule(QuantityRule::fromArray($choiceDto->quantityRule));

            if (true === $isNew) {
                $choicesCollection->add($choice);
            }

            $processedChoices->add($choice);
        }

        foreach ($choicesCollection as $choice) {
            if (true === $processedChoices->contains($choice)) {
                continue;
            }

            $choicesCollection->removeElement($choice);
        }
    }
}
