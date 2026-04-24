<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UsuarioTecnicoRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class UsuarioTecnico
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
    private $nombre;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $apellido1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $apellido2;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $dni;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $tecnico = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $medico = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $formador = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $administrativo = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
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
    private $observaciones;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
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
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * @param mixed $nombre
     */
    public function setNombre($nombre): void
    {
        $this->nombre = $nombre;
    }

    /**
     * @return mixed
     */
    public function getApellido1()
    {
        return $this->apellido1;
    }

    /**
     * @param mixed $apellido1
     */
    public function setApellido1($apellido1): void
    {
        $this->apellido1 = $apellido1;
    }

    /**
     * @return mixed
     */
    public function getApellido2()
    {
        return $this->apellido2;
    }

    /**
     * @param mixed $apellido2
     */
    public function setApellido2($apellido2): void
    {
        $this->apellido2 = $apellido2;
    }

    /**
     * @return mixed
     */
    public function getDni()
    {
        return $this->dni;
    }

    /**
     * @param mixed $dni
     */
    public function setDni($dni): void
    {
        $this->dni = $dni;
    }

    /**
     * @return bool
     */
    public function isTecnico(): bool
    {
        return $this->tecnico;
    }

    /**
     * @param bool $tecnico
     */
    public function setTecnico(bool $tecnico): void
    {
        $this->tecnico = $tecnico;
    }

    /**
     * @return bool
     */
    public function isMedico(): bool
    {
        return $this->medico;
    }

    /**
     * @param bool $medico
     */
    public function setMedico(bool $medico): void
    {
        $this->medico = $medico;
    }

    /**
     * @return bool
     */
    public function isFormador(): bool
    {
        return $this->formador;
    }

    /**
     * @param bool $formador
     */
    public function setFormador(bool $formador): void
    {
        $this->formador = $formador;
    }

    /**
     * @return bool
     */
    public function isAdministrativo(): bool
    {
        return $this->administrativo;
    }

    /**
     * @param bool $administrativo
     */
    public function setAdministrativo(bool $administrativo): void
    {
        $this->administrativo = $administrativo;
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

    public function getNombreCompleto()
    {
        return (string) $this->nombre .' '.$this->apellido1. ' '.$this->apellido2;
    }


}
