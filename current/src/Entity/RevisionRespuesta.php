<?php

namespace App\Entity;

use App\Repository\RevisionRespuestaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RevisionRespuestaRepository::class)
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class RevisionRespuesta
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Revision", inversedBy="RevisionRespuesta")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $revision;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Cuestionario", inversedBy="RevisionRespuesta")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $cuestionario;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pregunta", inversedBy="RevisionRespuesta")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $pregunta;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $respuesta;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getRevision()
    {
        return $this->revision;
    }

    /**
     * @param mixed $revision
     */
    public function setRevision($revision): void
    {
        $this->revision = $revision;
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
}
