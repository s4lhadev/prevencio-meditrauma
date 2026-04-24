<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RenovacionRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class Renovacion
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
    private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Contrato", inversedBy="Renovacion")
	 * @ORM\JoinColumn(nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $contrato;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\TipoContrato", inversedBy="Renovacion")
	 * @ORM\JoinColumn(nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $tipoContrato;

	/**
	 * @ORM\Column(type="datetime", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $fechainicio;

	/**
	 * @ORM\Column(type="datetime", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $fechavencimiento;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $renovado = false;

	/**
	 * @ORM\Column(type="integer", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $documentoRenovacion;

	/**
	 * @ORM\Column(type="integer", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $tipoFechaRenovacion;

	/**
	 * @ORM\Column(type="datetime", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $fechaRenovacion;

	/**
	 * @ORM\Column(type="integer", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $fraccionamientoPago;

	/**
	 * @ORM\Column(type="integer", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $numeroPagos;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $planificacionesVigSalud = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $cancelada = false;

	/**
	 * @ORM\Column(type="datetime", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $fechaDocumentoOrigen;

	/**
	 * @ORM\Column(type="integer", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $tipoDocumentoGenerado;

	/**
	 * @ORM\Column(type="datetime", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $fechaDocumentoGenerado;

	/**
	 * @ORM\Column(type="integer", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $serieDocOrigen;

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
	public function getTipoContrato() {
		return $this->tipoContrato;
	}

	/**
	 * @param mixed $tipoContrato
	 */
	public function setTipoContrato( $tipoContrato ): void {
		$this->tipoContrato = $tipoContrato;
	}

	/**
	 * @return mixed
	 */
	public function getFechainicio() {
		return $this->fechainicio;
	}

	/**
	 * @param mixed $fechainicio
	 */
	public function setFechainicio( $fechainicio ): void {
		$this->fechainicio = $fechainicio;
	}

	/**
	 * @return mixed
	 */
	public function getFechavencimiento() {
		return $this->fechavencimiento;
	}

	/**
	 * @param mixed $fechavencimiento
	 */
	public function setFechavencimiento( $fechavencimiento ): void {
		$this->fechavencimiento = $fechavencimiento;
	}

	/**
	 * @return mixed
	 */
	public function getRenovado() {
		return $this->renovado;
	}

	/**
	 * @param mixed $renovado
	 */
	public function setRenovado( $renovado ): void {
		$this->renovado = $renovado;
	}

	/**
	 * @return mixed
	 */
	public function getDocumentoRenovacion() {
		return $this->documentoRenovacion;
	}

	/**
	 * @param mixed $documentoRenovacion
	 */
	public function setDocumentoRenovacion( $documentoRenovacion ): void {
		$this->documentoRenovacion = $documentoRenovacion;
	}

	/**
	 * @return mixed
	 */
	public function getTipoFechaRenovacion() {
		return $this->tipoFechaRenovacion;
	}

	/**
	 * @param mixed $tipoFechaRenovacion
	 */
	public function setTipoFechaRenovacion( $tipoFechaRenovacion ): void {
		$this->tipoFechaRenovacion = $tipoFechaRenovacion;
	}

	/**
	 * @return mixed
	 */
	public function getFechaRenovacion() {
		return $this->fechaRenovacion;
	}

	/**
	 * @param mixed $fechaRenovacion
	 */
	public function setFechaRenovacion( $fechaRenovacion ): void {
		$this->fechaRenovacion = $fechaRenovacion;
	}

	/**
	 * @return mixed
	 */
	public function getFraccionamientoPago() {
		return $this->fraccionamientoPago;
	}

	/**
	 * @param mixed $fraccionamientoPago
	 */
	public function setFraccionamientoPago( $fraccionamientoPago ): void {
		$this->fraccionamientoPago = $fraccionamientoPago;
	}

	/**
	 * @return mixed
	 */
	public function getNumeroPagos() {
		return $this->numeroPagos;
	}

	/**
	 * @param mixed $numeroPagos
	 */
	public function setNumeroPagos( $numeroPagos ): void {
		$this->numeroPagos = $numeroPagos;
	}

	/**
	 * @return mixed
	 */
	public function getPlanificacionesVigSalud() {
		return $this->planificacionesVigSalud;
	}

	/**
	 * @param mixed $planificacionesVigSalud
	 */
	public function setPlanificacionesVigSalud( $planificacionesVigSalud ): void {
		$this->planificacionesVigSalud = $planificacionesVigSalud;
	}

	/**
	 * @return mixed
	 */
	public function getCancelada() {
		return $this->cancelada;
	}

	/**
	 * @param mixed $cancelada
	 */
	public function setCancelada( $cancelada ): void {
		$this->cancelada = $cancelada;
	}

	/**
	 * @return mixed
	 */
	public function getFechaDocumentoOrigen() {
		return $this->fechaDocumentoOrigen;
	}

	/**
	 * @param mixed $fechaDocumentoOrigen
	 */
	public function setFechaDocumentoOrigen( $fechaDocumentoOrigen ): void {
		$this->fechaDocumentoOrigen = $fechaDocumentoOrigen;
	}

	/**
	 * @return mixed
	 */
	public function getTipoDocumentoGenerado() {
		return $this->tipoDocumentoGenerado;
	}

	/**
	 * @param mixed $tipoDocumentoGenerado
	 */
	public function setTipoDocumentoGenerado( $tipoDocumentoGenerado ): void {
		$this->tipoDocumentoGenerado = $tipoDocumentoGenerado;
	}

	/**
	 * @return mixed
	 */
	public function getFechaDocumentoGenerado() {
		return $this->fechaDocumentoGenerado;
	}

	/**
	 * @param mixed $fechaDocumentoGenerado
	 */
	public function setFechaDocumentoGenerado( $fechaDocumentoGenerado ): void {
		$this->fechaDocumentoGenerado = $fechaDocumentoGenerado;
	}

	/**
	 * @return mixed
	 */
	public function getSerieDocOrigen() {
		return $this->serieDocOrigen;
	}

	/**
	 * @param mixed $serieDocOrigen
	 */
	public function setSerieDocOrigen( $serieDocOrigen ): void {
		$this->serieDocOrigen = $serieDocOrigen;
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
