<?php

namespace App\Entity;

use App\Repository\AnaliticasConfigRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AnaliticasConfigRepository::class)
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class AnaliticasConfig
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
    private $url;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $puerto;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $usuario;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $password;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $carpeta;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $carpetaResultadoAnalitica;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url): void
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getPuerto()
    {
        return $this->puerto;
    }

    /**
     * @param mixed $puerto
     */
    public function setPuerto($puerto): void
    {
        $this->puerto = $puerto;
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
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password): void
    {
        $this->password = $password;
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
    public function getCarpetaResultadoAnalitica()
    {
        return $this->carpetaResultadoAnalitica;
    }

    /**
     * @param mixed $carpetaResultadoAnalitica
     */
    public function setCarpetaResultadoAnalitica($carpetaResultadoAnalitica): void
    {
        $this->carpetaResultadoAnalitica = $carpetaResultadoAnalitica;
    }

}
