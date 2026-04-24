<?php

namespace App\Entity;

use App\Repository\CuestionarioPreguntaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CuestionarioPreguntaRepository::class)
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class CuestionarioPregunta
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $grupo;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $orden;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Cuestionario", inversedBy="CuestionarioPregunta")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $cuestionario;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pregunta", inversedBy="CuestionarioPregunta")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $pregunta;

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
    public function getGrupo()
    {
        return $this->grupo;
    }

    /**
     * @param mixed $grupo
     */
    public function setGrupo($grupo): void
    {
        $this->grupo = $grupo;
    }

    /**
     * @return mixed
     */
    public function getOrden()
    {
        return $this->orden;
    }

    /**
     * @param mixed $orden
     */
    public function setOrden($orden): void
    {
        $this->orden = $orden;
    }

    /**
     * @return mixed
     */
    public function getCuestionario()
    {
        return $this->cuestionario;
    }

    /**
     * @param mixed $cuestionario
     */
    public function setCuestionario($cuestionario): void
    {
        $this->cuestionario = $cuestionario;
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
