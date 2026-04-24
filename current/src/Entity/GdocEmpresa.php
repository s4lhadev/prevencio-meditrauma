<?php

namespace App\Entity;

use App\Repository\GdocEmpresaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GdocEmpresaRepository::class)
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class GdocEmpresa
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $dtcrea;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="GdocEmpresa")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $usuario;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $nombre;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $observaciones;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GdocEmpresaCarpeta", inversedBy="GdocEmpresa")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $carpeta;

    /**
     * @ORM\Column(type="boolean", length=2000, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $anulado;

    /**
     * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", inversedBy="GdocEmpresa")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $media;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $nombreCompleto;

    /**
     * @ORM\Column(type="datetime", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $dtmodifica;

    /**
     * @ORM\Column(type="datetime", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    public $fechaProxVisita;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="GdocEmpresa")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $usuarioModifica;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Empresa", inversedBy="GdocEmpresa")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $empresa;

    /**
     * @return mixed
     */
    public function getFechaProxVis()
    {
        return $this->fechaProxVisita;
    }

    /**
     * @param mixed $fechaProxVisita
     */
    public function setFechaProxVis($fechaProxVisita): void
    {
        $this->fechaProxVisita = $fechaProxVisita;
    }

    /**
     * @return mixed
     */
    public function getObservaciones()
    {
        return $this->observaciones;
    }

    /**
     * @param mixed $observaciones
     */
    public function setObservaciones($observaciones): void
    {
        $this->observaciones = $observaciones;
    }

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
    public function getDtcrea()
    {
        return $this->dtcrea;
    }

    /**
     * @param mixed $dtcrea
     */
    public function setDtcrea($dtcrea): void
    {
        $this->dtcrea = $dtcrea;
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
    public function getCarpeta()
    {
        return $this->carpeta;
    }

    /**
     * @param mixed $carpeta
     */
    public function setCarpeta($carpeta): void
    {
        $this->carpeta = $carpeta;
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
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @param mixed $media
     */
    public function setMedia($media): void
    {
        $this->media = $media;
    }

    /**
     * @return mixed
     */
    public function getNombreCompleto()
    {
        return $this->nombreCompleto;
    }

    /**
     * @param mixed $nombreCompleto
     */
    public function setNombreCompleto($nombreCompleto): void
    {
        $this->nombreCompleto = $nombreCompleto;
    }

    /**
     * @return mixed
     */
    public function getDtmodifica()
    {
        return $this->dtmodifica;
    }

    /**
     * @param mixed $dtmodifica
     */
    public function setDtmodifica($dtmodifica): void
    {
        $this->dtmodifica = $dtmodifica;
    }

    /**
     * @return mixed
     */
    public function getUsuarioModifica()
    {
        return $this->usuarioModifica;
    }

    /**
     * @param mixed $usuarioModifica
     */
    public function setUsuarioModifica($usuarioModifica): void
    {
        $this->usuarioModifica = $usuarioModifica;
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

}
