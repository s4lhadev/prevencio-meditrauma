<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PuestoTrabajoCentroRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class PuestoTrabajoCentro
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $descripcion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $codigo;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PuestoTrabajoGenerico", inversedBy="PuestoTrabajoCentro")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $puestoTrabajoGenerico;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Empresa", inversedBy="PuestoTrabajoCentro")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $empresa;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $anulado = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $observaciones;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ZonaTrabajo", inversedBy="PuestoTrabajoCentro")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $zonaTrabajo;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $idPrevenet;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $idRiesgos;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $actualizado = false;

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
    public function getPuestoTrabajoGenerico()
    {
        return $this->puestoTrabajoGenerico;
    }

    /**
     * @param mixed $puestoTrabajoGenerico
     */
    public function setPuestoTrabajoGenerico($puestoTrabajoGenerico): void
    {
        $this->puestoTrabajoGenerico = $puestoTrabajoGenerico;
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
    public function getZonaTrabajo()
    {
        return $this->zonaTrabajo;
    }

    /**
     * @param mixed $zonaTrabajo
     */
    public function setZonaTrabajo($zonaTrabajo): void
    {
        $this->zonaTrabajo = $zonaTrabajo;
    }

    /**
     * @return mixed
     */
    public function getIdPrevenet()
    {
        return $this->idPrevenet;
    }

    /**
     * @param mixed $idPrevenet
     */
    public function setIdPrevenet($idPrevenet): void
    {
        $this->idPrevenet = $idPrevenet;
    }

    /**
     * @return mixed
     */
    public function getIdRiesgos()
    {
        return $this->idRiesgos;
    }

    /**
     * @param mixed $idRiesgos
     */
    public function setIdRiesgos($idRiesgos): void
    {
        $this->idRiesgos = $idRiesgos;
    }

    public function __toString()
    {
        return (string) $this->descripcion;
    }

    public function puestoTrabajoEmpresa(){
        if(!is_null($this->empresa)){
            return (string) $this->descripcion .' ('. $this->getEmpresa()->getEmpresa(). ')';
        }else{
            return (string) $this->descripcion;
        }
    }

    /**
     * @return mixed
     */
    public function getActualizado()
    {
        return $this->actualizado;
    }

    /**
     * @param mixed $actualizado
     */
    public function setActualizado($actualizado): void
    {
        $this->actualizado = $actualizado;
    }

}
