<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PreventivaTrabajadorRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class PreventivaTrabajador
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
    private $descripcion;

    /**
     * @ORM\Column(type="boolean", length=2000, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $anulado = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GrupoPreventiva", inversedBy="PreventivaTrabajador")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $grupoPreventiva;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $descripcionCa;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $descripcionEs;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * @param mixed $descripcion
     */
    public function setDescripcion($descripcion): void
    {
        $this->descripcion = $descripcion;
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
    public function getGrupoPreventiva()
    {
        return $this->grupoPreventiva;
    }

    /**
     * @param mixed $grupoPreventiva
     */
    public function setGrupoPreventiva($grupoPreventiva): void
    {
        $this->grupoPreventiva = $grupoPreventiva;
    }

    /**
     * @return mixed
     */
    public function getDescripcionCa()
    {
        return $this->descripcionCa;
    }

    /**
     * @param mixed $descripcionCa
     */
    public function setDescripcionCa($descripcionCa): void
    {
        $this->descripcionCa = $descripcionCa;
    }

    /**
     * @return mixed
     */
    public function getDescripcionEs()
    {
        return $this->descripcionEs;
    }

    /**
     * @param mixed $descripcionEs
     */
    public function setDescripcionEs($descripcionEs): void
    {
        $this->descripcionEs = $descripcionEs;
    }

    public function getPreventivaDesc()
    {
        if(!is_null($this->getGrupoPreventiva())){
            return (string) $this->getGrupoPreventiva()->getDescripcion() . ' - '.$this->descripcion;
        }else{
            return (string) $this->descripcion;
        }
    }

    public function __toString()
    {
        return (string) $this->descripcion;
    }
}
