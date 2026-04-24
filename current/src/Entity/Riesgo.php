<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RiesgoRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class Riesgo
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $descripcion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $codigo;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $observaciones;

    /**
     * @ORM\Column(type="boolean", length=2000, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $anulado = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GrupoRiesgo", inversedBy="Riesgo")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $grupoRiesgo;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $descripcionCa;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $descripcionEs;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * @param mixed $descripcion
     */
    public function setDescripcion($descripcion): void
    {
        $this->descripcion = $descripcion;
    }

    /**
     * @return mixed
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * @param mixed $codigo
     */
    public function setCodigo($codigo): void
    {
        $this->codigo = $codigo;
    }

    /**
     * @return mixed
     */
    public function getObservaciones()
    {
        return $this->observaciones;
    }

    /**
     * @param mixed $observaciones
     */
    public function setObservaciones($observaciones): void
    {
        $this->observaciones = $observaciones;
    }

    /**
     * @return bool
     */
    public function getAnulado(): bool
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

    /**
     * @return mixed
     */
    public function getGrupoRiesgo()
    {
        return $this->grupoRiesgo;
    }

    /**
     * @param mixed $grupoRiesgo
     */
    public function setGrupoRiesgo($grupoRiesgo): void
    {
        $this->grupoRiesgo = $grupoRiesgo;
    }

    /**
     * @return mixed
     */
    public function getDescripcionCa()
    {
        return $this->descripcionCa;
    }

    /**
     * @param mixed $descripcionCa
     */
    public function setDescripcionCa($descripcionCa): void
    {
        $this->descripcionCa = $descripcionCa;
    }

    /**
     * @return mixed
     */
    public function getDescripcionEs()
    {
        return $this->descripcionEs;
    }

    /**
     * @param mixed $descripcionEs
     */
    public function setDescripcionEs($descripcionEs): void
    {
        $this->descripcionEs = $descripcionEs;
    }

    public function __toString()
    {
        return (string) $this->descripcion;
    }

    public function getRiesgoCodigo()
    {
        return (string) $this->codigo . ' - '. $this->descripcion;
    }
}
