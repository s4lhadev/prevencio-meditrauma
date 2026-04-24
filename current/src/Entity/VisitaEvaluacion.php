<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VisitaEvaluacionRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class VisitaEvaluacion
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Evaluacion", inversedBy="VisitaEvaluacion")
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
     * @ORM\Column(type="datetime", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $dtVisita;

    /**
     * @ORM\Column(type="time", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $hinicio;

    /**
     * @ORM\Column(type="time", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $hfin;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\UsuarioTecnico", inversedBy="VisitaEvaluacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $tecnico;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TipoActuacion", inversedBy="VisitaEvaluacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $tipoActuacion;

    public function getId(): ?int
    {
        return $this->id;
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
    public function getDtVisita()
    {
        return $this->dtVisita;
    }

    /**
     * @param mixed $dtVisita
     */
    public function setDtVisita($dtVisita): void
    {
        $this->dtVisita = $dtVisita;
    }

    /**
     * @return mixed
     */
    public function getHinicio()
    {
        return $this->hinicio;
    }

    /**
     * @param mixed $hinicio
     */
    public function setHinicio($hinicio): void
    {
        $this->hinicio = $hinicio;
    }

    /**
     * @return mixed
     */
    public function getHfin()
    {
        return $this->hfin;
    }

    /**
     * @param mixed $hfin
     */
    public function setHfin($hfin): void
    {
        $this->hfin = $hfin;
    }

    /**
     * @return mixed
     */
    public function getTecnico()
    {
        return $this->tecnico;
    }

    /**
     * @param mixed $tecnico
     */
    public function setTecnico($tecnico): void
    {
        $this->tecnico = $tecnico;
    }

    /**
     * @return mixed
     */
    public function getTipoActuacion()
    {
        return $this->tipoActuacion;
    }

    /**
     * @param mixed $tipoActuacion
     */
    public function setTipoActuacion($tipoActuacion): void
    {
        $this->tipoActuacion = $tipoActuacion;
    }
}
