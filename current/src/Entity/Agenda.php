<?php

namespace App\Entity;

use App\Repository\AgendaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AgendaRepository::class)
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class Agenda
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
     * @ORM\Column(type="time", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $horainicio;

    /**
     * @ORM\Column(type="time", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $horafin;

    /**
     * @ORM\Column(type="time", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $duracionTramo;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $finSemanaSn = false;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $anulado = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $direccion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $codigo;

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
    public function getHorainicio()
    {
        return $this->horainicio;
    }

    /**
     * @param mixed $horainicio
     */
    public function setHorainicio($horainicio): void
    {
        $this->horainicio = $horainicio;
    }

    /**
     * @return mixed
     */
    public function getHorafin()
    {
        return $this->horafin;
    }

    /**
     * @param mixed $horafin
     */
    public function setHorafin($horafin): void
    {
        $this->horafin = $horafin;
    }

    /**
     * @return mixed
     */
    public function getDuracionTramo()
    {
        return $this->duracionTramo;
    }

    /**
     * @param mixed $duracionTramo
     */
    public function setDuracionTramo($duracionTramo): void
    {
        $this->duracionTramo = $duracionTramo;
    }

    /**
     * @return mixed
     */
    public function getFinSemanaSn()
    {
        return $this->finSemanaSn;
    }

    /**
     * @param mixed $finSemanaSn
     */
    public function setFinSemanaSn($finSemanaSn): void
    {
        $this->finSemanaSn = $finSemanaSn;
    }

    /**
     * @return mixed
     */
    public function getDireccion()
    {
        return $this->direccion;
    }

    /**
     * @param mixed $direccion
     */
    public function setDireccion($direccion): void
    {
        $this->direccion = $direccion;
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

    public function __toString(){
        return (string) $this->descripcion;
    }
}
