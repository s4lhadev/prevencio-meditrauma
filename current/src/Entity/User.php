<?php
// src/Entity/User.php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use FOS\UserBundle\Model\GroupInterface;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $rol_id;
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\PrivilegioRoles", inversedBy="User")
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $rol;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $locale;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true)
     */
    private $credentialsExpired;

    /**
     * @ORM\Column(type="datetime", length=255, nullable=true)
     */
    private $passwordChangedAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $passwordMail;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $hostMail;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $puertoMail;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $encriptacionMail;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mail;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $userMail;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }

	/**
	 * @return mixed
	 */
	public function getRol() {
		return $this->rol;
	}

	/**
	 * @param mixed $rol
	 */
	public function setRol( $rol ): void {
		$this->rol = $rol;
	}
    public function getRolId() {
        return $this->rol_id;
    }
    //Peticio 28/07/2023
    /**
     * @param mixed $rol
     */
    public function setRolId( $rol_id ): void {
        $this->rol_id = $rol_id;
    }
	/**
	 * @return mixed
	 */
	public function getLocale() {
		return $this->locale;
	}

	/**
	 * @param mixed $locale
	 */
	public function setLocale( $locale ): void {
		$this->locale = $locale;
	}

    /**
     * @return mixed
     */
    public function getCredentialsExpired()
    {
        return $this->credentialsExpired;
    }

    /**
     * @param mixed $credentialsExpired
     */
    public function setCredentialsExpired($credentialsExpired): void
    {
        $this->credentialsExpired = $credentialsExpired;
    }

    /**
     * @return mixed
     */
    public function getPasswordChangedAt()
    {
        return $this->passwordChangedAt;
    }

    /**
     * @param mixed $passwordChangedAt
     */
    public function setPasswordChangedAt($passwordChangedAt): void
    {
        $this->passwordChangedAt = $passwordChangedAt;
    }

    /**
     * @return mixed
     */
    public function getPasswordMail()
    {
        return $this->passwordMail;
    }

    /**
     * @param mixed $passwordMail
     */
    public function setPasswordMail($passwordMail): void
    {
        $this->passwordMail = $passwordMail;
    }

    /**
     * @return mixed
     */
    public function getEncriptacionMail()
    {
        return $this->encriptacionMail;
    }

    /**
     * @param mixed $encriptacionMail
     */
    public function setEncriptacionMail($encriptacionMail): void
    {
        $this->encriptacionMail = $encriptacionMail;
    }

    /**
     * @return mixed
     */
    public function getHostMail()
    {
        return $this->hostMail;
    }

    /**
     * @param mixed $hostMail
     */
    public function setHostMail($hostMail): void
    {
        $this->hostMail = $hostMail;
    }

    /**
     * @return mixed
     */
    public function getPuertoMail()
    {
        return $this->puertoMail;
    }

    /**
     * @param mixed $puertoMail
     */
    public function setPuertoMail($puertoMail): void
    {
        $this->puertoMail = $puertoMail;
    }

    /**
     * @return mixed
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * @param mixed $mail
     */
    public function setMail($mail): void
    {
        $this->mail = $mail;
    }

    /**
     * @return mixed
     */
    public function getUserMail()
    {
        return $this->userMail;
    }

    /**
     * @param mixed $userMail
     */
    public function setUserMail($userMail): void
    {
        $this->userMail = $userMail;
    }
}