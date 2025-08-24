<?php

namespace DrSoftFr\Module\ProductWizard\Service;

use DrSoftFr\Module\ProductWizard\Dto\ConfiguratorDto;
use DrSoftFr\Module\ProductWizard\Dto\ProductChoiceDto;
use DrSoftFr\Module\ProductWizard\Dto\StepDto;
use DrSoftFr\Module\ProductWizard\Exception\Configurator\ConfiguratorConstraintException;
use DrSoftFr\Module\ProductWizard\Exception\ProductChoice\ProductChoiceConstraintException;
use DrSoftFr\Module\ProductWizard\Exception\Step\StepConstraintException;
use DrSoftFr\Module\ProductWizard\ValueObject\ProductChoice\QuantityRule;
use Exception;

final class ConfiguratorValidatorService
{
    /**
     * Validates the provided configurator data transfer object.
     *
     * @param ConfiguratorDto $dto The configurator data transfer object to be validated.
     *
     * @return void
     *
     * @throws ConfiguratorConstraintException If the configurator data does not meet the required constraints.
     * @throws ProductChoiceConstraintException
     * @throws StepConstraintException
     */
    public function validate(
        ConfiguratorDto $dto
    ): void
    {
        $this->validateConfigurator($dto);
    }

    /**
     * Validates the configurator object to ensure it meets the required constraints.
     *
     * @param ConfiguratorDto $dto The configurator data transfer object to be validated.
     *
     * @return void
     *
     * @throws ConfiguratorConstraintException If the name is empty or invalid.
     * @throws ProductChoiceConstraintException
     * @throws StepConstraintException
     * @throws Exception
     */
    private function validateConfigurator(
        ConfiguratorDto $dto
    ): void
    {
        if (true === empty(trim($dto->name ?? ''))) {
            throw new ConfiguratorConstraintException(
                'The name of the scenario is mandatory.',
                ConfiguratorConstraintException::INVALID_NAME
            );
        }

        $this->validateSteps(
            $dto->steps,
            $dto
        );

        $this->validateReduction($dto->reduction, $dto->reductionTax, $dto->reductionType, 'Configurator', new ConfiguratorConstraintException);
    }

    /**
     * Validates the array of steps and ensures that all provided steps meet the required constraints.
     *
     * @param array $steps An array of step objects to be validated.
     * @param ConfiguratorDto $configuratorDto The configurator data transfer object containing context for validation.
     *
     * @return void
     *
     * @throws ConfiguratorConstraintException If the steps array is empty.
     * @throws ProductChoiceConstraintException
     * @throws StepConstraintException If the steps' positions are not continuous starting from 0.
     */
    private function validateSteps(
        array           $steps,
        ConfiguratorDto $configuratorDto
    ): void
    {
        if (0 === count($steps)) {
            throw new ConfiguratorConstraintException(
                'At least one step is required.',
                ConfiguratorConstraintException::INVALID_STEPS
            );
        }

        $positions = [];

        /** @var StepDto $step */
        foreach ($steps as $idx => $step) {
            $position = (int)($step->position ?? -1);
            $positions[] = $position;

            $this->validateStep(
                $step,
                $position,
                $idx,
                $configuratorDto
            );
        }

        if (false === empty($positions)) {
            sort($positions);

            foreach ($positions as $idx => $p) {
                if ($p === $idx) {
                    continue;
                }

                throw new StepConstraintException(
                    'The positions of the steps must be continuous starting from 0 (no gaps).',
                    StepConstraintException::INVALID_POSITION
                );
            }
        }
    }

    /**
     * Validates a step's configuration. Ensures that the step has a valid label,
     * a valid position, and that its product choices are properly validated.
     *
     * @param StepDto $dto The data transfer object containing step information being validated.
     * @param int $position The position of the step within the configurator.
     * @param int $idx The index of the step being processed.
     * @param ConfiguratorDto $configuratorDto The configurator data containing all steps and choices.
     *
     * @return void Throws StepConstraintException if validation fails.
     *
     * @throws ProductChoiceConstraintException
     * @throws StepConstraintException
     * @throws Exception
     */
    private function validateStep(
        StepDto         $dto,
        int             $position,
        int             $idx,
        ConfiguratorDto $configuratorDto
    ): void
    {
        if (true === empty(trim($dto->label ?? ''))) {
            throw new StepConstraintException(
                sprintf('Step #%d: The wording is mandatory.', $idx + 1),
                StepConstraintException::INVALID_LABEL
            );
        }

        if (0 > $position) {
            throw new StepConstraintException(
                sprintf('Step “%s”: Invalid position.', $dto->label ?: (string)($idx + 1)),
                StepConstraintException::INVALID_POSITION
            );
        }

        $this->validateChoices(
            $dto->productChoices,
            $dto,
            $configuratorDto
        );

        $this->validateReduction($dto->reduction, $dto->reductionTax, $dto->reductionType, sprintf('Step "%s"', $dto->label), new StepConstraintException);
    }

