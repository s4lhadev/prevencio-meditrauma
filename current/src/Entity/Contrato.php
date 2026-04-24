<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ContratoRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class Contrato
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Empresa", inversedBy="Contrato")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $empresa;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $contrato;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $nCodigo;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $anyo;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $origen;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $referencia;

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
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $duracionRenovacion;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\TipoContrato", inversedBy="Contrato")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $tipoContrato;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\FaseContrato", inversedBy="Contrato")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $faseSituacion;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $nombreApellidos;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $dni;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $calidad;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Centro", inversedBy="Contrato")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $centro;

	/**
	 * @ORM\Column(type="integer", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $trabajadoresPrevencionTecnica;

	/**
	 * @ORM\Column(type="integer", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $trabajadoresVigilanciaSalud;

	/**
	 * @ORM\Column(type="integer", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $trabajadoresOtros;

	/**
	 * @ORM\Column(type="integer", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $visitasConcertadas;

	/**
	 * @ORM\Column(type="float", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $importeContrato;

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
	 * @ORM\Column(type="integer", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $numeroPagos;

	/**
	 * @ORM\Column(type="integer", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $porcentajePago;

	/**
	 * @ORM\Column(type="integer", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $vencimientoPago;

	/**
	 * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $textoFormaPago;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $contratoComiCol;

	/**
	 * @ORM\Column(type="float", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $comision1aCol;

	/**
	 * @ORM\Column(type="float", length=2000, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $comisionASucCol;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $contratoComiTec;

	/**
	 * @ORM\Column(type="float", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $comision1aTec;

	/**
	 * @ORM\Column(type="float", length=2000, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $comisionASucTec;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $contratoComiCom;

	/**
	 * @ORM\Column(type="float", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $comision1aCom;

	/**
	 * @ORM\Column(type="float", length=2000, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $comisionASucCom;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $contratoComiColCopy;

	/**
	 * @ORM\Column(type="float", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $comision1aColCopy;

	/**
	 * @ORM\Column(type="float", length=2000, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $comisionASucColCopy;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $contratoComiTecCopy;

	/**
	 * @ORM\Column(type="float", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $comision1aTecCopy;

	/**
	 * @ORM\Column(type="float", length=2000, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $comisionASucTecCopy;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $contratoComiComCopy;

	/**
	 * @ORM\Column(type="float", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $comision1aComCopy;

	/**
	 * @ORM\Column(type="float", length=2000, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $comisionASucComCopy;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $anulado = false;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\GdocFichero", inversedBy="Contrato")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $fichero;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $passwordPdf;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Contrato", inversedBy="Contrato")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $oldContrato;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $enviado = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $cancelado = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $renovado = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ContratoModalidad", inversedBy="Contrato")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $contratoModalidad;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $facturado = false;

    /**
     * @ORM\Column(type="datetime", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $fechaEnvio;

	/**
	 * @return mixed
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param mixed $id
	 */
	public function setId( $id ): void {
		$this->id = $id;
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
	public function getNCodigo() {
		return $this->nCodigo;
	}

	/**
	 * @param mixed $nCodigo
	 */
	public function setNCodigo( $nCodigo ): void {
		$this->nCodigo = $nCodigo;
	}

	/**
	 * @return mixed
	 */
	public function getOrigen() {
		return $this->origen;
	}

	/**
	 * @param mixed $origen
	 */
	public function setOrigen( $origen ): void {
		$this->origen = $origen;
	}

	/**
	 * @return mixed
	 */
	public function getReferencia() {
		return $this->referencia;
	}

	/**
	 * @param mixed $referencia
	 */
	public function setReferencia( $referencia ): void {
		$this->referencia = $referencia;
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
	public function getDuracionRenovacion() {
		return $this->duracionRenovacion;
	}

	/**
	 * @param mixed $duracionRenovacion
	 */
	public function setDuracionRenovacion( $duracionRenovacion ): void {
		$this->duracionRenovacion = $duracionRenovacion;
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
	public function getFaseSituacion() {
		return $this->faseSituacion;
	}

	/**
	 * @param mixed $faseSituacion
	 */
	public function setFaseSituacion( $faseSituacion ): void {
		$this->faseSituacion = $faseSituacion;
	}

	/**
	 * @return mixed
	 */
	public function getNombreApellidos() {
		return $this->nombreApellidos;
	}

	/**
	 * @param mixed $nombreApellidos
	 */
	public function setNombreApellidos( $nombreApellidos ): void {
		$this->nombreApellidos = $nombreApellidos;
	}

	/**
	 * @return mixed
	 */
	public function getDni() {
		return $this->dni;
	}

	/**
	 * @param mixed $dni
	 */
	public function setDni( $dni ): void {
		$this->dni = $dni;
	}

	/**
	 * @return mixed
	 */
	public function getCalidad() {
		return $this->calidad;
	}

	/**
	 * @param mixed $calidad
	 */
	public function setCalidad( $calidad ): void {
		$this->calidad = $calidad;
	}

	/**
	 * @return mixed
	 */
	public function getCentro() {
		return $this->centro;
	}

	/**
	 * @param mixed $centro
	 */
	public function setCentro( $centro ): void {
		$this->centro = $centro;
	}

	/**
	 * @return mixed
	 */
	public function getTrabajadoresPrevencionTecnica() {
		return $this->trabajadoresPrevencionTecnica;
	}

	/**
	 * @param mixed $trabajadoresPrevencionTecnica
	 */
	public function setTrabajadoresPrevencionTecnica( $trabajadoresPrevencionTecnica ): void {
		$this->trabajadoresPrevencionTecnica = $trabajadoresPrevencionTecnica;
	}

	/**
	 * @return mixed
	 */
	public function getTrabajadoresVigilanciaSalud() {
		return $this->trabajadoresVigilanciaSalud;
	}

	/**
	 * @param mixed $trabajadoresVigilanciaSalud
	 */
	public function setTrabajadoresVigilanciaSalud( $trabajadoresVigilanciaSalud ): void {
		$this->trabajadoresVigilanciaSalud = $trabajadoresVigilanciaSalud;
	}

	/**
	 * @return mixed
	 */
	public function getTrabajadoresOtros() {
		return $this->trabajadoresOtros;
	}

	/**
	 * @param mixed $trabajadoresOtros
	 */
	public function setTrabajadoresOtros( $trabajadoresOtros ): void {
		$this->trabajadoresOtros = $trabajadoresOtros;
	}

	/**
	 * @return mixed
	 */
	public function getVisitasConcertadas() {
		return $this->visitasConcertadas;
	}

	/**
	 * @param mixed $visitasConcertadas
	 */
	public function setVisitasConcertadas( $visitasConcertadas ): void {
		$this->visitasConcertadas = $visitasConcertadas;
	}

	/**
	 * @return mixed
	 */
	public function getImporteContrato() {
		return $this->importeContrato;
	}

	/**
	 * @param mixed $importeContrato
	 */
	public function setImporteContrato( $importeContrato ): void {
		$this->importeContrato = $importeContrato;
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
	public function getPorcentajePago() {
		return $this->porcentajePago;
	}

	/**
	 * @param mixed $porcentajePago
	 */
	public function setPorcentajePago( $porcentajePago ): void {
		$this->porcentajePago = $porcentajePago;
	}

	/**
	 * @return mixed
	 */
	public function getVencimientoPago() {
		return $this->vencimientoPago;
	}

	/**
	 * @param mixed $vencimientoPago
	 */
	public function setVencimientoPago( $vencimientoPago ): void {
		$this->vencimientoPago = $vencimientoPago;
	}

	/**
	 * @return mixed
	 */
	public function getTextoFormaPago() {
		return $this->textoFormaPago;
	}

	/**
	 * @param mixed $textoFormaPago
	 */
	public function setTextoFormaPago( $textoFormaPago ): void {
		$this->textoFormaPago = $textoFormaPago;
	}

	/**
	 * @return mixed
	 */
	public function getContratoComiCol() {
		return $this->contratoComiCol;
	}

	/**
	 * @param mixed $contratoComiCol
	 */
	public function setContratoComiCol( $contratoComiCol ): void {
		$this->contratoComiCol = $contratoComiCol;
	}

	/**
	 * @return mixed
	 */
	public function getComision1aCol() {
		return $this->comision1aCol;
	}

	/**
	 * @param mixed $comision1aCol
	 */
	public function setComision1aCol( $comision1aCol ): void {
		$this->comision1aCol = $comision1aCol;
	}

	/**
	 * @return mixed
	 */
	public function getComisionASucCol() {
		return $this->comisionASucCol;
	}

	/**
	 * @param mixed $comisionASucCol
	 */
	public function setComisionASucCol( $comisionASucCol ): void {
		$this->comisionASucCol = $comisionASucCol;
	}

	/**
	 * @return mixed
	 */
	public function getContratoComiTec() {
		return $this->contratoComiTec;
	}

	/**
	 * @param mixed $contratoComiTec
	 */
	public function setContratoComiTec( $contratoComiTec ): void {
		$this->contratoComiTec = $contratoComiTec;
	}

	/**
	 * @return mixed
	 */
	public function getComision1aTec() {
		return $this->comision1aTec;
	}

	/**
	 * @param mixed $comision1aTec
	 */
	public function setComision1aTec( $comision1aTec ): void {
		$this->comision1aTec = $comision1aTec;
	}

	/**
	 * @return mixed
	 */
	public function getComisionASucTec() {
		return $this->comisionASucTec;
	}

	/**
	 * @param mixed $comisionASucTec
	 */
	public function setComisionASucTec( $comisionASucTec ): void {
		$this->comisionASucTec = $comisionASucTec;
	}

	/**
	 * @return mixed
	 */
	public function getContratoComiCom() {
		return $this->contratoComiCom;
	}

	/**
	 * @param mixed $contratoComiCom
	 */
	public function setContratoComiCom( $contratoComiCom ): void {
		$this->contratoComiCom = $contratoComiCom;
	}

	/**
	 * @return mixed
	 */
	public function getComision1aCom() {
		return $this->comision1aCom;
	}

	/**
	 * @param mixed $comision1aCom
	 */
	public function setComision1aCom( $comision1aCom ): void {
		$this->comision1aCom = $comision1aCom;
	}

	/**
	 * @return mixed
	 */
	public function getComisionASucCom() {
		return $this->comisionASucCom;
	}

	/**
	 * @param mixed $comisionASucCom
	 */
	public function setComisionASucCom( $comisionASucCom ): void {
		$this->comisionASucCom = $comisionASucCom;
	}

	/**
	 * @return mixed
	 */
	public function getContratoComiColCopy() {
		return $this->contratoComiColCopy;
	}

	/**
	 * @param mixed $contratoComiColCopy
	 */
	public function setContratoComiColCopy( $contratoComiColCopy ): void {
		$this->contratoComiColCopy = $contratoComiColCopy;
	}

	/**
	 * @return mixed
	 */
	public function getComision1aColCopy() {
		return $this->comision1aColCopy;
	}

	/**
	 * @param mixed $comision1aColCopy
	 */
	public function setComision1aColCopy( $comision1aColCopy ): void {
		$this->comision1aColCopy = $comision1aColCopy;
	}

	/**
	 * @return mixed
	 */
	public function getComisionASucColCopy() {
		return $this->comisionASucColCopy;
	}

	/**
	 * @param mixed $comisionASucColCopy
	 */
	public function setComisionASucColCopy( $comisionASucColCopy ): void {
		$this->comisionASucColCopy = $comisionASucColCopy;
	}

	/**
	 * @return mixed
	 */
	public function getContratoComiTecCopy() {
		return $this->contratoComiTecCopy;
	}

	/**
	 * @param mixed $contratoComiTecCopy
	 */
	public function setContratoComiTecCopy( $contratoComiTecCopy ): void {
		$this->contratoComiTecCopy = $contratoComiTecCopy;
	}

	/**
	 * @return mixed
	 */
	public function getComision1aTecCopy() {
		return $this->comision1aTecCopy;
	}

	/**
	 * @param mixed $comision1aTecCopy
	 */
	public function setComision1aTecCopy( $comision1aTecCopy ): void {
		$this->comision1aTecCopy = $comision1aTecCopy;
	}

	/**
	 * @return mixed
	 */
	public function getComisionASucTecCopy() {
		return $this->comisionASucTecCopy;
	}

	/**
	 * @param mixed $comisionASucTecCopy
	 */
	public function setComisionASucTecCopy( $comisionASucTecCopy ): void {
		$this->comisionASucTecCopy = $comisionASucTecCopy;
	}

	/**
	 * @return mixed
	 */
	public function getContratoComiComCopy() {
		return $this->contratoComiComCopy;
	}

	/**
	 * @param mixed $contratoComiComCopy
	 */
	public function setContratoComiComCopy( $contratoComiComCopy ): void {
		$this->contratoComiComCopy = $contratoComiComCopy;
	}

	/**
	 * @return mixed
	 */
	public function getComision1aComCopy() {
		return $this->comision1aComCopy;
	}

	/**
	 * @param mixed $comision1aComCopy
	 */
	public function setComision1aComCopy( $comision1aComCopy ): void {
		$this->comision1aComCopy = $comision1aComCopy;
	}

	/**
	 * @return mixed
	 */
	public function getComisionASucComCopy() {
		return $this->comisionASucComCopy;
	}

	/**
	 * @param mixed $comisionASucComCopy
	 */
	public function setComisionASucComCopy( $comisionASucComCopy ): void {
		$this->comisionASucComCopy = $comisionASucComCopy;
	}

	/**
	 * @return mixed
	 */
	public function getAnyo() {
		return $this->anyo;
	}

	/**
	 * @param mixed $anyo
	 */
	public function setAnyo( $anyo ): void {
		$this->anyo = $anyo;
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
	public function getFichero() {
		return $this->fichero;
	}

	/**
	 * @param mixed $fichero
	 */
	public function setFichero( $fichero ): void {
		$this->fichero = $fichero;
	}

    /**
     * @return mixed
     */
    public function getPasswordPdf()
    {
        return $this->passwordPdf;
    }

    /**
     * @param mixed $passwordPdf
     */
    public function setPasswordPdf($passwordPdf): void
    {
        $this->passwordPdf = $passwordPdf;
    }

    /**
     * @return mixed
     */
    public function getOldContrato()
    {
        return $this->oldContrato;
    }

    /**
     * @param mixed $oldContrato
     */
    public function setOldContrato($oldContrato): void
    {
        $this->oldContrato = $oldContrato;
    }

	public function getContratoEmpresa(){
		return $this->contrato .' - '.$this->empresa;
	}

    /**
     * @return mixed
     */
    public function getEnviado()
    {
        return $this->enviado;
    }

    /**
     * @param mixed $enviado
     */
    public function setEnviado($enviado): void
    {
        $this->enviado = $enviado;
    }

    /**
     * @return mixed
     */
    public function getCancelado()
    {
        return $this->cancelado;
    }

    /**
     * @param mixed $cancelado
     */
    public function setCancelado($cancelado): void
    {
        $this->cancelado = $cancelado;
    }

    /**
     * @return mixed
     */
    public function getRenovado()
    {
        return $this->renovado;
    }

    /**
     * @param mixed $renovado
     */
    public function setRenovado($renovado): void
    {
        $this->renovado = $renovado;
    }

    /**
     * @return mixed
     */
    public function getContratoModalidad()
    {
        return $this->contratoModalidad;
    }

    /**
     * @param mixed $contratoModalidad
     */
    public function setContratoModalidad($contratoModalidad): void
    {
        $this->contratoModalidad = $contratoModalidad;
    }

    /**
     * @return mixed
     */
    public function getFacturado()
    {
        return $this->facturado;
    }

    /**
     * @param mixed $facturado
     */
    public function setFacturado($facturado): void
    {
        $this->facturado = $facturado;
    }

    /**
     * @return mixed
     */
    public function getFechaEnvio()
    {
        return $this->fechaEnvio;
    }

    /**
     * @param mixed $fechaEnvio
     */
    public function setFechaEnvio($fechaEnvio): void
    {
        $this->fechaEnvio = $fechaEnvio;
    }
}
