<?php

namespace App\Entity;

use App\Repository\NormativaRepository;
use Doctrine\ORM\Mapping as ORM;
use function GuzzleHttp\Psr7\str;

/**
 * @ORM\Entity(repositoryClass=NormativaRepository::class)
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class Normativa
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GrupoNormativa", inversedBy="Normativa")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $grupoNormativa;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $tituloEs;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $tituloCa;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $descripcionEs;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $descripcionCa;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
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
    public function getTituloEs()
    {
        return $this->tituloEs;
    }

    /**
     * @param mixed $tituloEs
     */
    public function setTituloEs($tituloEs): void
    {
        $this->tituloEs = $tituloEs;
    }

    /**
     * @return mixed
     */
    public function getTituloCa()
    {
        return $this->tituloCa;
    }

    /**
     * @param mixed $tituloCa
     */
    public function setTituloCa($tituloCa): void
    {
        $this->tituloCa = $tituloCa;
    }

    /**
     * @return mixed
     */
    public function getDescripcionEs()
    {
        return $this->descripcionEs;
    }

    /**
     * @param mixed $descripcionEs
     */
    public function setDescripcionEs($descripcionEs): void
    {
        $this->descripcionEs = $descripcionEs;
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

    public function __toString()
    {
        return (string) $this->tituloEs .' - '. $this->descripcionEs;
    }
}
