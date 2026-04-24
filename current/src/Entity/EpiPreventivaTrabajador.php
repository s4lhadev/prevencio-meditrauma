<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EpiPreventivaTrabajadorRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class EpiPreventivaTrabajador
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\AccionPreventivaTrabajadorRiesgoCausa", inversedBy="EpiPreventivaTrabajador")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $preventivaTrabajador;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Epi", inversedBy="EpiPreventivaTrabajador")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $epi;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $anulado = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getPreventivaTrabajador()
    {
        return $this->preventivaTrabajador;
    }

    /**
     * @param mixed $preventivaTrabajador
     */
    public function setPreventivaTrabajador($preventivaTrabajador): void
    {
        $this->preventivaTrabajador = $preventivaTrabajador;
    }

    /**
     * @return mixed
     */
    public function getEpi()
    {
        return $this->epi;
    }

    /**
     * @param mixed $epi
     */
    public function setEpi($epi): void
    {
        $this->epi = $epi;
    }

    /**
     * @return bool
     */
    public function isAnulado(): bool
    {
        return $this->anulado;
    }

    /**
     * @param bool $anulado
     */
    public function setAnulado(bool $anulado): void
    {
        $this->anulado = $anulado;
    }
}
