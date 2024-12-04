<?PHP

namespace Jobs\controllers;

include_once dirname(__DIR__) . "\..\Core\Job.php";
include_once dirname(__DIR__) . "\models\JobsAhorro.php";

use Core\Job;
use Jobs\models\JobsAhorro as JobsDao;

class JobsAhorro extends Job
{
    public function __construct()
    {
        parent::__construct("JobsAhorro");
    }

    public function DD_InteresAhorro()
    {
        self::SaveLog("Inicio");
        $resumen = [];
        $cuentas = JobsDao::GetCuentasActivas();
        if (!$cuentas["success"]) return self::SaveLog("Error al obtener las cuentas de ahorro activas: " . $cuentas["error"]);
        if (count($cuentas["datos"]) == 0) return self::SaveLog("No se encontraron cuentas de ahorro activas para aplicar devengo.");

        foreach ($cuentas["datos"] as $key => $cuenta) {
            $saldo = $cuenta["SALDO"];
            $tasa = $cuenta["TASA"] / 100;
            $devengo = $saldo * ($tasa / 360);

            $datos = [
                "cliente" => $cuenta["CLIENTE"],
                "contrato" => $cuenta["CONTRATO"],
                "saldo" => $saldo,
                "devengo" => $devengo,
                "tasa" => $tasa,
            ];

            $resumen[] = [
                "fecha" => date("Y-m-d H:i:s"),
                "datos" => $datos,
                "RES_APLICA_DEVENGO" => JobsDao::AplicaDevengoAhorro($datos),
            ];
        };

        self::SaveLog(json_encode($resumen)); //, JSON_PRETTY_PRINT));
        self::SaveLog("Finalizado");
    }

    public function DD_RendimientoInversion()
    {
        self::SaveLog("Inicio");
        $resumen = [];
        $inversiones = JobsDao::GetInversiones();
        if (!$inversiones["success"]) return self::SaveLog("Error al obtener las inversiones: " . $inversiones["error"]);
        if (count($inversiones["datos"]) == 0) return self::SaveLog("No se encontraron inversiones para aplicar devengo.");

        foreach ($inversiones["datos"] as $key => $inversion) {
            $vencimiento = strtotime($inversion["VENCIMIENTO"]);
            if ($vencimiento > strtotime(date("Y-m-d"))) continue;
            $datos = [
                "contrato" => $inversion["CONTRATO"],
                "id" => $inversion["CODIGO"],
                "monto" => $inversion["MONTO"],
                "tasa" => $inversion["TASA"],
                "rendimiento" => $inversion["RENDIMIENTO"]
            ];

            $resumen[] = [
                "fecha" => date("Y-m-d H:i:s"),
                "datos" => $datos,
                "resultado" => JobsDao::AplicaDevengoInversion($datos),
            ];
        };

        self::SaveLog(json_encode($resumen)); //, JSON_PRETTY_PRINT));
        self::SaveLog("Finalizado");
    }

    public function LiquidaInversion()
    {
        self::SaveLog("Inicio -> Liquidación de Inversiones");
        $resumen = [];
        $inversiones = JobsDao::GetInversiones();
        if (!$inversiones["success"]) return self::SaveLog("Error al obtener las inversiones: " . $inversiones["error"]);
        if (count($inversiones["datos"]) == 0) return self::SaveLog("No se encontraron inversiones para liquidar.");

        foreach ($inversiones["datos"] as $key => $inversion) {
            if (strtotime($inversion["VENCIMIENTO"]) > strtotime(date("d/m/Y"))) continue;

            $datos = [
                "codigo" => $inversion["CODIGO"],
                "contrato" => $inversion["CONTRATO"],
                "monto" => $inversion["MONTO"],
                "rendimiento" => $inversion["RENDIMIENTO"],
                "cliente" => substr($inversion["CONTRATO"], 0, 6),
            ];

            $resumen[] = [
                "fecha" => date("Y-m-d H:i:s"),
                "datos" => $datos,
                "RES_LIQUIDA_INVERSION" => JobsDao::LiquidaInversion($datos),
            ];
        };

        self::SaveLog(json_encode($resumen)); //, JSON_PRETTY_PRINT));
        self::SaveLog("Finalizado -> Liquidación de Inversiones");
    }

