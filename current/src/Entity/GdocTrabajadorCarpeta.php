<?php

namespace App\Entity;

use App\Repository\GdocTrabajadorCarpetaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GdocTrabajadorCarpetaRepository::class)
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class GdocTrabajadorCarpeta
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
     * @ORM\ManyToOne(targetEntity="App\Entity\GdocTrabajadorCarpeta", inversedBy="GdocTrabajadorCarpeta")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $padre;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Trabajador", inversedBy="GdocTrabajadorCarpeta")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $trabajador;

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
    public function getPadre()
    {
        return $this->padre;
    }

    /**
     * @param mixed $padre
     */
    public function setPadre($padre): void
    {
        $this->padre = $padre;
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
