<?php

namespace App\Entity;

use App\Repository\CitacionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CitacionRepository::class)
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class Citacion
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Agenda", inversedBy="Citacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $agenda;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\EstadoCitacion", inversedBy="Citacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $estado;

	/**
	 * @ORM\Column(type="datetime", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $fechainicio;

	/**
	 * @ORM\Column(type="datetime", length=255, nullable=true)
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $fechafin;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Empresa", inversedBy="Citacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $empresa;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Trabajador", inversedBy="Citacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $trabajador;

    /**
     * @ORM\Column(type="time", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $horainicio;

    /**
     * @ORM\Column(type="time", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $horafin;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $comentarios;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $pruebasComplementarias;

	/**
     * @ORM\Column(type="boolean", nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $anulado = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="Citacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $usuarioCrea;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\UsuarioTecnico", inversedBy="Citacion")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $tecnico;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getAgenda()
    {
        return $this->agenda;
    }

    /**
     * @param mixed $agenda
     */
    public function setAgenda($agenda): void
    {
        $this->agenda = $agenda;
    }

    /**
     * @return mixed
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * @param mixed $estado
     */
    public function setEstado($estado): void
    {
        $this->estado = $estado;
    }

	/**
	 * @return mixed
	 */
	public function getFechainicio() {
		return $this->fechainicio;
	}

	/**
	 * @param mixed $fechainicio
	 */
	public function setFechainicio( $fechainicio ): void {
		$this->fechainicio = $fechainicio;
	}

	/**
	 * @return mixed
	 */
	public function getFechafin() {
		return $this->fechafin;
	}

	/**
	 * @param mixed $fechafin
	 */
	public function setFechafin( $fechafin ): void {
		$this->fechafin = $fechafin;
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
    public function getTrabajador()
    {
        return $this->trabajador;
    }

    /**
     * @param mixed $trabajador
     */
    public function setTrabajador($trabajador): void
    {
        $this->trabajador = $trabajador;
    }

    /**
     * @return mixed
     */
    public function getHorainicio()
    {
        return $this->horainicio;
    }

    /**
     * @param mixed $horainicio
     */
    public function setHorainicio($horainicio): void
    {
        $this->horainicio = $horainicio;
    }

    /**
     * @return mixed
     */
    public function getHorafin()
    {
        return $this->horafin;
    }

    /**
     * @param mixed $horafin
     */
    public function setHorafin($horafin): void
    {
        $this->horafin = $horafin;
    }

    /**
     * @return mixed
     */
    public function getComentarios()
    {
        return $this->comentarios;
    }

    /**
     * @param mixed $comentarios
     */
    public function setComentarios($comentarios): void
    {
        $this->comentarios = $comentarios;
    }

    /**
     * @return mixed
     */
    public function getPruebasComplementarias()
    {
        return $this->pruebasComplementarias;
    }

    /**
     * @param mixed $pruebasComplementarias
     */
    public function setPruebasComplementarias($pruebasComplementarias): void
    {
        $this->pruebasComplementarias = $pruebasComplementarias;
    }

    public function formatoRevision()
    {
        if(!is_null($this->getAgenda())){
            return (string) $this->getFechainicio()->format('d-m-Y'). ' - ' . $this->getAgenda()->getDescripcion();
        }else{
            return (string) $this->getFechainicio()->format('d-m-Y'). ' - ';
        }
    }

    /**
     * @return mixed
     */
    public function getUsuarioCrea()
    {
        return $this->usuarioCrea;
    }

    /**
     * @param mixed $usuarioCrea
     */
    public function setUsuarioCrea($usuarioCrea): void
    {
        $this->usuarioCrea = $usuarioCrea;
    }

    /**
     * @return mixed
     */
    public function getTecnico()
    {
        return $this->tecnico;
    }

    /**
     * @param mixed $tecnico
     */
    public function setTecnico($tecnico): void
    {
        $this->tecnico = $tecnico;
    }

}
