<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GdocConfigRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class GdocConfig
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
	private $ruta;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $carpetaGenerada;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $carpetaTrabajador;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $carpetaTemporal;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $carpetaRevision;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $carpetaContrato;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $carpetaFactura;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $carpetaAccidente;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $carpetaCertificacion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $carpetaCitacion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $carpetaEvaluacion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $carpetaNotificacion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $carpetaPlanPrevencion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $carpetaModelo347;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $carpetaAptitud;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $carpetaEmpresa;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $passwordFicherosEncriptados;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $carpetaFichaRiesgos;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $carpetaResultadoAnaliticaTmp;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $carpetaMemoria;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $carpetaEstudioEpidemiologico;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $carpetaPlantillas;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $url;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $host;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $rutaPortal;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $carpetaManualVs;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $carpetaDocumentoAdjuntoRevision;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $carpetaProtocoloAcoso;

    public function getId(): ?int
    {
        return $this->id;
    }

	/**
	 * @return mixed
	 */
	public function getRuta() {
		return $this->ruta;
	}

	/**
	 * @param mixed $ruta
	 */
	public function setRuta( $ruta ): void {
		$this->ruta = $ruta;
	}

	/**
	 * @return mixed
	 */
	public function getCarpetaGenerada() {
		return $this->carpetaGenerada;
	}

	/**
	 * @param mixed $carpetaGenerada
	 */
	public function setCarpetaGenerada( $carpetaGenerada ): void {
		$this->carpetaGenerada = $carpetaGenerada;
	}

    /**
     * @return mixed
     */
    public function getCarpetaTrabajador()
    {
        return $this->carpetaTrabajador;
    }

    /**
     * @param mixed $carpetaTrabajador
     */
    public function setCarpetaTrabajador($carpetaTrabajador): void
    {
        $this->carpetaTrabajador = $carpetaTrabajador;
    }

    /**
     * @return mixed
     */
    public function getCarpetaTemporal()
    {
        return $this->carpetaTemporal;
    }

    /**
     * @param mixed $carpetaTemporal
     */
    public function setCarpetaTemporal($carpetaTemporal): void
    {
        $this->carpetaTemporal = $carpetaTemporal;
    }

    /**
     * @return mixed
     */
    public function getCarpetaRevision()
    {
        return $this->carpetaRevision;
    }

    /**
     * @param mixed $carpetaRevision
     */
    public function setCarpetaRevision($carpetaRevision): void
    {
        $this->carpetaRevision = $carpetaRevision;
    }

    /**
     * @return mixed
     */
    public function getCarpetaContrato()
    {
        return $this->carpetaContrato;
    }

    /**
     * @param mixed $carpetaContrato
     */
    public function setCarpetaContrato($carpetaContrato): void
    {
        $this->carpetaContrato = $carpetaContrato;
    }

    /**
     * @return mixed
     */
    public function getCarpetaFactura()
    {
        return $this->carpetaFactura;
    }

    /**
     * @param mixed $carpetaFactura
     */
    public function setCarpetaFactura($carpetaFactura): void
    {
        $this->carpetaFactura = $carpetaFactura;
    }

    /**
     * @return mixed
     */
    public function getCarpetaAccidente()
    {
        return $this->carpetaAccidente;
    }

    /**
     * @param mixed $carpetaAccidente
     */
    public function setCarpetaAccidente($carpetaAccidente): void
    {
        $this->carpetaAccidente = $carpetaAccidente;
    }

    /**
     * @return mixed
     */
    public function getCarpetaCertificacion()
    {
        return $this->carpetaCertificacion;
    }

    /**
     * @param mixed $carpetaCertificacion
     */
    public function setCarpetaCertificacion($carpetaCertificacion): void
    {
        $this->carpetaCertificacion = $carpetaCertificacion;
    }

    /**
     * @return mixed
     */
    public function getCarpetaCitacion()
    {
        return $this->carpetaCitacion;
    }

    /**
     * @param mixed $carpetaCitacion
     */
    public function setCarpetaCitacion($carpetaCitacion): void
    {
        $this->carpetaCitacion = $carpetaCitacion;
    }

    /**
     * @return mixed
     */
    public function getCarpetaEvaluacion()
    {
        return $this->carpetaEvaluacion;
    }

    /**
     * @param mixed $carpetaEvaluacion
     */
    public function setCarpetaEvaluacion($carpetaEvaluacion): void
    {
        $this->carpetaEvaluacion = $carpetaEvaluacion;
    }

    /**
     * @return mixed
     */
    public function getCarpetaNotificacion()
    {
        return $this->carpetaNotificacion;
    }

    /**
     * @param mixed $carpetaNotificacion
     */
    public function setCarpetaNotificacion($carpetaNotificacion): void
    {
        $this->carpetaNotificacion = $carpetaNotificacion;
    }

    /**
     * @return mixed
     */
    public function getCarpetaPlanPrevencion()
    {
        return $this->carpetaPlanPrevencion;
    }

    /**
     * @param mixed $carpetaPlanPrevencion
     */
    public function setCarpetaPlanPrevencion($carpetaPlanPrevencion): void
    {
        $this->carpetaPlanPrevencion = $carpetaPlanPrevencion;
    }

    /**
     * @return mixed
     */
    public function getCarpetaModelo347()
    {
        return $this->carpetaModelo347;
    }

    /**
     * @param mixed $carpetaModelo347
     */
    public function setCarpetaModelo347($carpetaModelo347): void
    {
        $this->carpetaModelo347 = $carpetaModelo347;
    }

    /**
     * @return mixed
     */
    public function getCarpetaAptitud()
    {
        return $this->carpetaAptitud;
    }

    /**
     * @param mixed $carpetaAptitud
     */
    public function setCarpetaAptitud($carpetaAptitud): void
    {
        $this->carpetaAptitud = $carpetaAptitud;
    }

    /**
     * @return mixed
     */
    public function getCarpetaEmpresa()
    {
        return $this->carpetaEmpresa;
    }

    /**
     * @param mixed $carpetaEmpresa
     */
    public function setCarpetaEmpresa($carpetaEmpresa): void
    {
        $this->carpetaEmpresa = $carpetaEmpresa;
    }

    /**
     * @return mixed
     */
    public function getPasswordFicherosEncriptados()
    {
        return $this->passwordFicherosEncriptados;
    }

    /**
     * @param mixed $passwordFicherosEncriptados
     */
    public function setPasswordFicherosEncriptados($passwordFicherosEncriptados): void
    {
        $this->passwordFicherosEncriptados = $passwordFicherosEncriptados;
    }

    /**
     * @return mixed
     */
    public function getCarpetaFichaRiesgos()
    {
        return $this->carpetaFichaRiesgos;
    }

    /**
     * @param mixed $carpetaFichaRiesgos
     */
    public function setCarpetaFichaRiesgos($carpetaFichaRiesgos): void
    {
        $this->carpetaFichaRiesgos = $carpetaFichaRiesgos;
    }

    /**
     * @return mixed
     */
    public function getCarpetaResultadoAnaliticaTmp()
    {
        return $this->carpetaResultadoAnaliticaTmp;
    }

    /**
     * @param mixed $carpetaResultadoAnaliticaTmp
     */
    public function setCarpetaResultadoAnaliticaTmp($carpetaResultadoAnaliticaTmp): void
    {
        $this->carpetaResultadoAnaliticaTmp = $carpetaResultadoAnaliticaTmp;
    }

    /**
     * @return mixed
     */
    public function getCarpetaMemoria()
    {
        return $this->carpetaMemoria;
    }

    /**
     * @param mixed $carpetaMemoria
     */
    public function setCarpetaMemoria($carpetaMemoria): void
    {
        $this->carpetaMemoria = $carpetaMemoria;
    }

    /**
     * @return mixed
     */
    public function getCarpetaEstudioEpidemiologico()
    {
        return $this->carpetaEstudioEpidemiologico;
    }

    /**
     * @param mixed $carpetaEstudioEpidemiologico
     */
    public function setCarpetaEstudioEpidemiologico($carpetaEstudioEpidemiologico): void
    {
        $this->carpetaEstudioEpidemiologico = $carpetaEstudioEpidemiologico;
    }

    /**
     * @return mixed
     */
    public function getCarpetaPlantillas()
    {
        return $this->carpetaPlantillas;
    }

    /**
     * @param mixed $carpetaPlantillas
     */
    public function setCarpetaPlantillas($carpetaPlantillas): void
    {
        $this->carpetaPlantillas = $carpetaPlantillas;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url): void
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param mixed $host
     */
    public function setHost($host): void
    {
        $this->host = $host;
    }

    /**
     * @return mixed
     */
    public function getRutaPortal()
    {
        return $this->rutaPortal;
    }

    /**
     * @param mixed $rutaPortal
     */
    public function setRutaPortal($rutaPortal): void
    {
        $this->rutaPortal = $rutaPortal;
    }

    /**
     * @return mixed
     */
    public function getCarpetaManualVs()
    {
        return $this->carpetaManualVs;
    }

    /**
     * @param mixed $carpetaManualVs
     */
    public function setCarpetaManualVs($carpetaManualVs): void
    {
        $this->carpetaManualVs = $carpetaManualVs;
    }

    /**
     * @return mixed
     */
    public function getCarpetaDocumentoAdjuntoRevision()
    {
        return $this->carpetaDocumentoAdjuntoRevision;
    }

    /**
     * @param mixed $carpetaDocumentoAdjuntoRevision
     */
    public function setCarpetaDocumentoAdjuntoRevision($carpetaDocumentoAdjuntoRevision): void
    {
        $this->carpetaDocumentoAdjuntoRevision = $carpetaDocumentoAdjuntoRevision;
    }

    /**
     * @return mixed
     */
    public function getCarpetaProtocoloAcoso()
    {
        return $this->carpetaProtocoloAcoso;
    }

    /**
     * @param mixed $carpetaProtocoloAcoso
     */
    public function setCarpetaProtocoloAcoso($carpetaProtocoloAcoso): void
    {
        $this->carpetaProtocoloAcoso = $carpetaProtocoloAcoso;
    }
}
