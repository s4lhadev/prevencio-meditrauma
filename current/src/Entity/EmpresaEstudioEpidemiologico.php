<?php

namespace App\Entity;

use App\Repository\EmpresaEstudioEpidemiologicoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EmpresaEstudioEpidemiologicoRepository::class)
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class EmpresaEstudioEpidemiologico
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Empresa", inversedBy="EmpresaEstudioEpidemiologico")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $empresa;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GdocFichero", inversedBy="EmpresaEstudioEpidemiologico")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $fichero;

    /**
     * @ORM\Column(type="boolean", length=2000, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $anulado = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $anyo;

    public function getId(): ?int
    {
        return $this->id;
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
    public function getFichero()
    {
        return $this->fichero;
    }

    /**
     * @param mixed $fichero
     */
    public function setFichero($fichero): void
    {
        $this->fichero = $fichero;
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
    public function getAnyo()
    {
        return $this->anyo;
    }

    /**
     * @param mixed $anyo
     */
    public function setAnyo($anyo): void
    {
        $this->anyo = $anyo;
    }
}
