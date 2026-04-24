<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\MaxDepth;


/**
 * @ORM\Entity(repositoryClass="App\Repository\EmpresaRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class Empresa
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $codigo;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\CodigoEmpresa", inversedBy="Empresa")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $codigoEmpresa;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $empresa;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $cif;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $marcaComercial;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\GrupoEmpresa", inversedBy="Empresa")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $grupoEmpresa;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\TipoEmpresa", inversedBy="Empresa")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $tipoEmpresa;

	/**
	 * @ORM\Column(type="integer", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $trabajadores;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Comercial", inversedBy="Empresa")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $agente;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Tecnico", inversedBy="Empresa")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $vigilanciaSalud;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Tecnico", inversedBy="Empresa")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $gestorAdministrativo;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\RegimenSegSocial", inversedBy="Empresa")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $regimen;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $ccc;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Asesoria", inversedBy="Empresa")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $colaborador;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Mutua", inversedBy="Empresa")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $mutua;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\ServicioPrevencion", inversedBy="Empresa")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $spa;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Prescriptor", inversedBy="Empresa")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $prescriptor;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\ModalidadPreventiva", inversedBy="Empresa")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $modalidadPreventiva;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $centroMedico;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $consentimientoCesionDatos;

	/**
	 * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $domicilioPostal;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\ProvinciaSerpa", inversedBy="Empresa")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $provinciaPostalSerpa;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\MunicipioSerpa", inversedBy="Empresa")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $municipioPostalSerpa;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $localidadPostal;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $codigoPostalPostal;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $provinciaPostal;

	/**
	 * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $domicilioFiscal;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\ProvinciaSerpa", inversedBy="Empresa")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $provinciaFiscalSerpa;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\MunicipioSerpa", inversedBy="Empresa")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $municipioFiscalSerpa;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $localidadFiscal;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $codigoPostalFiscal;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $provinciaFiscal;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Pais", inversedBy="Empresa")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $paisFiscal;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\EstadoEmpresa", inversedBy="Empresa")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $estadoAdministrativoPrevencion;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\EstadoEmpresaSalud", inversedBy="Empresa")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $estadoAdministrativoVigilanciaSalud;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\EstadoPrevencion", inversedBy="Empresa")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $estadoAreaAdministracion;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\EstadoTecnica", inversedBy="Empresa")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $estadoAreaTecnica;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\EstadoSalud", inversedBy="Empresa")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $estadoAreaVigilanciaSalud;

	/**
	 * @ORM\Column(type="datetime", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $fechaAltaTecnica;

    /**
     * @ORM\Column(type="datetime", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $fechaProximaVisita;

	/**
	 * @ORM\Column(type="datetime", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $fechaBajaTecnica;

	/**
	 * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $motivoBajaTecnica;

	/**
	 * @ORM\Column(type="datetime", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $fechaAltaVigilanciaSalud;

	/**
	 * @ORM\Column(type="datetime", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $fechaBajaVigilanciaSalud;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $motivoBajaVigilanciaSalud;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $actividad;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\SectorEmpresarial", inversedBy="Empresa")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $sectorEmpresarial;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\GrupoActividad", inversedBy="Empresa")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $grupoActividad;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\ActividadPreventiva", inversedBy="Empresa")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $actividadPreventiva;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\EspecialidadPreventiva", inversedBy="Empresa")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $especialidadPreventiva;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $actividadIncluidaAnexo;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $empresaEtt;

	/**
	 * @ORM\Column(type="integer", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $nivelRiesgoSubjetivoInterno;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\NivelSeguimiento", inversedBy="Empresa")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $nivelSeguimientoEmpresa;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $excluirCalculoRatios;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $excluirMemorialAnual;

	/**
	 * @ORM\Column(type="text",  nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $observacionesGenerales;

	/**
	 * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $observacionesAreaMedicina;

	/**
	 * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $observacionesAreaTecnica;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $telefono1;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $telefono2;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $fax;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $nombreRepresentante;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $dniRepresentante;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $cargoRepresentante;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $personaContacto;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $anulado = false;

	/**
	 * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $actuaciones;

	/**
	 * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $avisosControl;

	/**
	 * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $estructuraDepartamental;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $codigoTecnico;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $historicoPrevenet = false;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $idPrevenet;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\IdiomaEmpresa", inversedBy="Empresa")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $idioma;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\EstadoEmpresaTecnico", inversedBy="Empresa")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $estadoEmpresaTecnico;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $logo;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $idRiesgos;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $noAplicarIpc = false;

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

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $motivoBaja;

    /**
     * @ORM\OneToMany (targetEntity="App\Entity\TrabajadorAltaBaja", mappedBy="empresa")
     * @ORM\JoinColumn(nullable=true)
     * @MaxDepth(0)
     */
    private $trabajadoresSituacion;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $pruebasComplementarias;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\FormaPago", inversedBy="Empresa")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $formaPago;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $diaPago;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $procesoProductivo;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $pruebasEspeciales = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $centroTrabajoDeslocalizado = false;
    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $centroTrabajoDeslocalizadoConstruccio = false;
    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $centroTrabajoTiene1 = false;
    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $centroTrabajoTiene2 = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\FormaPago", inversedBy="Empresa")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $formaPagoRml;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodigo(): ?string
    {
        return $this->codigo;
    }

    public function setCodigo(?string $codigo): self
    {
        $this->codigo = $codigo;

        return $this;
    }

	/**
	 * @return mixed
	 */
	public function getCodigoEmpresa() {
		return $this->codigoEmpresa;
	}

	/**
	 * @param mixed $codigoEmpresa
	 */
	public function setCodigoEmpresa( $codigoEmpresa ): void {
		$this->codigoEmpresa = $codigoEmpresa;
	}

    public function getEmpresa(): ?string
    {
        return $this->empresa;
    }

    public function setEmpresa(?string $empresa): self
    {
        $this->empresa = $empresa;

        return $this;
    }

    public function getCif(): ?string
    {
        return $this->cif;
    }

    public function setCif(?string $cif): self
    {
        $this->cif = $cif;

        return $this;
    }

    public function getMarcaComercial(): ?string
    {
        return $this->marcaComercial;
    }

    public function setMarcaComercial(?string $marcaComercial): self
    {
        $this->marcaComercial = $marcaComercial;

        return $this;
    }

	/**
	 * @return mixed
	 */
	public function getGrupoEmpresa() {
		return $this->grupoEmpresa;
	}

	/**
	 * @param mixed $grupoEmpresa
	 */
	public function setGrupoEmpresa( $grupoEmpresa ): void {
		$this->grupoEmpresa = $grupoEmpresa;
	}

	/**
	 * @return mixed
	 */
	public function getTipoEmpresa() {
		return $this->tipoEmpresa;
	}

	/**
	 * @param mixed $tipoEmpresa
	 */
	public function setTipoEmpresa( $tipoEmpresa ): void {
		$this->tipoEmpresa = $tipoEmpresa;
	}

	/**
	 * @return mixed
	 */
	public function getTrabajadores() {
		return $this->trabajadores;
	}

	/**
	 * @param mixed $trabajadores
	 */
	public function setTrabajadores( $trabajadores ): void {
		$this->trabajadores = $trabajadores;
	}

	/**
	 * @return mixed
	 */
	public function getAgente() {
		return $this->agente;
	}

	/**
	 * @param mixed $agente
	 */
	public function setAgente( $agente ): void {
		$this->agente = $agente;
	}

	/**
	 * @return mixed
	 */
	public function getVigilanciaSalud() {
		return $this->vigilanciaSalud;
	}

	/**
	 * @param mixed $vigilanciaSalud
	 */
	public function setVigilanciaSalud( $vigilanciaSalud ): void {
		$this->vigilanciaSalud = $vigilanciaSalud;
	}

	/**
	 * @return mixed
	 */
	public function getGestorAdministrativo() {
		return $this->gestorAdministrativo;
	}

	/**
	 * @param mixed $gestorAdministrativo
	 */
	public function setGestorAdministrativo( $gestorAdministrativo ): void {
		$this->gestorAdministrativo = $gestorAdministrativo;
	}

	/**
	 * @return mixed
	 */
	public function getRegimen() {
		return $this->regimen;
	}

	/**
	 * @param mixed $regimen
	 */
	public function setRegimen( $regimen ): void {
		$this->regimen = $regimen;
	}

	/**
	 * @return mixed
	 */
	public function getCcc() {
		return $this->ccc;
	}

	/**
	 * @param mixed $ccc
	 */
	public function setCcc( $ccc ): void {
		$this->ccc = $ccc;
	}

	/**
	 * @return mixed
	 */
	public function getColaborador() {
		return $this->colaborador;
	}

	/**
	 * @param mixed $colaborador
	 */
	public function setColaborador( $colaborador ): void {
		$this->colaborador = $colaborador;
	}

	/**
	 * @return mixed
	 */
	public function getMutua() {
		return $this->mutua;
	}

	/**
	 * @param mixed $mutua
	 */
	public function setMutua( $mutua ): void {
		$this->mutua = $mutua;
	}

	/**
	 * @return mixed
	 */
	public function getSpa() {
		return $this->spa;
	}

	/**
	 * @param mixed $spa
	 */
	public function setSpa( $spa ): void {
		$this->spa = $spa;
	}

	/**
	 * @return mixed
	 */
	public function getPrescriptor() {
		return $this->prescriptor;
	}

	/**
	 * @param mixed $prescriptor
	 */
	public function setPrescriptor( $prescriptor ): void {
		$this->prescriptor = $prescriptor;
	}

	/**
	 * @return mixed
	 */
	public function getModalidadPreventiva() {
		return $this->modalidadPreventiva;
	}

	/**
	 * @param mixed $modalidadPreventiva
	 */
	public function setModalidadPreventiva( $modalidadPreventiva ): void {
		$this->modalidadPreventiva = $modalidadPreventiva;
	}

	/**
	 * @return mixed
	 */
	public function getCentroMedico() {
		return $this->centroMedico;
	}

	/**
	 * @param mixed $centroMedico
	 */
	public function setCentroMedico( $centroMedico ): void {
		$this->centroMedico = $centroMedico;
	}

	/**
	 * @return mixed
	 */
	public function getConsentimientoCesionDatos() {
		return $this->consentimientoCesionDatos;
	}

	/**
	 * @param mixed $consentimientoCesionDatos
	 */
	public function setConsentimientoCesionDatos( $consentimientoCesionDatos ): void {
		$this->consentimientoCesionDatos = $consentimientoCesionDatos;
	}

	/**
	 * @return mixed
	 */
	public function getDomicilioPostal() {
		return $this->domicilioPostal;
	}

	/**
	 * @param mixed $domicilioPostal
	 */
	public function setDomicilioPostal( $domicilioPostal ): void {
		$this->domicilioPostal = $domicilioPostal;
	}

	/**
	 * @return mixed
	 */
	public function getProvinciaPostalSerpa() {
		return $this->provinciaPostalSerpa;
	}

	/**
	 * @param mixed $provinciaPostalSerpa
	 */
	public function setProvinciaPostalSerpa( $provinciaPostalSerpa ): void {
		$this->provinciaPostalSerpa = $provinciaPostalSerpa;
	}

	/**
	 * @return mixed
	 */
	public function getMunicipioPostalSerpa() {
		return $this->municipioPostalSerpa;
	}

	/**
	 * @param mixed $municipioPostalSerpa
	 */
	public function setMunicipioPostalSerpa( $municipioPostalSerpa ): void {
		$this->municipioPostalSerpa = $municipioPostalSerpa;
	}

	/**
	 * @return mixed
	 */
	public function getLocalidadPostal() {
		return $this->localidadPostal;
	}

	/**
	 * @param mixed $localidadPostal
	 */
	public function setLocalidadPostal( $localidadPostal ): void {
		$this->localidadPostal = $localidadPostal;
	}

	/**
	 * @return mixed
	 */
	public function getCodigoPostalPostal() {
		return $this->codigoPostalPostal;
	}

	/**
	 * @param mixed $codigoPostalPostal
	 */
	public function setCodigoPostalPostal( $codigoPostalPostal ): void {
		$this->codigoPostalPostal = $codigoPostalPostal;
	}

	/**
	 * @return mixed
	 */
	public function getProvinciaPostal() {
		return $this->provinciaPostal;
	}

	/**
	 * @param mixed $provinciaPostal
	 */
	public function setProvinciaPostal( $provinciaPostal ): void {
		$this->provinciaPostal = $provinciaPostal;
	}

	/**
	 * @return mixed
	 */
	public function getDomicilioFiscal() {
		return $this->domicilioFiscal;
	}

	/**
	 * @param mixed $domicilioFiscal
	 */
	public function setDomicilioFiscal( $domicilioFiscal ): void {
		$this->domicilioFiscal = $domicilioFiscal;
	}

	/**
	 * @return mixed
	 */
	public function getProvinciaFiscalSerpa() {
		return $this->provinciaFiscalSerpa;
	}

	/**
	 * @param mixed $provinciaFiscalSerpa
	 */
	public function setProvinciaFiscalSerpa( $provinciaFiscalSerpa ): void {
		$this->provinciaFiscalSerpa = $provinciaFiscalSerpa;
	}

	/**
	 * @return mixed
	 */
	public function getMunicipioFiscalSerpa() {
		return $this->municipioFiscalSerpa;
	}

	/**
	 * @param mixed $municipioFiscalSerpa
	 */
	public function setMunicipioFiscalSerpa( $municipioFiscalSerpa ): void {
		$this->municipioFiscalSerpa = $municipioFiscalSerpa;
	}

	/**
	 * @return mixed
	 */
	public function getLocalidadFiscal() {
		return $this->localidadFiscal;
	}

	/**
	 * @param mixed $localidadFiscal
	 */
	public function setLocalidadFiscal( $localidadFiscal ): void {
		$this->localidadFiscal = $localidadFiscal;
	}

	/**
	 * @return mixed
	 */
	public function getCodigoPostalFiscal() {
		return $this->codigoPostalFiscal;
	}

	/**
	 * @param mixed $codigoPostalFiscal
	 */
	public function setCodigoPostalFiscal( $codigoPostalFiscal ): void {
		$this->codigoPostalFiscal = $codigoPostalFiscal;
	}

	/**
	 * @return mixed
	 */
	public function getProvinciaFiscal() {
		return $this->provinciaFiscal;
	}

	/**
	 * @param mixed $provinciaFiscal
	 */
	public function setProvinciaFiscal( $provinciaFiscal ): void {
		$this->provinciaFiscal = $provinciaFiscal;
	}

	/**
	 * @return mixed
	 */
	public function getPaisFiscal() {
		return $this->paisFiscal;
	}

	/**
	 * @param mixed $paisFiscal
	 */
	public function setPaisFiscal( $paisFiscal ): void {
		$this->paisFiscal = $paisFiscal;
	}

	/**
	 * @return mixed
	 */
	public function getEstadoAdministrativoPrevencion() {
		return $this->estadoAdministrativoPrevencion;
	}

	/**
	 * @param mixed $estadoAdministrativoPrevencion
	 */
	public function setEstadoAdministrativoPrevencion( $estadoAdministrativoPrevencion ): void {
		$this->estadoAdministrativoPrevencion = $estadoAdministrativoPrevencion;
	}

	/**
	 * @return mixed
	 */
	public function getEstadoAdministrativoVigilanciaSalud() {
		return $this->estadoAdministrativoVigilanciaSalud;
	}

	/**
	 * @param mixed $estadoAdministrativoVigilanciaSalud
	 */
	public function setEstadoAdministrativoVigilanciaSalud( $estadoAdministrativoVigilanciaSalud ): void {
		$this->estadoAdministrativoVigilanciaSalud = $estadoAdministrativoVigilanciaSalud;
	}

	/**
	 * @return mixed
	 */
	public function getEstadoAreaAdministracion() {
		return $this->estadoAreaAdministracion;
	}

	/**
	 * @param mixed $estadoAreaAdministracion
	 */
	public function setEstadoAreaAdministracion( $estadoAreaAdministracion ): void {
		$this->estadoAreaAdministracion = $estadoAreaAdministracion;
	}

	/**
	 * @return mixed
	 */
	public function getEstadoAreaTecnica() {
		return $this->estadoAreaTecnica;
	}

	/**
	 * @param mixed $estadoAreaTecnica
	 */
	public function setEstadoAreaTecnica( $estadoAreaTecnica ): void {
		$this->estadoAreaTecnica = $estadoAreaTecnica;
	}

	/**
	 * @return mixed
	 */
	public function getEstadoAreaVigilanciaSalud() {
		return $this->estadoAreaVigilanciaSalud;
	}

	/**
	 * @param mixed $estadoAreaVigilanciaSalud
	 */
	public function setEstadoAreaVigilanciaSalud( $estadoAreaVigilanciaSalud ): void {
		$this->estadoAreaVigilanciaSalud = $estadoAreaVigilanciaSalud;
	}

    /**
     * @return mixed
     */
    public function getFechaProximaVisita() {
        return $this->fechaProximaVisita;
    }

    /**
     * @param mixed $fechaProximaVisita
     */
    public function setFechaProximaVisita( $fechaProximaVisita ): void {
        $this->fechaProximaVisita = $fechaProximaVisita;
    }

	/**
	 * @return mixed
	 */
	public function getFechaAltaTecnica() {
		return $this->fechaAltaTecnica;
	}

	/**
	 * @param mixed $fechaAltaTecnica
	 */
	public function setFechaAltaTecnica( $fechaAltaTecnica ): void {
		$this->fechaAltaTecnica = $fechaAltaTecnica;
	}

	/**
	 * @return mixed
	 */
	public function getFechaBajaTecnica() {
		return $this->fechaBajaTecnica;
	}

	/**
	 * @param mixed $fechaBajaTecnica
	 */
	public function setFechaBajaTecnica( $fechaBajaTecnica ): void {
		$this->fechaBajaTecnica = $fechaBajaTecnica;
	}

	/**
	 * @return mixed
	 */
	public function getMotivoBajaTecnica() {
		return $this->motivoBajaTecnica;
	}

	/**
	 * @param mixed $motivoBajaTecnica
	 */
	public function setMotivoBajaTecnica( $motivoBajaTecnica ): void {
		$this->motivoBajaTecnica = $motivoBajaTecnica;
	}

	/**
	 * @return mixed
	 */
	public function getFechaAltaVigilanciaSalud() {
		return $this->fechaAltaVigilanciaSalud;
	}

	/**
	 * @param mixed $fechaAltaVigilanciaSalud
	 */
	public function setFechaAltaVigilanciaSalud( $fechaAltaVigilanciaSalud ): void {
		$this->fechaAltaVigilanciaSalud = $fechaAltaVigilanciaSalud;
	}

	/**
	 * @return mixed
	 */
	public function getFechaBajaVigilanciaSalud() {
		return $this->fechaBajaVigilanciaSalud;
	}

	/**
	 * @param mixed $fechaBajaVigilanciaSalud
	 */
	public function setFechaBajaVigilanciaSalud( $fechaBajaVigilanciaSalud ): void {
		$this->fechaBajaVigilanciaSalud = $fechaBajaVigilanciaSalud;
	}

	/**
	 * @return mixed
	 */
	public function getMotivoBajaVigilanciaSalud() {
		return $this->motivoBajaVigilanciaSalud;
	}

	/**
	 * @param mixed $motivoBajaVigilanciaSalud
	 */
	public function setMotivoBajaVigilanciaSalud( $motivoBajaVigilanciaSalud ): void {
		$this->motivoBajaVigilanciaSalud = $motivoBajaVigilanciaSalud;
	}

	/**
	 * @return mixed
	 */
	public function getActividad() {
		return $this->actividad;
	}

	/**
	 * @param mixed $actividad
	 */
	public function setActividad( $actividad ): void {
		$this->actividad = $actividad;
	}

	/**
	 * @return mixed
	 */
	public function getSectorEmpresarial() {
		return $this->sectorEmpresarial;
	}

	/**
	 * @param mixed $sectorEmpresarial
	 */
	public function setSectorEmpresarial( $sectorEmpresarial ): void {
		$this->sectorEmpresarial = $sectorEmpresarial;
	}

	/**
	 * @return mixed
	 */
	public function getGrupoActividad() {
		return $this->grupoActividad;
	}

	/**
	 * @param mixed $grupoActividad
	 */
	public function setGrupoActividad( $grupoActividad ): void {
		$this->grupoActividad = $grupoActividad;
	}

	/**
	 * @return mixed
	 */
	public function getActividadPreventiva() {
		return $this->actividadPreventiva;
	}

	/**
	 * @param mixed $actividadPreventiva
	 */
	public function setActividadPreventiva( $actividadPreventiva ): void {
		$this->actividadPreventiva = $actividadPreventiva;
	}

	/**
	 * @return mixed
	 */
	public function getEspecialidadPreventiva() {
		return $this->especialidadPreventiva;
	}

	/**
	 * @param mixed $especialidadPreventiva
	 */
	public function setEspecialidadPreventiva( $especialidadPreventiva ): void {
		$this->especialidadPreventiva = $especialidadPreventiva;
	}

	/**
	 * @return mixed
	 */
	public function getActividadIncluidaAnexo() {
		return $this->actividadIncluidaAnexo;
	}

	/**
	 * @param mixed $actividadIncluidaAnexo
	 */
	public function setActividadIncluidaAnexo( $actividadIncluidaAnexo ): void {
		$this->actividadIncluidaAnexo = $actividadIncluidaAnexo;
	}

	/**
	 * @return mixed
	 */
	public function getEmpresaEtt() {
		return $this->empresaEtt;
	}

	/**
	 * @param mixed $empresaEtt
	 */
	public function setEmpresaEtt( $empresaEtt ): void {
		$this->empresaEtt = $empresaEtt;
	}

	/**
	 * @return mixed
	 */
	public function getNivelRiesgoSubjetivoInterno() {
		return $this->nivelRiesgoSubjetivoInterno;
	}

	/**
	 * @param mixed $nivelRiesgoSubjetivoInterno
	 */
	public function setNivelRiesgoSubjetivoInterno( $nivelRiesgoSubjetivoInterno ): void {
		$this->nivelRiesgoSubjetivoInterno = $nivelRiesgoSubjetivoInterno;
	}

	/**
	 * @return mixed
	 */
	public function getNivelSeguimientoEmpresa() {
		return $this->nivelSeguimientoEmpresa;
	}

	/**
	 * @param mixed $nivelSeguimientoEmpresa
	 */
	public function setNivelSeguimientoEmpresa( $nivelSeguimientoEmpresa ): void {
		$this->nivelSeguimientoEmpresa = $nivelSeguimientoEmpresa;
	}

	/**
	 * @return mixed
	 */
	public function getExcluirCalculoRatios() {
		return $this->excluirCalculoRatios;
	}

	/**
	 * @param mixed $excluirCalculoRatios
	 */
	public function setExcluirCalculoRatios( $excluirCalculoRatios ): void {
		$this->excluirCalculoRatios = $excluirCalculoRatios;
	}

	/**
	 * @return mixed
	 */
	public function getExcluirMemorialAnual() {
		return $this->excluirMemorialAnual;
	}

	/**
	 * @param mixed $excluirMemorialAnual
	 */
	public function setExcluirMemorialAnual( $excluirMemorialAnual ): void {
		$this->excluirMemorialAnual = $excluirMemorialAnual;
	}

	/**
	 * @return mixed
	 */
	public function getObservacionesGenerales() {
		return $this->observacionesGenerales;
	}

	/**
	 * @param mixed $observacionesGenerales
	 */
	public function setObservacionesGenerales( $observacionesGenerales ): void {
		$this->observacionesGenerales = $observacionesGenerales;
	}

	/**
	 * @return mixed
	 */
	public function getObservacionesAreaMedicina() {
		return $this->observacionesAreaMedicina;
	}

	/**
	 * @param mixed $observacionesAreaMedicina
	 */
	public function setObservacionesAreaMedicina( $observacionesAreaMedicina ): void {
		$this->observacionesAreaMedicina = $observacionesAreaMedicina;
	}

	/**
	 * @return mixed
	 */
	public function getObservacionesAreaTecnica() {
		return $this->observacionesAreaTecnica;
	}

	/**
	 * @param mixed $observacionesAreaTecnica
	 */
	public function setObservacionesAreaTecnica( $observacionesAreaTecnica ): void {
		$this->observacionesAreaTecnica = $observacionesAreaTecnica;
	}

