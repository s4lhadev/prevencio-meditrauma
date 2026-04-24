<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TrabajadorRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class Trabajador
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
	private $ncodigo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
    private $dni;

	/**
	 * @ORM\Column(type="integer", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $sexo;

	/**
	 * @ORM\Column(type="datetime", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $fechaNacimiento;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $edad;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $grupoEdad;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $naf;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $identificacion;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $nacionalidad;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $discapacidad;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\PuestoTrabajoGenerico", inversedBy="Trabajador")
	 * @ORM\JoinColumn(nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $puestoTrabajo;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $cno;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $categoriaProfesional;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $regimenCotizacionSS;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $situacionProfesional;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $autonomo;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $ett;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $subvencionado;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $exclusion;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $anulado = false;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $avisosControl;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $domicilio;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $telefono1;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $telefono2;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $mail;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $observaciones;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $idPrevenet;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $historicoPrevenet = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $tipoFormacion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $formacion;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $fechaFormacion;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $fechaUltimaRevision;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $fechaNuevaRevision;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $idRiesgos;

    /**
     * @ORM\OneToMany (targetEntity="App\Entity\TrabajadorAltaBaja", mappedBy="trabajador")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $empresas;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(?string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
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

    public function getDni(): ?string
    {
        return $this->dni;
    }

    public function setDni(?string $dni): self
    {
        $this->dni = $dni;

        return $this;
    }

	/**
	 * @return mixed
	 */
	public function getSexo() {
		return $this->sexo;
	}

	/**
	 * @param mixed $sexo
	 */
	public function setSexo( $sexo ): void {
		$this->sexo = $sexo;
	}

	/**
	 * @return mixed
	 */
	public function getFechaNacimiento() {
		return $this->fechaNacimiento;
	}

	/**
	 * @param mixed $fechaNacimiento
	 */
	public function setFechaNacimiento( $fechaNacimiento ): void {
		$this->fechaNacimiento = $fechaNacimiento;
	}

	/**
	 * @return mixed
	 */
	public function getEdad() {
		return $this->edad;
	}

	/**
	 * @param mixed $edad
	 */
	public function setEdad( $edad ): void {
		$this->edad = $edad;
	}

	/**
	 * @return mixed
	 */
	public function getGrupoEdad() {
		return $this->grupoEdad;
	}

	/**
	 * @param mixed $grupoEdad
	 */
	public function setGrupoEdad( $grupoEdad ): void {
		$this->grupoEdad = $grupoEdad;
	}

	/**
	 * @return mixed
	 */
	public function getNaf() {
		return $this->naf;
	}

	/**
	 * @param mixed $naf
	 */
	public function setNaf( $naf ): void {
		$this->naf = $naf;
	}

	/**
	 * @return mixed
	 */
	public function getIdentificacion() {
		return $this->identificacion;
	}

	/**
	 * @param mixed $identificacion
	 */
	public function setIdentificacion( $identificacion ): void {
		$this->identificacion = $identificacion;
	}

	/**
	 * @return mixed
	 */
	public function getNacionalidad() {
		return $this->nacionalidad;
	}

	/**
	 * @param mixed $nacionalidad
	 */
	public function setNacionalidad( $nacionalidad ): void {
		$this->nacionalidad = $nacionalidad;
	}

	/**
	 * @return mixed
	 */
	public function getDiscapacidad() {
		return $this->discapacidad;
	}

	/**
	 * @param mixed $discapacidad
	 */
	public function setDiscapacidad( $discapacidad ): void {
		$this->discapacidad = $discapacidad;
	}

	/**
	 * @return mixed
	 */
	public function getPuestoTrabajo() {
		return $this->puestoTrabajo;
	}

	/**
	 * @param mixed $puestoTrabajo
	 */
	public function setPuestoTrabajo( $puestoTrabajo ): void {
		$this->puestoTrabajo = $puestoTrabajo;
	}

	/**
	 * @return mixed
	 */
	public function getCno() {
		return $this->cno;
	}

	/**
	 * @param mixed $cno
	 */
	public function setCno( $cno ): void {
		$this->cno = $cno;
	}

	/**
	 * @return mixed
	 */
	public function getCategoriaProfesional() {
		return $this->categoriaProfesional;
	}

	/**
	 * @param mixed $categoriaProfesional
	 */
	public function setCategoriaProfesional( $categoriaProfesional ): void {
		$this->categoriaProfesional = $categoriaProfesional;
	}

	/**
	 * @return mixed
	 */
	public function getRegimenCotizacionSS() {
		return $this->regimenCotizacionSS;
	}

	/**
	 * @param mixed $regimenCotizacionSS
	 */
	public function setRegimenCotizacionSS( $regimenCotizacionSS ): void {
		$this->regimenCotizacionSS = $regimenCotizacionSS;
	}

	/**
	 * @return mixed
	 */
	public function getSituacionProfesional() {
		return $this->situacionProfesional;
	}

	/**
	 * @param mixed $situacionProfesional
	 */
	public function setSituacionProfesional( $situacionProfesional ): void {
		$this->situacionProfesional = $situacionProfesional;
	}

	/**
	 * @return mixed
	 */
	public function getAutonomo() {
		return $this->autonomo;
	}

	/**
	 * @param mixed $autonomo
	 */
	public function setAutonomo( $autonomo ): void {
		$this->autonomo = $autonomo;
	}

	/**
	 * @return mixed
	 */
	public function getEtt() {
		return $this->ett;
	}

	/**
	 * @param mixed $ett
	 */
	public function setEtt( $ett ): void {
		$this->ett = $ett;
	}

	/**
	 * @return mixed
	 */
	public function getSubvencionado() {
		return $this->subvencionado;
	}

	/**
	 * @param mixed $subvencionado
	 */
	public function setSubvencionado( $subvencionado ): void {
		$this->subvencionado = $subvencionado;
	}

	/**
	 * @return mixed
	 */
	public function getExclusion() {
		return $this->exclusion;
	}

	/**
	 * @param mixed $exclusion
	 */
	public function setExclusion( $exclusion ): void {
		$this->exclusion = $exclusion;
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
    public function getDomicilio()
    {
        return $this->domicilio;
    }

    /**
     * @param mixed $domicilio
     */
    public function setDomicilio($domicilio): void
    {
        $this->domicilio = $domicilio;
    }

    /**
     * @return mixed
     */
    public function getTelefono1()
    {
        return $this->telefono1;
    }

    /**
     * @param mixed $telefono1
     */
    public function setTelefono1($telefono1): void
    {
        $this->telefono1 = $telefono1;
    }

    /**
     * @return mixed
     */
    public function getTelefono2()
    {
        return $this->telefono2;
    }

    /**
     * @param mixed $telefono2
     */
    public function setTelefono2($telefono2): void
    {
        $this->telefono2 = $telefono2;
    }

    /**
     * @return mixed
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * @param mixed $mail
     */
    public function setMail($mail): void
    {
        $this->mail = $mail;
    }

    /**
     * @return mixed
     */
    public function getObservaciones()
    {
        return $this->observaciones;
    }

    /**
     * @param mixed $observaciones
     */
    public function setObservaciones($observaciones): void
    {
        $this->observaciones = $observaciones;
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
     * @return bool
     */
    public function isHistoricoPrevenet(): bool
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
    public function getTipoFormacion()
    {
        return $this->tipoFormacion;
    }

    /**
     * @param mixed $tipoFormacion
     */
    public function setTipoFormacion($tipoFormacion): void
    {
        $this->tipoFormacion = $tipoFormacion;
    }

    /**
     * @return mixed
     */
    public function getFormacion()
    {
        return $this->formacion;
    }

    /**
     * @param mixed $formacion
     */
    public function setFormacion($formacion): void
    {
        $this->formacion = $formacion;
    }

    /**
     * @return mixed
     */
    public function getFechaFormacion()
    {
        return $this->fechaFormacion;
    }

    /**
     * @param mixed $fechaFormacion
     */
    public function setFechaFormacion($fechaFormacion): void
    {
        $this->fechaFormacion = $fechaFormacion;
    }

    /**
     * @return mixed
     */
    public function getFechaUltimaRevision()
    {
        return $this->fechaUltimaRevision;
    }

    /**
     * @param mixed $fechaUltimaRevision
     */
    public function setFechaUltimaRevision($fechaUltimaRevision): void
    {
        $this->fechaUltimaRevision = $fechaUltimaRevision;
    }

    /**
     * @return mixed
     */
    public function getFechaNuevaRevision()
    {
        return $this->fechaNuevaRevision;
    }

    /**
     * @param mixed $fechaNuevaRevision
     */
    public function setFechaNuevaRevision($fechaNuevaRevision): void
    {
        $this->fechaNuevaRevision = $fechaNuevaRevision;
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
    public function getEmpresas()
    {
        return $this->empresas;
    }

    /**
     * @param mixed $empresas
     */
    public function setEmpresas($empresas): void
    {
        $this->empresas = $empresas;
    }

    public function trabajadorDni(){
        if(!is_null($this->dni)){
            return (string) $this->nombre .' ('. $this->dni. ')';
        }else{
            return (string) $this->nombre;
        }
    }

    public function __toString()
    {
        return (string) $this->nombre;
    }
}