    /**
     * Validates product choices within a step configuration. Ensures that at least
     * one choice exists, no more than one choice is marked as default, and each choice
     * is validated individually.
     *
     * @param array $choices The list of product choices to validate.
     * @param StepDto $stepDto The current step data being evaluated.
     * @param ConfiguratorDto $configuratorDto The configurator data containing all steps and choices.
     *
     * @return void Throws exceptions if validation of choices or default constraints fails.
     *
     * @throws StepConstraintException If there are no product choices provided.
     * @throws ProductChoiceConstraintException If more than one choice is marked as the default.
     */
    private function validateChoices(
        array           $choices,
        StepDto         $stepDto,
        ConfiguratorDto $configuratorDto
    ): void
    {
        if (0 === count($choices)) {
            throw new StepConstraintException(
                'At least one product choice is required.',
                StepConstraintException::INVALID_PRODUCT_CHOICES
            );
        }

        $defaultCount = 0;

        /** @var ProductChoiceDto $choice */
        foreach ($choices as $cIdx => $choice) {
            if (false === empty($choice->isDefault)) {
                $defaultCount++;
            }

            $this->validateChoice(
                $choice,
                $cIdx,
                $stepDto,
                $configuratorDto
            );
        }

        if (1 < $defaultCount) {
            throw new ProductChoiceConstraintException(
                sprintf('Step “%s”: There can only be one default choice.', $stepDto->label),
                ProductChoiceConstraintException::INVALID_IS_DEFAULT
            );
        }
    }

    /**
     * Validates a product choice within a step configuration. Ensures that the
     * label is provided, quantity constraints are valid, and display conditions
     * are correctly defined.
     *
     * @param ProductChoiceDto $dto The product choice data being validated.
     * @param int $cIdx The index of the product choice being validated.
     * @param StepDto $stepDto The current step data being evaluated.
     * @param ConfiguratorDto $configuratorDto The configurator data containing all steps and choices.
     *
     * @return void Throws ProductChoiceConstraintException if validation fails.
     *
     * @throws ProductChoiceConstraintException
     * @throws Exception
     */
    private function validateChoice(
        ProductChoiceDto $dto,
        int              $cIdx,
        StepDto          $stepDto,
        ConfiguratorDto  $configuratorDto
    ): void
    {
        if (true === empty(trim($dto->label ?? ''))) {
            throw new ProductChoiceConstraintException(
                sprintf('Step “%s”: The label for choice #%d is required.', $stepDto->label, $cIdx + 1),
                ProductChoiceConstraintException::INVALID_LABEL
            );
        }

        $this->validateDisplayConditions(
            $dto->displayConditions,
            $stepDto,
            $configuratorDto
        );
        $this->validateQuantityRule($dto, $cIdx, $stepDto, $configuratorDto);
        $this->validateReduction($dto->reduction, $dto->reductionTax, $dto->reductionType, sprintf('Step "%s" choice "%s"', $stepDto->label, $dto->label), new ProductChoiceConstraintException);
    }

