<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GrupoNormativaEvaluacionRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class GrupoNormativaEvaluacion
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GrupoNormativa", inversedBy="GrupoNormativaEvaluacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $grupoNormativa;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Evaluacion", inversedBy="GrupoNormativaEvaluacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $evaluacion;

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
    public function getGrupoNormativa()
    {
        return $this->grupoNormativa;
    }

    /**
     * @param mixed $grupoNormativa
     */
    public function setGrupoNormativa($grupoNormativa): void
    {
        $this->grupoNormativa = $grupoNormativa;
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
}
