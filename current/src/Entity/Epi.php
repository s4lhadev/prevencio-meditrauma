<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EpiRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class Epi
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
    private $descripcion;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $infoDefEpi;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $infoComponents;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $infoRequisits;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $infoInsUtil;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $infoInsMant;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $infoInstMont;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $infoInstMag;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $infoInsNet;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $infoDisseny;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $infoRiscSol;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $infoPotencial;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $infoAdicional;

    /**
     * @ORM\Column(type="boolean", length=2000, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $empresa = false;

    /**
     * @ORM\Column(type="boolean", length=2000, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $trabajador = false;

    /**
     * @ORM\Column(type="boolean", length=2000, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $anulado = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $descripcionCa;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $descripcionEs;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * @param mixed $descripcion
     */
    public function setDescripcion($descripcion): void
    {
        $this->descripcion = $descripcion;
    }

    /**
     * @return mixed
     */
    public function getInfoDefEpi()
    {
        return $this->infoDefEpi;
    }

    /**
     * @param mixed $infoDefEpi
     */
    public function setInfoDefEpi($infoDefEpi): void
    {
        $this->infoDefEpi = $infoDefEpi;
    }

    /**
     * @return mixed
     */
    public function getInfoComponents()
    {
        return $this->infoComponents;
    }

    /**
     * @param mixed $infoComponents
     */
    public function setInfoComponents($infoComponents): void
    {
        $this->infoComponents = $infoComponents;
    }

    /**
     * @return mixed
     */
    public function getInfoRequisits()
    {
        return $this->infoRequisits;
    }

    /**
     * @param mixed $infoRequisits
     */
    public function setInfoRequisits($infoRequisits): void
    {
        $this->infoRequisits = $infoRequisits;
    }

    /**
     * @return mixed
     */
    public function getInfoInsUtil()
    {
        return $this->infoInsUtil;
    }

    /**
     * @param mixed $infoInsUtil
     */
    public function setInfoInsUtil($infoInsUtil): void
    {
        $this->infoInsUtil = $infoInsUtil;
    }

    /**
     * @return mixed
     */
    public function getInfoInsMant()
    {
        return $this->infoInsMant;
    }

    /**
     * @param mixed $infoInsMant
     */
    public function setInfoInsMant($infoInsMant): void
    {
        $this->infoInsMant = $infoInsMant;
    }

    /**
     * @return mixed
     */
    public function getInfoInstMont()
    {
        return $this->infoInstMont;
    }

    /**
     * @param mixed $infoInstMont
     */
    public function setInfoInstMont($infoInstMont): void
    {
        $this->infoInstMont = $infoInstMont;
    }

    /**
     * @return mixed
     */
    public function getInfoInstMag()
    {
        return $this->infoInstMag;
    }

    /**
     * @param mixed $infoInstMag
     */
    public function setInfoInstMag($infoInstMag): void
    {
        $this->infoInstMag = $infoInstMag;
    }

    /**
     * @return mixed
     */
    public function getInfoInsNet()
    {
        return $this->infoInsNet;
    }

    /**
     * @param mixed $infoInsNet
     */
    public function setInfoInsNet($infoInsNet): void
    {
        $this->infoInsNet = $infoInsNet;
    }

    /**
     * @return mixed
     */
    public function getInfoDisseny()
    {
        return $this->infoDisseny;
    }

    /**
     * @param mixed $infoDisseny
     */
    public function setInfoDisseny($infoDisseny): void
    {
        $this->infoDisseny = $infoDisseny;
    }

    /**
     * @return mixed
     */
    public function getInfoRiscSol()
    {
        return $this->infoRiscSol;
    }

    /**
     * @param mixed $infoRiscSol
     */
    public function setInfoRiscSol($infoRiscSol): void
    {
        $this->infoRiscSol = $infoRiscSol;
    }

    /**
     * @return mixed
     */
    public function getInfoPotencial()
    {
        return $this->infoPotencial;
    }

    /**
     * @param mixed $infoPotencial
     */
    public function setInfoPotencial($infoPotencial): void
    {
        $this->infoPotencial = $infoPotencial;
    }

    /**
     * @return mixed
     */
    public function getInfoAdicional()
    {
        return $this->infoAdicional;
    }

    /**
     * @param mixed $infoAdicional
     */
    public function setInfoAdicional($infoAdicional): void
    {
        $this->infoAdicional = $infoAdicional;
    }

    /**
     * @return bool
     */
    public function getEmpresa(): bool
    {
        return $this->empresa;
    }

    /**
     * @param bool $empresa
     */
    public function setEmpresa(bool $empresa): void
    {
        $this->empresa = $empresa;
    }

    /**
     * @return bool
     */
    public function getTrabajador(): bool
    {
        return $this->trabajador;
    }

    /**
     * @param bool $trabajador
     */
    public function setTrabajador(bool $trabajador): void
    {
        $this->trabajador = $trabajador;
    }

    /**
     * @return bool
     */
    public function getAnulado(): bool
    {
        return $this->anulado;
    }

    /**
     * @param bool $anulado
     */
    public function setAnulado(bool $anulado): void
    {
        $this->anulado = $anulado;
    }

    /**
     * @return mixed
     */
    public function getDescripcionCa()
    {
        return $this->descripcionCa;
    }

    /**
     * @param mixed $descripcionCa
     */
    public function setDescripcionCa($descripcionCa): void
    {
        $this->descripcionCa = $descripcionCa;
    }

    /**
     * @return mixed
     */
    public function getDescripcionEs()
    {
        return $this->descripcionEs;
    }

    /**
     * @param mixed $descripcionEs
     */
    public function setDescripcionEs($descripcionEs): void
    {
        $this->descripcionEs = $descripcionEs;
    }
}
