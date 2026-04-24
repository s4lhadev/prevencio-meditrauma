<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TecnicoRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class Tecnico
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
    private $nombre;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
    private $alias;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
    private $titulacion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
    private $especialidad;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
    private $dni;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
    private $porcComision;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $anulado = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Doctor", inversedBy="Tecnico")
     * @ORM\JoinColumn(nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $medico;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $correo;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $tecnico = false;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $vigilanciaSalud = false;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $gestorAdministrativo = false;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $due = false;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $agente = false;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $administracion = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(?string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function setAlias(?string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    public function getTitulacion(): ?string
    {
        return $this->titulacion;
    }

    public function setTitulacion(?string $titulacion): self
    {
        $this->titulacion = $titulacion;

        return $this;
    }

    public function getEspecialidad(): ?string
    {
        return $this->especialidad;
    }

    public function setEspecialidad(?string $especialidad): self
    {
        $this->especialidad = $especialidad;

        return $this;
    }

    public function getDni(): ?string
    {
        return $this->dni;
    }

    public function setDni(?string $dni): self
    {
        $this->dni = $dni;

        return $this;
    }

    public function getPorcComision(): ?string
    {
        return $this->porcComision;
    }

    public function setPorcComision(?string $porcComision): self
    {
        $this->porcComision = $porcComision;

        return $this;
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

    /**
     * @return mixed
     */
    public function getMedico()
    {
        return $this->medico;
    }

    /**
     * @param mixed $medico
     */
    public function setMedico($medico): void
    {
        $this->medico = $medico;
    }

    /**
     * @return mixed
     */
    public function getCorreo()
    {
        return $this->correo;
    }

    /**
     * @param mixed $correo
     */
    public function setCorreo($correo): void
    {
        $this->correo = $correo;
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

    /**
     * @return mixed
     */
    public function getVigilanciaSalud()
    {
        return $this->vigilanciaSalud;
    }

    /**
     * @param mixed $vigilanciaSalud
     */
    public function setVigilanciaSalud($vigilanciaSalud): void
    {
        $this->vigilanciaSalud = $vigilanciaSalud;
    }

    /**
     * @return mixed
     */
    public function getGestorAdministrativo()
    {
        return $this->gestorAdministrativo;
    }

    /**
     * @param mixed $gestorAdministrativo
     */
    public function setGestorAdministrativo($gestorAdministrativo): void
    {
        $this->gestorAdministrativo = $gestorAdministrativo;
    }

    /**
     * @return mixed
     */
    public function getDue()
    {
        return $this->due;
    }

    /**
     * @param mixed $due
     */
    public function setDue($due): void
    {
        $this->due = $due;
    }

    /**
     * @return mixed
     */
    public function getAgente()
    {
        return $this->agente;
    }

    /**
     * @param mixed $agente
     */
    public function setAgente($agente): void
    {
        $this->agente = $agente;
    }

    /**
     * @return mixed
     */
    public function getAdministracion()
    {
        return $this->administracion;
    }

    /**
     * @param mixed $administracion
     */
    public function setAdministracion($administracion): void
    {
        $this->administracion = $administracion;
    }
}