    public function RechazaSolicitudesSinAtender()
    {
        self::SaveLog("Inicio -> Rechazo de Solicitudes de retiro sin Atender");
        $resumen = [];
        $solicitudes = JobsDao::GetSolicitudesRetiro();
        if (!$solicitudes["success"]) return self::SaveLog("Error al obtener las solicitudes sin atender: " . $solicitudes["error"]);
        if (count($solicitudes["datos"]) == 0) return self::SaveLog("No se encontraron solicitudes sin atender para rechazar.");

        foreach ($solicitudes["datos"] as $key => $solicitud) {
            $datosRechazo = [
                "idSolicitud" => $solicitud["ID"]
            ];

            $datosDevolucion = [
                "contrato" => $solicitud["CONTRATO"],
                "monto" => $solicitud["MONTO"],
                "cliente" => $solicitud["CLIENTE"],
                "tipo" => $solicitud["TIPO_RETIRO"],
            ];

            $resumen[] = [
                "fecha" => date("Y-m-d H:i:s"),
                "datos_rechazo" => $datosRechazo,
                "RES_RECHAZA_SOLICITUD" => JobsDao::CancelaSolicitudRetiro($datosRechazo),
                "datos_devolucion" => $datosDevolucion,
                "RES_DEVUELVE_MONTO" => JobsDao::DevolucionRetiro($datosDevolucion),
            ];
        };

        self::SaveLog(json_encode($resumen)); //, JSON_PRETTY_PRINT));
        self::SaveLog("Finalizado -> Rechazo de Solicitudes de retiro sin Atender");
    }

    public function SucursalesSinArqueo()
    {
        self::SaveLog("Inicio -> Sucursales sin Arqueo");
        $resumen = [];
        $sucursales = JobsDao::GetSucursalesSinArqueo();

        if (!$sucursales["success"]) return self::SaveLog("Error al obtener las sucursales sin arqueo: " . $sucursales["error"]);
        if (count($sucursales["datos"]) == 0) return self::SaveLog("No se encontraron sucursales sin arqueo.");

        foreach ($sucursales["datos"] as $key => $sucursal) {
            $datos = [
                'sucursal' => $sucursal['CDG_SUCURSAL']
            ];

            $resumen[] = [
                "fecha" => date("Y-m-d H:i:s"),
                "datos" => $datos,
                "RES_REGISTRO_ARQUEO" => JobsDao::RegistraArqueoPendiente($datos)
            ];
        };

        self::SaveLog(json_encode($resumen)); //, JSON_PRETTY_PRINT));
        self::SaveLog("Finalizado -> Sucursales sin Arqueo");
    }

    public function CapturaSaldosSucursales()
    {
        if (date("H:i:s") < "17:30:00") return self::SaveLog("No se puede ejecutar la captura de saldos antes de las 5:30 pm.");

        self::SaveLog("Inicio -> Captura de Saldos de Sucursales");
        $resumen = [];
        $sucursales = JobsDao::GetSucursales();

        if (!$sucursales["success"]) return self::SaveLog("Error al obtener las sucursales: " . $sucursales["error"]);
        if (count($sucursales["datos"]) == 0) return self::SaveLog("No se encontraron sucursales para capturar saldos.");

        foreach ($sucursales["datos"] as $key => $sucursal) {
            $datos = [
                'codigo' => $sucursal['CODIGO'],
                'saldo' => $sucursal['SALDO']
            ];

            $resumen[] = [
                "fecha" => date("Y-m-d H:i:s"),
                "datos" => $datos,
                "RES_CAPTURA_SALDOS" => JobsDao::CapturaSaldos($datos)
            ];
        };

        self::SaveLog(json_encode($resumen)); //, JSON_PRETTY_PRINT));
        self::SaveLog("Finalizado -> Captura de Saldos de Sucursales");
    }

