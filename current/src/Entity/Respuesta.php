<?php

namespace App\Entity;

use App\Repository\RespuestaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RespuestaRepository::class)
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class Respuesta
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SerieRespuesta", inversedBy="Respuesta")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $serieRespuesta;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pregunta", inversedBy="Respuesta")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $pregunta;

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
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $sub;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $valorDesde;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $valorDesdeNumerico;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $valorHasta;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $valorHastaNumerico;

    /**
     * @ORM\Column(type="boolean", length=2000, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $informeMedico = false;

    /**
     * @ORM\Column(type="boolean", length=2000, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $informeFinal = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $textoSiValorCorresponde;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ConsejoMedico", inversedBy="Pregunta")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $consejoMedico;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $respuestaProblemas;

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
    public function getPregunta()
    {
        return $this->pregunta;
    }

    /**
     * @param mixed $pregunta
     */
    public function setPregunta($pregunta): void
    {
        $this->pregunta = $pregunta;
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
    public function getSub()
    {
        return $this->sub;
    }

    /**
     * @param mixed $sub
     */
    public function setSub($sub): void
    {
        $this->sub = $sub;
    }

    /**
     * @return mixed
     */
    public function getValorDesde()
    {
        return $this->valorDesde;
    }

    /**
     * @param mixed $valorDesde
     */
    public function setValorDesde($valorDesde): void
    {
        $this->valorDesde = $valorDesde;
    }

    /**
     * @return mixed
     */
    public function getValorDesdeNumerico()
    {
        return $this->valorDesdeNumerico;
    }

    /**
     * @param mixed $valorDesdeNumerico
     */
    public function setValorDesdeNumerico($valorDesdeNumerico): void
    {
        $this->valorDesdeNumerico = $valorDesdeNumerico;
    }

    /**
     * @return mixed
     */
    public function getValorHasta()
    {
        return $this->valorHasta;
    }

    /**
     * @param mixed $valorHasta
     */
    public function setValorHasta($valorHasta): void
    {
        $this->valorHasta = $valorHasta;
    }

    /**
     * @return mixed
     */
    public function getValorHastaNumerico()
    {
        return $this->valorHastaNumerico;
    }

    /**
     * @param mixed $valorHastaNumerico
     */
    public function setValorHastaNumerico($valorHastaNumerico): void
    {
        $this->valorHastaNumerico = $valorHastaNumerico;
    }

    /**
     * @return mixed
     */
    public function getInformeMedico()
    {
        return $this->informeMedico;
    }

    /**
     * @param mixed $informeMedico
     */
    public function setInformeMedico($informeMedico): void
    {
        $this->informeMedico = $informeMedico;
    }

    /**
     * @return mixed
     */
    public function getInformeFinal()
    {
        return $this->informeFinal;
    }

    /**
     * @param mixed $informeFinal
     */
    public function setInformeFinal($informeFinal): void
    {
        $this->informeFinal = $informeFinal;
    }

    /**
     * @return mixed
     */
    public function getTextoSiValorCorresponde()
    {
        return $this->textoSiValorCorresponde;
    }

    /**
     * @param mixed $textoSiValorCorresponde
     */
    public function setTextoSiValorCorresponde($textoSiValorCorresponde): void
    {
        $this->textoSiValorCorresponde = $textoSiValorCorresponde;
    }

    /**
     * @return mixed
     */
    public function getConsejoMedico()
    {
        return $this->consejoMedico;
    }

    /**
     * @param mixed $consejoMedico
     */
    public function setConsejoMedico($consejoMedico): void
    {
        $this->consejoMedico = $consejoMedico;
    }

    /**
     * @return mixed
     */
    public function getRespuestaProblemas()
    {
        return $this->respuestaProblemas;
    }

    /**
     * @param mixed $respuestaProblemas
     */
    public function setRespuestaProblemas($respuestaProblemas): void
    {
        $this->respuestaProblemas = $respuestaProblemas;
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
}
