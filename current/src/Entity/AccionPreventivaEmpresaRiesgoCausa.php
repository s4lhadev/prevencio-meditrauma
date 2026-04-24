<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AccionPreventivaEmpresaRiesgoCausaRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class AccionPreventivaEmpresaRiesgoCausa
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Trabajador", inversedBy="AccionPreventivaEmpresaRiesgoCausa")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $responsableInterno;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ResponsableExterno", inversedBy="AccionPreventivaEmpresaRiesgoCausa")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $responsableExterno;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\EmpresaExterna", inversedBy="AccionPreventivaEmpresaRiesgoCausa")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $empresaExterna;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $observaciones;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\RiesgoCausaEvaluacion", inversedBy="AccionPreventivaEmpresaRiesgoCausa")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $riesgoCausa;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $anulado = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PreventivaEmpresa", inversedBy="AccionPreventivaEmpresaRiesgoCausa")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $preventivaEmpresa;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $descripcion;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TipoPlanificacion", inversedBy="AccionPreventivaTrabajadorRiesgoCausa")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $tipoPlanificacion;

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
    public function getResponsableInterno()
    {
        return $this->responsableInterno;
    }

    /**
     * @param mixed $responsableInterno
     */
    public function setResponsableInterno($responsableInterno): void
    {
        $this->responsableInterno = $responsableInterno;
    }

    /**
     * @return mixed
     */
    public function getResponsableExterno()
    {
        return $this->responsableExterno;
    }

    /**
     * @param mixed $responsableExterno
     */
    public function setResponsableExterno($responsableExterno): void
    {
        $this->responsableExterno = $responsableExterno;
    }

    /**
     * @return mixed
     */
    public function getEmpresaExterna()
    {
        return $this->empresaExterna;
    }

    /**
     * @param mixed $empresaExterna
     */
    public function setEmpresaExterna($empresaExterna): void
    {
        $this->empresaExterna = $empresaExterna;
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
    public function getPreventivaEmpresa()
    {
        return $this->preventivaEmpresa;
    }

    /**
     * @param mixed $preventivaEmpresa
     */
    public function setPreventivaEmpresa($preventivaEmpresa): void
    {
        $this->preventivaEmpresa = $preventivaEmpresa;
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
}
