<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FacturacionLineasPagosRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class FacturacionLineasPagos
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
    private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Facturacion", inversedBy="FacturacionLineasPagos")
	 * @ORM\JoinColumn(nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $facturacion;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $concepto;

	/**
	 * @ORM\Column(type="float", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $porcentaje;

	/**
	 * @ORM\Column(type="integer", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $meses;

	/**
	 * @ORM\Column(type="datetime", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $vencimiento;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $facturado;

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
	 * @ORM\Column(type="integer", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $unidades;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\ContratoPago", inversedBy="FacturacionLineasPagos")
	 * @ORM\JoinColumn(nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $pago;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $manual;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $anulado = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Concepto", inversedBy="FacturacionLineasPagos")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $conceptoFacturacion;

    public function getId(): ?int
    {
        return $this->id;
    }

	/**
	 * @return mixed
	 */
	public function getPago() {
		return $this->pago;
	}

	/**
	 * @param mixed $pago
	 */
	public function setPago( $pago ): void {
		$this->pago = $pago;
	}

	/**
	 * @return mixed
	 */
	public function getFacturacion() {
		return $this->facturacion;
	}

	/**
	 * @param mixed $facturacion
	 */
	public function setFacturacion( $facturacion ): void {
		$this->facturacion = $facturacion;
	}

	/**
	 * @return mixed
	 */
	public function getUnidades() {
		return $this->unidades;
	}

	/**
	 * @param mixed $unidades
	 */
	public function setUnidades( $unidades ): void {
		$this->unidades = $unidades;
	}

	/**
	 * @return mixed
	 */
	public function getConcepto() {
		return $this->concepto;
	}

	/**
	 * @param mixed $concepto
	 */
	public function setConcepto( $concepto ): void {
		$this->concepto = $concepto;
	}

	/**
	 * @return mixed
	 */
	public function getPorcentaje() {
		return $this->porcentaje;
	}

	/**
	 * @param mixed $porcentaje
	 */
	public function setPorcentaje( $porcentaje ): void {
		$this->porcentaje = $porcentaje;
	}

	/**
	 * @return mixed
	 */
	public function getMeses() {
		return $this->meses;
	}

	/**
	 * @param mixed $meses
	 */
	public function setMeses( $meses ): void {
		$this->meses = $meses;
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
	public function getManual() {
		return $this->manual;
	}

	/**
	 * @param mixed $manual
	 */
	public function setManual( $manual ): void {
		$this->manual = $manual;
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
    public function getConceptoFacturacion()
    {
        return $this->conceptoFacturacion;
    }

    /**
     * @param mixed $conceptoFacturacion
     */
    public function setConceptoFacturacion($conceptoFacturacion): void
    {
        $this->conceptoFacturacion = $conceptoFacturacion;
    }

}
