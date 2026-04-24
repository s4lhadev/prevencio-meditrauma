<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TarifaContratoRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class TarifaContrato
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
    private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Contrato", inversedBy="tarifaContrato")
	 * @ORM\JoinColumn(nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $contrato;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $descripcion;

	/**
	 * @ORM\Column(type="float", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $importe;

	/**
	 * @ORM\Column(type="float", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $importeIva;

	/**
	 * @ORM\Column(type="integer", length=255, nullable=true)
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

	/**
	 * @return mixed
	 */
	public function getContrato() {
		return $this->contrato;
	}

	/**
	 * @param mixed $contrato
	 */
	public function setContrato( $contrato ): void {
		$this->contrato = $contrato;
	}

	/**
	 * @return mixed
	 */
	public function getDescripcion() {
		return $this->descripcion;
	}

	/**
	 * @param mixed $descripcion
	 */
	public function setDescripcion( $descripcion ): void {
		$this->descripcion = $descripcion;
	}

	/**
	 * @return mixed
	 */
	public function getImporte() {
		return $this->importe;
	}

	/**
	 * @param mixed $importe
	 */
	public function setImporte( $importe ): void {
		$this->importe = $importe;
	}

	/**
	 * @return mixed
	 */
	public function getImporteIva() {
		return $this->importeIva;
	}

	/**
	 * @param mixed $importeIva
	 */
	public function setImporteIva( $importeIva ): void {
		$this->importeIva = $importeIva;
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
