<?php

namespace App\Entity;

use App\Repository\TrabajadorEnfermedadRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TrabajadorEnfermedadRepository::class)
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class TrabajadorEnfermedad
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Trabajador", inversedBy="TrabajadorEnfermedad")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $trabajador;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Enfermedad", inversedBy="TrabajadorEnfermedad")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $enfermedad;

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
    public function getEnfermedad()
    {
        return $this->enfermedad;
    }

    /**
     * @param mixed $enfermedad
     */
    public function setEnfermedad($enfermedad): void
    {
        $this->enfermedad = $enfermedad;
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
