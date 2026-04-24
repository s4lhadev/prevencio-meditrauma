<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ZonaTrabajoMaquinaEmpresaRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class ZonaTrabajoMaquinaEmpresa
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ZonaTrabajo", inversedBy="ZonaTrabajoMaquinaEmpresa")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $zonaTrabajo;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MaquinaEmpresa", inversedBy="ZonaTrabajoMaquinaEmpresa")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $maquinaEmpresa;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Empresa", inversedBy="ZonaTrabajoMaquinaEmpresa")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $empresa;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Centro", inversedBy="ZonaTrabajoMaquinaEmpresa")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $centro;

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
    public function getMaquinaEmpresa()
    {
        return $this->maquinaEmpresa;
    }

    /**
     * @param mixed $maquinaEmpresa
     */
    public function setMaquinaEmpresa($maquinaEmpresa): void
    {
        $this->maquinaEmpresa = $maquinaEmpresa;
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
    public function getCentro()
    {
        return $this->centro;
    }

    /**
     * @param mixed $centro
     */
    public function setCentro($centro): void
    {
        $this->centro = $centro;
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
}
