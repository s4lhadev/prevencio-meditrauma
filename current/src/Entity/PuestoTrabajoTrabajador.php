<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PuestoTrabajoTrabajadorRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class PuestoTrabajoTrabajador
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PuestoTrabajoCentro", inversedBy="PuestoTrabajoTrabajador")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $puestoTrabajo;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Trabajador", inversedBy="PuestoTrabajoTrabajador")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $trabajador;

    /**
     * @ORM\Column(type="datetime", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $fechaAlta;

    /**
     * @ORM\Column(type="datetime", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $fechaBaja;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $dedicacion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $observaciones;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $delegadoPrevencion = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $delegadoPersonal = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $delegadoComite = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Centro", inversedBy="PuestoTrabajoTrabajador")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $centro;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $anulado = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Empresa", inversedBy="PuestoTrabajoTrabajador")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $empresa;

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
    public function getTrabajador()
    {
        return $this->trabajador;
    }

    /**
     * @param mixed $trabajador
     */
    public function setTrabajador($trabajador): void
    {
        $this->trabajador = $trabajador;
    }

    /**
     * @return mixed
     */
    public function getFechaAlta()
    {
        return $this->fechaAlta;
    }

    /**
     * @param mixed $fechaAlta
     */
    public function setFechaAlta($fechaAlta): void
    {
        $this->fechaAlta = $fechaAlta;
    }

    /**
     * @return mixed
     */
    public function getFechaBaja()
    {
        return $this->fechaBaja;
    }

    /**
     * @param mixed $fechaBaja
     */
    public function setFechaBaja($fechaBaja): void
    {
        $this->fechaBaja = $fechaBaja;
    }

    /**
     * @return mixed
     */
    public function getDedicacion()
    {
        return $this->dedicacion;
    }

    /**
     * @param mixed $dedicacion
     */
    public function setDedicacion($dedicacion): void
    {
        $this->dedicacion = $dedicacion;
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
    public function isDelegadoPrevencion(): bool
    {
        return $this->delegadoPrevencion;
    }

    /**
     * @param bool $delegadoPrevencion
     */
    public function setDelegadoPrevencion(bool $delegadoPrevencion): void
    {
        $this->delegadoPrevencion = $delegadoPrevencion;
    }

    /**
     * @return bool
     */
    public function isDelegadoPersonal(): bool
    {
        return $this->delegadoPersonal;
    }

    /**
     * @param bool $delegadoPersonal
     */
    public function setDelegadoPersonal(bool $delegadoPersonal): void
    {
        $this->delegadoPersonal = $delegadoPersonal;
    }

    /**
     * @return bool
     */
    public function isDelegadoComite(): bool
    {
        return $this->delegadoComite;
    }

    /**
     * @param bool $delegadoComite
     */
    public function setDelegadoComite(bool $delegadoComite): void
    {
        $this->delegadoComite = $delegadoComite;
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
}
