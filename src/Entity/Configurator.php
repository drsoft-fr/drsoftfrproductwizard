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
 * @ORM\Table(name=Configuration::CONFIGURATOR_TABLE_NAME)
 * @ORM\Entity(repositoryClass="DrSoftFr\Module\ProductWizard\Repository\ConfiguratorRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Configurator
{
    use ClassHydrateTrait;

    /**
     * @var int $id
     *
     * @ORM\Id
     * @ORM\Column(name="id_configurator", type="integer", nullable=false, options={"unsigned"=true})
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
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name = '';

    /**
     * @var Collection<Step> $steps
     *
     * @ORM\OneToMany(targetEntity="DrSoftFr\Module\ProductWizard\Entity\Step", cascade={"persist", "remove"}, orphanRemoval=true, mappedBy="configurator")
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $steps;

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
        $this->dateAdd = new \DateTimeImmutable();
        $this->steps = new ArrayCollection();
    }

    /**
     * @param Step $step
     *
     * @return $this
     */
    public function addStep(Step $step): Configurator
    {
        if (true === $this->steps->contains($step)) {
            return $this;
        }

        $step->setConfigurator($this);
        $this->steps->add($step);

        return $this;
    }

    /**
     * @param Step $step
     *
     * @return $this
     */
    public function removeStep(Step $step): Configurator
    {
        if (false === $this->steps->contains($step)) {
            return $this;
        }

        $this->steps->removeElement($step);

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        /** @var Step[] $steps */
        $steps = [];

        /** @var Step $step */
        foreach ($this->getSteps() as $step) {
            $steps[] = $step->toArray();
        }

        return [
            'id' => $this->getId(),
            'id_configurator' => $this->getId(),
            'active' => $this->isActive(),
            'date_add' => $this->getDateAdd(),
            'date_upd' => $this->getDateUpd(),
            'steps' => $steps,
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

    public function setId(int $id): Configurator
    {
        $this->id = $id;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): Configurator
    {
        $this->active = $active;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Configurator
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getSteps(): Collection
    {
        return $this->steps;
    }

    /**
     * @param Collection $steps
     * @return Configurator
     */
    public function setSteps(Collection $steps): Configurator
    {
        foreach ($this->steps as $step) {
            $this->removeStep($step);
        }

        foreach ($steps as $step) {
            $this->addStep($step);
        }

        return $this;
    }

    public function getDateAdd(): DateTimeInterface
    {
        return $this->dateAdd;
    }

    public function setDateAdd(DateTimeInterface $dateAdd): Configurator
    {
        $this->dateAdd = $dateAdd;
        return $this;
    }

    public function getDateUpd(): DateTimeInterface
    {
        return $this->dateUpd;
    }

    public function setDateUpd(DateTimeInterface $dateUpd): Configurator
    {
        $this->dateUpd = $dateUpd;
        return $this;
    }
}
