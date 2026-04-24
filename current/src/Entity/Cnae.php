<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CnaeRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class Cnae
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
    private $padre;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $cnae;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $descripcion;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $anulado = false;

	/**
	 * @return mixed
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param mixed $id
	 */
	public function setId( $id ): void {
		$this->id = $id;
	}

	/**
	 * @return mixed
	 */
	public function getPadre() {
		return $this->padre;
	}

	/**
	 * @param mixed $padre
	 */
	public function setPadre( $padre ): void {
		$this->padre = $padre;
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
	public function getDescripcion() {
		return $this->descripcion;
	}

	/**
	 * @param mixed $descripcion
	 */
	public function setDescripcion( $descripcion ): void {
		$this->descripcion = $descripcion;
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
