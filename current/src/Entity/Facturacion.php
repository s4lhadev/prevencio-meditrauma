<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FacturacionRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class Facturacion
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
    private $fecha;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $importeSinIva;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $iva;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $importeTotal;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $numFac;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Contrato", inversedBy="Facturacion")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $contrato;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\FormaPago", inversedBy="Facturacion")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $formaPago;

	/**
	 * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $observaciones;

	/**
	 * @ORM\Column(type="boolean", length=2000, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $renovacion;

	/**
	 * @ORM\Column(type="boolean", length=2000, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $comisionableColaborador;

	/**
	 * @ORM\Column(type="float", length=2000, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $comisionColaborador;

	/**
	 * @ORM\Column(type="boolean", length=2000, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $pagadaComisionColaborador;

	/**
	 * @ORM\Column(type="boolean", length=2000, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $comisionableTecnico;

	/**
	 * @ORM\Column(type="float", length=2000, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $comisionTecnico;

	/**
	 * @ORM\Column(type="boolean", length=2000, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $pagadaComisionTecnico;

	/**
	 * @ORM\Column(type="boolean", length=2000, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $comisionableComercial;

	/**
	 * @ORM\Column(type="float", length=2000, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $comisionComercial;

	/**
	 * @ORM\Column(type="boolean", length=2000, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $pagadaComisionComercial;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Empresa", inversedBy="Facturacion")
	 * @ORM\JoinColumn(nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $empresa;

	/**
	 * @ORM\Column(type="boolean", length=2000, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $pagada;

	/**
	 * @ORM\Column(type="boolean", length=2000, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $cancelada;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\SerieFactura", inversedBy="Facturacion")
	 * @ORM\JoinColumn(nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $serie;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $codigo;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $anyo;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $numero;

	/**
	 * @ORM\Column(type="boolean", length=2000, nullable=true, options={"default":0})
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Facturacion", inversedBy="Facturacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $facturaAsociada;

	/**
	 * @ORM\Column(type="boolean", length=2000, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $enviada = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Facturacion", inversedBy="Facturacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $oldFactura;

    /**
     * @ORM\Column(type="boolean", length=2000, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $historico = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $passwordPdf;

    /**
     * @ORM\Column(type="datetime", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $fechaEnvio;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Facturacion", inversedBy="Facturacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $facturaRectificativa;

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
	public function getNumFac() {
		return $this->numFac;
	}

	/**
	 * @param mixed $numFac
	 */
	public function setNumFac( $numFac ): void {
		$this->numFac = $numFac;
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
	public function getFormaPago() {
		return $this->formaPago;
	}

	/**
	 * @param mixed $formaPago
	 */
	public function setFormaPago( $formaPago ): void {
		$this->formaPago = $formaPago;
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
	public function getRenovacion() {
		return $this->renovacion;
	}

	/**
	 * @param mixed $renovacion
	 */
	public function setRenovacion( $renovacion ): void {
		$this->renovacion = $renovacion;
	}

	/**
	 * @return mixed
	 */
	public function getComisionableColaborador() {
		return $this->comisionableColaborador;
	}

	/**
	 * @param mixed $comisionableColaborador
	 */
	public function setComisionableColaborador( $comisionableColaborador ): void {
		$this->comisionableColaborador = $comisionableColaborador;
	}

	/**
	 * @return mixed
	 */
	public function getComisionColaborador() {
		return $this->comisionColaborador;
	}

	/**
	 * @param mixed $comisionColaborador
	 */
	public function setComisionColaborador( $comisionColaborador ): void {
		$this->comisionColaborador = $comisionColaborador;
	}

	/**
	 * @return mixed
	 */
	public function getPagadaComisionColaborador() {
		return $this->pagadaComisionColaborador;
	}

	/**
	 * @param mixed $pagadaComisionColaborador
	 */
	public function setPagadaComisionColaborador( $pagadaComisionColaborador ): void {
		$this->pagadaComisionColaborador = $pagadaComisionColaborador;
	}

	/**
	 * @return mixed
	 */
	public function getComisionableTecnico() {
		return $this->comisionableTecnico;
	}

	/**
	 * @param mixed $comisionableTecnico
	 */
	public function setComisionableTecnico( $comisionableTecnico ): void {
		$this->comisionableTecnico = $comisionableTecnico;
	}

	/**
	 * @return mixed
	 */
	public function getComisionTecnico() {
		return $this->comisionTecnico;
	}

	/**
	 * @param mixed $comisionTecnico
	 */
	public function setComisionTecnico( $comisionTecnico ): void {
		$this->comisionTecnico = $comisionTecnico;
	}

	/**
	 * @return mixed
	 */
	public function getPagadaComisionTecnico() {
		return $this->pagadaComisionTecnico;
	}

	/**
	 * @param mixed $pagadaComisionTecnico
	 */
	public function setPagadaComisionTecnico( $pagadaComisionTecnico ): void {
		$this->pagadaComisionTecnico = $pagadaComisionTecnico;
	}

	/**
	 * @return mixed
	 */
	public function getComisionableComercial() {
		return $this->comisionableComercial;
	}

	/**
	 * @param mixed $comisionableComercial
	 */
	public function setComisionableComercial( $comisionableComercial ): void {
		$this->comisionableComercial = $comisionableComercial;
	}

	/**
	 * @return mixed
	 */
	public function getComisionComercial() {
		return $this->comisionComercial;
	}

	/**
	 * @param mixed $comisionComercial
	 */
	public function setComisionComercial( $comisionComercial ): void {
		$this->comisionComercial = $comisionComercial;
	}

	/**
	 * @return mixed
	 */
	public function getPagadaComisionComercial() {
		return $this->pagadaComisionComercial;
	}

	/**
	 * @param mixed $pagadaComisionComercial
	 */
	public function setPagadaComisionComercial( $pagadaComisionComercial ): void {
		$this->pagadaComisionComercial = $pagadaComisionComercial;
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
	public function getPagada() {
		return $this->pagada;
	}

	/**
	 * @param mixed $pagada
	 */
	public function setPagada( $pagada ): void {
		$this->pagada = $pagada;
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
	public function getSerie() {
		return $this->serie;
	}

	/**
	 * @param mixed $serie
	 */
	public function setSerie( $serie ): void {
		$this->serie = $serie;
	}

	/**
	 * @return mixed
	 */
	public function getCodigo() {
		return $this->codigo;
	}

	/**
	 * @param mixed $codigo
	 */
	public function setCodigo( $codigo ): void {
		$this->codigo = $codigo;
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
	public function getNumero() {
		return $this->numero;
	}

	/**
	 * @param mixed $numero
	 */
	public function setNumero( $numero ): void {
		$this->numero = $numero;
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
    public function getFichero()
    {
        return $this->fichero;
    }

    /**
     * @param mixed $fichero
     */
    public function setFichero($fichero): void
    {
        $this->fichero = $fichero;
    }

    /**
     * @return mixed
     */
    public function getFacturaAsociada()
    {
        return $this->facturaAsociada;
    }

    /**
     * @param mixed $facturaAsociada
     */
    public function setFacturaAsociada($facturaAsociada): void
    {
        $this->facturaAsociada = $facturaAsociada;
    }

	/**
	 * @return mixed
	 */
	public function getEnviada() {
		return $this->enviada;
	}

	/**
	 * @param mixed $enviada
	 */
	public function setEnviada( $enviada ): void {
		$this->enviada = $enviada;
	}

    /**
     * @return mixed
     */
    public function getOldFactura()
    {
        return $this->oldFactura;
    }

    /**
     * @param mixed $oldFactura
     */
    public function setOldFactura($oldFactura): void
    {
        $this->oldFactura = $oldFactura;
    }

	public function numeroFacturaSerie(){
		$serie = null;
		if(!is_null($this->getSerie())){
			$serie = $this->getSerie()->getSerie();
		}
		return $serie .''.$this->getNumFac().' - '.$this->getEmpresa()->getEmpresa();
	}

    /**
     * @return mixed
     */
    public function getHistorico()
    {
        return $this->historico;
    }

    /**
     * @param mixed $historico
     */
    public function setHistorico($historico): void
    {
        $this->historico = $historico;
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

    /**
     * @return mixed
     */
    public function getFacturaRectificativa()
    {
        return $this->facturaRectificativa;
    }

    /**
     * @param mixed $facturaRectificativa
     */
    public function setFacturaRectificativa($facturaRectificativa): void
    {
        $this->facturaRectificativa = $facturaRectificativa;
    }

}
