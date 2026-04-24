<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MunicipioSerpaRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class MunicipioSerpa
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
    private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\ProvinciaSerpa", inversedBy="MunicipioSerpa")
	 * @ORM\JoinColumn(nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
    private $provincia;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
    private $codMunicipio;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
    private $descripcion;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProvincia(): ?string
    {
        return $this->provincia;
    }

    public function setProvincia(?string $provincia): self
    {
        $this->provincia = $provincia;

        return $this;
    }

	/**
	 * @return mixed
	 */
	public function getCodMunicipio() {
		return $this->codMunicipio;
	}

	/**
	 * @param mixed $codMunicipio
	 */
	public function setCodMunicipio( $codMunicipio ): void {
		$this->codMunicipio = $codMunicipio;
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
}
