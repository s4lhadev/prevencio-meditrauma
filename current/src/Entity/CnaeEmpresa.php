<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CnaeEmpresaRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class CnaeEmpresa
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $principal;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Empresa", inversedBy="CnaeEmpresa")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $empresa;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Cnae", inversedBy="CnaeEmpresa")
	 * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $cnae;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $anulado = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrincipal(): ?bool
    {
        return $this->principal;
    }

    public function setPrincipal(?bool $principal): self
    {
        $this->principal = $principal;

        return $this;
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
	public function getAnulado() {
		return $this->anulado;
	}

	/**
	 * @param mixed $anulado
	 */
	public function setAnulado( $anulado ): void {
		$this->anulado = $anulado;
	}
}