    /**
     * Validates the quantity rule of a product choice and ensures all related constraints are met.
     *
     * @param ProductChoiceDto $dto The product choice data transfer object containing the quantity rule.
     * @param int $cIdx The index of the product choice within the current step.
     * @param StepDto $stepDto The step data transfer object containing the product choice.
     * @param ConfiguratorDto $configuratorDto The configurator data transfer object providing the context for validation.
     *
     * @return void
     *
     * @throws ProductChoiceConstraintException If the quantity rule is missing or invalid.
     */
    private function validateQuantityRule(
        ProductChoiceDto $dto,
        int              $cIdx,
        StepDto          $stepDto,
        ConfiguratorDto  $configuratorDto
    ): void
    {
        if (true === empty($dto->quantityRule)) {
            throw new ProductChoiceConstraintException(
                sprintf('Step "%s": Choice "%s" quantity rule is required.', $stepDto->label, $dto->label ?: ('#' . ($cIdx + 1))),
                ProductChoiceConstraintException::INVALID_QUANTITY_RULE
            );
        }

        $qr = $dto->quantityRule;

        $this->validateQuantityRuleMode($qr, $dto, $cIdx, $stepDto);
        $this->validateQuantityRuleLocked($qr, $dto, $cIdx, $stepDto);
        $this->validateQuantityRuleRound($qr, $dto, $cIdx, $stepDto);
        $this->validateQuantityRuleOffset($qr, $dto, $cIdx, $stepDto);
        $this->validateQuantityRuleMinAndMax($qr, $dto, $cIdx, $stepDto);
        $this->validateQuantityRuleSources($qr, $dto, $cIdx, $stepDto, $configuratorDto);
    }

    /**
     * Validates the quantity rule mode to ensure it adheres to the allowable modes.
     *
     * @param array $qr An array containing quantity rule data, including the mode to be validated.
     * @param ProductChoiceDto $dto The product choice data transfer object providing context for validation.
     * @param int $cIdx The index of the current choice in the sequence.
     * @param StepDto $stepDto The step data transfer object providing additional context for validation.
     *
     * @return void
     *
     * @throws ProductChoiceConstraintException If the mode is not in the list of allowed quantity rule modes.
     */
    private function validateQuantityRuleMode(
        array            $qr,
        ProductChoiceDto $dto,
        int              $cIdx,
        StepDto          $stepDto
    ): void
    {
        if (false === in_array($qr['mode'], QuantityRule::ALLOWED_MODES)) {
            throw new ProductChoiceConstraintException(
                sprintf('Step “%s”: Choice “%s”, invalid mode.', $stepDto->label, $dto->label ?: ('#' . ($cIdx + 1))),
                ProductChoiceConstraintException::INVALID_QUANTITY_RULE_MODE
            );
        }

        if (null !== $dto->productId && QuantityRule::MODE_NONE === $qr['mode']) {
            throw new ProductChoiceConstraintException(
                sprintf('Step “%s”: Choice “%s”, the mode cannot be NONE when a product is assigned.', $stepDto->label, $dto->label ?: ('#' . ($cIdx + 1))),
                ProductChoiceConstraintException::INVALID_QUANTITY_RULE_MODE
            );
        }
    }

    /**
     * Validates the quantity rule locking constraints for a given choice within a step.
     *
     * @param array $qr The quantity rule data for the choice, including 'locked' status and 'mode'.
     * @param ProductChoiceDto $dto The product choice data transfer object associated with the validation context.
     * @param int $cIdx The index of the current product choice within the step.
     * @param StepDto $stepDto The step data transfer object containing context for validation.
     *
     * @return void
     *
     * @throws ProductChoiceConstraintException If the 'locked' value is not a valid boolean,
     *                                          or if the 'locked' constraint is invalid based on the 'mode'.
     */
    private function validateQuantityRuleLocked(
        array            $qr,
        ProductChoiceDto $dto,
        int              $cIdx,
        StepDto          $stepDto
    ): void
    {
        if (false === is_bool($qr['locked'])) {
            throw new ProductChoiceConstraintException(
                sprintf('Step “%s”: Choice “%s”, is not a valid boolean.', $stepDto->label, $dto->label ?: ('#' . ($cIdx + 1))),
                ProductChoiceConstraintException::INVALID_QUANTITY_RULE_LOCKED
            );
        }

        if (true === $qr['locked'] && QuantityRule::MODE_NONE === $qr['mode']) {
            throw new ProductChoiceConstraintException(
                sprintf('Step “%s”: Choice “%s”, in NONE mode, locked should not be defined.', $stepDto->label, $dto->label ?: ('#' . ($cIdx + 1))),
                ProductChoiceConstraintException::INVALID_QUANTITY_RULE_LOCKED
            );
        }

        if (false === $qr['locked'] && QuantityRule::MODE_EXPRESSION === $qr['mode']) {
            throw new ProductChoiceConstraintException(
                sprintf('Step “%s”: Choice “%s”, in EXPRESSION mode, locked should be defined.', $stepDto->label, $dto->label ?: ('#' . ($cIdx + 1))),
                ProductChoiceConstraintException::INVALID_QUANTITY_RULE_LOCKED
            );
        }
    }

