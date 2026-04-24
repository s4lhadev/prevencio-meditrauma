<?php

namespace App\Entity;

use App\Repository\PreventivaEmpresaCausaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PreventivaEmpresaCausaRepository::class)
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class PreventivaEmpresaCausa
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PreventivaEmpresa", inversedBy="PreventivaEmpresaCausa")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $preventivaEmpresa;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Causa", inversedBy="PreventivaEmpresaCausa")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $causa;

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
    public function getPreventivaEmpresa()
    {
        return $this->preventivaEmpresa;
    }

    /**
     * @param mixed $preventivaEmpresa
     */
    public function setPreventivaEmpresa($preventivaEmpresa): void
    {
        $this->preventivaEmpresa = $preventivaEmpresa;
    }

    /**
     * @return mixed
     */
    public function getCausa()
    {
        return $this->causa;
    }

    /**
     * @param mixed $causa
     */
    public function setCausa($causa): void
    {
        $this->causa = $causa;
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
}
