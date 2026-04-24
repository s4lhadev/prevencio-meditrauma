<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PlanificacionRiesgoCausaRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class PlanificacionRiesgoCausa
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $fechaPrevista;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $fechaRealizacion;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $costePrevisto;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TipoPlanificacion", inversedBy="PlanificacionRiesgoCausa")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $tipoPlanificacion;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\RiesgoCausaEvaluacion", inversedBy="PlanificacionRiesgoCausa")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $riesgoCausa;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $responsable;

    /**
     * @ORM\Column(type="boolean", length=2000, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $trabajadores = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getFechaPrevista()
    {
        return $this->fechaPrevista;
    }

    /**
     * @param mixed $fechaPrevista
     */
    public function setFechaPrevista($fechaPrevista): void
    {
        $this->fechaPrevista = $fechaPrevista;
    }

    /**
     * @return mixed
     */
    public function getFechaRealizacion()
    {
        return $this->fechaRealizacion;
    }

    /**
     * @param mixed $fechaRealizacion
     */
    public function setFechaRealizacion($fechaRealizacion): void
    {
        $this->fechaRealizacion = $fechaRealizacion;
    }

    /**
     * @return mixed
     */
    public function getCostePrevisto()
    {
        return $this->costePrevisto;
    }

    /**
     * @param mixed $costePrevisto
     */
    public function setCostePrevisto($costePrevisto): void
    {
        $this->costePrevisto = $costePrevisto;
    }

    /**
     * @return mixed
     */
    public function getTipoPlanificacion()
    {
        return $this->tipoPlanificacion;
    }

    /**
     * @param mixed $tipoPlanificacion
     */
    public function setTipoPlanificacion($tipoPlanificacion): void
    {
        $this->tipoPlanificacion = $tipoPlanificacion;
    }

    /**
     * @return mixed
     */
    public function getRiesgoCausa()
    {
        return $this->riesgoCausa;
    }

    /**
     * @param mixed $riesgoCausa
     */
    public function setRiesgoCausa($riesgoCausa): void
    {
        $this->riesgoCausa = $riesgoCausa;
    }

    /**
     * @return mixed
     */
    public function getResponsable()
    {
        return $this->responsable;
    }

    /**
     * @param mixed $responsable
     */
    public function setResponsable($responsable): void
    {
        $this->responsable = $responsable;
    }

    /**
     * @return mixed
     */
    public function getTrabajadores()
    {
        return $this->trabajadores;
    }

    /**
     * @param mixed $trabajadores
     */
    public function setTrabajadores($trabajadores): void
    {
        $this->trabajadores = $trabajadores;
    }
}
