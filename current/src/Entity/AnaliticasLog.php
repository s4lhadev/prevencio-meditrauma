<?php

namespace App\Entity;

use App\Repository\AnaliticasLogRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AnaliticasLogRepository::class)
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class AnaliticasLog
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $dtcrea;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Revision", inversedBy="AnaliticasLog")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $revision;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $error;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $descargado = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $nombreFichero;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getDtcrea()
    {
        return $this->dtcrea;
    }

    /**
     * @param mixed $dtcrea
     */
    public function setDtcrea($dtcrea): void
    {
        $this->dtcrea = $dtcrea;
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
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param mixed $error
     */
    public function setError($error): void
    {
        $this->error = $error;
    }

    /**
     * @return mixed
     */
    public function getDescargado()
    {
        return $this->descargado;
    }

    /**
     * @param mixed $descargado
     */
    public function setDescargado($descargado): void
    {
        $this->descargado = $descargado;
    }

    /**
     * @return mixed
     */
    public function getNombreFichero()
    {
        return $this->nombreFichero;
    }

    /**
     * @param mixed $nombreFichero
     */
    public function setNombreFichero($nombreFichero): void
    {
        $this->nombreFichero = $nombreFichero;
    }

}
