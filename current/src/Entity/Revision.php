<?php

namespace App\Entity;

use App\Repository\RevisionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RevisionRepository::class)
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class Revision
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Trabajador", inversedBy="Revision")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $trabajador;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PuestoTrabajoCentro", inversedBy="Revision")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $puestoTrabajo;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Empresa", inversedBy="Revision")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $empresa;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $fecha;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Doctor", inversedBy="Revision")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $medico;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Apto", inversedBy="Revision")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $apto;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $fechaCertificacion;

    /**
     * @ORM\Column(type="boolean", length=2000, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $certificado = false;

    /**
     * @ORM\Column(type="boolean", length=2000, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $informe = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $restricciones;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $observacionMedico;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $observacionInterna;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $observaciones;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $conclusiones;

    /**
     * @ORM\Column(type="boolean", length=2000, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $anulado = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GdocFichero", inversedBy="Revision")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $fichero;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\EstadoRevision", inversedBy="Revision")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $estado;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $passwordPdf;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GdocFichero", inversedBy="Revision")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $ficheroResumen;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GdocFichero", inversedBy="Revision")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $ficheroRevisionMedica;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $numeroPeticion;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $fechaRecuperacionResultado;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Citacion", inversedBy="Revision")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $citacion;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\AptitudRestriccion", inversedBy="Revision")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $aptitudRestriccion;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $aptitudEnviada = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $token;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GdocFichero", inversedBy="Revision")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $ficheroRestriccion;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ValidezAptitud", inversedBy="Revision")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $validez;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $pruebasComplementarias;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $telefono;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $analitica = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $recomendaciones;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $electrocardiograma;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Facturacion", inversedBy="Revision")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $factura;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $fechaFirma;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $fechaEnvio;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $usuarioFirma;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $numeroPeticionVerificacion;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getTrabajador()
    {
        return $this->trabajador;
    }

    /**
     * @param mixed $trabajador
     */
    public function setTrabajador($trabajador): void
    {
        $this->trabajador = $trabajador;
    }

    /**
     * @return mixed
     */
    public function getPuestoTrabajo()
    {
        return $this->puestoTrabajo;
    }

    /**
     * @param mixed $puestoTrabajo
     */
    public function setPuestoTrabajo($puestoTrabajo): void
    {
        $this->puestoTrabajo = $puestoTrabajo;
    }

    /**
     * @return mixed
     */
    public function getEmpresa()
    {
        return $this->empresa;
    }

    /**
     * @param mixed $empresa
     */
    public function setEmpresa($empresa): void
    {
        $this->empresa = $empresa;
    }

    /**
     * @return mixed
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * @param mixed $fecha
     */
    public function setFecha($fecha): void
    {
        $this->fecha = $fecha;
    }

    /**
     * @return mixed
     */
    public function getMedico()
    {
        return $this->medico;
    }

    /**
     * @param mixed $medico
     */
    public function setMedico($medico): void
    {
        $this->medico = $medico;
    }

    /**
     * @return mixed
     */
    public function getAnulado()
    {
        return $this->anulado;
    }

    /**
     * @param mixed $anulado
     */
    public function setAnulado($anulado): void
    {
        $this->anulado = $anulado;
    }

    /**
     * @return mixed
     */
    public function getApto()
    {
        return $this->apto;
    }

    /**
     * @param mixed $apto
     */
    public function setApto($apto): void
    {
        $this->apto = $apto;
    }

    /**
     * @return mixed
     */
    public function getFechaCertificacion()
    {
        return $this->fechaCertificacion;
    }

    /**
     * @param mixed $fechaCertificacion
     */
    public function setFechaCertificacion($fechaCertificacion): void
    {
        $this->fechaCertificacion = $fechaCertificacion;
    }

    /**
     * @return mixed
     */
    public function getCertificado()
    {
        return $this->certificado;
    }

    /**
     * @param mixed $certificado
     */
    public function setCertificado($certificado): void
    {
        $this->certificado = $certificado;
    }

    /**
     * @return mixed
     */
    public function getInforme()
    {
        return $this->informe;
    }

    /**
     * @param mixed $informe
     */
    public function setInforme($informe): void
    {
        $this->informe = $informe;
    }

    /**
     * @return mixed
     */
    public function getRestricciones()
    {
        return $this->restricciones;
    }

    /**
     * @param mixed $restricciones
     */
    public function setRestricciones($restricciones): void
    {
        $this->restricciones = $restricciones;
    }

    /**
     * @return mixed
     */
    public function getObservacionMedico()
    {
        return $this->observacionMedico;
    }

    /**
     * @param mixed $observacionMedico
     */
    public function setObservacionMedico($observacionMedico): void
    {
        $this->observacionMedico = $observacionMedico;
    }

    /**
     * @return mixed
     */
    public function getObservacionInterna()
    {
        return $this->observacionInterna;
    }

    /**
     * @param mixed $observacionInterna
     */
    public function setObservacionInterna($observacionInterna): void
    {
        $this->observacionInterna = $observacionInterna;
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
    public function getConclusiones()
    {
        return $this->conclusiones;
    }

    /**
     * @param mixed $conclusiones
     */
    public function setConclusiones($conclusiones): void
    {
        $this->conclusiones = $conclusiones;
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
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * @param mixed $estado
     */
    public function setEstado($estado): void
    {
        $this->estado = $estado;
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
    public function getFicheroResumen()
    {
        return $this->ficheroResumen;
    }

    /**
     * @param mixed $ficheroResumen
     */
    public function setFicheroResumen($ficheroResumen): void
    {
        $this->ficheroResumen = $ficheroResumen;
    }

    /**
     * @return mixed
     */
    public function getFicheroRevisionMedica()
    {
        return $this->ficheroRevisionMedica;
    }

    /**
     * @param mixed $ficheroRevisionMedica
     */
    public function setFicheroRevisionMedica($ficheroRevisionMedica): void
    {
        $this->ficheroRevisionMedica = $ficheroRevisionMedica;
    }

    /**
     * @return mixed
     */
    public function getNumeroPeticion()
    {
        return $this->numeroPeticion;
    }

    /**
     * @param mixed $numeroPeticion
     */
    public function setNumeroPeticion($numeroPeticion): void
    {
        $this->numeroPeticion = $numeroPeticion;
    }

    /**
     * @return mixed
     */
    public function getFechaRecuperacionResultado()
    {
        return $this->fechaRecuperacionResultado;
    }

    /**
     * @param mixed $fechaRecuperacionResultado
     */
    public function setFechaRecuperacionResultado($fechaRecuperacionResultado): void
    {
        $this->fechaRecuperacionResultado = $fechaRecuperacionResultado;
    }

    /**
     * @return mixed
     */
    public function getCitacion()
    {
        return $this->citacion;
    }

    /**
     * @param mixed $citacion
     */
    public function setCitacion($citacion): void
    {
        $this->citacion = $citacion;
    }

    /**
     * @return mixed
     */
    public function getAptitudRestriccion()
    {
        return $this->aptitudRestriccion;
    }

    /**
     * @param mixed $aptitudRestriccion
     */
    public function setAptitudRestriccion($aptitudRestriccion): void
    {
        $this->aptitudRestriccion = $aptitudRestriccion;
    }

    /**
     * @return mixed
     */
    public function getAptitudEnviada()
    {
        return $this->aptitudEnviada;
    }

    /**
     * @param mixed $aptitudEnviada
     */
    public function setAptitudEnviada($aptitudEnviada): void
    {
        $this->aptitudEnviada = $aptitudEnviada;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token): void
    {
        $this->token = $token;
    }

    /**
     * @return mixed
     */
    public function getFicheroRestriccion()
    {
        return $this->ficheroRestriccion;
    }

    /**
     * @param mixed $ficheroRestriccion
     */
    public function setFicheroRestriccion($ficheroRestriccion): void
    {
        $this->ficheroRestriccion = $ficheroRestriccion;
    }

    /**
     * @return mixed
     */
    public function getValidez()
    {
        return $this->validez;
    }

    /**
     * @param mixed $validez
     */
    public function setValidez($validez): void
    {
        $this->validez = $validez;
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
    public function getTelefono()
    {
        return $this->telefono;
    }

    /**
     * @param mixed $telefono
     */
    public function setTelefono($telefono): void
    {
        $this->telefono = $telefono;
    }

    /**
     * @return mixed
     */
    public function getAnalitica()
    {
        return $this->analitica;
    }

    /**
     * @param mixed $analitica
     */
    public function setAnalitica($analitica): void
    {
        $this->analitica = $analitica;
    }

    /**
     * @return mixed
     */
    public function getRecomendaciones()
    {
        return $this->recomendaciones;
    }

    /**
     * @param mixed $recomendaciones
     */
    public function setRecomendaciones($recomendaciones): void
    {
        $this->recomendaciones = $recomendaciones;
    }

    /**
     * @return mixed
     */
    public function getElectrocardiograma()
    {
        return $this->electrocardiograma;
    }

    /**
     * @param mixed $electrocardiograma
     */
    public function setElectrocardiograma($electrocardiograma): void
    {
        $this->electrocardiograma = $electrocardiograma;
    }

    /**
     * @return mixed
     */
    public function getFactura()
    {
        return $this->factura;
    }

    /**
     * @param mixed $factura
     */
    public function setFactura($factura): void
    {
        $this->factura = $factura;
    }

    /**
     * @return mixed
     */
    public function getFechaFirma()
    {
        return $this->fechaFirma;
    }

    /**
     * @param mixed $fechaFirma
     */
    public function setFechaFirma($fechaFirma): void
    {
        $this->fechaFirma = $fechaFirma;
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
    public function getUsuarioFirma()
    {
        return $this->usuarioFirma;
    }

    /**
     * @param mixed $usuarioFirma
     */
    public function setUsuarioFirma($usuarioFirma): void
    {
        $this->usuarioFirma = $usuarioFirma;
    }

    /**
     * @return mixed
     */
    public function getNumeroPeticionVerificacion()
    {
        return $this->numeroPeticionVerificacion;
    }

    /**
     * @param mixed $numeroPeticionVerificacion
     */
    public function setNumeroPeticionVerificacion($numeroPeticionVerificacion): void
    {
        $this->numeroPeticionVerificacion = $numeroPeticionVerificacion;
    }
}
