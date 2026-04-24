<?php

namespace App\Entity;

use App\Repository\PuestoTrabajoContaminanteRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PuestoTrabajoContaminanteRepository::class)
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class PuestoTrabajoContaminante
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $nombre;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $cas;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $epis;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $composicion;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $anulado = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PuestoTrabajoCentro", inversedBy="PuestoTrabajoContaminante")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $puestoTrabajo;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GrupoContaminante", inversedBy="PuestoTrabajoContaminante")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $grupoContaminante;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * @param mixed $nombre
     */
    public function setNombre($nombre): void
    {
        $this->nombre = $nombre;
    }

    /**
     * @return mixed
     */
    public function getCas()
    {
        return $this->cas;
    }

    /**
     * @param mixed $cas
     */
    public function setCas($cas): void
    {
        $this->cas = $cas;
    }

    /**
     * @return mixed
     */
    public function getEpis()
    {
        return $this->epis;
    }

    /**
     * @param mixed $epis
     */
    public function setEpis($epis): void
    {
        $this->epis = $epis;
    }

    /**
     * @return mixed
     */
    public function getComposicion()
    {
        return $this->composicion;
    }

    /**
     * @param mixed $composicion
     */
    public function setComposicion($composicion): void
    {
        $this->composicion = $composicion;
    }

    /**
     * @return bool
     */
    public function getAnulado(): bool
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
    public function getGrupoContaminante()
    {
        return $this->grupoContaminante;
    }

    /**
     * @param mixed $grupoContaminante
     */
    public function setGrupoContaminante($grupoContaminante): void
    {
        $this->grupoContaminante = $grupoContaminante;
    }
}
