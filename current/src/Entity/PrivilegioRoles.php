<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PrivilegioRolesRepository")
 * @Gedmo\Mapping\Annotation\Loggable()
 */
class PrivilegioRoles
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
    private $descripcion;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $empresaSn = false;

    /**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $importeBalance = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $addEmpresaSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $editEmpresaSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $deleteEmpresaSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $viewEmpresaSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $exportEmpresaSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $centroTrabajoSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $addCentroTrabajoSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $editCentroTrabajoSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $deleteCentroTrabajoSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $viewCentroTrabajoSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $exportCentroTrabajoSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $trabajadorSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $addTrabajadorSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $editTrabajadorSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $deleteTrabajadorSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $viewTrabajadorSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $exportTrabajadorSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $importTrabajadorSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $contratoSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $addContratoSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $editContratoSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $deleteContratoSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $viewContratoSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $printContratoSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $exportContratoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $renovarContratoMultipleSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $sendContratoSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $renovacionSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $addRenovacionSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $editRenovacionSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $deleteRenovacionSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $viewRenovacionSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $renovarRenovacionSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $exportRenovacionSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $pagoPendienteSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $addPagoPendienteSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $editPagoPendienteSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $deletePagoPendienteSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $viewPagoPendienteSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $exportPagoPendienteSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $facturarPagoPendienteSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $facturacionSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $addFacturacionSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $editFacturacionSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $deleteFacturacionSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $viewFacturacionSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $exportFacturacionSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $printFacturacionSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $sendFacturacionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $addVencimientoFacturacionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $deleteVencimientoFacturacionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $confirmarVencimientoFacturacionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $facturarContratoMultipleSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $datosBancariosSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $addDatosBancariosSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $editDatosBancariosSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $deleteDatosBancariosSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $viewDatosBancariosSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $exportDatosBancariosSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $balanceEconomicoSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $viewBalanceEconomicoSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $addGiroBalanceEconomicoSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $editGiroBalanceEconomicoSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $deleteGiroBalanceEconomicoSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $exportGiroBalanceEconomicoSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $addTransferenciaBalanceEconomicoSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $deleteTransferenciaBalanceEconomicoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $addEntradaBalanceEconomicoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $editEntradaBalanceEconomicoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $deleteEntradaBalanceEconomicoSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $remesaSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $addRemesaSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $deleteRemesaSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $downRemesaSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $exportRemesaSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $buscadorSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $addBuscadorSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $editBuscadorSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $copyBuscadorSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $executeBuscadorSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $deleteBuscadorSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $exportBuscadorSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $gdocSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $addGdocSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $editGdocSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $deleteGdocSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $addGdocFileSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $deleteGdocFileSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $downGdocFileSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $openGdocFileSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
    private $monitorAuditoriaSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
	 */
    private $exportMonitorAuditoriaSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $addNotificacionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $printNotificacionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $deleteNotificacionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $exportNotificacionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $sendNotificacionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $addCertificacionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $printCertificacionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $deleteCertificacionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $exportCertificacionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $addTarifaSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $editTarifaSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $deleteTarifaSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $exportTarifaSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $addModelo347Sn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $printModelo347Sn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $deleteModelo347Sn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $exportModelo347Sn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $sendModelo347Sn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $empresaTecnicoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $viewEmpresaTecnicoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $addEmpresaTecnicoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $editEmpresaTecnicoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $deleteEmpresaTecnicoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $exportEmpresaTecnicoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $evaluacionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $addEvaluacionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $editEvaluacionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $deleteEvaluacionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $exportEvaluacionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $finalizarEvaluacionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $printEvaluacionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $grupoEmpresaSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $addGrupoEmpresaSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $editGrupoEmpresaSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $deleteGrupoEmpresaSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $exportGrupoEmpresaSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $trabajadorTecnicoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $addTrabajadorTecnicoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $editTrabajadorTecnicoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $deleteTrabajadorTecnicoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $exportTrabajadorTecnicoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $puestoTrabajoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $addPuestoTrabajoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $editPuestoTrabajoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $deletePuestoTrabajoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $exportPuestoTrabajoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $addMaquinaEmpresaSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $editMaquinaEmpresaSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $deleteMaquinaEmpresaSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $viewMaquinaEmpresaSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $exportMaquinaEmpresaSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $addPlanPrevencionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $deletePlanPrevencionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $printPlanPrevencionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $exportPlanPrevencionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $addProtocoloAcosoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $deleteProtocoloAcosoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $printProtocoloAcosoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $exportProtocoloAcosoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $trabajadorMedicoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $editTrabajadorMedicoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $exportTrabajadorMedicoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $documentacionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $addDocumentacionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $editDocumentacionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $deleteDocumentacionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $exportDocumentacionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $historialLaboralSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $addHistorialLaboralSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $editHistorialLaboralSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $deleteHistorialLaboralSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $exportHistorialLaboralSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $revisionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $addRevisionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $editRevisionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $deleteRevisionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $exportRevisionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $sendCuestionarioRevisionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $printAptitudRevisionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $printResumenRevisionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $facturarRevisionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $investigacionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $addInvestigacionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $editInvestigacionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $deleteInvestigacionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $exportInvestigacionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $enfermedadProfesionalSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $addEnfermedadProfesionalSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $editEnfermedadProfesionalSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $deleteEnfermedadProfesionalSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $exportEnfermedadProfesionalSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $firmaTecnicoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $citacionSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $addCitacionSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $editCitacionSn = false;

	/**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $deleteCitacionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $empresaMedicoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $viewEmpresaMedicoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $addEmpresaMedicoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $editEmpresaMedicoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $deleteEmpresaMedicoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $exportEmpresaMedicoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $mantenimientoReconocimientosSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $mantenimientoSerieRespuestaSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $mantenimientoPreguntasSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $mantenimientoConsejosMedicosSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $mantenimientoRespuestasSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $mantenimientoFormulasSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $mantenimientoCuestionariosSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $mantenimientoSubPreguntasSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $puestoTrabajoProtocoloCuestionarioSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $puestoTrabajoProtocoloSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $protocoloSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $intranetSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $logEnvioMailSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $agendaTecnicoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $addAgendaTecnicoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $editAgendaTecnicoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $deleteAgendaTecnicoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $firmaMedicoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $enviarAptitudRevisionSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $enviarCorreoMasivoSn = false;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $verHuecosFactura = false;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default":0})
     * @Gedmo\Mapping\Annotation\Versioned()
     */
    private $avisoVencimientoAptitud = false;

    /**
	 * @ORM\Column(type="boolean", length=255, nullable=true, options={"default":0})
	 * @Gedmo\Mapping\Annotation\Versioned()
	 */
	private $administracionSn = false;

	public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * @param mixed $descripcion
     */
    public function setDescripcion($descripcion): void
    {
        $this->descripcion = $descripcion;
    }

     /**
     * @return mixed
     */
    public function getImporteBalance()
    {
        return $this->importeBalance;
    }

    /**
     * @param mixed $importeBalance
     */
    public function setImporteBalance($importeBalance): void
    {
        $this->importeBalance = $importeBalance;
    }

	/**
	 * @return mixed
	 */
	public function getEmpresaSn() {
		return $this->empresaSn;
	}

	/**
	 * @param mixed $empresaSn
	 */
	public function setEmpresaSn( $empresaSn ): void {
		$this->empresaSn = $empresaSn;
	}

	/**
	 * @return mixed
	 */
	public function getAddEmpresaSn() {
		return $this->addEmpresaSn;
	}

	/**
	 * @param mixed $addEmpresaSn
	 */
	public function setAddEmpresaSn( $addEmpresaSn ): void {
		$this->addEmpresaSn = $addEmpresaSn;
	}
    
	/**
	 * @return mixed
	 */
	public function getEditEmpresaSn() {
		return $this->editEmpresaSn;
	}

	/**
	 * @param mixed $editEmpresaSn
	 */
	public function setEditEmpresaSn( $editEmpresaSn ): void {
		$this->editEmpresaSn = $editEmpresaSn;
	}

	/**
	 * @return mixed
	 */
	public function getDeleteEmpresaSn() {
		return $this->deleteEmpresaSn;
	}

	/**
	 * @param mixed $deleteEmpresaSn
	 */
	public function setDeleteEmpresaSn( $deleteEmpresaSn ): void {
		$this->deleteEmpresaSn = $deleteEmpresaSn;
	}

	/**
	 * @return mixed
	 */
	public function getViewEmpresaSn() {
		return $this->viewEmpresaSn;
	}

	/**
	 * @param mixed $viewEmpresaSn
	 */
	public function setViewEmpresaSn( $viewEmpresaSn ): void {
		$this->viewEmpresaSn = $viewEmpresaSn;
	}

	/**
	 * @return mixed
	 */
	public function getExportEmpresaSn() {
		return $this->exportEmpresaSn;
	}

	/**
	 * @param mixed $exportEmpresaSn
	 */
	public function setExportEmpresaSn( $exportEmpresaSn ): void {
		$this->exportEmpresaSn = $exportEmpresaSn;
	}

	/**
	 * @return mixed
	 */
	public function getCentroTrabajoSn() {
		return $this->centroTrabajoSn;
	}

	/**
	 * @param mixed $centroTrabajoSn
	 */
	public function setCentroTrabajoSn( $centroTrabajoSn ): void {
		$this->centroTrabajoSn = $centroTrabajoSn;
	}

	/**
	 * @return mixed
	 */
	public function getAddCentroTrabajoSn() {
		return $this->addCentroTrabajoSn;
	}

	/**
	 * @param mixed $addCentroTrabajoSn
	 */
	public function setAddCentroTrabajoSn( $addCentroTrabajoSn ): void {
		$this->addCentroTrabajoSn = $addCentroTrabajoSn;
	}

	/**
	 * @return mixed
	 */
	public function getEditCentroTrabajoSn() {
		return $this->editCentroTrabajoSn;
	}

	/**
	 * @param mixed $editCentroTrabajoSn
	 */
	public function setEditCentroTrabajoSn( $editCentroTrabajoSn ): void {
		$this->editCentroTrabajoSn = $editCentroTrabajoSn;
	}

	/**
	 * @return mixed
	 */
	public function getDeleteCentroTrabajoSn() {
		return $this->deleteCentroTrabajoSn;
	}

	/**
	 * @param mixed $deleteCentroTrabajoSn
	 */
	public function setDeleteCentroTrabajoSn( $deleteCentroTrabajoSn ): void {
		$this->deleteCentroTrabajoSn = $deleteCentroTrabajoSn;
	}

	/**
	 * @return mixed
	 */
	public function getViewCentroTrabajoSn() {
		return $this->viewCentroTrabajoSn;
	}

	/**
	 * @param mixed $viewCentroTrabajoSn
	 */
	public function setViewCentroTrabajoSn( $viewCentroTrabajoSn ): void {
		$this->viewCentroTrabajoSn = $viewCentroTrabajoSn;
	}

	/**
	 * @return mixed
	 */
	public function getExportCentroTrabajoSn() {
		return $this->exportCentroTrabajoSn;
	}

	/**
	 * @param mixed $exportCentroTrabajoSn
	 */
	public function setExportCentroTrabajoSn( $exportCentroTrabajoSn ): void {
		$this->exportCentroTrabajoSn = $exportCentroTrabajoSn;
	}

	/**
	 * @return mixed
	 */
	public function getTrabajadorSn() {
		return $this->trabajadorSn;
	}

	/**
	 * @param mixed $trabajadorSn
	 */
	public function setTrabajadorSn( $trabajadorSn ): void {
		$this->trabajadorSn = $trabajadorSn;
	}

	/**
	 * @return mixed
	 */
	public function getAddTrabajadorSn() {
		return $this->addTrabajadorSn;
	}

	/**
	 * @param mixed $addTrabajadorSn
	 */
	public function setAddTrabajadorSn( $addTrabajadorSn ): void {
		$this->addTrabajadorSn = $addTrabajadorSn;
	}

	/**
	 * @return mixed
	 */
	public function getEditTrabajadorSn() {
		return $this->editTrabajadorSn;
	}

	/**
	 * @param mixed $editTrabajadorSn
	 */
	public function setEditTrabajadorSn( $editTrabajadorSn ): void {
		$this->editTrabajadorSn = $editTrabajadorSn;
	}

	/**
	 * @return mixed
	 */
	public function getDeleteTrabajadorSn() {
		return $this->deleteTrabajadorSn;
	}

	/**
	 * @param mixed $deleteTrabajadorSn
	 */
	public function setDeleteTrabajadorSn( $deleteTrabajadorSn ): void {
		$this->deleteTrabajadorSn = $deleteTrabajadorSn;
	}

	/**
	 * @return mixed
	 */
	public function getViewTrabajadorSn() {
		return $this->viewTrabajadorSn;
	}

	/**
	 * @param mixed $viewTrabajadorSn
	 */
	public function setViewTrabajadorSn( $viewTrabajadorSn ): void {
		$this->viewTrabajadorSn = $viewTrabajadorSn;
	}

	/**
	 * @return mixed
	 */
	public function getExportTrabajadorSn() {
		return $this->exportTrabajadorSn;
	}

	/**
	 * @param mixed $exportTrabajadorSn
	 */
	public function setExportTrabajadorSn( $exportTrabajadorSn ): void {
		$this->exportTrabajadorSn = $exportTrabajadorSn;
	}

	/**
	 * @return mixed
	 */
	public function getImportTrabajadorSn() {
		return $this->importTrabajadorSn;
	}

	/**
	 * @param mixed $importTrabajadorSn
	 */
	public function setImportTrabajadorSn( $importTrabajadorSn ): void {
		$this->importTrabajadorSn = $importTrabajadorSn;
	}

	/**
	 * @return mixed
	 */
	public function getContratoSn() {
		return $this->contratoSn;
	}

	/**
	 * @param mixed $contratoSn
	 */
	public function setContratoSn( $contratoSn ): void {
		$this->contratoSn = $contratoSn;
	}

	/**
	 * @return mixed
	 */
	public function getAddContratoSn() {
		return $this->addContratoSn;
	}

	/**
	 * @param mixed $addContratoSn
	 */
	public function setAddContratoSn( $addContratoSn ): void {
		$this->addContratoSn = $addContratoSn;
	}

	/**
	 * @return mixed
	 */
	public function getEditContratoSn() {
		return $this->editContratoSn;
	}

	/**
	 * @param mixed $editContratoSn
	 */
	public function setEditContratoSn( $editContratoSn ): void {
		$this->editContratoSn = $editContratoSn;
	}

	/**
	 * @return mixed
	 */
	public function getDeleteContratoSn() {
		return $this->deleteContratoSn;
	}

	/**
	 * @param mixed $deleteContratoSn
	 */
	public function setDeleteContratoSn( $deleteContratoSn ): void {
		$this->deleteContratoSn = $deleteContratoSn;
	}

	/**
	 * @return mixed
	 */
	public function getViewContratoSn() {
		return $this->viewContratoSn;
	}

	/**
	 * @param mixed $viewContratoSn
	 */
	public function setViewContratoSn( $viewContratoSn ): void {
		$this->viewContratoSn = $viewContratoSn;
	}

	/**
	 * @return mixed
	 */
	public function getPrintContratoSn() {
		return $this->printContratoSn;
	}

	/**
	 * @param mixed $printContratoSn
	 */
	public function setPrintContratoSn( $printContratoSn ): void {
		$this->printContratoSn = $printContratoSn;
	}

	/**
	 * @return mixed
	 */
	public function getExportContratoSn() {
		return $this->exportContratoSn;
	}

	/**
	 * @param mixed $exportContratoSn
	 */
	public function setExportContratoSn( $exportContratoSn ): void {
		$this->exportContratoSn = $exportContratoSn;
	}

	/**
	 * @return mixed
	 */
	public function getRenovacionSn() {
		return $this->renovacionSn;
	}

	/**
	 * @param mixed $renovacionSn
	 */
	public function setRenovacionSn( $renovacionSn ): void {
		$this->renovacionSn = $renovacionSn;
	}

	/**
	 * @return mixed
	 */
	public function getAddRenovacionSn() {
		return $this->addRenovacionSn;
	}

	/**
	 * @param mixed $addRenovacionSn
	 */
	public function setAddRenovacionSn( $addRenovacionSn ): void {
		$this->addRenovacionSn = $addRenovacionSn;
	}

	/**
	 * @return mixed
	 */
	public function getEditRenovacionSn() {
		return $this->editRenovacionSn;
	}

	/**
	 * @param mixed $editRenovacionSn
	 */
	public function setEditRenovacionSn( $editRenovacionSn ): void {
		$this->editRenovacionSn = $editRenovacionSn;
	}

	/**
	 * @return mixed
	 */
	public function getDeleteRenovacionSn() {
		return $this->deleteRenovacionSn;
	}

	/**
	 * @param mixed $deleteRenovacionSn
	 */
	public function setDeleteRenovacionSn( $deleteRenovacionSn ): void {
		$this->deleteRenovacionSn = $deleteRenovacionSn;
	}

	/**
	 * @return mixed
	 */
	public function getViewRenovacionSn() {
		return $this->viewRenovacionSn;
	}

	/**
	 * @param mixed $viewRenovacionSn
	 */
	public function setViewRenovacionSn( $viewRenovacionSn ): void {
		$this->viewRenovacionSn = $viewRenovacionSn;
	}

	/**
	 * @return mixed
	 */
	public function getRenovarRenovacionSn() {
		return $this->renovarRenovacionSn;
	}

	/**
	 * @param mixed $renovarRenovacionSn
	 */
	public function setRenovarRenovacionSn( $renovarRenovacionSn ): void {
		$this->renovarRenovacionSn = $renovarRenovacionSn;
	}

	/**
	 * @return mixed
	 */
	public function getExportRenovacionSn() {
		return $this->exportRenovacionSn;
	}

	/**
	 * @param mixed $exportRenovacionSn
	 */
	public function setExportRenovacionSn( $exportRenovacionSn ): void {
		$this->exportRenovacionSn = $exportRenovacionSn;
	}

	/**
	 * @return mixed
	 */
	public function getPagoPendienteSn() {
		return $this->pagoPendienteSn;
	}

	/**
	 * @param mixed $pagoPendienteSn
	 */
	public function setPagoPendienteSn( $pagoPendienteSn ): void {
		$this->pagoPendienteSn = $pagoPendienteSn;
	}

	/**
	 * @return mixed
	 */
	public function getAddPagoPendienteSn() {
		return $this->addPagoPendienteSn;
	}

	/**
	 * @param mixed $addPagoPendienteSn
	 */
	public function setAddPagoPendienteSn( $addPagoPendienteSn ): void {
		$this->addPagoPendienteSn = $addPagoPendienteSn;
	}

	/**
	 * @return mixed
	 */
	public function getEditPagoPendienteSn() {
		return $this->editPagoPendienteSn;
	}

	/**
	 * @param mixed $editPagoPendienteSn
	 */
	public function setEditPagoPendienteSn( $editPagoPendienteSn ): void {
		$this->editPagoPendienteSn = $editPagoPendienteSn;
	}

	/**
	 * @return mixed
	 */
	public function getDeletePagoPendienteSn() {
		return $this->deletePagoPendienteSn;
	}

	/**
	 * @param mixed $deletePagoPendienteSn
	 */
	public function setDeletePagoPendienteSn( $deletePagoPendienteSn ): void {
		$this->deletePagoPendienteSn = $deletePagoPendienteSn;
	}

	/**
	 * @return mixed
	 */
	public function getViewPagoPendienteSn() {
		return $this->viewPagoPendienteSn;
	}

	/**
	 * @param mixed $viewPagoPendienteSn
	 */
	public function setViewPagoPendienteSn( $viewPagoPendienteSn ): void {
		$this->viewPagoPendienteSn = $viewPagoPendienteSn;
	}

	/**
	 * @return mixed
	 */
	public function getExportPagoPendienteSn() {
		return $this->exportPagoPendienteSn;
	}

	/**
	 * @param mixed $exportPagoPendienteSn
	 */
	public function setExportPagoPendienteSn( $exportPagoPendienteSn ): void {
		$this->exportPagoPendienteSn = $exportPagoPendienteSn;
	}

    /**
     * @return mixed
     */
    public function getFacturarPagoPendienteSn()
    {
        return $this->facturarPagoPendienteSn;
    }

    /**
     * @param mixed $facturarPagoPendienteSn
     */
    public function setFacturarPagoPendienteSn($facturarPagoPendienteSn): void
    {
        $this->facturarPagoPendienteSn = $facturarPagoPendienteSn;
    }

	/**
	 * @return mixed
	 */
	public function getFacturacionSn() {
		return $this->facturacionSn;
	}

	/**
	 * @param mixed $facturacionSn
	 */
	public function setFacturacionSn( $facturacionSn ): void {
		$this->facturacionSn = $facturacionSn;
	}

	/**
	 * @return mixed
	 */
	public function getAddFacturacionSn() {
		return $this->addFacturacionSn;
	}

	/**
	 * @param mixed $addFacturacionSn
	 */
	public function setAddFacturacionSn( $addFacturacionSn ): void {
		$this->addFacturacionSn = $addFacturacionSn;
	}

	/**
	 * @return mixed
	 */
	public function getEditFacturacionSn() {
		return $this->editFacturacionSn;
	}

	/**
	 * @param mixed $editFacturacionSn
	 */
	public function setEditFacturacionSn( $editFacturacionSn ): void {
		$this->editFacturacionSn = $editFacturacionSn;
	}

	/**
	 * @return mixed
	 */
	public function getDeleteFacturacionSn() {
		return $this->deleteFacturacionSn;
	}

	/**
	 * @param mixed $deleteFacturacionSn
	 */
	public function setDeleteFacturacionSn( $deleteFacturacionSn ): void {
		$this->deleteFacturacionSn = $deleteFacturacionSn;
	}

	/**
	 * @return mixed
	 */
	public function getViewFacturacionSn() {
		return $this->viewFacturacionSn;
	}

	/**
	 * @param mixed $viewFacturacionSn
	 */
	public function setViewFacturacionSn( $viewFacturacionSn ): void {
		$this->viewFacturacionSn = $viewFacturacionSn;
	}

	/**
	 * @return mixed
	 */
	public function getExportFacturacionSn() {
		return $this->exportFacturacionSn;
	}

	/**
	 * @param mixed $exportFacturacionSn
	 */
	public function setExportFacturacionSn( $exportFacturacionSn ): void {
		$this->exportFacturacionSn = $exportFacturacionSn;
	}

	/**
	 * @return mixed
	 */
	public function getPrintFacturacionSn() {
		return $this->printFacturacionSn;
	}

	/**
	 * @param mixed $printFacturacionSn
	 */
	public function setPrintFacturacionSn( $printFacturacionSn ): void {
		$this->printFacturacionSn = $printFacturacionSn;
	}

	/**
	 * @return mixed
	 */
	public function getSendFacturacionSn() {
		return $this->sendFacturacionSn;
	}

	/**
	 * @param mixed $sendFacturacionSn
	 */
	public function setSendFacturacionSn( $sendFacturacionSn ): void {
		$this->sendFacturacionSn = $sendFacturacionSn;
	}

    /**
     * @return bool
     */
    public function getAddVencimientoFacturacionSn(): bool
    {
        return $this->addVencimientoFacturacionSn;
    }

    /**
     * @param bool $addVencimientoFacturacionSn
     */
    public function setAddVencimientoFacturacionSn(bool $addVencimientoFacturacionSn): void
    {
        $this->addVencimientoFacturacionSn = $addVencimientoFacturacionSn;
    }

    /**
     * @return bool
     */
    public function getDeleteVencimientoFacturacionSn(): bool
    {
        return $this->deleteVencimientoFacturacionSn;
    }

    /**
     * @param bool $deleteVencimientoFacturacionSn
     */
    public function setDeleteVencimientoFacturacionSn(bool $deleteVencimientoFacturacionSn): void
    {
        $this->deleteVencimientoFacturacionSn = $deleteVencimientoFacturacionSn;
    }

    /**
     * @return bool
     */
    public function getConfirmarVencimientoFacturacionSn(): bool
    {
        return $this->confirmarVencimientoFacturacionSn;
    }

    /**
     * @param bool $confirmarVencimientoFacturacionSn
     */
    public function setConfirmarVencimientoFacturacionSn(bool $confirmarVencimientoFacturacionSn): void
    {
        $this->confirmarVencimientoFacturacionSn = $confirmarVencimientoFacturacionSn;
    }

	/**
	 * @return mixed
	 */
	public function getDatosBancariosSn() {
		return $this->datosBancariosSn;
	}

	/**
	 * @param mixed $datosBancariosSn
	 */
	public function setDatosBancariosSn( $datosBancariosSn ): void {
		$this->datosBancariosSn = $datosBancariosSn;
	}

	/**
	 * @return mixed
	 */
	public function getAddDatosBancariosSn() {
		return $this->addDatosBancariosSn;
	}

	/**
	 * @param mixed $addDatosBancariosSn
	 */
	public function setAddDatosBancariosSn( $addDatosBancariosSn ): void {
		$this->addDatosBancariosSn = $addDatosBancariosSn;
	}

	/**
	 * @return mixed
	 */
	public function getEditDatosBancariosSn() {
		return $this->editDatosBancariosSn;
	}

	/**
	 * @param mixed $editDatosBancariosSn
	 */
	public function setEditDatosBancariosSn( $editDatosBancariosSn ): void {
		$this->editDatosBancariosSn = $editDatosBancariosSn;
	}

	/**
	 * @return mixed
	 */
	public function getDeleteDatosBancariosSn() {
		return $this->deleteDatosBancariosSn;
	}

	/**
	 * @param mixed $deleteDatosBancariosSn
	 */
	public function setDeleteDatosBancariosSn( $deleteDatosBancariosSn ): void {
		$this->deleteDatosBancariosSn = $deleteDatosBancariosSn;
	}

	/**
	 * @return mixed
	 */
	public function getViewDatosBancariosSn() {
		return $this->viewDatosBancariosSn;
	}

	/**
	 * @param mixed $viewDatosBancariosSn
	 */
	public function setViewDatosBancariosSn( $viewDatosBancariosSn ): void {
		$this->viewDatosBancariosSn = $viewDatosBancariosSn;
	}

	/**
	 * @return mixed
	 */
	public function getExportDatosBancariosSn() {
		return $this->exportDatosBancariosSn;
	}

	/**
	 * @param mixed $exportDatosBancariosSn
	 */
	public function setExportDatosBancariosSn( $exportDatosBancariosSn ): void {
		$this->exportDatosBancariosSn = $exportDatosBancariosSn;
	}

	/**
	 * @return mixed
	 */
	public function getBalanceEconomicoSn() {
		return $this->balanceEconomicoSn;
	}

	/**
	 * @param mixed $balanceEconomicoSn
	 */
	public function setBalanceEconomicoSn( $balanceEconomicoSn ): void {
		$this->balanceEconomicoSn = $balanceEconomicoSn;
	}

	/**
	 * @return mixed
	 */
	public function getViewBalanceEconomicoSn() {
		return $this->viewBalanceEconomicoSn;
	}

	/**
	 * @param mixed $viewBalanceEconomicoSn
	 */
	public function setViewBalanceEconomicoSn( $viewBalanceEconomicoSn ): void {
		$this->viewBalanceEconomicoSn = $viewBalanceEconomicoSn;
	}

	/**
	 * @return mixed
	 */
	public function getAddGiroBalanceEconomicoSn() {
		return $this->addGiroBalanceEconomicoSn;
	}

	/**
	 * @param mixed $addGiroBalanceEconomicoSn
	 */
	public function setAddGiroBalanceEconomicoSn( $addGiroBalanceEconomicoSn ): void {
		$this->addGiroBalanceEconomicoSn = $addGiroBalanceEconomicoSn;
	}

	/**
	 * @return mixed
	 */
	public function getEditGiroBalanceEconomicoSn() {
		return $this->editGiroBalanceEconomicoSn;
	}

	/**
	 * @param mixed $editGiroBalanceEconomicoSn
	 */
	public function setEditGiroBalanceEconomicoSn( $editGiroBalanceEconomicoSn ): void {
		$this->editGiroBalanceEconomicoSn = $editGiroBalanceEconomicoSn;
	}

	/**
	 * @return mixed
	 */
	public function getDeleteGiroBalanceEconomicoSn() {
		return $this->deleteGiroBalanceEconomicoSn;
	}

	/**
	 * @param mixed $deleteGiroBalanceEconomicoSn
	 */
	public function setDeleteGiroBalanceEconomicoSn( $deleteGiroBalanceEconomicoSn ): void {
		$this->deleteGiroBalanceEconomicoSn = $deleteGiroBalanceEconomicoSn;
	}

	/**
	 * @return mixed
	 */
	public function getExportGiroBalanceEconomicoSn() {
		return $this->exportGiroBalanceEconomicoSn;
	}

	/**
	 * @param mixed $exportGiroBalanceEconomicoSn
	 */
	public function setExportGiroBalanceEconomicoSn( $exportGiroBalanceEconomicoSn ): void {
		$this->exportGiroBalanceEconomicoSn = $exportGiroBalanceEconomicoSn;
	}

	/**
	 * @return mixed
	 */
	public function getAddTransferenciaBalanceEconomicoSn() {
		return $this->addTransferenciaBalanceEconomicoSn;
	}

	/**
	 * @param mixed $addTransferenciaBalanceEconomicoSn
	 */
	public function setAddTransferenciaBalanceEconomicoSn( $addTransferenciaBalanceEconomicoSn ): void {
		$this->addTransferenciaBalanceEconomicoSn = $addTransferenciaBalanceEconomicoSn;
	}

	/**
	 * @return mixed
	 */
	public function getDeleteTransferenciaBalanceEconomicoSn() {
		return $this->deleteTransferenciaBalanceEconomicoSn;
	}

	/**
	 * @param mixed $deleteTransferenciaBalanceEconomicoSn
	 */
	public function setDeleteTransferenciaBalanceEconomicoSn( $deleteTransferenciaBalanceEconomicoSn ): void {
		$this->deleteTransferenciaBalanceEconomicoSn = $deleteTransferenciaBalanceEconomicoSn;
	}

    /**
     * @return mixed
     */
    public function getAddEntradaBalanceEconomicoSn()
    {
        return $this->addEntradaBalanceEconomicoSn;
    }

    /**
     * @param mixed $addEntradaBalanceEconomicoSn
     */
    public function setAddEntradaBalanceEconomicoSn($addEntradaBalanceEconomicoSn): void
    {
        $this->addEntradaBalanceEconomicoSn = $addEntradaBalanceEconomicoSn;
    }

    /**
     * @return mixed
     */
    public function getEditEntradaBalanceEconomicoSn()
    {
        return $this->editEntradaBalanceEconomicoSn;
    }

    /**
     * @param mixed $editEntradaBalanceEconomicoSn
     */
    public function setEditEntradaBalanceEconomicoSn($editEntradaBalanceEconomicoSn): void
    {
        $this->editEntradaBalanceEconomicoSn = $editEntradaBalanceEconomicoSn;
    }

    /**
     * @return mixed
     */
    public function getDeleteEntradaBalanceEconomicoSn()
    {
        return $this->deleteEntradaBalanceEconomicoSn;
    }

    /**
     * @param mixed $deleteEntradaBalanceEconomicoSn
     */
    public function setDeleteEntradaBalanceEconomicoSn($deleteEntradaBalanceEconomicoSn): void
    {
        $this->deleteEntradaBalanceEconomicoSn = $deleteEntradaBalanceEconomicoSn;
    }

	/**
	 * @return mixed
	 */
	public function getRemesaSn() {
		return $this->remesaSn;
	}

	/**
	 * @param mixed $remesaSn
	 */
	public function setRemesaSn( $remesaSn ): void {
		$this->remesaSn = $remesaSn;
	}

	/**
	 * @return mixed
	 */
	public function getAddRemesaSn() {
		return $this->addRemesaSn;
	}

	/**
	 * @param mixed $addRemesaSn
	 */
	public function setAddRemesaSn( $addRemesaSn ): void {
		$this->addRemesaSn = $addRemesaSn;
	}

    /**
     * @return mixed
     */
    public function getDeleteRemesaSn()
    {
        return $this->deleteRemesaSn;
    }

    /**
     * @param mixed $deleteRemesaSn
     */
    public function setDeleteRemesaSn($deleteRemesaSn): void
    {
        $this->deleteRemesaSn = $deleteRemesaSn;
    }

	/**
	 * @return mixed
	 */
	public function getDownRemesaSn() {
		return $this->downRemesaSn;
	}

	/**
	 * @param mixed $downRemesaSn
	 */
	public function setDownRemesaSn( $downRemesaSn ): void {
		$this->downRemesaSn = $downRemesaSn;
	}

	/**
	 * @return mixed
	 */
	public function getExportRemesaSn() {
		return $this->exportRemesaSn;
	}

	/**
	 * @param mixed $exportRemesaSn
	 */
	public function setExportRemesaSn( $exportRemesaSn ): void {
		$this->exportRemesaSn = $exportRemesaSn;
	}

	/**
	 * @return mixed
	 */
	public function getBuscadorSn() {
		return $this->buscadorSn;
	}

	/**
	 * @param mixed $buscadorSn
	 */
	public function setBuscadorSn( $buscadorSn ): void {
		$this->buscadorSn = $buscadorSn;
	}

	/**
	 * @return mixed
	 */
	public function getAddBuscadorSn() {
		return $this->addBuscadorSn;
	}

	/**
	 * @param mixed $addBuscadorSn
	 */
	public function setAddBuscadorSn( $addBuscadorSn ): void {
		$this->addBuscadorSn = $addBuscadorSn;
	}

	/**
	 * @return mixed
	 */
	public function getEditBuscadorSn() {
		return $this->editBuscadorSn;
	}

	/**
	 * @param mixed $editBuscadorSn
	 */
	public function setEditBuscadorSn( $editBuscadorSn ): void {
		$this->editBuscadorSn = $editBuscadorSn;
	}

	/**
	 * @return mixed
	 */
	public function getCopyBuscadorSn() {
		return $this->copyBuscadorSn;
	}

	/**
	 * @param mixed $copyBuscadorSn
	 */
	public function setCopyBuscadorSn( $copyBuscadorSn ): void {
		$this->copyBuscadorSn = $copyBuscadorSn;
	}

	/**
	 * @return mixed
	 */
	public function getExecuteBuscadorSn() {
		return $this->executeBuscadorSn;
	}

	/**
	 * @param mixed $executeBuscadorSn
	 */
	public function setExecuteBuscadorSn( $executeBuscadorSn ): void {
		$this->executeBuscadorSn = $executeBuscadorSn;
	}

	/**
	 * @return mixed
	 */
	public function getDeleteBuscadorSn() {
		return $this->deleteBuscadorSn;
	}

	/**
	 * @param mixed $deleteBuscadorSn
	 */
	public function setDeleteBuscadorSn( $deleteBuscadorSn ): void {
		$this->deleteBuscadorSn = $deleteBuscadorSn;
	}

	/**
	 * @return mixed
	 */
	public function getExportBuscadorSn() {
		return $this->exportBuscadorSn;
	}

	/**
	 * @param mixed $exportBuscadorSn
	 */
	public function setExportBuscadorSn( $exportBuscadorSn ): void {
		$this->exportBuscadorSn = $exportBuscadorSn;
	}

	/**
	 * @return mixed
	 */
	public function getGdocSn() {
		return $this->gdocSn;
	}

	/**
	 * @param mixed $gdocSn
	 */
	public function setGdocSn( $gdocSn ): void {
		$this->gdocSn = $gdocSn;
	}

	/**
	 * @return mixed
	 */
	public function getAddGdocSn() {
		return $this->addGdocSn;
	}

	/**
	 * @param mixed $addGdocSn
	 */
	public function setAddGdocSn( $addGdocSn ): void {
		$this->addGdocSn = $addGdocSn;
	}

	/**
	 * @return mixed
	 */
	public function getEditGdocSn() {
		return $this->editGdocSn;
	}

	/**
	 * @param mixed $editGdocSn
	 */
	public function setEditGdocSn( $editGdocSn ): void {
		$this->editGdocSn = $editGdocSn;
	}

	/**
	 * @return mixed
	 */
	public function getDeleteGdocSn() {
		return $this->deleteGdocSn;
	}

	/**
	 * @param mixed $deleteGdocSn
	 */
	public function setDeleteGdocSn( $deleteGdocSn ): void {
		$this->deleteGdocSn = $deleteGdocSn;
	}

	/**
	 * @return mixed
	 */
	public function getAddGdocFileSn() {
		return $this->addGdocFileSn;
	}

	/**
	 * @param mixed $addGdocFileSn
	 */
	public function setAddGdocFileSn( $addGdocFileSn ): void {
		$this->addGdocFileSn = $addGdocFileSn;
	}

	/**
	 * @return mixed
	 */
	public function getDeleteGdocFileSn() {
		return $this->deleteGdocFileSn;
	}

	/**
	 * @param mixed $deleteGdocFileSn
	 */
	public function setDeleteGdocFileSn( $deleteGdocFileSn ): void {
		$this->deleteGdocFileSn = $deleteGdocFileSn;
	}

	/**
	 * @return mixed
	 */
	public function getDownGdocFileSn() {
		return $this->downGdocFileSn;
	}

	/**
	 * @param mixed $downGdocFileSn
	 */
	public function setDownGdocFileSn( $downGdocFileSn ): void {
		$this->downGdocFileSn = $downGdocFileSn;
	}

	/**
	 * @return mixed
	 */
	public function getOpenGdocFileSn() {
		return $this->openGdocFileSn;
	}

	/**
	 * @param mixed $openGdocFileSn
	 */
	public function setOpenGdocFileSn( $openGdocFileSn ): void {
		$this->openGdocFileSn = $openGdocFileSn;
	}

    /**
     * @return bool
     */
    public function getMonitorAuditoriaSn(): bool
    {
        return $this->monitorAuditoriaSn;
    }

    /**
     * @param bool $monitorAuditoriaSn
     */
    public function setMonitorAuditoriaSn(bool $monitorAuditoriaSn): void
    {
        $this->monitorAuditoriaSn = $monitorAuditoriaSn;
    }

    /**
     * @return bool
     */
    public function getExportMonitorAuditoriaSn(): bool
    {
        return $this->exportMonitorAuditoriaSn;
    }

    /**
     * @param bool $exportMonitorAuditoriaSn
     */
    public function setExportMonitorAuditoriaSn(bool $exportMonitorAuditoriaSn): void
    {
        $this->exportMonitorAuditoriaSn = $exportMonitorAuditoriaSn;
    }

    /**
     * @return bool
     */
    public function getAddNotificacionSn(): bool
    {
        return $this->addNotificacionSn;
    }

    /**
     * @param bool $addNotificacionSn
     */
    public function setAddNotificacionSn(bool $addNotificacionSn): void
    {
        $this->addNotificacionSn = $addNotificacionSn;
    }

    /**
     * @return bool
     */
    public function getPrintNotificacionSn(): bool
    {
        return $this->printNotificacionSn;
    }

    /**
     * @param bool $printNotificacionSn
     */
    public function setPrintNotificacionSn(bool $printNotificacionSn): void
    {
        $this->printNotificacionSn = $printNotificacionSn;
    }

    /**
     * @return bool
     */
    public function getDeleteNotificacionSn(): bool
    {
        return $this->deleteNotificacionSn;
    }

    /**
     * @param bool $deleteNotificacionSn
     */
    public function setDeleteNotificacionSn(bool $deleteNotificacionSn): void
    {
        $this->deleteNotificacionSn = $deleteNotificacionSn;
    }

    /**
     * @return bool
     */
    public function getExportNotificacionSn(): bool
    {
        return $this->exportNotificacionSn;
    }

    /**
     * @param bool $exportNotificacionSn
     */
    public function setExportNotificacionSn(bool $exportNotificacionSn): void
    {
        $this->exportNotificacionSn = $exportNotificacionSn;
    }

    /**
     * @return mixed
     */
    public function getSendNotificacionSn()
    {
        return $this->sendNotificacionSn;
    }

    /**
     * @param mixed $sendNotificacionSn
     */
    public function setSendNotificacionSn($sendNotificacionSn): void
    {
        $this->sendNotificacionSn = $sendNotificacionSn;
    }

    /**
     * @return bool
     */
    public function getAddCertificacionSn(): bool
    {
        return $this->addCertificacionSn;
    }

    /**
     * @param bool $addCertificacionSn
     */
    public function setAddCertificacionSn(bool $addCertificacionSn): void
    {
        $this->addCertificacionSn = $addCertificacionSn;
    }

    /**
     * @return bool
     */
    public function getPrintCertificacionSn(): bool
    {
        return $this->printCertificacionSn;
    }

    /**
     * @param bool $printCertificacionSn
     */
    public function setPrintCertificacionSn(bool $printCertificacionSn): void
    {
        $this->printCertificacionSn = $printCertificacionSn;
    }

    /**
     * @return bool
     */
    public function getDeleteCertificacionSn(): bool
    {
        return $this->deleteCertificacionSn;
    }

    /**
     * @param bool $deleteCertificacionSn
     */
    public function setDeleteCertificacionSn(bool $deleteCertificacionSn): void
    {
        $this->deleteCertificacionSn = $deleteCertificacionSn;
    }

    /**
     * @return bool
     */
    public function getExportCertificacionSn(): bool
    {
        return $this->exportCertificacionSn;
    }

    /**
     * @param bool $exportCertificacionSn
     */
    public function setExportCertificacionSn(bool $exportCertificacionSn): void
    {
        $this->exportCertificacionSn = $exportCertificacionSn;
    }

    /**
     * @return bool
     */
    public function getAddTarifaSn(): bool
    {
        return $this->addTarifaSn;
    }

    /**
     * @param bool $addTarifaSn
     */
    public function setAddTarifaSn(bool $addTarifaSn): void
    {
        $this->addTarifaSn = $addTarifaSn;
    }

    /**
     * @return bool
     */
    public function getEditTarifaSn(): bool
    {
        return $this->editTarifaSn;
    }

    /**
     * @param bool $editTarifaSn
     */
    public function setEditTarifaSn(bool $editTarifaSn): void
    {
        $this->editTarifaSn = $editTarifaSn;
    }

    /**
     * @return bool
     */
    public function getDeleteTarifaSn(): bool
    {
        return $this->deleteTarifaSn;
    }

    /**
     * @param bool $deleteTarifaSn
     */
    public function setDeleteTarifaSn(bool $deleteTarifaSn): void
    {
        $this->deleteTarifaSn = $deleteTarifaSn;
    }

    /**
     * @return bool
     */
    public function getExportTarifaSn(): bool
    {
        return $this->exportTarifaSn;
    }

    /**
     * @param bool $exportTarifaSn
     */
    public function setExportTarifaSn(bool $exportTarifaSn): void
    {
        $this->exportTarifaSn = $exportTarifaSn;
    }

	/**
	 * @return mixed
	 */
	public function getAddModelo347Sn() {
		return $this->addModelo347Sn;
	}

	/**
	 * @param mixed $addModelo347Sn
	 */
	public function setAddModelo347Sn( $addModelo347Sn ): void {
		$this->addModelo347Sn = $addModelo347Sn;
	}

	/**
	 * @return mixed
	 */
	public function getPrintModelo347Sn() {
		return $this->printModelo347Sn;
	}

	/**
	 * @param mixed $printModelo347Sn
	 */
	public function setPrintModelo347Sn( $printModelo347Sn ): void {
		$this->printModelo347Sn = $printModelo347Sn;
	}

	/**
	 * @return mixed
	 */
	public function getDeleteModelo347Sn() {
		return $this->deleteModelo347Sn;
	}

	/**
	 * @param mixed $deleteModelo347Sn
	 */
	public function setDeleteModelo347Sn( $deleteModelo347Sn ): void {
		$this->deleteModelo347Sn = $deleteModelo347Sn;
	}

	/**
	 * @return mixed
	 */
	public function getExportModelo347Sn() {
		return $this->exportModelo347Sn;
	}

	/**
	 * @param mixed $exportModelo347Sn
	 */
	public function setExportModelo347Sn( $exportModelo347Sn ): void {
		$this->exportModelo347Sn = $exportModelo347Sn;
	}

    /**
     * @return mixed
     */
    public function getSendModelo347Sn()
    {
        return $this->sendModelo347Sn;
    }

    /**
     * @param mixed $sendModelo347Sn
     */
    public function setSendModelo347Sn($sendModelo347Sn): void
    {
        $this->sendModelo347Sn = $sendModelo347Sn;
    }

    /**
     * @return bool
     */
    public function getEmpresaTecnicoSn(): bool
    {
        return $this->empresaTecnicoSn;
    }

    /**
     * @param bool $empresaTecnicoSn
     */
    public function setEmpresaTecnicoSn(bool $empresaTecnicoSn): void
    {
        $this->empresaTecnicoSn = $empresaTecnicoSn;
    }

    /**
     * @return bool
     */
    public function getViewEmpresaTecnicoSn(): bool
    {
        return $this->viewEmpresaTecnicoSn;
    }

    /**
     * @param bool $viewEmpresaTecnicoSn
     */
    public function setViewEmpresaTecnicoSn(bool $viewEmpresaTecnicoSn): void
    {
        $this->viewEmpresaTecnicoSn = $viewEmpresaTecnicoSn;
    }

    /**
     * @return bool
     */
    public function getAddEmpresaTecnicoSn(): bool
    {
        return $this->addEmpresaTecnicoSn;
    }

    /**
     * @param bool $addEmpresaTecnicoSn
     */
    public function setAddEmpresaTecnicoSn(bool $addEmpresaTecnicoSn): void
    {
        $this->addEmpresaTecnicoSn = $addEmpresaTecnicoSn;
    }

    /**
     * @return bool
     */
    public function getEditEmpresaTecnicoSn(): bool
    {
        return $this->editEmpresaTecnicoSn;
    }

    /**
     * @param bool $editEmpresaTecnicoSn
     */
    public function setEditEmpresaTecnicoSn(bool $editEmpresaTecnicoSn): void
    {
        $this->editEmpresaTecnicoSn = $editEmpresaTecnicoSn;
    }

    /**
     * @return bool
     */
    public function getDeleteEmpresaTecnicoSn(): bool
    {
        return $this->deleteEmpresaTecnicoSn;
    }

    /**
     * @param bool $deleteEmpresaTecnicoSn
     */
    public function setDeleteEmpresaTecnicoSn(bool $deleteEmpresaTecnicoSn): void
    {
        $this->deleteEmpresaTecnicoSn = $deleteEmpresaTecnicoSn;
    }

    /**
     * @return bool
     */
    public function getExportEmpresaTecnicoSn(): bool
    {
        return $this->exportEmpresaTecnicoSn;
    }

    /**
     * @param bool $exportEmpresaTecnicoSn
     */
    public function setExportEmpresaTecnicoSn(bool $exportEmpresaTecnicoSn): void
    {
        $this->exportEmpresaTecnicoSn = $exportEmpresaTecnicoSn;
    }

    /**
     * @return bool
     */
    public function getEvaluacionSn(): bool
    {
        return $this->evaluacionSn;
    }

    /**
     * @param bool $evaluacionSn
     */
    public function setEvaluacionSn(bool $evaluacionSn): void
    {
        $this->evaluacionSn = $evaluacionSn;
    }

    /**
     * @return bool
     */
    public function getAddEvaluacionSn(): bool
    {
        return $this->addEvaluacionSn;
    }

    /**
     * @param bool $addEvaluacionSn
     */
    public function setAddEvaluacionSn(bool $addEvaluacionSn): void
    {
        $this->addEvaluacionSn = $addEvaluacionSn;
    }

    /**
     * @return bool
     */
    public function getEditEvaluacionSn(): bool
    {
        return $this->editEvaluacionSn;
    }

    /**
     * @param bool $editEvaluacionSn
     */
    public function setEditEvaluacionSn(bool $editEvaluacionSn): void
    {
        $this->editEvaluacionSn = $editEvaluacionSn;
    }

    /**
     * @return bool
     */
    public function getDeleteEvaluacionSn(): bool
    {
        return $this->deleteEvaluacionSn;
    }

    /**
     * @param bool $deleteEvaluacionSn
     */
    public function setDeleteEvaluacionSn(bool $deleteEvaluacionSn): void
    {
        $this->deleteEvaluacionSn = $deleteEvaluacionSn;
    }

    /**
     * @return bool
     */
    public function getExportEvaluacionSn(): bool
    {
        return $this->exportEvaluacionSn;
    }

    /**
     * @param bool $exportEvaluacionSn
     */
    public function setExportEvaluacionSn(bool $exportEvaluacionSn): void
    {
        $this->exportEvaluacionSn = $exportEvaluacionSn;
    }

    /**
     * @return bool
     */
    public function getFinalizarEvaluacionSn(): bool
    {
        return $this->finalizarEvaluacionSn;
    }

    /**
     * @param bool $finalizarEvaluacionSn
     */
    public function setFinalizarEvaluacionSn(bool $finalizarEvaluacionSn): void
    {
        $this->finalizarEvaluacionSn = $finalizarEvaluacionSn;
    }

    /**
     * @return bool
     */
    public function getPrintEvaluacionSn(): bool
    {
        return $this->printEvaluacionSn;
    }

    /**
     * @param bool $printEvaluacionSn
     */
    public function setPrintEvaluacionSn(bool $printEvaluacionSn): void
    {
        $this->printEvaluacionSn = $printEvaluacionSn;
    }

    /**
     * @return bool
     */
    public function getGrupoEmpresaSn(): bool
    {
        return $this->grupoEmpresaSn;
    }

    /**
     * @param bool $grupoEmpresaSn
     */
    public function setGrupoEmpresaSn(bool $grupoEmpresaSn): void
    {
        $this->grupoEmpresaSn = $grupoEmpresaSn;
    }

    /**
     * @return bool
     */
    public function getAddGrupoEmpresaSn(): bool
    {
        return $this->addGrupoEmpresaSn;
    }

    /**
     * @param bool $addGrupoEmpresaSn
     */
    public function setAddGrupoEmpresaSn(bool $addGrupoEmpresaSn): void
    {
        $this->addGrupoEmpresaSn = $addGrupoEmpresaSn;
    }

    /**
     * @return bool
     */
    public function getEditGrupoEmpresaSn(): bool
    {
        return $this->editGrupoEmpresaSn;
    }

    /**
     * @param bool $editGrupoEmpresaSn
     */
    public function setEditGrupoEmpresaSn(bool $editGrupoEmpresaSn): void
    {
        $this->editGrupoEmpresaSn = $editGrupoEmpresaSn;
    }

    /**
     * @return bool
     */
    public function getDeleteGrupoEmpresaSn(): bool
    {
        return $this->deleteGrupoEmpresaSn;
    }

    /**
     * @param bool $deleteGrupoEmpresaSn
     */
    public function setDeleteGrupoEmpresaSn(bool $deleteGrupoEmpresaSn): void
    {
        $this->deleteGrupoEmpresaSn = $deleteGrupoEmpresaSn;
    }

    /**
     * @return bool
     */
    public function getExportGrupoEmpresaSn(): bool
    {
        return $this->exportGrupoEmpresaSn;
    }

    /**
     * @param bool $exportGrupoEmpresaSn
     */
    public function setExportGrupoEmpresaSn(bool $exportGrupoEmpresaSn): void
    {
        $this->exportGrupoEmpresaSn = $exportGrupoEmpresaSn;
    }

    /**
     * @return bool
     */
    public function getTrabajadorTecnicoSn(): bool
    {
        return $this->trabajadorTecnicoSn;
    }

    /**
     * @param bool $trabajadorTecnicoSn
     */
    public function setTrabajadorTecnicoSn(bool $trabajadorTecnicoSn): void
    {
        $this->trabajadorTecnicoSn = $trabajadorTecnicoSn;
    }

    /**
     * @return bool
     */
    public function getAddTrabajadorTecnicoSn(): bool
    {
        return $this->addTrabajadorTecnicoSn;
    }

    /**
     * @param bool $addTrabajadorTecnicoSn
     */
    public function setAddTrabajadorTecnicoSn(bool $addTrabajadorTecnicoSn): void
    {
        $this->addTrabajadorTecnicoSn = $addTrabajadorTecnicoSn;
    }

    /**
     * @return bool
     */
    public function getEditTrabajadorTecnicoSn(): bool
    {
        return $this->editTrabajadorTecnicoSn;
    }

    /**
     * @param bool $editTrabajadorTecnicoSn
     */
    public function setEditTrabajadorTecnicoSn(bool $editTrabajadorTecnicoSn): void
    {
        $this->editTrabajadorTecnicoSn = $editTrabajadorTecnicoSn;
    }

    /**
     * @return bool
     */
    public function getDeleteTrabajadorTecnicoSn(): bool
    {
        return $this->deleteTrabajadorTecnicoSn;
    }

    /**
     * @param bool $deleteTrabajadorTecnicoSn
     */
    public function setDeleteTrabajadorTecnicoSn(bool $deleteTrabajadorTecnicoSn): void
    {
        $this->deleteTrabajadorTecnicoSn = $deleteTrabajadorTecnicoSn;
    }

    /**
     * @return bool
     */
    public function getExportTrabajadorTecnicoSn(): bool
    {
        return $this->exportTrabajadorTecnicoSn;
    }

    /**
     * @param bool $exportTrabajadorTecnicoSn
     */
    public function setExportTrabajadorTecnicoSn(bool $exportTrabajadorTecnicoSn): void
    {
        $this->exportTrabajadorTecnicoSn = $exportTrabajadorTecnicoSn;
    }

    /**
     * @return bool
     */
    public function getPuestoTrabajoSn(): bool
    {
        return $this->puestoTrabajoSn;
    }

    /**
     * @param bool $puestoTrabajoSn
     */
    public function setPuestoTrabajoSn(bool $puestoTrabajoSn): void
    {
        $this->puestoTrabajoSn = $puestoTrabajoSn;
    }

    /**
     * @return bool
     */
    public function getAddPuestoTrabajoSn(): bool
    {
        return $this->addPuestoTrabajoSn;
    }

    /**
     * @param bool $addPuestoTrabajoSn
     */
    public function setAddPuestoTrabajoSn(bool $addPuestoTrabajoSn): void
    {
        $this->addPuestoTrabajoSn = $addPuestoTrabajoSn;
    }

    /**
     * @return bool
     */
    public function getEditPuestoTrabajoSn(): bool
    {
        return $this->editPuestoTrabajoSn;
    }

    /**
     * @param bool $editPuestoTrabajoSn
     */
    public function setEditPuestoTrabajoSn(bool $editPuestoTrabajoSn): void
    {
        $this->editPuestoTrabajoSn = $editPuestoTrabajoSn;
    }

    /**
     * @return bool
     */
    public function getDeletePuestoTrabajoSn(): bool
    {
        return $this->deletePuestoTrabajoSn;
    }

    /**
     * @param bool $deletePuestoTrabajoSn
     */
    public function setDeletePuestoTrabajoSn(bool $deletePuestoTrabajoSn): void
    {
        $this->deletePuestoTrabajoSn = $deletePuestoTrabajoSn;
    }

    /**
     * @return bool
     */
    public function getExportPuestoTrabajoSn(): bool
    {
        return $this->exportPuestoTrabajoSn;
    }

    /**
     * @param bool $exportPuestoTrabajoSn
     */
    public function setExportPuestoTrabajoSn(bool $exportPuestoTrabajoSn): void
    {
        $this->exportPuestoTrabajoSn = $exportPuestoTrabajoSn;
    }

    /**
     * @return bool
     */
    public function getAddMaquinaEmpresaSn(): bool
    {
        return $this->addMaquinaEmpresaSn;
    }

    /**
     * @param bool $addMaquinaEmpresaSn
     */
    public function setAddMaquinaEmpresaSn(bool $addMaquinaEmpresaSn): void
    {
        $this->addMaquinaEmpresaSn = $addMaquinaEmpresaSn;
    }

    /**
     * @return bool
     */
    public function getEditMaquinaEmpresaSn(): bool
    {
        return $this->editMaquinaEmpresaSn;
    }

    /**
     * @param bool $editMaquinaEmpresaSn
     */
    public function setEditMaquinaEmpresaSn(bool $editMaquinaEmpresaSn): void
    {
        $this->editMaquinaEmpresaSn = $editMaquinaEmpresaSn;
    }

    /**
     * @return bool
     */
    public function getDeleteMaquinaEmpresaSn(): bool
    {
        return $this->deleteMaquinaEmpresaSn;
    }

    /**
     * @param bool $deleteMaquinaEmpresaSn
     */
    public function setDeleteMaquinaEmpresaSn(bool $deleteMaquinaEmpresaSn): void
    {
        $this->deleteMaquinaEmpresaSn = $deleteMaquinaEmpresaSn;
    }

    /**
     * @return bool
     */
    public function getViewMaquinaEmpresaSn(): bool
    {
        return $this->viewMaquinaEmpresaSn;
    }

    /**
     * @param bool $viewMaquinaEmpresaSn
     */
    public function setViewMaquinaEmpresaSn(bool $viewMaquinaEmpresaSn): void
    {
        $this->viewMaquinaEmpresaSn = $viewMaquinaEmpresaSn;
    }

    /**
     * @return bool
     */
    public function getExportMaquinaEmpresaSn(): bool
    {
        return $this->exportMaquinaEmpresaSn;
    }

    /**
     * @param bool $exportMaquinaEmpresaSn
     */
    public function setExportMaquinaEmpresaSn(bool $exportMaquinaEmpresaSn): void
    {
        $this->exportMaquinaEmpresaSn = $exportMaquinaEmpresaSn;
    }

    /**
     * @return bool
     */
    public function getAddPlanPrevencionSn(): bool
    {
        return $this->addPlanPrevencionSn;
    }

    /**
     * @param bool $addPlanPrevencionSn
     */
    public function setAddPlanPrevencionSn(bool $addPlanPrevencionSn): void
    {
        $this->addPlanPrevencionSn = $addPlanPrevencionSn;
    }

    /**
     * @return bool
     */
    public function getDeletePlanPrevencionSn(): bool
    {
        return $this->deletePlanPrevencionSn;
    }

    /**
     * @param bool $deletePlanPrevencionSn
     */
    public function setDeletePlanPrevencionSn(bool $deletePlanPrevencionSn): void
    {
        $this->deletePlanPrevencionSn = $deletePlanPrevencionSn;
    }

    /**
     * @return bool
     */
    public function getPrintPlanPrevencionSn(): bool
    {
        return $this->printPlanPrevencionSn;
    }

    /**
     * @param bool $printPlanPrevencionSn
     */
    public function setPrintPlanPrevencionSn(bool $printPlanPrevencionSn): void
    {
        $this->printPlanPrevencionSn = $printPlanPrevencionSn;
    }

    /**
     * @return bool
     */
    public function getExportPlanPrevencionSn(): bool
    {
        return $this->exportPlanPrevencionSn;
    }

    /**
     * @param bool $exportPlanPrevencionSn
     */
    public function setExportPlanPrevencionSn(bool $exportPlanPrevencionSn): void
    {
        $this->exportPlanPrevencionSn = $exportPlanPrevencionSn;
    }

    /**
     * @return bool
     */
    public function getTrabajadorMedicoSn(): bool
    {
        return $this->trabajadorMedicoSn;
    }

    /**
     * @param bool $trabajadorMedicoSn
     */
    public function setTrabajadorMedicoSn(bool $trabajadorMedicoSn): void
    {
        $this->trabajadorMedicoSn = $trabajadorMedicoSn;
    }

    /**
     * @return bool
     */
    public function getEditTrabajadorMedicoSn(): bool
    {
        return $this->editTrabajadorMedicoSn;
    }

    /**
     * @param bool $editTrabajadorMedicoSn
     */
    public function setEditTrabajadorMedicoSn(bool $editTrabajadorMedicoSn): void
    {
        $this->editTrabajadorMedicoSn = $editTrabajadorMedicoSn;
    }

    /**
     * @return bool
     */
    public function getExportTrabajadorMedicoSn(): bool
    {
        return $this->exportTrabajadorMedicoSn;
    }

    /**
     * @param bool $exportTrabajadorMedicoSn
     */
    public function setExportTrabajadorMedicoSn(bool $exportTrabajadorMedicoSn): void
    {
        $this->exportTrabajadorMedicoSn = $exportTrabajadorMedicoSn;
    }

    /**
     * @return bool
     */
    public function getDocumentacionSn(): bool
    {
        return $this->documentacionSn;
    }

    /**
     * @param bool $documentacionSn
     */
    public function setDocumentacionSn(bool $documentacionSn): void
    {
        $this->documentacionSn = $documentacionSn;
    }

    /**
     * @return bool
     */
    public function getAddDocumentacionSn(): bool
    {
        return $this->addDocumentacionSn;
    }

    /**
     * @param bool $addDocumentacionSn
     */
    public function setAddDocumentacionSn(bool $addDocumentacionSn): void
    {
        $this->addDocumentacionSn = $addDocumentacionSn;
    }

    /**
     * @return bool
     */
    public function getEditDocumentacionSn(): bool
    {
        return $this->editDocumentacionSn;
    }

    /**
     * @param bool $editDocumentacionSn
     */
    public function setEditDocumentacionSn(bool $editDocumentacionSn): void
    {
        $this->editDocumentacionSn = $editDocumentacionSn;
    }

    /**
     * @return bool
     */
    public function getDeleteDocumentacionSn(): bool
    {
        return $this->deleteDocumentacionSn;
    }

    /**
     * @param bool $deleteDocumentacionSn
     */
    public function setDeleteDocumentacionSn(bool $deleteDocumentacionSn): void
    {
        $this->deleteDocumentacionSn = $deleteDocumentacionSn;
    }

    /**
     * @return bool
     */
    public function getExportDocumentacionSn(): bool
    {
        return $this->exportDocumentacionSn;
    }

    /**
     * @param bool $exportDocumentacionSn
     */
    public function setExportDocumentacionSn(bool $exportDocumentacionSn): void
    {
        $this->exportDocumentacionSn = $exportDocumentacionSn;
    }

    /**
     * @return bool
     */
    public function getHistorialLaboralSn(): bool
    {
        return $this->historialLaboralSn;
    }

    /**
     * @param bool $historialLaboralSn
     */
    public function setHistorialLaboralSn(bool $historialLaboralSn): void
    {
        $this->historialLaboralSn = $historialLaboralSn;
    }

    /**
     * @return bool
     */
    public function getAddHistorialLaboralSn(): bool
    {
        return $this->addHistorialLaboralSn;
    }

    /**
     * @param bool $addHistorialLaboralSn
     */
    public function setAddHistorialLaboralSn(bool $addHistorialLaboralSn): void
    {
        $this->addHistorialLaboralSn = $addHistorialLaboralSn;
    }

    /**
     * @return bool
     */
    public function getEditHistorialLaboralSn(): bool
    {
        return $this->editHistorialLaboralSn;
    }

    /**
     * @param bool $editHistorialLaboralSn
     */
    public function setEditHistorialLaboralSn(bool $editHistorialLaboralSn): void
    {
        $this->editHistorialLaboralSn = $editHistorialLaboralSn;
    }

    /**
     * @return bool
     */
    public function getDeleteHistorialLaboralSn(): bool
    {
        return $this->deleteHistorialLaboralSn;
    }

    /**
     * @param bool $deleteHistorialLaboralSn
     */
    public function setDeleteHistorialLaboralSn(bool $deleteHistorialLaboralSn): void
    {
        $this->deleteHistorialLaboralSn = $deleteHistorialLaboralSn;
    }

    /**
     * @return bool
     */
    public function getExportHistorialLaboralSn(): bool
    {
        return $this->exportHistorialLaboralSn;
    }

    /**
     * @param bool $exportHistorialLaboralSn
     */
    public function setExportHistorialLaboralSn(bool $exportHistorialLaboralSn): void
    {
        $this->exportHistorialLaboralSn = $exportHistorialLaboralSn;
    }

    /**
     * @return mixed
     */
    public function getRevisionSn()
    {
        return $this->revisionSn;
    }

    /**
     * @param mixed $revisionSn
     */
    public function setRevisionSn($revisionSn): void
    {
        $this->revisionSn = $revisionSn;
    }

    /**
     * @return mixed
     */
    public function getAddRevisionSn()
    {
        return $this->addRevisionSn;
    }

    /**
     * @param mixed $addRevisionSn
     */
    public function setAddRevisionSn($addRevisionSn): void
    {
        $this->addRevisionSn = $addRevisionSn;
    }

    /**
     * @return mixed
     */
    public function getEditRevisionSn()
    {
        return $this->editRevisionSn;
    }

    /**
     * @param mixed $editRevisionSn
     */
    public function setEditRevisionSn($editRevisionSn): void
    {
        $this->editRevisionSn = $editRevisionSn;
    }

    /**
     * @return mixed
     */
    public function getDeleteRevisionSn()
    {
        return $this->deleteRevisionSn;
    }

    /**
     * @param mixed $deleteRevisionSn
     */
    public function setDeleteRevisionSn($deleteRevisionSn): void
    {
        $this->deleteRevisionSn = $deleteRevisionSn;
    }

    /**
     * @return mixed
     */
    public function getExportRevisionSn()
    {
        return $this->exportRevisionSn;
    }

    /**
     * @param mixed $exportRevisionSn
     */
    public function setExportRevisionSn($exportRevisionSn): void
    {
        $this->exportRevisionSn = $exportRevisionSn;
    }

    /**
     * @return mixed
     */
    public function getSendCuestionarioRevisionSn()
    {
        return $this->sendCuestionarioRevisionSn;
    }

    /**
     * @param mixed $sendCuestionarioRevisionSn
     */
    public function setSendCuestionarioRevisionSn($sendCuestionarioRevisionSn): void
    {
        $this->sendCuestionarioRevisionSn = $sendCuestionarioRevisionSn;
    }

    /**
     * @return mixed
     */
    public function getPrintAptitudRevisionSn()
    {
        return $this->printAptitudRevisionSn;
    }

    /**
     * @param mixed $printAptitudRevisionSn
     */
    public function setPrintAptitudRevisionSn($printAptitudRevisionSn): void
    {
        $this->printAptitudRevisionSn = $printAptitudRevisionSn;
    }

    /**
     * @return mixed
     */
    public function getPrintResumenRevisionSn()
    {
        return $this->printResumenRevisionSn;
    }

    /**
     * @param mixed $printResumenRevisionSn
     */
    public function setPrintResumenRevisionSn($printResumenRevisionSn): void
    {
        $this->printResumenRevisionSn = $printResumenRevisionSn;
    }

    /**
     * @return mixed
     */
    public function getFacturarRevisionSn()
    {
        return $this->facturarRevisionSn;
    }

    /**
     * @param mixed $facturarRevisionSn
     */
    public function setFacturarRevisionSn($facturarRevisionSn): void
    {
        $this->facturarRevisionSn = $facturarRevisionSn;
    }

    /**
     * @return mixed
     */
    public function getInvestigacionSn()
    {
        return $this->investigacionSn;
    }

    /**
     * @param mixed $investigacionSn
     */
    public function setInvestigacionSn($investigacionSn): void
    {
        $this->investigacionSn = $investigacionSn;
    }

    /**
     * @return mixed
     */
    public function getAddInvestigacionSn()
    {
        return $this->addInvestigacionSn;
    }

    /**
     * @param mixed $addInvestigacionSn
     */
    public function setAddInvestigacionSn($addInvestigacionSn): void
    {
        $this->addInvestigacionSn = $addInvestigacionSn;
    }

    /**
     * @return mixed
     */
    public function getEditInvestigacionSn()
    {
        return $this->editInvestigacionSn;
    }

    /**
     * @param mixed $editInvestigacionSn
     */
    public function setEditInvestigacionSn($editInvestigacionSn): void
    {
        $this->editInvestigacionSn = $editInvestigacionSn;
    }

    /**
     * @return mixed
     */
    public function getDeleteInvestigacionSn()
    {
        return $this->deleteInvestigacionSn;
    }

    /**
     * @param mixed $deleteInvestigacionSn
     */
    public function setDeleteInvestigacionSn($deleteInvestigacionSn): void
    {
        $this->deleteInvestigacionSn = $deleteInvestigacionSn;
    }

    /**
     * @return mixed
     */
    public function getExportInvestigacionSn()
    {
        return $this->exportInvestigacionSn;
    }

    /**
     * @param mixed $exportInvestigacionSn
     */
    public function setExportInvestigacionSn($exportInvestigacionSn): void
    {
        $this->exportInvestigacionSn = $exportInvestigacionSn;
    }

    /**
     * @return mixed
     */
    public function getEnfermedadProfesionalSn()
    {
        return $this->enfermedadProfesionalSn;
    }

    /**
     * @param mixed $enfermedadProfesionalSn
     */
    public function setEnfermedadProfesionalSn($enfermedadProfesionalSn): void
    {
        $this->enfermedadProfesionalSn = $enfermedadProfesionalSn;
    }

    /**
     * @return mixed
     */
    public function getAddEnfermedadProfesionalSn()
    {
        return $this->addEnfermedadProfesionalSn;
    }

    /**
     * @param mixed $addEnfermedadProfesionalSn
     */
    public function setAddEnfermedadProfesionalSn($addEnfermedadProfesionalSn): void
    {
        $this->addEnfermedadProfesionalSn = $addEnfermedadProfesionalSn;
    }

    /**
     * @return mixed
     */
    public function getEditEnfermedadProfesionalSn()
    {
        return $this->editEnfermedadProfesionalSn;
    }

    /**
     * @param mixed $editEnfermedadProfesionalSn
     */
    public function setEditEnfermedadProfesionalSn($editEnfermedadProfesionalSn): void
    {
        $this->editEnfermedadProfesionalSn = $editEnfermedadProfesionalSn;
    }

    /**
     * @return mixed
     */
    public function getDeleteEnfermedadProfesionalSn()
    {
        return $this->deleteEnfermedadProfesionalSn;
    }

    /**
     * @param mixed $deleteEnfermedadProfesionalSn
     */
    public function setDeleteEnfermedadProfesionalSn($deleteEnfermedadProfesionalSn): void
    {
        $this->deleteEnfermedadProfesionalSn = $deleteEnfermedadProfesionalSn;
    }

    /**
     * @return mixed
     */
    public function getExportEnfermedadProfesionalSn()
    {
        return $this->exportEnfermedadProfesionalSn;
    }

    /**
     * @param mixed $exportEnfermedadProfesionalSn
     */
    public function setExportEnfermedadProfesionalSn($exportEnfermedadProfesionalSn): void
    {
        $this->exportEnfermedadProfesionalSn = $exportEnfermedadProfesionalSn;
    }

    /**
     * @return bool
     */
    public function getFirmaTecnicoSn(): bool
    {
        return $this->firmaTecnicoSn;
    }

    /**
     * @param bool $firmaTecnicoSn
     */
    public function setFirmaTecnicoSn(bool $firmaTecnicoSn): void
    {
        $this->firmaTecnicoSn = $firmaTecnicoSn;
    }

    /**
     * @return mixed
     */
    public function getCitacionSn()
    {
        return $this->citacionSn;
    }

    /**
     * @param mixed $citacionSn
     */
    public function setCitacionSn($citacionSn): void
    {
        $this->citacionSn = $citacionSn;
    }

	/**
	 * @return mixed
	 */
	public function getAddCitacionSn() {
		return $this->addCitacionSn;
	}

	/**
	 * @param mixed $addCitacionSn
	 */
	public function setAddCitacionSn( $addCitacionSn ): void {
		$this->addCitacionSn = $addCitacionSn;
	}

	/**
	 * @return mixed
	 */
	public function getEditCitacionSn() {
		return $this->editCitacionSn;
	}

	/**
	 * @param mixed $editCitacionSn
	 */
	public function setEditCitacionSn( $editCitacionSn ): void {
		$this->editCitacionSn = $editCitacionSn;
	}

	/**
	 * @return mixed
	 */
	public function getDeleteCitacionSn() {
		return $this->deleteCitacionSn;
	}

	/**
	 * @param mixed $deleteCitacionSn
	 */
	public function setDeleteCitacionSn( $deleteCitacionSn ): void {
		$this->deleteCitacionSn = $deleteCitacionSn;
	}

    /**
     * @return mixed
     */
    public function getEmpresaMedicoSn()
    {
        return $this->empresaMedicoSn;
    }

    /**
     * @param mixed $empresaMedicoSn
     */
    public function setEmpresaMedicoSn($empresaMedicoSn): void
    {
        $this->empresaMedicoSn = $empresaMedicoSn;
    }

    /**
     * @return mixed
     */
    public function getViewEmpresaMedicoSn()
    {
        return $this->viewEmpresaMedicoSn;
    }

    /**
     * @param mixed $viewEmpresaMedicoSn
     */
    public function setViewEmpresaMedicoSn($viewEmpresaMedicoSn): void
    {
        $this->viewEmpresaMedicoSn = $viewEmpresaMedicoSn;
    }

    /**
     * @return mixed
     */
    public function getAddEmpresaMedicoSn()
    {
        return $this->addEmpresaMedicoSn;
    }

    /**
     * @param mixed $addEmpresaMedicoSn
     */
    public function setAddEmpresaMedicoSn($addEmpresaMedicoSn): void
    {
        $this->addEmpresaMedicoSn = $addEmpresaMedicoSn;
    }

    /**
     * @return mixed
     */
    public function getEditEmpresaMedicoSn()
    {
        return $this->editEmpresaMedicoSn;
    }

    /**
     * @param mixed $editEmpresaMedicoSn
     */
    public function setEditEmpresaMedicoSn($editEmpresaMedicoSn): void
    {
        $this->editEmpresaMedicoSn = $editEmpresaMedicoSn;
    }

    /**
     * @return mixed
     */
    public function getDeleteEmpresaMedicoSn()
    {
        return $this->deleteEmpresaMedicoSn;
    }

    /**
     * @param mixed $deleteEmpresaMedicoSn
     */
    public function setDeleteEmpresaMedicoSn($deleteEmpresaMedicoSn): void
    {
        $this->deleteEmpresaMedicoSn = $deleteEmpresaMedicoSn;
    }

    /**
     * @return mixed
     */
    public function getExportEmpresaMedicoSn()
    {
        return $this->exportEmpresaMedicoSn;
    }

    /**
     * @param mixed $exportEmpresaMedicoSn
     */
    public function setExportEmpresaMedicoSn($exportEmpresaMedicoSn): void
    {
        $this->exportEmpresaMedicoSn = $exportEmpresaMedicoSn;
    }

    /**
     * @return mixed
     */
    public function getMantenimientoReconocimientosSn()
    {
        return $this->mantenimientoReconocimientosSn;
    }

    /**
     * @param mixed $mantenimientoReconocimientosSn
     */
    public function setMantenimientoReconocimientosSn($mantenimientoReconocimientosSn): void
    {
        $this->mantenimientoReconocimientosSn = $mantenimientoReconocimientosSn;
    }

    /**
     * @return mixed
     */
    public function getMantenimientoSerieRespuestaSn()
    {
        return $this->mantenimientoSerieRespuestaSn;
    }

    /**
     * @param mixed $mantenimientoSerieRespuestaSn
     */
    public function setMantenimientoSerieRespuestaSn($mantenimientoSerieRespuestaSn): void
    {
        $this->mantenimientoSerieRespuestaSn = $mantenimientoSerieRespuestaSn;
    }

    /**
     * @return mixed
     */
    public function getMantenimientoPreguntasSn()
    {
        return $this->mantenimientoPreguntasSn;
    }

    /**
     * @param mixed $mantenimientoPreguntasSn
     */
    public function setMantenimientoPreguntasSn($mantenimientoPreguntasSn): void
    {
        $this->mantenimientoPreguntasSn = $mantenimientoPreguntasSn;
    }

    /**
     * @return mixed
     */
    public function getMantenimientoConsejosMedicosSn()
    {
        return $this->mantenimientoConsejosMedicosSn;
    }

    /**
     * @param mixed $mantenimientoConsejosMedicosSn
     */
    public function setMantenimientoConsejosMedicosSn($mantenimientoConsejosMedicosSn): void
    {
        $this->mantenimientoConsejosMedicosSn = $mantenimientoConsejosMedicosSn;
    }

    /**
     * @return mixed
     */
    public function getMantenimientoRespuestasSn()
    {
        return $this->mantenimientoRespuestasSn;
    }

    /**
     * @param mixed $mantenimientoRespuestasSn
     */
    public function setMantenimientoRespuestasSn($mantenimientoRespuestasSn): void
    {
        $this->mantenimientoRespuestasSn = $mantenimientoRespuestasSn;
    }

    /**
     * @return mixed
     */
    public function getMantenimientoFormulasSn()
    {
        return $this->mantenimientoFormulasSn;
    }

    /**
     * @param mixed $mantenimientoFormulasSn
     */
    public function setMantenimientoFormulasSn($mantenimientoFormulasSn): void
    {
        $this->mantenimientoFormulasSn = $mantenimientoFormulasSn;
    }

    /**
     * @return mixed
     */
    public function getMantenimientoCuestionariosSn()
    {
        return $this->mantenimientoCuestionariosSn;
    }

    /**
     * @param mixed $mantenimientoCuestionariosSn
     */
    public function setMantenimientoCuestionariosSn($mantenimientoCuestionariosSn): void
    {
        $this->mantenimientoCuestionariosSn = $mantenimientoCuestionariosSn;
    }

    /**
     * @return mixed
     */
    public function getMantenimientoSubPreguntasSn()
    {
        return $this->mantenimientoSubPreguntasSn;
    }

    /**
     * @param mixed $mantenimientoSubPreguntasSn
     */
    public function setMantenimientoSubPreguntasSn($mantenimientoSubPreguntasSn): void
    {
        $this->mantenimientoSubPreguntasSn = $mantenimientoSubPreguntasSn;
    }

    /**
     * @return mixed
     */
    public function getPuestoTrabajoProtocoloCuestionarioSn()
    {
        return $this->puestoTrabajoProtocoloCuestionarioSn;
    }

    /**
     * @param mixed $puestoTrabajoProtocoloCuestionarioSn
     */
    public function setPuestoTrabajoProtocoloCuestionarioSn($puestoTrabajoProtocoloCuestionarioSn): void
    {
        $this->puestoTrabajoProtocoloCuestionarioSn = $puestoTrabajoProtocoloCuestionarioSn;
    }

    /**
     * @return mixed
     */
    public function getPuestoTrabajoProtocoloSn()
    {
        return $this->puestoTrabajoProtocoloSn;
    }

    /**
     * @param mixed $puestoTrabajoProtocoloSn
     */
    public function setPuestoTrabajoProtocoloSn($puestoTrabajoProtocoloSn): void
    {
        $this->puestoTrabajoProtocoloSn = $puestoTrabajoProtocoloSn;
    }

    /**
     * @return mixed
     */
    public function getProtocoloSn()
    {
        return $this->protocoloSn;
    }

    /**
     * @param mixed $protocoloSn
     */
    public function setProtocoloSn($protocoloSn): void
    {
        $this->protocoloSn = $protocoloSn;
    }

    /**
     * @return mixed
     */
    public function getIntranetSn()
    {
        return $this->intranetSn;
    }

    /**
     * @param mixed $intranetSn
     */
    public function setIntranetSn($intranetSn): void
    {
        $this->intranetSn = $intranetSn;
    }

    /**
     * @return mixed
     */
    public function getLogEnvioMailSn()
    {
        return $this->logEnvioMailSn;
    }

    /**
     * @param mixed $logEnvioMailSn
     */
    public function setLogEnvioMailSn($logEnvioMailSn): void
    {
        $this->logEnvioMailSn = $logEnvioMailSn;
    }

    /**
     * @return mixed
     */
    public function getAgendaTecnicoSn()
    {
        return $this->agendaTecnicoSn;
    }

    /**
     * @param mixed $agendaTecnicoSn
     */
    public function setAgendaTecnicoSn($agendaTecnicoSn): void
    {
        $this->agendaTecnicoSn = $agendaTecnicoSn;
    }

    /**
     * @return mixed
     */
    public function getAddAgendaTecnicoSn()
    {
        return $this->addAgendaTecnicoSn;
    }

    /**
     * @param mixed $addAgendaTecnicoSn
     */
    public function setAddAgendaTecnicoSn($addAgendaTecnicoSn): void
    {
        $this->addAgendaTecnicoSn = $addAgendaTecnicoSn;
    }

    /**
     * @return mixed
     */
    public function getEditAgendaTecnicoSn()
    {
        return $this->editAgendaTecnicoSn;
    }

    /**
     * @param mixed $editAgendaTecnicoSn
     */
    public function setEditAgendaTecnicoSn($editAgendaTecnicoSn): void
    {
        $this->editAgendaTecnicoSn = $editAgendaTecnicoSn;
    }

    /**
     * @return mixed
     */
    public function getDeleteAgendaTecnicoSn()
    {
        return $this->deleteAgendaTecnicoSn;
    }

    /**
     * @param mixed $deleteAgendaTecnicoSn
     */
    public function setDeleteAgendaTecnicoSn($deleteAgendaTecnicoSn): void
    {
        $this->deleteAgendaTecnicoSn = $deleteAgendaTecnicoSn;
    }

    /**
     * @return mixed
     */
    public function getRenovarContratoMultipleSn()
    {
        return $this->renovarContratoMultipleSn;
    }

    /**
     * @param mixed $renovarContratoMultipleSn
     */
    public function setRenovarContratoMultipleSn($renovarContratoMultipleSn): void
    {
        $this->renovarContratoMultipleSn = $renovarContratoMultipleSn;
    }

    /**
     * @return mixed
     */
    public function getSendContratoSn()
    {
        return $this->sendContratoSn;
    }

    /**
     * @param mixed $sendContratoSn
     */
    public function setSendContratoSn($sendContratoSn): void
    {
        $this->sendContratoSn = $sendContratoSn;
    }

    /**
     * @return mixed
     */
    public function getFacturarContratoMultipleSn()
    {
        return $this->facturarContratoMultipleSn;
    }

    /**
     * @param mixed $facturarContratoMultipleSn
     */
    public function setFacturarContratoMultipleSn($facturarContratoMultipleSn): void
    {
        $this->facturarContratoMultipleSn = $facturarContratoMultipleSn;
    }

    /**
     * @return mixed
     */
    public function getFirmaMedicoSn()
    {
        return $this->firmaMedicoSn;
    }

    /**
     * @return mixed
     */
    public function getEnviarAptitudRevisionSn()
    {
        return $this->enviarAptitudRevisionSn;
    }

    /**
     * @param mixed $enviarAptitudRevisionSn
     */
    public function setEnviarAptitudRevisionSn($enviarAptitudRevisionSn): void
    {
        $this->enviarAptitudRevisionSn = $enviarAptitudRevisionSn;
    }

    /**
     * @param mixed $firmaMedicoSn
     */
    public function setFirmaMedicoSn($firmaMedicoSn): void
    {
        $this->firmaMedicoSn = $firmaMedicoSn;
    }

    /**
     * @return mixed
     */
    public function getEnviarCorreoMasivoSn()
    {
        return $this->enviarCorreoMasivoSn;
    }

    /**
     * @param mixed $enviarCorreoMasivoSn
     */
    public function setEnviarCorreoMasivoSn($enviarCorreoMasivoSn): void
    {
        $this->enviarCorreoMasivoSn = $enviarCorreoMasivoSn;
    }

    /**
     * @return mixed
     */
    public function getVerHuecosFactura()
    {
        return $this->verHuecosFactura;
    }

    /**
     * @param mixed $verHuecosFactura
     */
    public function setVerHuecosFactura($verHuecosFactura): void
    {
        $this->verHuecosFactura = $verHuecosFactura;
    }

	/**
	 * @return mixed
	 */
	public function getAdministracionSn() {
		return $this->administracionSn;
	}

	/**
	 * @param mixed $administracionSn
	 */
	public function setAdministracionSn( $administracionSn ): void {
		$this->administracionSn = $administracionSn;
	}

    /**
     * @return mixed
     */
    public function getAvisoVencimientoAptitud()
    {
        return $this->avisoVencimientoAptitud;
    }

    /**
     * @param mixed $avisoVencimientoAptitud
     */
    public function setAvisoVencimientoAptitud($avisoVencimientoAptitud): void
    {
        $this->avisoVencimientoAptitud = $avisoVencimientoAptitud;
    }

    /**
     * @return mixed
     */
    public function getAddProtocoloAcosoSn()
    {
        return $this->addProtocoloAcosoSn;
    }

    /**
     * @param mixed $addProtocoloAcosoSn
     */
    public function setAddProtocoloAcosoSn($addProtocoloAcosoSn): void
    {
        $this->addProtocoloAcosoSn = $addProtocoloAcosoSn;
    }

    /**
     * @return mixed
     */
    public function getDeleteProtocoloAcosoSn()
    {
        return $this->deleteProtocoloAcosoSn;
    }

    /**
     * @param mixed $deleteProtocoloAcosoSn
     */
    public function setDeleteProtocoloAcosoSn($deleteProtocoloAcosoSn): void
    {
        $this->deleteProtocoloAcosoSn = $deleteProtocoloAcosoSn;
    }

    /**
     * @return mixed
     */
    public function getPrintProtocoloAcosoSn()
    {
        return $this->printProtocoloAcosoSn;
    }

    /**
     * @param mixed $printProtocoloAcosoSn
     */
    public function setPrintProtocoloAcosoSn($printProtocoloAcosoSn): void
    {
        $this->printProtocoloAcosoSn = $printProtocoloAcosoSn;
    }

    /**
     * @return mixed
     */
    public function getExportProtocoloAcosoSn()
    {
        return $this->exportProtocoloAcosoSn;
    }

    /**
     * @param mixed $exportProtocoloAcosoSn
     */
    public function setExportProtocoloAcosoSn($exportProtocoloAcosoSn): void
    {
        $this->exportProtocoloAcosoSn = $exportProtocoloAcosoSn;
    }

    public function __toString()
    {
        return $this->descripcion;
    }
}
