<?php

namespace App\Entity;

use App\Repository\PuestoTrabajoProtocoloRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PuestoTrabajoProtocoloRepository::class)
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class PuestoTrabajoProtocolo
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PuestoTrabajoCentro", inversedBy="PuestoTrabajoProtocolo")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $puestoTrabajo;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Protocolo", inversedBy="PuestoTrabajoProtocolo")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $protocolo;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Empresa", inversedBy="PuestoTrabajoProtocolo")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $empresa;

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
