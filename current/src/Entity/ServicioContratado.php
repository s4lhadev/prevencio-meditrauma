<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ServicioContratadoRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class ServicioContratado
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
    private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Contrato", inversedBy="ServicioContratado")
	 * @ORM\JoinColumn(nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $contrato;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\ListaServiciosContratados", inversedBy="ServicioContratado")
	 * @ORM\JoinColumn(nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $servicio;

	/**
	 * @ORM\Column(type="float", length=2000, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $precio;

    /**
     * @ORM\Column(type="float", length=2000, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $precioRenovacion;

	/**
	 * @ORM\Column(type="boolean", length=2000, nullable=true, options={"default":0})
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
	public function getContrato() {
		return $this->contrato;
	}

	/**
	 * @param mixed $contrato
	 */
	public function setContrato( $contrato ): void {
		$this->contrato = $contrato;
	}

	/**
	 * @return mixed
	 */
	public function getServicio() {
		return $this->servicio;
	}

	/**
	 * @param mixed $servicio
	 */
	public function setServicio( $servicio ): void {
		$this->servicio = $servicio;
	}

	/**
	 * @return mixed
	 */
	public function getPrecio() {
		return $this->precio;
	}

	/**
	 * @param mixed $precio
	 */
	public function setPrecio( $precio ): void {
		$this->precio = $precio;
	}

    /**
     * @return mixed
     */
    public function getPrecioRenovacion()
    {
        return $this->precioRenovacion;
    }

    /**
     * @param mixed $precioRenovacion
     */
    public function setPrecioRenovacion($precioRenovacion): void
    {
        $this->precioRenovacion = $precioRenovacion;
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
