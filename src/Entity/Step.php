<?php

namespace DrSoftFr\Module\ProductWizard\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use DrSoftFr\Module\ProductWizard\Config as Configuration;
use DrSoftFr\PrestaShopModuleHelper\Traits\ClassHydrateTrait;

/**
 * @ORM\Table(name=Configuration::STEP_TABLE_NAME)
 * @ORM\Entity(repositoryClass="DrSoftFr\Module\ProductWizard\Repository\StepRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Step
{
    use ClassHydrateTrait;

    /**
     * @var int $id
     *
     * @ORM\Id
     * @ORM\Column(name="id_step", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var bool $active
     *
     * @ORM\Column(name="active", type="boolean", nullable=false, options={"default":1, "unsigned"=true})
     */
    private $active;

    /**
     * @var string $label
     *
     * @ORM\Column(name="label", type="string", length=255, nullable=false)
     */
    private $label;

    /**
     * @var Configurator $configurator
     *
     * @ORM\ManyToOne(targetEntity="DrSoftFr\Module\ProductWizard\Entity\Configurator", inversedBy="steps")
     * @ORM\JoinColumn(name="id_configurator", referencedColumnName="id_configurator", nullable=false, onDelete="CASCADE")
     */
    private $configurator;

    /**
     * @var Collection<Choice> $choices
     *
     * @ORM\OneToMany(targetEntity="DrSoftFr\Module\ProductWizard\Entity\Choice", cascade={"persist", "remove"}, mappedBy="step")
     */
    private $choices;

    /**
     * @var int $position
     *
     * @ORM\Column(name="position", type="integer", nullable=false, options={"default":0, "unsigned"=true})
     */
    private $position = 0;

    /**
     * @var DateTimeInterface $dateAdd creation date
     *
     * @ORM\Column(name="date_add", type="datetime", nullable=false)
     */
    private $dateAdd;

    /**
     * @var DateTimeInterface $dateUpd last modification date
     *
     * @ORM\Column(name="date_upd", type="datetime", options={"default": "CURRENT_TIMESTAMP"}, nullable=false)
     */
    private $dateUpd;

    public function __construct()
    {
        $this->choices = new ArrayCollection();
        $this->dateAdd = new \DateTimeImmutable();
    }

    /**
     * @param Choice $choice
     *
     * @return $this
     */
    public function addChoice(Choice $choice): Step
    {
        $choice->setStep($this);
        $this->choices->add($choice);

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        /** @var Choice[] $choices */
        $choices = [];

        /** @var Choice $choice */
        foreach ($this->getChoices() as $choice) {
            $choices[] = $choice->toArray();
        }

        return [
            'id' => $this->getId(),
            'id_step' => $this->getId(),
            'id_configurator' => $this->getConfigurator()->getId(),
            'active' => $this->isActive(),
            'date_add' => $this->getDateAdd(),
            'date_upd' => $this->getDateUpd(),
            'label' => $this->getLabel(),
            'position' => $this->getPosition(),
            'choices' => $choices,
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

    public function setId(int $id): Step
    {
        $this->id = $id;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): Step
    {
        $this->active = $active;
        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): Step
    {
        $this->label = $label;
        return $this;
    }

    public function getConfigurator(): Configurator
    {
        return $this->configurator;
    }

    public function setConfigurator(Configurator $configurator): Step
    {
        $this->configurator = $configurator;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getChoices(): Collection
    {
        return $this->choices;
    }

    /**
     * @param Collection $choices
     * @return Step
     */
    public function setChoices(Collection $choices): Step
    {
        $this->choices = $choices;
        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): Step
    {
        $this->position = $position;
        return $this;
    }

    public function getDateAdd(): DateTimeInterface
    {
        return $this->dateAdd;
    }

    public function setDateAdd(DateTimeInterface $dateAdd): Step
    {
        $this->dateAdd = $dateAdd;
        return $this;
    }

    public function getDateUpd(): DateTimeInterface
    {
        return $this->dateUpd;
    }

    public function setDateUpd(DateTimeInterface $dateUpd): Step
    {
        $this->dateUpd = $dateUpd;
        return $this;
    }
}
