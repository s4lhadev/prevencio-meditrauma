<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ListaServiciosContratadosRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class ListaServiciosContratados
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
	 * @ORM\ManyToOne(targetEntity="App\Entity\TipoServicioContratado", inversedBy="ListaServiciosContratados")
	 * @ORM\JoinColumn(nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $tipo;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $anulado = false;

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

	/**
	 * @return mixed
	 */
	public function getTipo() {
		return $this->tipo;
	}

	/**
	 * @param mixed $tipo
	 */
	public function setTipo( $tipo ): void {
		$this->tipo = $tipo;
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
