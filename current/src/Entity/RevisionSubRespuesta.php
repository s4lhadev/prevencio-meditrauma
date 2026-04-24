<?php

namespace App\Entity;

use App\Repository\RevisionSubRespuestaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RevisionSubRespuestaRepository::class)
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class RevisionSubRespuesta
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\RevisionRespuesta", inversedBy="RevisionSubRespuesta")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $revisionRespuesta;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $respuesta;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $orden;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $idRiesgo;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CuestionarioPregunta", inversedBy="RevisionSubRespuesta")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $cuestionarioPregunta;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getRevisionRespuesta()
    {
        return $this->revisionRespuesta;
    }

    /**
     * @param mixed $revisionRespuesta
     */
    public function setRevisionRespuesta($revisionRespuesta): void
    {
        $this->revisionRespuesta = $revisionRespuesta;
    }

    /**
     * @return mixed
     */
    public function getRespuesta()
    {
        return $this->respuesta;
    }

    /**
     * @param mixed $respuesta
     */
    public function setRespuesta($respuesta): void
    {
        $this->respuesta = $respuesta;
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
    public function getIdRiesgo()
    {
        return $this->idRiesgo;
    }

    /**
     * @param mixed $idRiesgo
     */
    public function setIdRiesgo($idRiesgo): void
    {
        $this->idRiesgo = $idRiesgo;
    }

    /**
     * @return mixed
     */
    public function getCuestionarioPregunta()
    {
        return $this->cuestionarioPregunta;
    }

    /**
     * @param mixed $cuestionarioPregunta
     */
    public function setCuestionarioPregunta($cuestionarioPregunta): void
    {
        $this->cuestionarioPregunta = $cuestionarioPregunta;
    }
}
