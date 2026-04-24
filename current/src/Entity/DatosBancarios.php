<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DatosBancariosRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class DatosBancarios
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
    private $codigo;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\EntidadBancaria", inversedBy="DatosBancarios")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
    private $entidadBancaria;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $oficina;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $numCuenta;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $direccion;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $localidad;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $telefon;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $dc;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $principal;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Pais", inversedBy="DatosBancarios")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $pais;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $ibanDigital;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $ibanPapel;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $bic;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\FormaPago", inversedBy="DatosBancarios")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $formaPago;

	/**
	 * @ORM\Column(type="integer", length=2000, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $diaPago;

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
	public function getEntidadBancaria() {
		return $this->entidadBancaria;
	}

	/**
	 * @param mixed $entidadBancaria
	 */
	public function setEntidadBancaria( $entidadBancaria ): void {
		$this->entidadBancaria = $entidadBancaria;
	}

	/**
	 * @return mixed
	 */
	public function getOficina() {
		return $this->oficina;
	}

	/**
	 * @param mixed $oficina
	 */
	public function setOficina( $oficina ): void {
		$this->oficina = $oficina;
	}

	/**
	 * @return mixed
	 */
	public function getNumCuenta() {
		return $this->numCuenta;
	}

	/**
	 * @param mixed $numCuenta
	 */
	public function setNumCuenta( $numCuenta ): void {
		$this->numCuenta = $numCuenta;
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
	public function getTelefon() {
		return $this->telefon;
	}

	/**
	 * @param mixed $telefon
	 */
	public function setTelefon( $telefon ): void {
		$this->telefon = $telefon;
	}

	/**
	 * @return mixed
	 */
	public function getDc() {
		return $this->dc;
	}

	/**
	 * @param mixed $dc
	 */
	public function setDc( $dc ): void {
		$this->dc = $dc;
	}

	/**
	 * @return mixed
	 */
	public function getPrincipal() {
		return $this->principal;
	}

	/**
	 * @param mixed $principal
	 */
	public function setPrincipal( $principal ): void {
		$this->principal = $principal;
	}

	/**
	 * @return mixed
	 */
	public function getPais() {
		return $this->pais;
	}

	/**
	 * @param mixed $pais
	 */
	public function setPais( $pais ): void {
		$this->pais = $pais;
	}

	/**
	 * @return mixed
	 */
	public function getIbanDigital() {
		return $this->ibanDigital;
	}

	/**
	 * @param mixed $ibanDigital
	 */
	public function setIbanDigital( $ibanDigital ): void {
		$this->ibanDigital = $ibanDigital;
	}

	/**
	 * @return mixed
	 */
	public function getIbanPapel() {
		return $this->ibanPapel;
	}

	/**
	 * @param mixed $ibanPapel
	 */
	public function setIbanPapel( $ibanPapel ): void {
		$this->ibanPapel = $ibanPapel;
	}

	/**
	 * @return mixed
	 */
	public function getBic() {
		return $this->bic;
	}

	/**
	 * @param mixed $bic
	 */
	public function setBic( $bic ): void {
		$this->bic = $bic;
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
	public function getDiaPago() {
		return $this->diaPago;
	}

	/**
	 * @param mixed $diaPago
	 */
	public function setDiaPago( $diaPago ): void {
		$this->diaPago = $diaPago;
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

	public function cuentaEmpresa(){
		return $this->getIbanDigital() .' - '. $this->getEmpresa()->getEmpresa();
	}

}
