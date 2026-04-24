<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PuestoTrabajoEvaluacionRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class PuestoTrabajoEvaluacion
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PuestoTrabajoCentro", inversedBy="PuestoTrabajoEvaluacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $puestoTrabajo;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Evaluacion", inversedBy="PuestoTrabajoEvaluacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $evaluacion;

    /**
     * @ORM\Column(type="boolean", length=2000, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $anulado = false;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $trabajadores;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $motivoEvaluacion;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $tarea;

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

    /**
     * @return mixed
     */
    public function getMotivoEvaluacion()
    {
        return $this->motivoEvaluacion;
    }

    /**
     * @param mixed $motivoEvaluacion
     */
    public function setMotivoEvaluacion($motivoEvaluacion): void
    {
        $this->motivoEvaluacion = $motivoEvaluacion;
    }

    /**
     * @return mixed
     */
    public function getTarea()
    {
        return $this->tarea;
    }

    /**
     * @param mixed $tarea
     */
    public function setTarea($tarea): void
    {
        $this->tarea = $tarea;
    }
}
