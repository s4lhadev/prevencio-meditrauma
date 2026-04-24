<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BuscadorQueriesRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class BuscadorQueries
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
    private $dtcrea;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $nombre;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $compartida = false;

	/**
	 * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $restricciones;

	/**
	 * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $restriccionesSql;

	/**
	 * @ORM\Column(type="boolean", length=2000, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $anulado = false;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="BuscadorQueries")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $usuario;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDtcrea(): ?\DateTimeInterface
    {
        return $this->dtcrea;
    }

    public function setDtcrea(?\DateTimeInterface $dtcrea): self
    {
        $this->dtcrea = $dtcrea;

        return $this;
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

    public function getCompartida(): ?bool
    {
        return $this->compartida;
    }

    public function setCompartida(?bool $compartida): self
    {
        $this->compartida = $compartida;

        return $this;
    }

	/**
	 * @return mixed
	 */
	public function getRestricciones() {
		return $this->restricciones;
	}

	/**
	 * @param mixed $restricciones
	 */
	public function setRestricciones( $restricciones ): void {
		$this->restricciones = $restricciones;
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

	/**
	 * @return mixed
	 */
	public function getUsuario() {
		return $this->usuario;
	}

	/**
	 * @param mixed $usuario
	 */
	public function setUsuario( $usuario ): void {
		$this->usuario = $usuario;
	}

	/**
	 * @return mixed
	 */
	public function getRestriccionesSql() {
		return $this->restriccionesSql;
	}

	/**
	 * @param mixed $restriccionesSql
	 */
	public function setRestriccionesSql( $restriccionesSql ): void {
		$this->restriccionesSql = $restriccionesSql;
	}
}
