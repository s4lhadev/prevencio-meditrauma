<?php

namespace App\Entity;

use App\Repository\BalanceEconomicoEntradaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BalanceEconomicoEntradaRepository::class)
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class BalanceEconomicoEntrada
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $concepto;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Empresa", inversedBy="BalanceEconomicoEntrada")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $empresa;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Facturacion", inversedBy="BalanceEconomicoEntrada")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $facturacion;

    /**
     * @ORM\Column(type="datetime", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $fecha;

    /**
     * @ORM\Column(type="float", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $importe;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $tipo;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\FormaPago", inversedBy="BalanceEconomicoEntrada")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $formaPago;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $observaciones;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $anulado = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $pagoConfirmado = false;

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return mixed
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * @param mixed $tipo
     */
    public function setTipo($tipo): void
    {
        $this->tipo = $tipo;
    }

    /**
     * @return mixed
     */
    public function getFormaPago()
    {
        return $this->formaPago;
    }

    /**
     * @param mixed $formaPago
     */
    public function setFormaPago($formaPago): void
    {
        $this->formaPago = $formaPago;
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
    public function getPagoConfirmado()
    {
        return $this->pagoConfirmado;
    }

    /**
     * @param mixed $pagoConfirmado
     */
    public function setPagoConfirmado($pagoConfirmado): void
    {
        $this->pagoConfirmado = $pagoConfirmado;
    }
}