//    /**
//     * @return Collection|Centro[]
//     */
//    public function getCentros(): Collection
//    {
//        return $this->centros;
//    }
//
//    public function addCentro(Centro $centro): self
//    {
//        if (!$this->centros->contains($centro)) {
//            $this->centros[] = $centro;
//            $centro->setEmpresa($this);
//        }
//
//        return $this;
//    }
//
//    public function removeCentro(Centro $centro): self
//    {
//        if ($this->centros->contains($centro)) {
//            $this->centros->removeElement($centro);
//            // set the owning side to null (unless already changed)
//            if ($centro->getEmpresa() === $this) {
//                $centro->setEmpresa(null);
//            }
//        }
//
//        return $this;
//    }

	public function __toString() {
		return $this->empresa;
	}

	/**
	 * @return mixed
	 */
	public function getTelefono1() {
		return $this->telefono1;
	}

	/**
	 * @param mixed $telefono1
	 */
	public function setTelefono1( $telefono1 ): void {
		$this->telefono1 = $telefono1;
	}

	/**
	 * @return mixed
	 */
	public function getTelefono2() {
		return $this->telefono2;
	}

	/**
	 * @param mixed $telefono2
	 */
	public function setTelefono2( $telefono2 ): void {
		$this->telefono2 = $telefono2;
	}

	/**
	 * @return mixed
	 */
	public function getFax() {
		return $this->fax;
	}

	/**
	 * @param mixed $fax
	 */
	public function setFax( $fax ): void {
		$this->fax = $fax;
	}

	/**
	 * @return mixed
	 */
	public function getNombreRepresentante() {
		return $this->nombreRepresentante;
	}

	/**
	 * @param mixed $nombreRepresentante
	 */
	public function setNombreRepresentante( $nombreRepresentante ): void {
		$this->nombreRepresentante = $nombreRepresentante;
	}

	/**
	 * @return mixed
	 */
	public function getDniRepresentante() {
		return $this->dniRepresentante;
	}

	/**
	 * @param mixed $dniRepresentante
	 */
	public function setDniRepresentante( $dniRepresentante ): void {
		$this->dniRepresentante = $dniRepresentante;
	}

	/**
	 * @return mixed
	 */
	public function getCargoRepresentante() {
		return $this->cargoRepresentante;
	}

	/**
	 * @param mixed $cargoRepresentante
	 */
	public function setCargoRepresentante( $cargoRepresentante ): void {
		$this->cargoRepresentante = $cargoRepresentante;
	}

	/**
	 * @return mixed
	 */
	public function getPersonaContacto() {
		return $this->personaContacto;
	}

	/**
	 * @param mixed $personaContacto
	 */
	public function setPersonaContacto( $personaContacto ): void {
		$this->personaContacto = $personaContacto;
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
	public function getActuaciones() {
		return $this->actuaciones;
	}

	/**
	 * @param mixed $actuaciones
	 */
	public function setActuaciones( $actuaciones ): void {
		$this->actuaciones = $actuaciones;
	}

	/**
	 * @return mixed
	 */
	public function getAvisosControl() {
		return $this->avisosControl;
	}

	/**
	 * @param mixed $avisosControl
	 */
	public function setAvisosControl( $avisosControl ): void {
		$this->avisosControl = $avisosControl;
	}

	/**
	 * @return mixed
	 */
	public function getEstructuraDepartamental() {
		return $this->estructuraDepartamental;
	}

	/**
	 * @param mixed $estructuraDepartamental
	 */
	public function setEstructuraDepartamental( $estructuraDepartamental ): void {
		$this->estructuraDepartamental = $estructuraDepartamental;
	}

    /**
     * @return mixed
     */
    public function getCodigoTecnico()
    {
        return $this->codigoTecnico;
    }

    /**
     * @param mixed $codigoTecnico
     */
    public function setCodigoTecnico($codigoTecnico): void
    {
        $this->codigoTecnico = $codigoTecnico;
    }

    /**
     * @return bool
     */
    public function getHistoricoPrevenet(): bool
    {
        return $this->historicoPrevenet;
    }

    /**
     * @param bool $historicoPrevenet
     */
    public function setHistoricoPrevenet(bool $historicoPrevenet): void
    {
        $this->historicoPrevenet = $historicoPrevenet;
    }

    /**
     * @return mixed
     */
    public function getIdPrevenet()
    {
        return $this->idPrevenet;
    }

    /**
     * @param mixed $idPrevenet
     */
    public function setIdPrevenet($idPrevenet): void
    {
        $this->idPrevenet = $idPrevenet;
    }

    /**
     * @return mixed
     */
    public function getIdioma()
    {
        return $this->idioma;
    }

    /**
     * @param mixed $idioma
     */
    public function setIdioma($idioma): void
    {
        $this->idioma = $idioma;
    }

    /**
     * @return mixed
     */
    public function getEstadoEmpresaTecnico()
    {
        return $this->estadoEmpresaTecnico;
    }

    /**
     * @param mixed $estadoEmpresaTecnico
     */
    public function setEstadoEmpresaTecnico($estadoEmpresaTecnico): void
    {
        $this->estadoEmpresaTecnico = $estadoEmpresaTecnico;
    }

    /**
     * @return mixed
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @param mixed $logo
     */
    public function setLogo($logo): void
    {
        $this->logo = $logo;
    }

    public function getEmpresaMedico(){

        if(!is_null($this->getEstadoAdministrativoVigilanciaSalud())){
            return $this->getEstadoAdministrativoVigilanciaSalud() . ' - '. $this->empresa;
        }else{
            return $this->empresa;
        }
    }

    /**
     * @return mixed
     */
    public function getIdRiesgos()
    {
        return $this->idRiesgos;
    }

    /**
     * @param mixed $idRiesgos
     */
    public function setIdRiesgos($idRiesgos): void
    {
        $this->idRiesgos = $idRiesgos;
    }

    /**
     * @return mixed
     */
    public function getNoAplicarIpc()
    {
        return $this->noAplicarIpc;
    }

    /**
     * @param mixed $noAplicarIpc
     */
    public function setNoAplicarIpc($noAplicarIpc): void
    {
        $this->noAplicarIpc = $noAplicarIpc;
    }

    /**
     * @return mixed
     */
    public function getFechaAlta()
    {
        return $this->fechaAlta;
    }

    /**
     * @param mixed $fechaAlta
     */
    public function setFechaAlta($fechaAlta): void
    {
        $this->fechaAlta = $fechaAlta;
    }

    /**
     * @return mixed
     */
    public function getFechaBaja()
    {
        return $this->fechaBaja;
    }

    /**
     * @param mixed $fechaBaja
     */
    public function setFechaBaja($fechaBaja): void
    {
        $this->fechaBaja = $fechaBaja;
    }

    /**
     * @return mixed
     */
    public function getMotivoBaja()
    {
        return $this->motivoBaja;
    }

    /**
     * @param mixed $motivoBaja
     */
    public function setMotivoBaja($motivoBaja): void
    {
        $this->motivoBaja = $motivoBaja;
    }

    /**
     * @return mixed
     */
    public function getTrabajadoresSituacion()
    {
        return $this->trabajadoresSituacion;
    }

    /**
     * @param mixed $trabajadoresSituacion
     */
    public function setTrabajadoresSituacion($trabajadoresSituacion): void
    {
        $this->trabajadoresSituacion = $trabajadoresSituacion;
    }

    /**
     * @return mixed
     */
    public function getPruebasComplementarias()
    {
        return $this->pruebasComplementarias;
    }

    /**
     * @param mixed $pruebasComplementarias
     */
    public function setPruebasComplementarias($pruebasComplementarias): void
    {
        $this->pruebasComplementarias = $pruebasComplementarias;
    }

    /**
     * @return mixed
     */
    public function getFormaPago()
    {
        return $this->formaPago;
    }

    /**
     * @param mixed $formaPago
     */
    public function setFormaPago($formaPago): void
    {
        $this->formaPago = $formaPago;
    }

    /**
     * @return mixed
     */
    public function getDiaPago()
    {
        return $this->diaPago;
    }

    /**
     * @param mixed $diaPago
     */
    public function setDiaPago($diaPago): void
    {
        $this->diaPago = $diaPago;
    }

    /**
     * @return mixed
     */
    public function getProcesoProductivo()
    {
        return $this->procesoProductivo;
    }

    /**
     * @param mixed $procesoProductivo
     */
    public function setProcesoProductivo($procesoProductivo): void
    {
        $this->procesoProductivo = $procesoProductivo;
    }

    /**
     * @return mixed
     */
    public function getPruebasEspeciales()
    {
        return $this->pruebasEspeciales;
    }

    /**
     * @param mixed $pruebasEspeciales
     */
    public function setPruebasEspeciales($pruebasEspeciales): void
    {
        $this->pruebasEspeciales = $pruebasEspeciales;
    }

    /**
     * @return mixed
     */
    public function getCentroTrabajoDeslocalizado()
    {
        return $this->centroTrabajoDeslocalizado;
    }

    /**
     * @param mixed $centroTrabajoDeslocalizado
     */
    public function setCentroTrabajoDeslocalizado($centroTrabajoDeslocalizado): void
    {
        $this->centroTrabajoDeslocalizado = $centroTrabajoDeslocalizado;
    }

    /**
     * @return mixed
     */
    public function getCentroTrabajoDeslocalizadoConstruccio()
    {
        return $this->centroTrabajoDeslocalizadoConstruccio;
    }

    /**
     * @param mixed $centroTrabajoDeslocalizadoConstruccio
     */
    public function setCentroTrabajoDeslocalizadoConstruccio($centroTrabajoDeslocalizadoConstruccio): void
    {
        $this->centroTrabajoDeslocalizadoConstruccio = $centroTrabajoDeslocalizadoConstruccio;
    }

    /**
     * @return mixed
     */
    public function getCentroTrabajoTiene1()
    {
        return $this->centroTrabajoTiene1;
    }

    /**
     * @param mixed $centroTrabajoTiene1
     */
    public function setCentroTrabajoTiene1($centroTrabajoTiene1): void
    {
        $this->centroTrabajoTiene1 = $centroTrabajoTiene1;
    }

    /**
     * @return mixed
     */
    public function getCentroTrabajoTiene2()
    {
        return $this->centroTrabajoTiene2;
    }

    /**
     * @param mixed $centroTrabajoTiene2
     */
    public function setCentroTrabajoTiene2($centroTrabajoTiene2): void
    {
        $this->centroTrabajoTiene2 = $centroTrabajoTiene2;
    }

    /**
     * @return mixed
     */
    public function getFormaPagoRml()
    {
        return $this->formaPagoRml;
    }

    /**
     * @param mixed $formaPagoRml
     */
    public function setFormaPagoRml($formaPagoRml): void
    {
        $this->formaPagoRml = $formaPagoRml;
    }

}
