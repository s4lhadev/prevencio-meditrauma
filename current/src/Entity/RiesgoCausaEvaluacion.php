<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RiesgoCausaEvaluacionRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class RiesgoCausaEvaluacion
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Evaluacion", inversedBy="RiesgoCausaEvaluacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $evaluacion;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Severidad", inversedBy="RiesgoCausaEvaluacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $severidad;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Probabilidad", inversedBy="RiesgoCausaEvaluacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $probabilidad;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ValorRiesgo", inversedBy="RiesgoCausaEvaluacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $valorRiesgo;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PuestoTrabajoCentro", inversedBy="RiesgoCausaEvaluacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $puestoTrabajo;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $anulado = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $observacionCausa;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Causa", inversedBy="RiesgoCausaEvaluacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $causa;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GrupoRiesgo", inversedBy="RiesgoCausaEvaluacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $grupoRiesgo;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Riesgo", inversedBy="RiesgoCausaEvaluacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $riesgo;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ZonaTrabajo", inversedBy="RiesgoCausaEvaluacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $zonaTrabajo;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Actividad", inversedBy="RiesgoCausaEvaluacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $actividad;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Consecuencia", inversedBy="RiesgoCausaEvaluacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $consecuencia;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Danyo", inversedBy="RiesgoCausaEvaluacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $danyo;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Exposicion", inversedBy="RiesgoCausaEvaluacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $exposicion;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\FactorCoste", inversedBy="RiesgoCausaEvaluacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $factorCoste;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GradoCorreccion", inversedBy="RiesgoCausaEvaluacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $gradoCorreccion;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $finalizado = false;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $ultimoModificado = false;

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
     * @return mixed
     */
    public function getSeveridad()
    {
        return $this->severidad;
    }

    /**
     * @param mixed $severidad
     */
    public function setSeveridad($severidad): void
    {
        $this->severidad = $severidad;
    }

    /**
     * @return mixed
     */
    public function getProbabilidad()
    {
        return $this->probabilidad;
    }

    /**
     * @param mixed $probabilidad
     */
    public function setProbabilidad($probabilidad): void
    {
        $this->probabilidad = $probabilidad;
    }

    /**
     * @return mixed
     */
    public function getValorRiesgo()
    {
        return $this->valorRiesgo;
    }

    /**
     * @param mixed $valorRiesgo
     */
    public function setValorRiesgo($valorRiesgo): void
    {
        $this->valorRiesgo = $valorRiesgo;
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
    public function getObservacionCausa()
    {
        return $this->observacionCausa;
    }

    /**
     * @param mixed $observacionCausa
     */
    public function setObservacionCausa($observacionCausa): void
    {
        $this->observacionCausa = $observacionCausa;
    }

    /**
     * @return mixed
     */
    public function getCausa()
    {
        return $this->causa;
    }

    /**
     * @param mixed $causa
     */
    public function setCausa($causa): void
    {
        $this->causa = $causa;
    }

    /**
     * @return mixed
     */
    public function getGrupoRiesgo()
    {
        return $this->grupoRiesgo;
    }

    /**
     * @param mixed $grupoRiesgo
     */
    public function setGrupoRiesgo($grupoRiesgo): void
    {
        $this->grupoRiesgo = $grupoRiesgo;
    }

    /**
     * @return mixed
     */
    public function getRiesgo()
    {
        return $this->riesgo;
    }

    /**
     * @param mixed $riesgo
     */
    public function setRiesgo($riesgo): void
    {
        $this->riesgo = $riesgo;
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
    public function getActividad()
    {
        return $this->actividad;
    }

    /**
     * @param mixed $actividad
     */
    public function setActividad($actividad): void
    {
        $this->actividad = $actividad;
    }

    /**
     * @return mixed
     */
    public function getConsecuencia()
    {
        return $this->consecuencia;
    }

    /**
     * @param mixed $consecuencia
     */
    public function setConsecuencia($consecuencia): void
    {
        $this->consecuencia = $consecuencia;
    }

    /**
     * @return mixed
     */
    public function getDanyo()
    {
        return $this->danyo;
    }

    /**
     * @param mixed $danyo
     */
    public function setDanyo($danyo): void
    {
        $this->danyo = $danyo;
    }

    /**
     * @return mixed
     */
    public function getExposicion()
    {
        return $this->exposicion;
    }

    /**
     * @param mixed $exposicion
     */
    public function setExposicion($exposicion): void
    {
        $this->exposicion = $exposicion;
    }

    /**
     * @return mixed
     */
    public function getFactorCoste()
    {
        return $this->factorCoste;
    }

    /**
     * @param mixed $factorCoste
     */
    public function setFactorCoste($factorCoste): void
    {
        $this->factorCoste = $factorCoste;
    }

    /**
     * @return mixed
     */
    public function getGradoCorreccion()
    {
        return $this->gradoCorreccion;
    }

    /**
     * @param mixed $gradoCorreccion
     */
    public function setGradoCorreccion($gradoCorreccion): void
    {
        $this->gradoCorreccion = $gradoCorreccion;
    }

    /**
     * @return mixed
     */
    public function getFinalizado()
    {
        return $this->finalizado;
    }

    /**
     * @param mixed $finalizado
     */
    public function setFinalizado($finalizado): void
    {
        $this->finalizado = $finalizado;
    }

    /**
     * @return mixed
     */
    public function getUltimoModificado()
    {
        return $this->ultimoModificado;
    }

    /**
     * @param mixed $ultimoModificado
     */
    public function setUltimoModificado($ultimoModificado): void
    {
        $this->ultimoModificado = $ultimoModificado;
    }
}
