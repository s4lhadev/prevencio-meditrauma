<?php

namespace App\Entity;

use App\Repository\DoctorRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DoctorRepository::class)
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class Doctor
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
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $numeroColegiado;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $especialidad;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $poblacion;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $gestora;

    /**
     * @ORM\Column(type="boolean", length=2000, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $anulado = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $firma;

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
    public function getNumeroColegiado()
    {
        return $this->numeroColegiado;
    }

    /**
     * @param mixed $numeroColegiado
     */
    public function setNumeroColegiado($numeroColegiado): void
    {
        $this->numeroColegiado = $numeroColegiado;
    }

    /**
     * @return mixed
     */
    public function getEspecialidad()
    {
        return $this->especialidad;
    }

    /**
     * @param mixed $especialidad
     */
    public function setEspecialidad($especialidad): void
    {
        $this->especialidad = $especialidad;
    }

    /**
     * @return mixed
     */
    public function getPoblacion()
    {
        return $this->poblacion;
    }

    /**
     * @param mixed $poblacion
     */
    public function setPoblacion($poblacion): void
    {
        $this->poblacion = $poblacion;
    }

    /**
     * @return mixed
     */
    public function getGestora()
    {
        return $this->gestora;
    }

    /**
     * @param mixed $gestora
     */
    public function setGestora($gestora): void
    {
        $this->gestora = $gestora;
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

    /**
     * @return mixed
     */
    public function getFirma()
    {
        return $this->firma;
    }

    /**
     * @param mixed $firma
     */
    public function setFirma($firma): void
    {
        $this->firma = $firma;
    }

    public function __toString()
    {
       return (string) $this->descripcion;
    }


}
