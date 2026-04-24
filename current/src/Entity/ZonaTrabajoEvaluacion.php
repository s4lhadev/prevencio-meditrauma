<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ZonaTrabajoEvaluacionRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class ZonaTrabajoEvaluacion
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ZonaTrabajo", inversedBy="ZonaTrabajoEvaluacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $zonaTrabajo;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Evaluacion", inversedBy="ZonaTrabajoEvaluacion")
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

    public function getId(): ?int
    {
        return $this->id;
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
}
