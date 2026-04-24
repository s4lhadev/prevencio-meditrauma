<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GdocFicheroRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class GdocFichero
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
	 * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="GdocPlantillas")
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
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $anulado = false;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Empresa", inversedBy="GdocFichero")
	 * @ORM\JoinColumn(nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $empresa;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\GdocPlantillas", inversedBy="GdocFichero")
	 * @ORM\JoinColumn(nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $plantilla;

    public function getId(): ?int
    {
        return $this->id;
    }

	/**
	 * @return mixed
	 */
	public function getDtcrea() {
		return $this->dtcrea;
	}

	/**
	 * @param mixed $dtcrea
	 */
	public function setDtcrea( $dtcrea ): void {
		$this->dtcrea = $dtcrea;
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
	public function getNombre() {
		return $this->nombre;
	}

	/**
	 * @param mixed $nombre
	 */
	public function setNombre( $nombre ): void {
		$this->nombre = $nombre;
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
	public function getEmpresa() {
		return $this->empresa;
	}

	/**
	 * @param mixed $empresa
	 */
	public function setEmpresa( $empresa ): void {
		$this->empresa = $empresa;
	}

	/**
	 * @return mixed
	 */
	public function getPlantilla() {
		return $this->plantilla;
	}

	/**
	 * @param mixed $plantilla
	 */
	public function setPlantilla( $plantilla ): void {
		$this->plantilla = $plantilla;
	}
}
