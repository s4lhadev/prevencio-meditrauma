<?php

namespace App\Entity;

use App\Repository\ProtocoloCuestionarioRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProtocoloCuestionarioRepository::class)
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class ProtocoloCuestionario
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Protocolo", inversedBy="ProtocoloCuestionario")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $protocolo;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Cuestionario", inversedBy="ProtocoloCuestionario")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $cuestionario;

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
    public function getProtocolo()
    {
        return $this->protocolo;
    }

    /**
     * @param mixed $protocolo
     */
    public function setProtocolo($protocolo): void
    {
        $this->protocolo = $protocolo;
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
