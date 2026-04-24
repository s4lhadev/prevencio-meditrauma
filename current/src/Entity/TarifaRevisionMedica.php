<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TarifaRevisionMedicaRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class TarifaRevisionMedica
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Empresa", inversedBy="TarifaPrl")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $empresa;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Concepto", inversedBy="TarifaPrl")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $concepto;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $anulado = false;

    /**
     * @ORM\Column(type="float", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $importe;

    /**
     * @ORM\Column(type="float", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $iva;

    public function getId(): ?int
    {
        return $this->id;
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
    public function getIva()
    {
        return $this->iva;
    }

    /**
     * @param mixed $iva
     */
    public function setIva($iva): void
    {
        $this->iva = $iva;
    }
}
