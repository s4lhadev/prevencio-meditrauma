<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PuestoTrabajoMaquinaGenericaRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class PuestoTrabajoMaquinaGenerica
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PuestoTrabajoCentro", inversedBy="PuestoTrabajoMaquinaGenerica")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $puestoTrabajo;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MaquinaGenerica", inversedBy="PuestoTrabajoMaquinaGenerica")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $maquinaGenerica;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Empresa", inversedBy="PuestoTrabajoMaquinaGenerica")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $empresa;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Centro", inversedBy="PuestoTrabajoMaquinaGenerica")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $centro;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
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
    public function getPuestoTrabajo()
    {
        return $this->puestoTrabajo;
    }

    /**
     * @param mixed $puestoTrabajo
     */
    public function setPuestoTrabajo($puestoTrabajo): void
    {
        $this->puestoTrabajo = $puestoTrabajo;
    }

    /**
     * @return mixed
     */
    public function getMaquinaGenerica()
    {
        return $this->maquinaGenerica;
    }

    /**
     * @param mixed $maquinaGenerica
     */
    public function setMaquinaGenerica($maquinaGenerica): void
    {
        $this->maquinaGenerica = $maquinaGenerica;
    }

    /**
     * @return mixed
     */
    public function getEmpresa()
    {
        return $this->empresa;
    }

    /**
     * @param mixed $empresa
     */
    public function setEmpresa($empresa): void
    {
        $this->empresa = $empresa;
    }

    /**
     * @return mixed
     */
    public function getCentro()
    {
        return $this->centro;
    }

    /**
     * @param mixed $centro
     */
    public function setCentro($centro): void
    {
        $this->centro = $centro;
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
