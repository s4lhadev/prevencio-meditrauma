<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ContratoPagoRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class ContratoPago
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Contrato", inversedBy="ContratoPago")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $contrato;

	/**
	 * @ORM\Column(type="datetime", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $vencimiento;

	/**
	 * @ORM\Column(type="integer", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $vencimientoMeses;

	/**
	 * @ORM\Column(type="integer", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $nPago;

	/**
	 * @ORM\Column(type="float", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $porcentaje;

	/**
	 * @ORM\Column(type="float", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $importeSinIva;

	/**
	 * @ORM\Column(type="float", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $importeExentoIva;

	/**
	 * @ORM\Column(type="float", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $importeSujetoIva;

	/**
	 * @ORM\Column(type="float", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $importeIva;

	/**
	 * @ORM\Column(type="float", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $importeTotal;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $facturado = false;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $textoPago;

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
	public function getVencimiento() {
		return $this->vencimiento;
	}

	/**
	 * @param mixed $vencimiento
	 */
	public function setVencimiento( $vencimiento ): void {
		$this->vencimiento = $vencimiento;
	}

	/**
	 * @return mixed
	 */
	public function getVencimientoMeses() {
		return $this->vencimientoMeses;
	}

	/**
	 * @param mixed $vencimientoMeses
	 */
	public function setVencimientoMeses( $vencimientoMeses ): void {
		$this->vencimientoMeses = $vencimientoMeses;
	}

	/**
	 * @return mixed
	 */
	public function getNPago() {
		return $this->nPago;
	}

	/**
	 * @param mixed $nPago
	 */
	public function setNPago( $nPago ): void {
		$this->nPago = $nPago;
	}

	/**
	 * @return mixed
	 */
	public function getPorcentaje() {
		return $this->porcentaje;
	}

	/**
	 * @param mixed $pocrentaje
	 */
	public function setPorcentaje( $porcentaje ): void {
		$this->porcentaje = $porcentaje;
	}

	/**
	 * @return mixed
	 */
	public function getImporteSinIva() {
		return $this->importeSinIva;
	}

	/**
	 * @param mixed $importeSinIva
	 */
	public function setImporteSinIva( $importeSinIva ): void {
		$this->importeSinIva = $importeSinIva;
	}

	/**
	 * @return mixed
	 */
	public function getImporteExentoIva() {
		return $this->importeExentoIva;
	}

	/**
	 * @param mixed $importeExentoIva
	 */
	public function setImporteExentoIva( $importeExentoIva ): void {
		$this->importeExentoIva = $importeExentoIva;
	}

	/**
	 * @return mixed
	 */
	public function getImporteSujetoIva() {
		return $this->importeSujetoIva;
	}

	/**
	 * @param mixed $importeSujetoIva
	 */
	public function setImporteSujetoIva( $importeSujetoIva ): void {
		$this->importeSujetoIva = $importeSujetoIva;
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
	public function getImporteTotal() {
		return $this->importeTotal;
	}

	/**
	 * @param mixed $importeTotal
	 */
	public function setImporteTotal( $importeTotal ): void {
		$this->importeTotal = $importeTotal;
	}

	/**
	 * @return mixed
	 */
	public function getFacturado() {
		return $this->facturado;
	}

	/**
	 * @param mixed $facturado
	 */
	public function setFacturado( $facturado ): void {
		$this->facturado = $facturado;
	}

	/**
	 * @return mixed
	 */
	public function getTextoPago() {
		return $this->textoPago;
	}

	/**
	 * @param mixed $textoPago
	 */
	public function setTextoPago( $textoPago ): void {
		$this->textoPago = $textoPago;
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
