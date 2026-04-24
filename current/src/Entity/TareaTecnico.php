<?php

namespace App\Entity;

use App\Repository\TareaTecnicoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TareaTecnicoRepository::class)
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class TareaTecnico
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="TareaTecnico")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $usuario;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\EstadoTareaTecnico", inversedBy="TareaTecnico")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $estado;

    /**
     * @ORM\Column(type="datetime", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $fechainicio;

    /**
     * @ORM\Column(type="datetime", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $fechafin;

    /**
     * @ORM\Column(type="time", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $duracion;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $descripcion;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $anulado = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Evaluacion", inversedBy="TareaTecnico")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $evaluacion;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * @param mixed $usuario
     */
    public function setUsuario($usuario): void
    {
        $this->usuario = $usuario;
    }

    /**
     * @return mixed
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * @param mixed $estado
     */
    public function setEstado($estado): void
    {
        $this->estado = $estado;
    }

    /**
     * @return mixed
     */
    public function getFechainicio()
    {
        return $this->fechainicio;
    }

    /**
     * @param mixed $fechainicio
     */
    public function setFechainicio($fechainicio): void
    {
        $this->fechainicio = $fechainicio;
    }

    /**
     * @return mixed
     */
    public function getFechafin()
    {
        return $this->fechafin;
    }

    /**
     * @param mixed $fechafin
     */
    public function setFechafin($fechafin): void
    {
        $this->fechafin = $fechafin;
    }

    /**
     * @return mixed
     */
    public function getDuracion()
    {
        return $this->duracion;
    }

    /**
     * @param mixed $duracion
     */
    public function setDuracion($duracion): void
    {
        $this->duracion = $duracion;
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
    public function getEvaluacion()
    {
        return $this->evaluacion;
    }

    /**
     * @param mixed $evaluacion
     */
    public function setEvaluacion($evaluacion): void
    {
        $this->evaluacion = $evaluacion;
    }
}