    /**
     * Validates the provided quantity rule round and ensures it meets defined constraints.
     *
     * @param array $qr An associative array representing the quantity rule parameters.
     * @param ProductChoiceDto $dto The product choice data transfer object containing context for validation.
     * @param int $cIdx The index of the current product choice for context in error messages.
     * @param StepDto $stepDto The step data transfer object containing the current step's context.
     *
     * @return void
     *
     * @throws ProductChoiceConstraintException If the quantity rule round is invalid or conflicts with the mode.
     */
    private function validateQuantityRuleRound(
        array            $qr,
        ProductChoiceDto $dto,
        int              $cIdx,
        StepDto          $stepDto
    ): void
    {
        if (false === in_array($qr['round'], QuantityRule::ALLOWED_ROUNDS)) {
            throw new ProductChoiceConstraintException(
                sprintf('Step “%s”: Choice “%s”, invalid round.', $stepDto->label, $dto->label ?: ('#' . ($cIdx + 1))),
                ProductChoiceConstraintException::INVALID_QUANTITY_RULE_ROUND
            );
        }

        if (QuantityRule::ROUND_NONE !== $qr['round'] && QuantityRule::MODE_EXPRESSION !== $qr['mode']) {
            throw new ProductChoiceConstraintException(
                sprintf('Step “%s”: Choice “%s”, in FIXED or NONE mode, round should be NONE.', $stepDto->label, $dto->label ?: ('#' . ($cIdx + 1))),
                ProductChoiceConstraintException::INVALID_QUANTITY_RULE_ROUND
            );
        }
    }

    /**
     * Validates the minimum and maximum quantity rules for a product choice in a specific step.
     *
     * @param array $qr The quantity rule array containing 'min', 'max', and 'locked' keys.
     * @param ProductChoiceDto $dto The data transfer object representing the product choice.
     * @param int $cIdx The index of the current choice in the step.
     * @param StepDto $stepDto The data transfer object representing the step.
     *
     * @return void
     *
     * @throws ProductChoiceConstraintException If the minimum quantity is invalid.
     * @throws ProductChoiceConstraintException If the maximum quantity is invalid.
     * @throws ProductChoiceConstraintException If the minimum quantity is greater than the maximum quantity.
     */
    private function validateQuantityRuleMinAndMax(
        array            $qr,
        ProductChoiceDto $dto,
        int              $cIdx,
        StepDto          $stepDto
    ): void
    {
        $minQ = $qr['min'];
        $maxQ = $qr['max'];

        if (true === $qr['locked'] && (null !== $minQ || null !== $maxQ)) {
            if (null !== $minQ) {
                throw new ProductChoiceConstraintException(
                    sprintf('Step “%s”: Since the quantity is blocked for the choice “%s”, the minimum quantity must be empty.', $stepDto->label, $dto->label ?: ('#' . ($cIdx + 1))),
                    ProductChoiceConstraintException::INVALID_QUANTITY_RULE_MIN
                );
            }

            if (null !== $maxQ) {
                throw new ProductChoiceConstraintException(
                    sprintf('Step “%s”: Since the quantity is blocked for the choice “%s”, the maximal quantity must be empty.', $stepDto->label, $dto->label ?: ('#' . ($cIdx + 1))),
                    ProductChoiceConstraintException::INVALID_QUANTITY_RULE_MAX
                );
            }
        }

        if ('fixed' !== $qr['mode'] && (null !== $minQ || null !== $maxQ)) {
            throw new ProductChoiceConstraintException(
                sprintf('Step “%s”: Choice “%s”, the minimum and maximum quantities can only be set in “FIXED” mode.', $stepDto->label, $dto->label ?: ('#' . ($cIdx + 1))),
                ProductChoiceConstraintException::INVALID_QUANTITY_RULE_MIN_MAX_LOGIC
            );
        }

        if (null !== $minQ && (!is_int($minQ) || $minQ < 1)) {
            throw new ProductChoiceConstraintException(
                sprintf('Step “%s”: The choice “%s” has an invalid minimal quantity (integer >= 1 required).', $stepDto->label, $dto->label ?: ('#' . ($cIdx + 1))),
                ProductChoiceConstraintException::INVALID_QUANTITY_RULE_MIN
            );
        }

        if (null !== $maxQ && (!is_int($maxQ) || $maxQ < 1)) {
            throw new ProductChoiceConstraintException(
                sprintf('Step “%s”: The choice “%s” has an invalid maximal quantity (integer >= 1 required).', $stepDto->label, $dto->label ?: ('#' . ($cIdx + 1))),
                ProductChoiceConstraintException::INVALID_QUANTITY_RULE_MAX
            );
        }

        if (null !== $minQ && null !== $maxQ && $minQ > $maxQ) {
            throw new ProductChoiceConstraintException(
                sprintf('Step “%s”: The choice “%s” minimal quantity cannot be greater than maximal quantity.', $stepDto->label, $dto->label ?: ('#' . ($cIdx + 1))),
                ProductChoiceConstraintException::INVALID_QUANTITY_RULE_MIN_MAX_LOGIC
            );
        }
    }

