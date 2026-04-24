<?php

namespace App\Entity;

use App\Repository\PreguntaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PreguntaRepository::class)
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class Pregunta
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
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $descripcionCa;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TipoRespuesta", inversedBy="Pregunta")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $tipoRespuesta;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SerieRespuesta", inversedBy="Pregunta")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $serieRespuesta;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $respuestaNormal;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $valorPorDefecto;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $familia;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $subFamilia;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\IndicarHistorico", inversedBy="Pregunta")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $indicarHistorico;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Formula", inversedBy="Pregunta")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $formula;

    /**
     * @ORM\Column(type="boolean", length=2000, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $anulado = false;

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
    public function getDescripcionCa()
    {
        return $this->descripcionCa;
    }

    /**
     * @param mixed $descripcionCa
     */
    public function setDescripcionCa($descripcionCa): void
    {
        $this->descripcionCa = $descripcionCa;
    }

    /**
     * @return mixed
     */
    public function getTipoRespuesta()
    {
        return $this->tipoRespuesta;
    }

    /**
     * @param mixed $tipoRespuesta
     */
    public function setTipoRespuesta($tipoRespuesta): void
    {
        $this->tipoRespuesta = $tipoRespuesta;
    }

    /**
     * @return mixed
     */
    public function getSerieRespuesta()
    {
        return $this->serieRespuesta;
    }

    /**
     * @param mixed $serieRespuesta
     */
    public function setSerieRespuesta($serieRespuesta): void
    {
        $this->serieRespuesta = $serieRespuesta;
    }

    /**
     * @return mixed
     */
    public function getRespuestaNormal()
    {
        return $this->respuestaNormal;
    }

    /**
     * @param mixed $respuestaNormal
     */
    public function setRespuestaNormal($respuestaNormal): void
    {
        $this->respuestaNormal = $respuestaNormal;
    }

    /**
     * @return mixed
     */
    public function getValorPorDefecto()
    {
        return $this->valorPorDefecto;
    }

    /**
     * @param mixed $valorPorDefecto
     */
    public function setValorPorDefecto($valorPorDefecto): void
    {
        $this->valorPorDefecto = $valorPorDefecto;
    }

    /**
     * @return mixed
     */
    public function getFamilia()
    {
        return $this->familia;
    }

    /**
     * @param mixed $familia
     */
    public function setFamilia($familia): void
    {
        $this->familia = $familia;
    }

    /**
     * @return mixed
     */
    public function getSubFamilia()
    {
        return $this->subFamilia;
    }

    /**
     * @param mixed $subFamilia
     */
    public function setSubFamilia($subFamilia): void
    {
        $this->subFamilia = $subFamilia;
    }

    /**
     * @return mixed
     */
    public function getIndicarHistorico()
    {
        return $this->indicarHistorico;
    }

    /**
     * @param mixed $indicarHistorico
     */
    public function setIndicarHistorico($indicarHistorico): void
    {
        $this->indicarHistorico = $indicarHistorico;
    }

    /**
     * @return mixed
     */
    public function getFormula()
    {
        return $this->formula;
    }

    /**
     * @param mixed $formula
     */
    public function setFormula($formula): void
    {
        $this->formula = $formula;
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

    public function __toString()
    {
        return (string) $this->descripcion;
    }


}