    public function ComprobacionDevengoAhorro()
    {
        self::SaveLog("Inicio -> Comprobación de Devengo Ahorro");
        $resumen = [];
        $cuentas = JobsDao::GetCuentasAhorroValidacionDevengo();
        if (!$cuentas["success"]) return self::SaveLog("Error al obtener las cuentas de ahorro activas: " . $cuentas["error"]);
        if (count($cuentas["datos"]) == 0) return self::SaveLog("No se encontraron cuentas de ahorro activas para aplicar devengo.");

        foreach ($cuentas["datos"] as $key => $cuenta) {
            $saldo = $cuenta["SALDO"];
            $tasa = $cuenta["TASA"] / 100;
            $devengo = $saldo * ($tasa / 365);

            $datos = [
                "cliente" => $cuenta["CLIENTE"],
                "contrato" => $cuenta["CONTRATO"],
                "fecha" => $cuenta["FECHA"],
                "saldo" => $saldo,
                "devengo" => $devengo,
                "tasa" => $tasa,
            ];

            $resumen[] = [
                "fecha" => date("Y-m-d H:i:s"),
                "datos" => $datos,
                "RES_APLICA_DEVENGO" => JobsDao::AplicaDevengo($datos),
            ];
        };

        self::SaveLog(json_encode($resumen)); //, JSON_PRETTY_PRINT));
        self::SaveLog("Finalizado -> Comprobación de Devengo Ahorro");
    }
}

if (isset($argv[1])) {
    $jobs = new JobsAhorro();

    switch ($argv[1]) {
        case 'SucursalesSinArqueo':
            // Programar a las 5:20 pm, de lunes a viernes
            $jobs->SucursalesSinArqueo();
            break;
        case 'CapturaSaldosSucursales':
            // Programas a las 5:22 pm, de lunes a viernes
            $jobs->CapturaSaldosSucursales();
            break;
        case 'RechazaSolicitudesSinAtender':
            // Programar a las 5:25 pm, de lunes a viernes
            $jobs->RechazaSolicitudesSinAtender();
            break;
        case 'DD_InteresAhorro':
            // Programar a las 6:00 pm, todos los días
            $jobs->DD_InteresAhorro();
            break;
        case 'DD_RendimientoInversion':
            $jobs->DD_RendimientoInversion();
            break;
        case 'LiquidaInversion':
            // Programar 11:50 pm, todos los dias
            $jobs->LiquidaInversion();
            break;
        case 'ComprobacionDevengoAhorro':
            $jobs->ComprobacionDevengoAhorro();
            break;
        case 'prueba_horario':
            echo date("Y-m-d H:i:s") . "\n";
            break;
        case 'help':
            echo "Los jobs disponibles son: \n";
            echo "SucursalesSinArqueo: Se recomienda se ejecute a las 5:20 pm, de lunes a viernes.\n";
            echo "CapturaSaldosSucursales: Se recomienda se ejecute a las 5:22 pm, de lunes a viernes.\n";
            echo "DD_InteresAhorro: Se recomienda se ejecute a las 6:00 pm, todos los días.\n";
            echo "DD_RendimientoInversion: Se recomienda se ejecute a las 11:50 pm, todos los días.\n";
            echo "RechazaSolicitudesSinAtender: Se recomienda se ejecute a las 5:25 pm, de lunes a viernes.\n";
            echo "LiquidaInversion: Se recomienda se ejecute a las 11:50 pm, todos los días.\n";
            echo "ComprobacionDevengoAhorro: Se plica de forma manual unicamente cuando se requiera validar los intereses devengados.\n";
            break;
        default:
            echo "No se encontró el job solicitado.\nEjecute 'php JobsAhorro.php help' para ver los jobs disponibles.\n";
            break;
    }
} else echo "Debe especificar el job a ejecutar.\nEjecute 'php JobsAhorro.php help' para ver los jobs disponibles.\n";
