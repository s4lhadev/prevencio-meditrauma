<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GdocPlantillasCarpetaRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class GdocPlantillasCarpeta
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
	 * @ORM\ManyToOne(targetEntity="App\Entity\GdocPlantillasCarpeta", inversedBy="GdocPlantillasCarpeta")
	 * @ORM\JoinColumn(nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $padre;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $anulado = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(?string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

	/**
	 * @return mixed
	 */
	public function getPadre() {
		return $this->padre;
	}

	/**
	 * @param mixed $padre
	 */
	public function setPadre( $padre ): void {
		$this->padre = $padre;
	}

	/**
	 * @return mixed
	 */
	public function getAnulado() {
		return $this->anulado;
	}

	/**
	 * @param mixed $anulado
	 */
	public function setAnulado( $anulado ): void {
		$this->anulado = $anulado;
	}
}
