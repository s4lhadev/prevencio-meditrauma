<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GiroBancarioRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class GiroBancario
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
    private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Facturacion", inversedBy="GiroBancario")
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
	 * @ORM\Column(type="datetime", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $fecha;

	/**
	 * @ORM\Column(type="datetime", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $vencimiento;

	/**
	 * @ORM\Column(type="float", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $importe;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\DatosBancarios", inversedBy="GiroBancario")
	 * @ORM\JoinColumn(nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $cuenta;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $girado = false;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $observaciones;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $manual = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $devolucion = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $comision = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $esFactura = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $pagoConfirmado = false;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Remesa", inversedBy="GiroBancario")
	 * @ORM\JoinColumn(nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $remesa;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $remesado = false;

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
	public function getFecha() {
		return $this->fecha;
	}

	/**
	 * @param mixed $fecha
	 */
	public function setFecha( $fecha ): void {
		$this->fecha = $fecha;
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
	public function getCuenta() {
		return $this->cuenta;
	}

	/**
	 * @param mixed $cuenta
	 */
	public function setCuenta( $cuenta ): void {
		$this->cuenta = $cuenta;
	}

	/**
	 * @return mixed
	 */
	public function getGirado() {
		return $this->girado;
	}

	/**
	 * @param mixed $girado
	 */
	public function setGirado( $girado ): void {
		$this->girado = $girado;
	}

	/**
	 * @return mixed
	 */
	public function getObservaciones() {
		return $this->observaciones;
	}

	/**
	 * @param mixed $observaciones
	 */
	public function setObservaciones( $observaciones ): void {
		$this->observaciones = $observaciones;
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
	public function getDevolucion() {
		return $this->devolucion;
	}

	/**
	 * @param mixed $devolucion
	 */
	public function setDevolucion( $devolucion ): void {
		$this->devolucion = $devolucion;
	}

	/**
	 * @return mixed
	 */
	public function getComision() {
		return $this->comision;
	}

	/**
	 * @param mixed $comision
	 */
	public function setComision( $comision ): void {
		$this->comision = $comision;
	}

	/**
	 * @return mixed
	 */
	public function getEsFactura() {
		return $this->esFactura;
	}

	/**
	 * @param mixed $esFactura
	 */
	public function setEsFactura( $esFactura ): void {
		$this->esFactura = $esFactura;
	}

	/**
	 * @return mixed
	 */
	public function getPagoConfirmado() {
		return $this->pagoConfirmado;
	}

	/**
	 * @param mixed $pagoConfirmado
	 */
	public function setPagoConfirmado( $pagoConfirmado ): void {
		$this->pagoConfirmado = $pagoConfirmado;
	}

	/**
	 * @return mixed
	 */
	public function getRemesa() {
		return $this->remesa;
	}

	/**
	 * @param mixed $remesa
	 */
	public function setRemesa( $remesa ): void {
		$this->remesa = $remesa;
	}

    /**
     * @return mixed
     */
    public function getRemesado()
    {
        return $this->remesado;
    }

    /**
     * @param mixed $remesado
     */
    public function setRemesado($remesado): void
    {
        $this->remesado = $remesado;
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
