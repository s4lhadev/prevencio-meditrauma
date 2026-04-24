<?php

namespace App\Entity;

use App\Repository\PreventivaTrabajadorCausaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PreventivaTrabajadorCausaRepository::class)
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class PreventivaTrabajadorCausa
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PreventivaTrabajador", inversedBy="PreventivaTrabajadorCausa")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $preventivaTrabajador;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Causa", inversedBy="PreventivaTrabajadorCausa")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $causa;

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
    public function getCausa()
    {
        return $this->causa;
    }

    /**
     * @param mixed $causa
     */
    public function setCausa($causa): void
    {
        $this->causa = $causa;
    }

    /**
     * @return mixed
     */
    public function getAnulado()
    {
        return $this->anulado;
    }

    /**
     * @param mixed $anulado
     */
    public function setAnulado($anulado): void
    {
        $this->anulado = $anulado;
    }
}
