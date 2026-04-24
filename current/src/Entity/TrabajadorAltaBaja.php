<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TrabajadorAltaBajaRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class TrabajadorAltaBaja
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
    private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Trabajador", inversedBy="TrabajadorAltaBaja")
	 * @ORM\JoinColumn(nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $trabajador;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Empresa", inversedBy="TrabajadorAltaBaja")
	 * @ORM\JoinColumn(nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $empresa;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $activo = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $anulado = false;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $motivoBaja;

	/**
	 * @ORM\Column(type="datetime", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $fechaAlta;

	/**
	 * @ORM\Column(type="datetime", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $fechaBaja;

    public function getId(): ?int
    {
        return $this->id;
    }

	/**
	 * @return mixed
	 */
	public function getTrabajador() {
		return $this->trabajador;
	}

	/**
	 * @param mixed $trabajador
	 */
	public function setTrabajador( $trabajador ): void {
		$this->trabajador = $trabajador;
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
	public function getActivo() {
		return $this->activo;
	}

	/**
	 * @param mixed $activo
	 */
	public function setActivo( $activo ): void {
		$this->activo = $activo;
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
	public function getMotivoBaja() {
		return $this->motivoBaja;
	}

	/**
	 * @param mixed $motivoBaja
	 */
	public function setMotivoBaja( $motivoBaja ): void {
		$this->motivoBaja = $motivoBaja;
	}

	/**
	 * @return mixed
	 */
	public function getFechaAlta() {
		return $this->fechaAlta;
	}

	/**
	 * @param mixed $fechaAlta
	 */
	public function setFechaAlta( $fechaAlta ): void {
		$this->fechaAlta = $fechaAlta;
	}

	/**
	 * @return mixed
	 */
	public function getFechaBaja() {
		return $this->fechaBaja;
	}

	/**
	 * @param mixed $fechaBaja
	 */
	public function setFechaBaja( $fechaBaja ): void {
		$this->fechaBaja = $fechaBaja;
	}
}
