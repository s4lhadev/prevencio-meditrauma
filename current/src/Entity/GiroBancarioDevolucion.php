<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GiroBancarioDevolucionRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class GiroBancarioDevolucion
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
    private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Facturacion", inversedBy="GiroBancarioDevolucion")
	 * @ORM\JoinColumn(nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $facturacion;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GiroBancario", inversedBy="GiroBancarioDevolucion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $giroBancario;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $concepto;

	/**
	 * @ORM\Column(type="datetime", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $fecha;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $anulado = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $reciboGenerado = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $observaciones;

    /**
     * @ORM\Column(type="float", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $importe;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getFacturacion()
    {
        return $this->facturacion;
    }

    /**
     * @param mixed $facturacion
     */
    public function setFacturacion($facturacion): void
    {
        $this->facturacion = $facturacion;
    }

    /**
     * @return mixed
     */
    public function getGiroBancario()
    {
        return $this->giroBancario;
    }

    /**
     * @param mixed $giroBancario
     */
    public function setGiroBancario($giroBancario): void
    {
        $this->giroBancario = $giroBancario;
    }

    /**
     * @return mixed
     */
    public function getConcepto()
    {
        return $this->concepto;
    }

    /**
     * @param mixed $concepto
     */
    public function setConcepto($concepto): void
    {
        $this->concepto = $concepto;
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
    public function getReciboGenerado()
    {
        return $this->reciboGenerado;
    }

    /**
     * @param mixed $reciboGenerado
     */
    public function setReciboGenerado($reciboGenerado): void
    {
        $this->reciboGenerado = $reciboGenerado;
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
    public function getImporte()
    {
        return $this->importe;
    }

    /**
     * @param mixed $importe
     */
    public function setImporte($importe): void
    {
        $this->importe = $importe;
    }
}