    /**
     * Validates the quantity rule offset of a product choice and ensures it meets the required constraints.
     *
     * @param array $qr The quantity rule data containing attributes such as mode, locked status, and offset.
     * @param ProductChoiceDto $dto The product choice data transfer object containing context for validation.
     * @param int $cIdx The index of the product choice within the current context.
     * @param StepDto $stepDto The step data transfer object providing information about the current step.
     *
     * @return void
     *
     * @throws ProductChoiceConstraintException If the quantity rule offset is invalid.
     */
    private function validateQuantityRuleOffset(
        array            $qr,
        ProductChoiceDto $dto,
        int              $cIdx,
        StepDto          $stepDto
    ): void
    {
        $minQ = $qr['min'];
        $maxQ = $qr['max'];

        if (QuantityRule::MODE_NONE === $qr['mode'] && 0 !== (int)$qr['offset']) {
            throw new ProductChoiceConstraintException(
                sprintf('Step “%s”: The choice “%s” is in NONE mode, but the quantity selection is enabled.', $stepDto->label, $dto->label ?: ('#' . ($cIdx + 1))),
                ProductChoiceConstraintException::INVALID_QUANTITY_RULE_OFFSET
            );
        }

        if (null !== $minQ && $minQ > $qr['offset']) {
            throw new ProductChoiceConstraintException(
                sprintf('Step “%s”: The choice “%s” must have a valid imposed quantity (offset) (integer >= min).', $stepDto->label, $dto->label ?: ('#' . ($cIdx + 1))),
                ProductChoiceConstraintException::INVALID_QUANTITY_RULE_OFFSET
            );
        }

        if (null !== $maxQ && $maxQ < $qr['offset']) {
            throw new ProductChoiceConstraintException(
                sprintf('Step “%s”: The choice “%s” must have a valid imposed quantity (offset) (integer <= max).', $stepDto->label, $dto->label ?: ('#' . ($cIdx + 1))),
                ProductChoiceConstraintException::INVALID_QUANTITY_RULE_OFFSET
            );
        }

        if (true === $qr['locked'] && QuantityRule::MODE_FIXED === $qr['mode'] && (!is_int($qr['offset']) || $qr['offset'] < 1)) {
            throw new ProductChoiceConstraintException(
                sprintf('Step “%s”: The choice “%s” must have a valid imposed quantity (offset) (integer >= 1) when quantity selection is disabled.', $stepDto->label, $dto->label ?: ('#' . ($cIdx + 1))),
                ProductChoiceConstraintException::INVALID_QUANTITY_RULE_OFFSET
            );
        }
    }

