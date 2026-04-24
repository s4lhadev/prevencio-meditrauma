<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MaquinaEmpresaRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class MaquinaEmpresa
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
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $descripcion;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GrupoMaquina", inversedBy="MaquinaEmpresa")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $grupoMaquina;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MaquinaGenerica", inversedBy="MaquinaEmpresa")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $maquinaGenerica;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Empresa", inversedBy="MaquinaEmpresa")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $empresa;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Centro", inversedBy="MaquinaEmpresa")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $centro;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $fabricante;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $modelo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $numSerie;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $anyoFabricacion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $anyoCompra;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $placaCaracteristica;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $marcadoCE;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $conformidad = false;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $manualInstrucciones = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $observaciones;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default":0})
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
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * @param mixed $codigo
     */
    public function setCodigo($codigo): void
    {
        $this->codigo = $codigo;
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
    public function getGrupoMaquina()
    {
        return $this->grupoMaquina;
    }

    /**
     * @param mixed $grupoMaquina
     */
    public function setGrupoMaquina($grupoMaquina): void
    {
        $this->grupoMaquina = $grupoMaquina;
    }

    /**
     * @return mixed
     */
    public function getMaquinaGenerica()
    {
        return $this->maquinaGenerica;
    }

    /**
     * @param mixed $maquinaGenerica
     */
    public function setMaquinaGenerica($maquinaGenerica): void
    {
        $this->maquinaGenerica = $maquinaGenerica;
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
    public function getCentro()
    {
        return $this->centro;
    }

    /**
     * @param mixed $centro
     */
    public function setCentro($centro): void
    {
        $this->centro = $centro;
    }

    /**
     * @return mixed
     */
    public function getFabricante()
    {
        return $this->fabricante;
    }

    /**
     * @param mixed $fabricante
     */
    public function setFabricante($fabricante): void
    {
        $this->fabricante = $fabricante;
    }

    /**
     * @return mixed
     */
    public function getModelo()
    {
        return $this->modelo;
    }

    /**
     * @param mixed $modelo
     */
    public function setModelo($modelo): void
    {
        $this->modelo = $modelo;
    }

    /**
     * @return mixed
     */
    public function getNumSerie()
    {
        return $this->numSerie;
    }

    /**
     * @param mixed $numSerie
     */
    public function setNumSerie($numSerie): void
    {
        $this->numSerie = $numSerie;
    }

    /**
     * @return mixed
     */
    public function getAnyoFabricacion()
    {
        return $this->anyoFabricacion;
    }

    /**
     * @param mixed $anyoFabricacion
     */
    public function setAnyoFabricacion($anyoFabricacion): void
    {
        $this->anyoFabricacion = $anyoFabricacion;
    }

    /**
     * @return mixed
     */
    public function getAnyoCompra()
    {
        return $this->anyoCompra;
    }

    /**
     * @param mixed $anyoCompra
     */
    public function setAnyoCompra($anyoCompra): void
    {
        $this->anyoCompra = $anyoCompra;
    }

    /**
     * @return mixed
     */
    public function getPlacaCaracteristica()
    {
        return $this->placaCaracteristica;
    }

    /**
     * @param mixed $placaCaracteristica
     */
    public function setPlacaCaracteristica($placaCaracteristica): void
    {
        $this->placaCaracteristica = $placaCaracteristica;
    }

    /**
     * @return mixed
     */
    public function getMarcadoCE()
    {
        return $this->marcadoCE;
    }

    /**
     * @param mixed $marcadoCE
     */
    public function setMarcadoCE($marcadoCE): void
    {
        $this->marcadoCE = $marcadoCE;
    }

    /**
     * @return bool
     */
    public function isConformidad(): bool
    {
        return $this->conformidad;
    }

    /**
     * @param bool $conformidad
     */
    public function setConformidad(bool $conformidad): void
    {
        $this->conformidad = $conformidad;
    }

    /**
     * @return bool
     */
    public function isManualInstrucciones(): bool
    {
        return $this->manualInstrucciones;
    }

    /**
     * @param bool $manualInstrucciones
     */
    public function setManualInstrucciones(bool $manualInstrucciones): void
    {
        $this->manualInstrucciones = $manualInstrucciones;
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
     * @return bool
     */
    public function isAnulado(): bool
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
}
