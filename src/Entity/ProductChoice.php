<?php

namespace DrSoftFr\Module\ProductWizard\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use DrSoftFr\Module\ProductWizard\Domain\Exception\ProductChoice\ProductChoiceConstraintException;
use DrSoftFr\Module\ProductWizard\Domain\Exception\Step\StepConstraintException;
use DrSoftFr\Module\ProductWizard\Domain\ValueObject\ProductChoice\DisplayCondition;
use DrSoftFr\Module\ProductWizard\Domain\ValueObject\ProductChoice\QuantityRule;
use DrSoftFr\Module\ProductWizard\Infrastructure\Persistence\TableNames;
use DrSoftFr\PrestaShopModuleHelper\Traits\ClassHydrateTrait;

/**
 * @ORM\Table(name=TableNames::PRODUCT_CHOICE)
 * @ORM\Entity(repositoryClass="DrSoftFr\Module\ProductWizard\Infrastructure\Persistence\Doctrine\ProductChoiceRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ProductChoice
{
    use ClassHydrateTrait;

    /**
     * @var int $id
     *
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id = 0;

    /**
     * @var bool $active
     *
     * @ORM\Column(type="boolean", nullable=false, options={"default":1, "unsigned"=true})
     */
    private $active = true;

    /**
     * @var string $label
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $label = '';

    /**
     * @var string|null $description
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $description = null;

    /**
     * @var ?int $productId
     *
     * @ORM\Column(name="id_product", type="integer", length=10, nullable=true, options={"unsigned"=true})
     */
    private $productId;

    /**
     * @var bool $isDefault
     *
     * @ORM\Column(type="boolean", nullable=false, options={"default":0, "unsigned"=true})
     */
    private $isDefault = false;

    /**
     * @var float $reduction
     *
     * @ORM\Column(type="float", nullable=false, options={"default":0})
     */
    private $reduction = 0.0;

    /**
     * @var bool $reductionTax TAX included ?
     *
     * @ORM\Column(type="boolean", nullable=false, options={"default":1, "unsigned"=true})
     */
    private $reductionTax = true;

    /**
     * @var string $reductionType percentage OR amount ('amount' | 'percentage')
     *
     * @ORM\Column(type="string", nullable=false, options={"default":"amount"}, columnDefinition="ENUM('amount','percentage')")
     */
    private $reductionType = 'amount';

    /**
     * @var Step $step
     *
     * @ORM\ManyToOne(targetEntity="DrSoftFr\Module\ProductWizard\Entity\Step", inversedBy="productChoices")
     * @ORM\JoinColumn(name="id_step", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $step;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private array $displayConditions = [];

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private ?array $quantityRule = null;

    /**
     * @var ?DateTimeInterface $dateAdd creation date
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $dateAdd;

    /**
     * @var ?DateTimeInterface $dateUpd last modification date
     *
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"}, nullable=false)
     */
    private $dateUpd;

    public function __construct()
    {
        $this->dateAdd = new \DateTimeImmutable();
    }

    /**
     * @return array
     *
     * @throws ProductChoiceConstraintException
     * @throws StepConstraintException
     */
    public function toArray(): array
    {
        $arr = [];

        foreach ($this->getDisplayConditions() as $condition) {
            $arr[] = $condition->getValue();
        }

        return [
            'id' => $this->getId(),
            'id_product_choice' => $this->getId(),
            'id_step' => $this->getStep()->getId(),
            'active' => $this->isActive(),
            'date_add' => $this->getDateAdd(),
            'date_upd' => $this->getDateUpd(),
            'label' => $this->getLabel(),
            'description' => $this->getDescription(),
            'product_id' => $this->getProductId(),
            'is_default' => $this->isDefault(),
            'reduction' => $this->getReduction(),
            'reduction_tax' => $this->isReductionTax(),
            'reduction_type' => $this->getReductionType(),
            'display_conditions' => $arr,
            'quantity_rule' => $this->getQuantityRule()->getValue(),
        ];
    }

    /**
     * Now we tell doctrine that before we persist or update, we call the updatedTimestamps() function.
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatedTimestamps(): void
    {
        $this->setDateUpd(new DateTime());

        if ($this->dateAdd === null) {
            $this->setDateAdd(new DateTime());
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): ProductChoice
    {
        $this->id = $id;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): ProductChoice
    {
        $this->active = $active;
        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): ProductChoice
    {
        $this->label = $label;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): ProductChoice
    {
        $this->description = $description;
        return $this;
    }

    public function getProductId(): ?int
    {
        return $this->productId;
    }

    public function setProductId(?int $productId): ProductChoice
    {
        $this->productId = $productId;
        return $this;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): ProductChoice
    {
        $this->isDefault = $isDefault;
        return $this;
    }

    public function getStep(): Step
    {
        return $this->step;
    }

    public function setStep(Step $step): ProductChoice
    {
        $this->step = $step;
        return $this;
    }

    /**
     * @return DisplayCondition[]
     *
     * @throws ProductChoiceConstraintException
     * @throws StepConstraintException
     */
    public function getDisplayConditions(): array
    {
        $arr = [];

        foreach ($this->displayConditions as $condition) {
            $arr[] = DisplayCondition::fromArray($condition ?: []);
        }

        return $arr;
    }

    /**
     * @param DisplayCondition[] $displayConditions
     *
     * @return $this
     */
    public function setDisplayConditions(array $displayConditions): ProductChoice
    {
        $conditionsMap = [];
        $uniqueConditions = [];

        foreach ($displayConditions as $condition) {
            $value = $condition->getValue();

            $key = $value['step'] . '_' . $value['choice'];

            if (isset($conditionsMap[$key])) {
                continue;
            }

            $conditionsMap[$key] = true;
            $uniqueConditions[] = $value;
        }

        $this->displayConditions = $uniqueConditions;

        return $this;
    }

    public function getReduction(): float
    {
        return $this->reduction;
    }

    public function setReduction(float $reduction): ProductChoice
    {
        $this->reduction = $reduction;
        return $this;
    }

    public function isReductionTax(): bool
    {
        return $this->reductionTax;
    }

    public function setReductionTax(bool $reductionTax): ProductChoice
    {
        $this->reductionTax = $reductionTax;
        return $this;
    }

    public function getReductionType(): string
    {
        return $this->reductionType;
    }

    public function setReductionType(string $reductionType): ProductChoice
    {
        $this->reductionType = $reductionType;
        return $this;
    }

    /**
     * @return DateTimeInterface
     */
    public function getDateAdd(): ?DateTimeInterface
    {
        return $this->dateAdd;
    }

    /**
     * @param DateTimeInterface $dateAdd
     * @return ProductChoice
     */
    public function setDateAdd(DateTimeInterface $dateAdd)
    {
        $this->dateAdd = $dateAdd;
        return $this;
    }

    public function getDateUpd(): ?DateTimeInterface
    {
        return $this->dateUpd;
    }

    public function setDateUpd(DateTimeInterface $dateUpd): ProductChoice
    {
        $this->dateUpd = $dateUpd;
        return $this;
    }

    public function getQuantityRule(): QuantityRule
    {
        return QuantityRule::fromArray($this->quantityRule ?: []);
    }

    public function setQuantityRule(QuantityRule $rule): void
    {
        $this->quantityRule = $rule->getValue();
    }
}
