<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EpiPreventivaEmpresaRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class EpiPreventivaEmpresa
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\AccionPreventivaEmpresaRiesgoCausa", inversedBy="EpiPreventivaEmpresa")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $preventivaEmpresa;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Epi", inversedBy="EpiPreventivaEmpresa")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $epi;

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
    public function getEpi()
    {
        return $this->epi;
    }

    /**
     * @param mixed $epi
     */
    public function setEpi($epi): void
    {
        $this->epi = $epi;
    }

    /**
     * @return bool
     */
    public function getAnulado(): bool
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
