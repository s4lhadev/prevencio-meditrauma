<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EvaluacionRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class Evaluacion
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
    private $fechaInicio;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $fechaFin;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $fechaProxima;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TipoEvaluacion", inversedBy="Evaluacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $tipoEvaluacion;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MetodologiaEvaluacion", inversedBy="Evaluacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $metodologia;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Empresa", inversedBy="Evaluacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $empresa;

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
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $tipo;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $finalizada = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GdocFichero", inversedBy="Evaluacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $fichero;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $passwordPdf;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getFechaInicio()
    {
        return $this->fechaInicio;
    }

    /**
     * @param mixed $fechaInicio
     */
    public function setFechaInicio($fechaInicio): void
    {
        $this->fechaInicio = $fechaInicio;
    }

    /**
     * @return mixed
     */
    public function getFechaFin()
    {
        return $this->fechaFin;
    }

    /**
     * @param mixed $fechaFin
     */
    public function setFechaFin($fechaFin): void
    {
        $this->fechaFin = $fechaFin;
    }

    /**
     * @return mixed
     */
    public function getFechaProxima()
    {
        return $this->fechaProxima;
    }

    /**
     * @param mixed $fechaProxima
     */
    public function setFechaProxima($fechaProxima): void
    {
        $this->fechaProxima = $fechaProxima;
    }

    /**
     * @return mixed
     */
    public function getTipoEvaluacion()
    {
        return $this->tipoEvaluacion;
    }

    /**
     * @param mixed $tipoEvaluacion
     */
    public function setTipoEvaluacion($tipoEvaluacion): void
    {
        $this->tipoEvaluacion = $tipoEvaluacion;
    }

    /**
     * @return mixed
     */
    public function getMetodologia()
    {
        return $this->metodologia;
    }

    /**
     * @param mixed $metodologia
     */
    public function setMetodologia($metodologia): void
    {
        $this->metodologia = $metodologia;
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
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * @param mixed $tipo
     */
    public function setTipo($tipo): void
    {
        $this->tipo = $tipo;
    }

    /**
     * @return bool
     */
    public function isFinalizada(): bool
    {
        return $this->finalizada;
    }

    /**
     * @param bool $finalizada
     */
    public function setFinalizada(bool $finalizada): void
    {
        $this->finalizada = $finalizada;
    }

    /**
     * @return mixed
     */
    public function getFichero()
    {
        return $this->fichero;
    }

    /**
     * @param mixed $fichero
     */
    public function setFichero($fichero): void
    {
        $this->fichero = $fichero;
    }

    /**
     * @return mixed
     */
    public function getPasswordPdf()
    {
        return $this->passwordPdf;
    }

    /**
     * @param mixed $passwordPdf
     */
    public function setPasswordPdf($passwordPdf): void
    {
        $this->passwordPdf = $passwordPdf;
    }
}
