<?php

namespace DrSoftFr\Module\ProductWizard\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use DrSoftFr\Module\ProductWizard\Config as Configuration;
use DrSoftFr\PrestaShopModuleHelper\Traits\ClassHydrateTrait;

/**
 * @ORM\Table(name=Configuration::PRODUCT_CHOICE_TABLE_NAME)
 * @ORM\Entity(repositoryClass="DrSoftFr\Module\ProductWizard\Repository\ProductChoiceRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ProductChoice
{
    use ClassHydrateTrait;

    /**
     * @var int $id
     *
     * @ORM\Id
     * @ORM\Column(name="id_product_choice", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id = 0;

    /**
     * @var bool $active
     *
     * @ORM\Column(name="active", type="boolean", nullable=false, options={"default":1, "unsigned"=true})
     */
    private $active = true;

    /**
     * @var string $label
     *
     * @ORM\Column(name="label", type="string", length=255, nullable=false)
     */
    private $label = '';

    /**
     * @var ?int $productId
     *
     * @ORM\Column(name="id_product", type="integer", length=10, nullable=true, options={"unsigned"=true})
     */
    private $productId;

    /**
     * @var bool $isDefault
     *
     * @ORM\Column(name="is_default", type="boolean", nullable=false, options={"default":0, "unsigned"=true})
     */
    private $isDefault = false;

    /**
     * @var bool $allowQuantity
     *
     * @ORM\Column(name="allow_quantity", type="boolean", nullable=false, options={"default":1, "unsigned"=true})
     */
    private $allowQuantity = true;

    /**
     * @var ?int $forcedQuantity
     *
     * @ORM\Column(name="forced_quantity", type="integer", nullable=true, options={"default":null, "unsigned"=true})
     */
    private $forcedQuantity;

    /**
     * @var float $reduction
     *
     * @ORM\Column(name="reduction", type="float", nullable=false, options={"default":0})
     */
    private $reduction = 0.0;

    /**
     * @var bool $reductionTax TAX included ?
     *
     * @ORM\Column(name="reduction_tax", type="boolean", nullable=false, options={"default":1, "unsigned"=true})
     */
    private $reductionTax = true;

    /**
     * @var string $reductionType percentage OR amount ('amount' | 'percentage')
     *
     * @ORM\Column(name="reduction_type", type="string", nullable=false, options={"default":"amount"}, columnDefinition="ENUM('amount','percentage')")
     */
    private $reductionType = 'amount';

    /**
     * @var Step $step
     *
     * @ORM\ManyToOne(targetEntity="DrSoftFr\Module\ProductWizard\Entity\Step", inversedBy="productChoices")
     * @ORM\JoinColumn(name="id_step", referencedColumnName="id_step", nullable=false, onDelete="CASCADE")
     */
    private $step;

    /**
     * @ORM\Column(name="display_conditions", type="json", nullable=true)
     */
    private $displayConditions = [];

    /**
     * @var ?DateTimeInterface $dateAdd creation date
     *
     * @ORM\Column(name="date_add", type="datetime", nullable=false)
     */
    private $dateAdd;

    /**
     * @var ?DateTimeInterface $dateUpd last modification date
     *
     * @ORM\Column(name="date_upd", type="datetime", options={"default": "CURRENT_TIMESTAMP"}, nullable=false)
     */
    private $dateUpd;

    public function __construct()
    {
        $this->dateAdd = new \DateTimeImmutable();
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'id_product_choice' => $this->getId(),
            'id_step' => $this->getStep()->getId(),
            'active' => $this->isActive(),
            'date_add' => $this->getDateAdd(),
            'date_upd' => $this->getDateUpd(),
            'label' => $this->getLabel(),
            'product_id' => $this->getProductId(),
            'is_default' => $this->isDefault(),
            'allow_quantity' => $this->isAllowQuantity(),
            'forced_quantity' => $this->getForcedQuantity(),
            'reduction' => $this->getReduction(),
            'reduction_tax' => $this->isReductionTax(),
            'reduction_type' => $this->getReductionType(),
            'display_conditions' => $this->getDisplayConditions(),
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

    public function isAllowQuantity(): bool
    {
        return $this->allowQuantity;
    }

    public function setAllowQuantity(bool $allowQuantity): ProductChoice
    {
        $this->allowQuantity = $allowQuantity;
        return $this;
    }

    public function getForcedQuantity(): ?int
    {
        return $this->forcedQuantity;
    }

    public function setForcedQuantity(?int $forcedQuantity): ProductChoice
    {
        $this->forcedQuantity = $forcedQuantity;
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

    public function getDisplayConditions(): array
    {
        return $this->displayConditions;
    }

    public function setDisplayConditions(array $displayConditions): ProductChoice
    {
        $conditionsMap = [];
        $uniqueConditions = [];

        /** @var array $condition */
        foreach ($displayConditions as $condition) {
            if (false === isset(
                    $condition['step'],
                    $condition['choice']
                )) {
                continue;
            }

            $arr = [
                'choice' => (int)$condition['choice'],
                'step' => (int)$condition['step'],
            ];

            $key = $arr['step'] . '_' . $arr['choice'];

            if (isset($conditionsMap[$key])) {
                continue;
            }

            $conditionsMap[$key] = true;
            $uniqueConditions[] = $arr;
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
}