    /**
     * Validates the quantity rule sources for a given product choice, ensuring their consistency and correctness.
     *
     * @param array $qr An array containing the quantity rule data, including the sources to validate.
     * @param ProductChoiceDto $dto The product choice data transfer object being evaluated.
     * @param int $cIdx The index of the current product choice in the step.
     * @param StepDto $stepDto The step data transfer object containing the current step context.
     * @param ConfiguratorDto $configuratorDto The configurator data transfer object containing the overall configuration.
     *
     * @return void
     *
     * @throws ProductChoiceConstraintException If any source has missing or invalid references, references a non-existent or invalid step/choice, or contains duplicate sources.
     */
    private function validateQuantityRuleSources(
        array            $qr,
        ProductChoiceDto $dto,
        int              $cIdx,
        StepDto          $stepDto,
        ConfiguratorDto  $configuratorDto
    ): void
    {
        if ($qr['mode'] !== 'expression' && false === empty($qr['sources'])) {
            // Fixed: no sources allowed
            throw new ProductChoiceConstraintException(
                sprintf('Step "%s": Choice "%s" quantity rule in FIXED or NONE mode must not define any sources.', $stepDto->label, $dto->label ?: ('#' . ($cIdx + 1))),
                ProductChoiceConstraintException::INVALID_QUANTITY_RULE_SOURCES
            );
        } elseif ($qr['mode'] === 'expression' && true === empty($qr['sources'])) {
            // Expression: at least one source
            throw new ProductChoiceConstraintException(
                sprintf('Step "%s": Choice "%s" quantity rule in EXPRESSION mode must define at least one source.', $stepDto->label, $dto->label ?: ('#' . ($cIdx + 1))),
                ProductChoiceConstraintException::INVALID_QUANTITY_RULE_SOURCES
            );
        }

        $seen = [];

        foreach ($qr['sources'] as $idxSrc => $src) {
            $refStepId = $src['step'] ?? null;

            if (empty($refStepId)) {
                throw new ProductChoiceConstraintException(
                    sprintf('Step "%s": Choice "%s" quantity rule source #%d is invalid.', $stepDto->label, $dto->label ?: ('#' . ($cIdx + 1)), $idxSrc + 1),
                    ProductChoiceConstraintException::INVALID_QUANTITY_RULE_SOURCES
                );
            }

            // Locate referenced step
            $refStep = null;

            foreach ($configuratorDto->steps as $s) {
                if ((string)$s->id !== (string)$refStepId) {
                    continue;
                }

                $refStep = $s;

                break;
            }

            if ($refStep === null) {
                throw new ProductChoiceConstraintException(
                    sprintf('Step "%s": Quantity rule source #%d references a non-existent step.', $stepDto->label, $idxSrc + 1),
                    ProductChoiceConstraintException::INVALID_QUANTITY_RULE_SOURCES
                );
            }

            // Must be previous step strictly
            if ((int)$refStep->position >= (int)$stepDto->position) {
                throw new ProductChoiceConstraintException(
                    sprintf('Step "%s": Quantity rule source #%d must reference a previous step.', $stepDto->label, $idxSrc + 1),
                    ProductChoiceConstraintException::INVALID_QUANTITY_RULE_SOURCES
                );
            }

            // Prevent duplicate (step, choice)
            if (true === isset($seen[$refStepId])) {
                throw new ProductChoiceConstraintException(
                    sprintf('Step "%s": Quantity rule has duplicate source (step).', $stepDto->label),
                    ProductChoiceConstraintException::INVALID_QUANTITY_RULE_SOURCES
                );
            }

            $seen[$refStepId] = true;
        }
    }

    /**
     * Validates multiple display conditions for a given step configuration. Each condition is individually
     * validated to ensure its reference step and choice exist and meet the required constraints.
     *
     * @param array $conditions An array of display conditions to be validated.
     * @param StepDto $stepDto The current step data being evaluated.
     * @param ConfiguratorDto $configuratorDto The configurator data containing all steps and choices.
     *
     * @return void Throws ProductChoiceConstraintException if any condition validation fails.
     *
     * @throws ProductChoiceConstraintException
     */
    private function validateDisplayConditions(
        array           $conditions,
        StepDto         $stepDto,
        ConfiguratorDto $configuratorDto
    ): void
    {
        if (true === empty($conditions)) {
            return;
        }

        foreach ($conditions as $dcIdx => $condition) {
            $this->validateDisplayCondition(
                $dcIdx,
                $condition,
                $stepDto,
                $configuratorDto
            );
        }
    }

