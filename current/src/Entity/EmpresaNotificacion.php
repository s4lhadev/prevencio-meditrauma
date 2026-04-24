<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EmpresaNotificacionRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class EmpresaNotificacion
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Empresa", inversedBy="EmpresaNotificacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $empresa;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GdocFichero", inversedBy="EmpresaNotificacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $fichero;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $enviada = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $passwordPdf;

    /**
     * @ORM\Column(type="datetime", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $fechaEnvio;

    public function getId(): ?int
    {
        return $this->id;
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
    public function getEnviada()
    {
        return $this->enviada;
    }

    /**
     * @param mixed $enviada
     */
    public function setEnviada($enviada): void
    {
        $this->enviada = $enviada;
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

    /**
     * @return mixed
     */
    public function getFechaEnvio()
    {
        return $this->fechaEnvio;
    }

    /**
     * @param mixed $fechaEnvio
     */
    public function setFechaEnvio($fechaEnvio): void
    {
        $this->fechaEnvio = $fechaEnvio;
    }
}
