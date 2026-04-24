<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProvinciaSerpaRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class ProvinciaSerpa
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
    private $codProvincia;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
    private $descripcion;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodProvincia(): ?string
    {
        return $this->codProvincia;
    }

    public function setCodProvincia(?string $codProvincia): self
    {
        $this->codProvincia = $codProvincia;

        return $this;
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

	public function __toString() {
		return $this->descripcion;
	}


}
