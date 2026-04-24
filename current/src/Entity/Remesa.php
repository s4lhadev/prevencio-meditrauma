<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RemesaRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class Remesa
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
    private $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
    private $fecha;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $descripcion;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
    private $archivo;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
    private $ordenante;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
    private $cuaderno;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
    private $fechaCargo;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
    private $ruta;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
    private $numRemesa;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media", inversedBy="GdocPlantillas")
	 * @ORM\JoinColumn(nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $media;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(?\DateTimeInterface $fecha): self
    {
        $this->fecha = $fecha;

        return $this;
    }

    public function getArchivo(): ?string
    {
        return $this->archivo;
    }

    public function setArchivo(?string $archivo): self
    {
        $this->archivo = $archivo;

        return $this;
    }

    public function getOrdenante(): ?int
    {
        return $this->ordenante;
    }

    public function setOrdenante(?int $ordenante): self
    {
        $this->ordenante = $ordenante;

        return $this;
    }

    public function getCuaderno(): ?int
    {
        return $this->cuaderno;
    }

    public function setCuaderno(?int $cuaderno): self
    {
        $this->cuaderno = $cuaderno;

        return $this;
    }

    public function getFechaCargo(): ?\DateTimeInterface
    {
        return $this->fechaCargo;
    }

    public function setFechaCargo(?\DateTimeInterface $fechaCargo): self
    {
        $this->fechaCargo = $fechaCargo;

        return $this;
    }

    public function getRuta(): ?string
    {
        return $this->ruta;
    }

    public function setRuta(?string $ruta): self
    {
        $this->ruta = $ruta;

        return $this;
    }

    public function getNumRemesa(): ?string
    {
        return $this->numRemesa;
    }

    public function setNumRemesa(?string $numRemesa): self
    {
        $this->numRemesa = $numRemesa;

        return $this;
    }

	/**
	 * @return mixed
	 */
	public function getMedia() {
		return $this->media;
	}

	/**
	 * @param mixed $media
	 */
	public function setMedia( $media ): void {
		$this->media = $media;
	}
}
