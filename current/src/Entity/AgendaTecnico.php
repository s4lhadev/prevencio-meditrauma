<?php

namespace App\Entity;

use App\Repository\AgendaTecnicoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AgendaTecnicoRepository::class)
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class AgendaTecnico
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="AgendaTecnico")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $usuario;

    /**
     * @ORM\Column(type="time", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $horainicio;

    /**
     * @ORM\Column(type="time", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $horafin;

    /**
     * @ORM\Column(type="time", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $duracionTramo;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $finSemanaSn = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * @param mixed $usuario
     */
    public function setUsuario($usuario): void
    {
        $this->usuario = $usuario;
    }

    /**
     * @return mixed
     */
    public function getHorainicio()
    {
        return $this->horainicio;
    }

    /**
     * @param mixed $horainicio
     */
    public function setHorainicio($horainicio): void
    {
        $this->horainicio = $horainicio;
    }

    /**
     * @return mixed
     */
    public function getHorafin()
    {
        return $this->horafin;
    }

    /**
     * @param mixed $horafin
     */
    public function setHorafin($horafin): void
    {
        $this->horafin = $horafin;
    }

    /**
     * @return mixed
     */
    public function getDuracionTramo()
    {
        return $this->duracionTramo;
    }

    /**
     * @param mixed $duracionTramo
     */
    public function setDuracionTramo($duracionTramo): void
    {
        $this->duracionTramo = $duracionTramo;
    }

    /**
     * @return mixed
     */
    public function getFinSemanaSn()
    {
        return $this->finSemanaSn;
    }

    /**
     * @param mixed $finSemanaSn
     */
    public function setFinSemanaSn($finSemanaSn): void
    {
        $this->finSemanaSn = $finSemanaSn;
    }
}
