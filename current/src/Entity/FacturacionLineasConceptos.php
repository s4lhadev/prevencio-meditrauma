<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FacturacionLineasConceptosRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class FacturacionLineasConceptos
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
    private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Facturacion", inversedBy="FacturacionLineas")
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
	 * @ORM\Column(type="integer", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $unidades;

	/**
	 * @ORM\Column(type="float", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $importeUnidad;

	/**
	 * @ORM\Column(type="float", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $iva;

	/**
	 * @ORM\Column(type="float", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $importe;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $manual;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $ivaSn;

	/**
	 * @ORM\Column(type="float", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $porciva;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $anulado = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Concepto", inversedBy="FacturacionLineas")
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
	public function getImporteUnidad() {
		return $this->importeUnidad;
	}

	/**
	 * @param mixed $importeUnidad
	 */
	public function setImporteUnidad( $importeUnidad ): void {
		$this->importeUnidad = $importeUnidad;
	}

	/**
	 * @return mixed
	 */
	public function getIva() {
		return $this->iva;
	}

	/**
	 * @param mixed $iva
	 */
	public function setIva( $iva ): void {
		$this->iva = $iva;
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
	public function getIvaSn() {
		return $this->ivaSn;
	}

	/**
	 * @param mixed $ivaSn
	 */
	public function setIvaSn( $ivaSn ): void {
		$this->ivaSn = $ivaSn;
	}

	/**
	 * @return mixed
	 */
	public function getPorciva() {
		return $this->porciva;
	}

	/**
	 * @param mixed $porciva
	 */
	public function setPorciva( $porciva ): void {
		$this->porciva = $porciva;
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
