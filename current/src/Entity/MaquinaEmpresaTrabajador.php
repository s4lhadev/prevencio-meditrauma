<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MaquinaEmpresaTrabajadorRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class MaquinaEmpresaTrabajador
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MaquinaEmpresa", inversedBy="MaquinaEmpresaTrabajador")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $maquinaEmpresa;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PuestoTrabajoCentro", inversedBy="MaquinaEmpresaTrabajador")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $puestoTrabajo;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ZonaTrabajo", inversedBy="MaquinaEmpresaTrabajador")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $zonaTrabajo;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Trabajador", inversedBy="MaquinaEmpresaTrabajador")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $trabajador;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default":0})
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
    public function getTrabajador()
    {
        return $this->trabajador;
    }

    /**
     * @param mixed $trabajador
     */
    public function setTrabajador($trabajador): void
    {
        $this->trabajador = $trabajador;
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
