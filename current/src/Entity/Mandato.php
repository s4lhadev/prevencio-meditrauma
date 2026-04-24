<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MandatoRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class Mandato
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
    private $id;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $referencia;

	/**
	 * @ORM\Column(type="datetime", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $firma;

	/**
	 * @ORM\Column(type="integer", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $tipoPago;

	/**
	 * @ORM\Column(type="integer", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $tipoMandato;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $anulado = false;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $documento;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $firmado = false;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Empresa", inversedBy="Mandato")
	 * @ORM\JoinColumn(nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $empresa;

    public function getId(): ?int
    {
        return $this->id;
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
	public function getFirma() {
		return $this->firma;
	}

	/**
	 * @param mixed $firma
	 */
	public function setFirma( $firma ): void {
		$this->firma = $firma;
	}

	/**
	 * @return mixed
	 */
	public function getTipoPago() {
		return $this->tipoPago;
	}

	/**
	 * @param mixed $tipoPago
	 */
	public function setTipoPago( $tipoPago ): void {
		$this->tipoPago = $tipoPago;
	}

	/**
	 * @return mixed
	 */
	public function getTipoMandato() {
		return $this->tipoMandato;
	}

	/**
	 * @param mixed $tipoMandato
	 */
	public function setTipoMandato( $tipoMandato ): void {
		$this->tipoMandato = $tipoMandato;
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
	public function getDocumento() {
		return $this->documento;
	}

	/**
	 * @param mixed $documento
	 */
	public function setDocumento( $documento ): void {
		$this->documento = $documento;
	}

	/**
	 * @return mixed
	 */
	public function getFirmado() {
		return $this->firmado;
	}

	/**
	 * @param mixed $firmado
	 */
	public function setFirmado( $firmado ): void {
		$this->firmado = $firmado;
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

}