    /**
     * Validates a display condition within a step configuration. Ensures that the
     * reference step and choice exist and that the reference step precedes the current step.
     *
     * @param int $dcIdx The index of the display condition being validated.
     * @param array $condition The display condition configuration containing 'step' and 'choice' keys.
     * @param StepDto $stepDto The current step data being evaluated.
     * @param ConfiguratorDto $configuratorDto The configurator data containing all steps and choices.
     *
     * @return void Throws ProductChoiceConstraintException if validation fails.
     *
     * @throws ProductChoiceConstraintException If the referenced step does not exist, if the step is not a previous step,
     *      or if the referenced choice does not exist in the target step.
     */
    private function validateDisplayCondition(
        int             $dcIdx,
        array           $condition,
        StepDto         $stepDto,
        ConfiguratorDto $configuratorDto
    ): void
    {
        $refStepId = $condition['step'];
        $refChoiceId = $condition['choice'];

        $refStep = null;

        foreach ($configuratorDto->steps as $s) {
            if ((string)$s->id !== (string)$refStepId) {
                continue;
            }

            $refStep = $s;

            break;
        }

        if (null === $refStep) {
            throw new ProductChoiceConstraintException(
                sprintf('Step “%s”: Condition #%d refers to a non-existent step.', $stepDto->label, $dcIdx + 1),
                ProductChoiceConstraintException::INVALID_DISPLAY_CONDITION_STEP
            );
        }

        if ((int)$refStep->position >= (int)$stepDto->position) {
            throw new ProductChoiceConstraintException(
                sprintf('Step “%s”: Condition #%d must reference a previous step.', $stepDto->label, $dcIdx + 1),
                ProductChoiceConstraintException::INVALID_DISPLAY_CONDITION_STEP
            );
        }

        $refChoice = null;
        $refChoices = is_array($refStep->productChoices) ? $refStep->productChoices : [];

        foreach ($refChoices as $rc) {
            if ((string)($rc->id) !== (string)$refChoiceId) {
                continue;
            }

            $refChoice = $rc;

            break;
        }

        if (null === $refChoice) {
            throw new ProductChoiceConstraintException(
                sprintf('Step “%s”: Condition #%d references a choice that does not exist in the target step.', $stepDto->label, $dcIdx + 1),
                ProductChoiceConstraintException::INVALID_DISPLAY_CONDITION_CHOICE
            );
        }
    }

    /**
     * Validates the reduction value, type, and tax status based on the provided context and throws an exception when constraints are violated.
     *
     * @param float|null $reduction The reduction value to validate. Can be null, in which case it defaults to 0.
     * @param bool|null $tax The tax status associated with the reduction. Must be either true, false, or null.
     * @param string|null $type The type of reduction, either 'percentage' or 'amount'. Defaults to 'amount' if null.
     * @param string $context The context in which the validation is performed, used for exception messages.
     * @param Exception $exception The exception class to throw when validation fails.
     *
     * @return void
     *
     * @throws Exception If the reduction type is invalid.
     * @throws Exception If the reduction value is out of the allowed range for its type.
     * @throws Exception If the tax status is invalid.
     */
    private function validateReduction(?float $reduction, ?bool $tax, ?string $type, string $context, Exception $exception): void
    {
        $value = (float)($reduction ?? 0);
        $t = ($type ?? 'amount');

        if ($t === 'percentage') {
            if ($value < 0 || $value > 100) {
                throw new $exception(
                    sprintf('%s: The percentage reduction must be between 0 and 100.', $context),
                    $this->getExceptionCode('REDUCTION', $exception)
                );
            }
        } elseif ($t === 'amount') {
            if ($value < 0) {
                throw new $exception(
                    sprintf('%s: The amount reduction must be >= 0.', $context),
                    $this->getExceptionCode('REDUCTION', $exception)
                );
            }
        } else {
            throw new $exception(
                sprintf('%s: Invalid reduction type.', $context),
                $this->getExceptionCode('REDUCTION_TYPE', $exception)
            );
        }

        if (false === is_bool($tax)) {
            throw new $exception(
                sprintf('%s: Invalid reduction tax.', $context),
                $this->getExceptionCode('REDUCTION_TAX', $exception)
            );
        }
    }

    /**
     * Retrieves the exception code for a specific constant in an exception class.
     *
     * @param string $const The constant to generate the exception code from.
     * @param Exception $class The exception class.
     *
     * @return int The exception code for the specified constant, or 0 if the constant does not exist.
     */
    private function getExceptionCode(string $const, Exception $class): int
    {
        $exceptionCodeConst = 'INVALID_' . strtoupper($const);
        $fullyQualifiedConstantName = get_class($class) . '::' . $exceptionCodeConst;

        if (defined($fullyQualifiedConstantName)) {
            return (int)constant($fullyQualifiedConstantName);
        }

        return 0;
    }
}
