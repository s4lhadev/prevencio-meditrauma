<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CentroRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class Centro
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
    private $nombre;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $codigo;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $ncodigo;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $trabajadores;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\TipoCentro", inversedBy="centro")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $tipo;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $direccion;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $localidad;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $codigoPostal;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $provincia;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $zona;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\ProvinciaSerpa", inversedBy="centro")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $provinciaSerpa;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\MunicipioSerpa", inversedBy="centro")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $municipioSerpa;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $centroMismaProvincia;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $email;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $telefono;

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
	 * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $actividadCentro;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Cnae", inversedBy="centro")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $cnae;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $excluirRatios;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $excluirMemoria;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\RegimenSegSocial", inversedBy="centro")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $regimenss;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $ccc;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $centroMedico;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $cesionDatos;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Empresa", inversedBy="centro")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $empresa;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $anulado = false;

	/**
	 * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $estructuraCentro;

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
     * @ORM\Column(type="boolean", nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $historicoPrevenet = false;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $idPrevenet;

    public function getId(): ?int
    {
        return $this->id;
    }

	/**
	 * @return mixed
	 */
	public function getNombre() {
		return $this->nombre;
	}

	/**
	 * @param mixed $nombre
	 */
	public function setNombre( $nombre ): void {
		$this->nombre = $nombre;
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
	public function getNcodigo() {
		return $this->ncodigo;
	}

	/**
	 * @param mixed $ncodigo
	 */
	public function setNcodigo( $ncodigo ): void {
		$this->ncodigo = $ncodigo;
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
	public function getDireccion() {
		return $this->direccion;
	}

	/**
	 * @param mixed $direccion
	 */
	public function setDireccion( $direccion ): void {
		$this->direccion = $direccion;
	}

	/**
	 * @return mixed
	 */
	public function getLocalidad() {
		return $this->localidad;
	}

	/**
	 * @param mixed $localidad
	 */
	public function setLocalidad( $localidad ): void {
		$this->localidad = $localidad;
	}

	/**
	 * @return mixed
	 */
	public function getCodigoPostal() {
		return $this->codigoPostal;
	}

	/**
	 * @param mixed $codigoPostal
	 */
	public function setCodigoPostal( $codigoPostal ): void {
		$this->codigoPostal = $codigoPostal;
	}

	/**
	 * @return mixed
	 */
	public function getProvincia() {
		return $this->provincia;
	}

	/**
	 * @param mixed $provincia
	 */
	public function setProvincia( $provincia ): void {
		$this->provincia = $provincia;
	}

	/**
	 * @return mixed
	 */
	public function getZona() {
		return $this->zona;
	}

	/**
	 * @param mixed $zona
	 */
	public function setZona( $zona ): void {
		$this->zona = $zona;
	}

	/**
	 * @return mixed
	 */
	public function getProvinciaSerpa() {
		return $this->provinciaSerpa;
	}

	/**
	 * @param mixed $provinciaSerpa
	 */
	public function setProvinciaSerpa( $provinciaSerpa ): void {
		$this->provinciaSerpa = $provinciaSerpa;
	}

	/**
	 * @return mixed
	 */
	public function getMunicipioSerpa() {
		return $this->municipioSerpa;
	}

	/**
	 * @param mixed $municipioSerpa
	 */
	public function setMunicipioSerpa( $municipioSerpa ): void {
		$this->municipioSerpa = $municipioSerpa;
	}

	/**
	 * @return mixed
	 */
	public function getCentroMismaProvincia() {
		return $this->centroMismaProvincia;
	}

	/**
	 * @param mixed $centroMismaProvincia
	 */
	public function setCentroMismaProvincia( $centroMismaProvincia ): void {
		$this->centroMismaProvincia = $centroMismaProvincia;
	}

	/**
	 * @return mixed
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * @param mixed $email
	 */
	public function setEmail( $email ): void {
		$this->email = $email;
	}

	/**
	 * @return mixed
	 */
	public function getTelefono() {
		return $this->telefono;
	}

	/**
	 * @param mixed $telefono
	 */
	public function setTelefono( $telefono ): void {
		$this->telefono = $telefono;
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
	public function getActividadCentro() {
		return $this->actividadCentro;
	}

	/**
	 * @param mixed $actividadCentro
	 */
	public function setActividadCentro( $actividadCentro ): void {
		$this->actividadCentro = $actividadCentro;
	}

	/**
	 * @return mixed
	 */
	public function getCnae() {
		return $this->cnae;
	}

	/**
	 * @param mixed $cnae
	 */
	public function setCnae( $cnae ): void {
		$this->cnae = $cnae;
	}

	/**
	 * @return mixed
	 */
	public function getExcluirRatios() {
		return $this->excluirRatios;
	}

	/**
	 * @param mixed $excluirRatios
	 */
	public function setExcluirRatios( $excluirRatios ): void {
		$this->excluirRatios = $excluirRatios;
	}

	/**
	 * @return mixed
	 */
	public function getExcluirMemoria() {
		return $this->excluirMemoria;
	}

	/**
	 * @param mixed $excluirMemoria
	 */
	public function setExcluirMemoria( $excluirMemoria ): void {
		$this->excluirMemoria = $excluirMemoria;
	}

	/**
	 * @return mixed
	 */
	public function getRegimenss() {
		return $this->regimenss;
	}

	/**
	 * @param mixed $regimenss
	 */
	public function setRegimenss( $regimenss ): void {
		$this->regimenss = $regimenss;
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
	public function getCesionDatos() {
		return $this->cesionDatos;
	}

	/**
	 * @param mixed $cesionDatos
	 */
	public function setCesionDatos( $cesionDatos ): void {
		$this->cesionDatos = $cesionDatos;
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
	public function getEstructuraCentro() {
		return $this->estructuraCentro;
	}

	/**
	 * @param mixed $estructuraCentro
	 */
	public function setEstructuraCentro( $estructuraCentro ): void {
		$this->estructuraCentro = $estructuraCentro;
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

    public function __toString() {
        return $this->nombre;
    }
}
