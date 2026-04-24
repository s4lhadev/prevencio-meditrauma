<?php

namespace App\Entity;

use App\Repository\GdocEmpresaCarpetaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GdocEmpresaCarpetaRepository::class)
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class GdocEmpresaCarpeta
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
     * @ORM\ManyToOne(targetEntity="App\Entity\GdocEmpresaCarpeta", inversedBy="GdocEmpresaCarpeta")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $padre;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Empresa", inversedBy="GdocEmpresaCarpeta")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $empresa;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $anulado = false;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $compartida = false;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
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

    /**
     * @return mixed
     */
    public function getCompartida()
    {
        return $this->compartida;
    }

    /**
     * @param mixed $compartida
     */
    public function setCompartida($compartida): void
    {
        $this->compartida = $compartida;
    }

}
