<?php

namespace App\Entity;

use App\Repository\RemesaConfigRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RemesaConfigRepository::class)
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class RemesaConfig
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
    private $ordenante;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $ccc;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $bic;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getOrdenante()
    {
        return $this->ordenante;
    }

    /**
     * @param mixed $ordenante
     */
    public function setOrdenante($ordenante): void
    {
        $this->ordenante = $ordenante;
    }

    /**
     * @return mixed
     */
    public function getCcc()
    {
        return $this->ccc;
    }

    /**
     * @param mixed $ccc
     */
    public function setCcc($ccc): void
    {
        $this->ccc = $ccc;
    }

    /**
     * @return mixed
     */
    public function getBic()
    {
        return $this->bic;
    }

    /**
     * @param mixed $bic
     */
    public function setBic($bic): void
    {
        $this->bic = $bic;
    }
}
