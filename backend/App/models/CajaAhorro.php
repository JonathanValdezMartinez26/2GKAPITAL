<?php

namespace App\models;

defined("APPPATH") or die("Access denied");

use \Core\Database;
use \Core\Model;
use \App\models\LogTransaccionesAhorro;
use Exception;
use DateTime;

class CajaAhorro extends Model
{
    public static function GetSucCajeraAhorro($cajera)
    {
        $qry = <<<sql
        SELECT
            SUC_ESTADO_AHORRO.CDG_SUCURSAL AS CDGCO_AHORRO
        FROM
            SUC_CAJERA_AHORRO
        INNER JOIN
            SUC_ESTADO_AHORRO ON CDG_ESTADO_AHORRO = CODIGO
        WHERE
            SUC_CAJERA_AHORRO.CDG_USUARIO = :cajera
        sql;

        try {
            $mysqli = new Database();
            return $mysqli->queryOne($qry, ['cajera' => $cajera]);
        } catch (Exception $e) {
            return 0;
        }
    }

    public static function GetEFed()
    {
        $query = <<<sql
        SELECT NOMBRE, CDGCURP FROM EF WHERE NOMBRE != 'Desconocido'
        sql;

        $mysqli = new Database();
        return $mysqli->queryAll($query);
    }

    public static function GetCatalogoParentescos()
    {
        $query = <<<sql
        SELECT
            *
        FROM
            CAT_PARENTESCO
        sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryAll($query);
            if ($res) return $res;
            return array();
        } catch (Exception $e) {
            return array();
        }
    }

    public static function GetSucursalAsignadaCajeraAhorro($usuario = '')
    {
        $var = $usuario == "" ? "" : "WHERE SUC_CAJERA_AHORRO.CDG_USUARIO = '" . $usuario . "'";

        $query = <<<sql
        SELECT
            CO.CODIGO, CO.NOMBRE  
        FROM
            SUC_ESTADO_AHORRO 
        INNER JOIN SUC_CAJERA_AHORRO ON SUC_ESTADO_AHORRO.CODIGO = SUC_CAJERA_AHORRO.CDG_ESTADO_AHORRO
        INNER JOIN CO ON CO.CODIGO = SUC_ESTADO_AHORRO.CDG_SUCURSAL 
        $var
        ORDER BY CO.CODIGO
        sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryAll($query);
            if ($res) return $res;
            return array();
        } catch (Exception $e) {
            return array();
        }
    }

    public static function GetEjecutivosSucursal($sucursal)
    {
        $query = <<<sql
        SELECT
            CONCATENA_NOMBRE(PE.NOMBRE1, PE.NOMBRE2, PE.PRIMAPE, PE.SEGAPE) EJECUTIVO,
            CODIGO ID_EJECUTIVO
        FROM
            PE
        WHERE
            CDGEM = 'EMPFIN' 
            AND CDGCO IN( '$sucursal')
            AND ACTIVO = 'S'
            AND BLOQUEO = 'N'
        ORDER BY 1
        sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryAll($query);
            if ($res) return $res;
            return array();
        } catch (Exception $e) {
            return array();
        }
    }

    public static function GetSaldoMinimoInversion()
    {
        $query = <<<sql
        SELECT
            MIN(MONTO_MINIMO) AS MONTO_MINIMO
        FROM
            TASA_INVERSION
        WHERE
            ESTATUS = 'A'
        sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryOne($query);
            if ($res) return $res['MONTO_MINIMO'];
            return 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    public static function GetTasas()
    {
        $query = <<<SQL
            SELECT
                TI.CODIGO,
                TI.TASA,
                ROUND(((TI.TASA / 100) / 365), 6) * TRUNC(PI.DIAS_PLAZO) AS TASA_PLAZO,
                TI.MONTO_MINIMO,
                TI.CDG_PLAZO,
                PI.CODIGO,
                PI.PLAZO AS PLAZO_NUMERO,
                PI.PERIODICIDAD,
                CONCAT(
                    CONCAT(PI.PLAZO, ' '),
                    CASE PI.PERIODICIDAD
                        WHEN 'D' THEN 'Días'
                        WHEN 'S' THEN 'Semanas'
                        WHEN 'M' THEN 'Meses'
                        WHEN 'A' THEN 'Años'
                    END
                ) AS PLAZO
            FROM
                TASA_INVERSION TI
            LEFT JOIN
                PLAZO_INVERSION PI
            ON
                TI.CDG_PLAZO = PI.CODIGO
            WHERE
                TI.ESTATUS = 'A'
            ORDER BY 
                TI.MONTO_MINIMO,
                TI.TASA
        SQL;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryAll($query);
            if ($res) return $res;
            return array();
        } catch (Exception $e) {
            return array();
        }
    }

    public static function GetOperacionesLog()
    {
        $qry = <<<sql
        SELECT
            TIPO
        FROM
            LOG_TRANSACCIONES_AHORRO
        GROUP BY
            TIPO
        ORDER BY
            TIPO
        sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryAll($qry);
            if ($res) return $res;
            return array();
        } catch (Exception $e) {
            return array();
        }
    }

    public static function GetUsuariosLog()
    {
        $qry = <<<sql
        SELECT
            USUARIO
        FROM
            LOG_TRANSACCIONES_AHORRO
        GROUP BY
            USUARIO
        ORDER BY
            USUARIO
        sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryAll($qry);
            if ($res) return $res;
            return array();
        } catch (Exception $e) {
            return array();
        }
    }

    public static function GetSucursalesLog()
    {
        $qry = <<<sql
        SELECT
            SUCURSAL
        FROM
            LOG_TRANSACCIONES_AHORRO
        GROUP BY
            SUCURSAL
        ORDER BY
            SUCURSAL
        sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryAll($qry);
            if ($res) return $res;
            return array();
        } catch (Exception $e) {
            return array();
        }
    }

    public static function GetBeneficiarios($contrato)
    {
        $query = <<<sql
        SELECT
            *
        FROM
            BENEFICIARIOS_AHORRO
        WHERE
            CDG_CONTRATO = '$contrato'
            AND ESTATUS = 'A'
        sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryAll($query);
            if ($res) return self::Responde(true, "Consulta realizada correctamente.", $res);
            return self::Responde(false, "No se encontraron beneficiarios para el contrato {$contrato}.");
        } catch (Exception $e) {
            return self::Responde(false, "Ocurrió un error al consultar los beneficiarios del contrato {$contrato}.", null, $e->getMessage());
        }
    }

    public static function ConsultaClientesProducto($cliente)
    {

        $query_valida_es_cliente_ahorro = <<<sql
        SELECT * FROM CL WHERE CODIGO = '$cliente'
        sql;

        $query_busca_cliente = <<<sql
        SELECT (CL.NOMBRE1 || ' ' || CL.NOMBRE2 || ' ' || CL.PRIMAPE || ' ' || CL.SEGAPE) AS NOMBRE, CL.CURP, TO_CHAR(CL.REGISTRO ,'DD-MM-YYYY')AS REGISTRO, 
        TRUNC(MONTHS_BETWEEN(TO_DATE(SYSDATE,'dd-mm-yy'),CL.NACIMIENTO)/12)AS EDAD,  UPPER((CL.CALLE || ', ' || COL.NOMBRE|| ', ' || LO.NOMBRE || ', ' || MU.NOMBRE  || ', ' || EF.NOMBRE)) AS DIRECCION   
        FROM CL, COL, LO, MU,EF 
        WHERE EF.CODIGO = CL.CDGEF
        AND MU.CODIGO = CL.CDGMU
        AND LO.CODIGO = CL.CDGLO 
        AND COL.CODIGO = CL.CDGCOL
        AND EF.CODIGO = MU.CDGEF 
        AND EF.CODIGO = LO.CDGEF
        AND EF.CODIGO = COL.CDGEF
        AND MU.CODIGO = LO.CDGMU 
        AND MU.CODIGO = COL.CDGMU 
        AND LO.CODIGO = COL.CDGLO 
        AND CL.CODIGO = '$cliente'
        sql;


        $query_tiene_creditos = <<<sql
        SELECT * FROM CL WHERE CODIGO = '$cliente'
        sql;

        $query_es_aval = <<<sql
        SELECT * FROM CL WHERE CODIGO = '$cliente'
        sql;

        try {
            $mysqli = new Database();
            return $mysqli->queryAll($query_busca_cliente);
        } catch (Exception $e) {
            return "";
        }
    }

    public static function BuscaClienteNvoContrato($datos)
    {
        $queryValidacion = <<<sql
        SELECT
            CONCATENA_NOMBRE(CL.NOMBRE1, CL.NOMBRE2, CL.PRIMAPE, CL.SEGAPE) AS NOMBRE,
            (SELECT CDGPR_PRIORITARIO FROM ASIGNA_PROD_AHORRO WHERE CDGCL = CL.CODIGO AND CDGPR_PRIORITARIO != 2) AS PRODUCTO,
            CL.CURP,
            TO_CHAR(CL.REGISTRO, 'DD-MM-YYYY') AS FECHA_REGISTRO,
            TRUNC(MONTHS_BETWEEN(TO_DATE(SYSDATE, 'dd-mm-yy'), CL.NACIMIENTO)/12)AS EDAD,
            UPPER(DOMICILIO_CLIENTE(CL.CODIGO)) AS DIRECCION,
            (SELECT CONTRATO FROM ASIGNA_PROD_AHORRO WHERE CDGCL = CL.CODIGO AND CDGPR_PRIORITARIO != 2) AS CONTRATO,
            (SELECT TO_CHAR(FECHA_APERTURA, 'DD/MM/YYYY HH24:MI:SS') FROM ASIGNA_PROD_AHORRO WHERE CDGCL = CL.CODIGO AND CDGPR_PRIORITARIO != 2) AS FECHA_CONTRATO,
            NVL((SELECT SALDO FROM ASIGNA_PROD_AHORRO WHERE CDGCL = CL.CODIGO AND CDGPR_PRIORITARIO != 2), 0) AS SALDO,
            (SELECT CDGPE_COMISIONA FROM ASIGNA_PROD_AHORRO WHERE CDGCL = CL.CODIGO AND CDGPR_PRIORITARIO != 2) AS EJECUTIVO_COMISIONA,
            (
                SELECT COUNT(*) FROM APODERADO_AHORRO WHERE CONTRATO = (SELECT CONTRATO FROM ASIGNA_PROD_AHORRO WHERE CDGCL = CL.CODIGO AND CDGPR_PRIORITARIO != 2)
            ) AS APODERADO,
            (
                SELECT
                    CONCATENA_NOMBRE(PE.NOMBRE1, PE.NOMBRE2, PE.PRIMAPE, PE.SEGAPE)
                FROM
                    PE
                WHERE
                    PE.CODIGO = (SELECT CDGPE_COMISIONA FROM ASIGNA_PROD_AHORRO WHERE CDGCL = CL.CODIGO AND CDGPR_PRIORITARIO != 2)
                    AND PE.CDGEM = 'EMPFIN'
            ) AS NOMBRE_EJECUTIVO_COMISIONA,
            CL.CODIGO AS CDGCL,
            (
                SELECT
                    MA.MONTO
                FROM
                    MOVIMIENTOS_AHORRO MA
                WHERE
                    MA.CDG_CONTRATO = (SELECT CONTRATO FROM ASIGNA_PROD_AHORRO WHERE CDGCL = CL.CODIGO AND CDGPR_PRIORITARIO != 2)
                    AND CDG_TIPO_PAGO = 2
                    AND MA.FECHA_MOV = (
                        SELECT
                            MAX(MA.FECHA_MOV)
                        FROM
                            MOVIMIENTOS_AHORRO MA
                        WHERE
                            MA.CDG_CONTRATO = (SELECT CONTRATO FROM ASIGNA_PROD_AHORRO WHERE CDGCL = CL.CODIGO AND CDGPR_PRIORITARIO != 2)
                            AND CDG_TIPO_PAGO = 2
                    )
            ) AS INSCRIPCION,
            (
                SELECT
                    CASE 
                        WHEN COUNT(MA.CDG_CONTRATO) > 0 THEN 1
                        ELSE 0
                    END 
                FROM
                    MOVIMIENTOS_AHORRO MA
                WHERE
                    MA.CDG_TIPO_PAGO = 2
                    AND MA.CDG_CONTRATO = (SELECT CONTRATO FROM ASIGNA_PROD_AHORRO WHERE CDGCL = CL.CODIGO AND CDGPR_PRIORITARIO != 2)
            ) AS CONTRATO_COMPLETO,
            (
                SELECT
                    COUNT(APA.CONTRATO)
                FROM
                    ASIGNA_PROD_AHORRO APA
                WHERE
                    APA.CDGCL = CL.CODIGO
                    AND CDGPR_PRIORITARIO != 2
            ) AS NO_CONTRATOS
        FROM
            CL
        WHERE
            CL.CODIGO = '{$datos['cliente']}'
        sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryOne($queryValidacion);
            if (!$res) return self::Responde(false, "No se encontraron datos para el cliente {$datos['cliente']}.");
            if ($res['NO_CONTRATOS'] >= 1) return self::Responde(false, "El cliente {$datos['cliente']} ya cuenta con un contrato de ahorro.", $res);
            if ($res) return self::Responde(true, "Consulta realizada correctamente.", $res);
            return self::Responde(false, "No se encontraron datos para el cliente {$datos['cliente']}.");
        } catch (Exception $e) {
            return self::Responde(false, "Ocurrió un error al consultar los datos del cliente.", null, $e->getMessage());
        }
    }

    public static function BuscaContratoAhorro($datos)
    {
        $query = <<<SQL
        SELECT
            CONCATENA_NOMBRE(CL.NOMBRE1, CL.NOMBRE2, CL.PRIMAPE, CL.SEGAPE) AS NOMBRE,
            CL.CURP,
            (SELECT CONTRATO FROM ASIGNA_PROD_AHORRO WHERE CDGCL = CL.CODIGO AND CDGPR_PRIORITARIO > 2) AS CONTRATO,
            NVL((SELECT SALDO_REAL FROM ASIGNA_PROD_AHORRO WHERE CDGCL = CL.CODIGO AND CDGPR_PRIORITARIO > 2), 0) AS SALDO,
            CL.CODIGO AS CDGCL,
            (SELECT CDGCO FROM ASIGNA_PROD_AHORRO WHERE CDGEM = 'EMPFIN' AND CDGCL = CL.CODIGO AND CDGPR_PRIORITARIO > 2) AS SUCURSAL,
            (SELECT NOMBRE FROM CO WHERE CODIGO = (SELECT CDGCO FROM ASIGNA_PROD_AHORRO WHERE CDGEM = 'EMPFIN' AND CDGCL = CL.CODIGO AND CDGPR_PRIORITARIO > 2)) AS NOMBRE_SUCURSAL,
            (SELECT COUNT(*) FROM HUELLAS WHERE CLIENTE = CL.CODIGO) AS HUELLAS,
            (
                SELECT COUNT(*) FROM APODERADO_AHORRO WHERE CONTRATO = (SELECT CONTRATO FROM ASIGNA_PROD_AHORRO WHERE CDGCL = CL.CODIGO AND CDGPR_PRIORITARIO != 2)
            ) AS APODERADO,
            (
                SELECT
                    CASE 
                        WHEN COUNT(MA.CDG_CONTRATO) > 0 THEN 1
                        ELSE 0
                    END 
                FROM
                    MOVIMIENTOS_AHORRO MA
                WHERE
                    MA.CDG_TIPO_PAGO = 2
                    AND MA.CDG_CONTRATO = (SELECT CONTRATO FROM ASIGNA_PROD_AHORRO WHERE CDGCL = CL.CODIGO AND CDGPR_PRIORITARIO != 2)
            ) AS CONTRATO_COMPLETO,
            (
                SELECT
                    COUNT(APA.CONTRATO)
                FROM
                    ASIGNA_PROD_AHORRO APA
                WHERE
                    APA.CDGCL = CL.CODIGO
                    AND CDGPR_PRIORITARIO > 2
            ) AS NO_CONTRATOS,
            (
                SELECT
                    COUNT(CU.CDG_CONTRATO)
                FROM
                    CUENTA_INVERSION CU
                WHERE
                    CU.CDG_CONTRATO = (SELECT CONTRATO FROM ASIGNA_PROD_AHORRO WHERE CDGCL = CL.CODIGO AND CDGPR_PRIORITARIO != 2)
                    AND ESTATUS = 'A'
            ) AS NO_INVERSIONES
        FROM
            CL
        WHERE
            CL.CODIGO = '{$datos['cliente']}'
        SQL;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryOne($query);
            if (!$res) return self::Responde(false, "No se encontraron datos para el cliente {$datos['cliente']}.");
            if ($res['NO_CONTRATOS'] == 0) return self::Responde(false, "El cliente {$datos['cliente']} no cuenta con un contrato de ahorro.", $res);
            if ($res['NO_CONTRATOS'] >= 1 && $res['CONTRATO_COMPLETO'] == 0) return self::Responde(false, "El cliente {$datos['cliente']} no ha concluido el proceso de apertura de su cuenta de ahorro.", $res);
            return self::Responde(true, "Consulta realizada correctamente.", $res);
        } catch (Exception $e) {
            return self::Responde(false, "Ocurrió un error al consultar los datos del cliente.", null, $e->getMessage());
        }
    }

    public static function ActualizaContratoAhorro($datos)
    {
        $qry = <<<SQL
        UPDATE
            ASIGNA_PROD_AHORRO
        SET
            CDGPR_PRIORITARIO = :producto,
            TASA = :tasa,
            FECHA_APERTURA = SYSDATE,
            CDGPE_ACTUALIZA = :ejecutivo
        WHERE
            CONTRATO = :contrato
            AND CDGPR_PRIORITARIO != 2
        SQL;

        $parametros = [
            'producto' => $datos['tipo_ahorro'],
            'tasa' => $datos['tasa'],
            'ejecutivo' => $datos['ejecutivo'],
            'contrato' => $datos['contrato']
        ];

        try {
            $mysqli = new Database();
            $mysqli->actualizar($qry, $parametros);
            return self::Responde(true, "Contrato de ahorro actualizado correctamente.");
        } catch (Exception $e) {
            return self::Responde(false, "Ocurrió un error al actualizar el contrato de ahorro.", null, $e->getMessage());
        }
    }

    public static function AgregaContratoAhorro($datos)
    {
        $queryValidacion = <<<sql
        SELECT
            *
        FROM
            ASIGNA_PROD_AHORRO APA
        WHERE
            CDGCL = :cliente
            AND (
                SELECT
                    COUNT(MA.CDG_CONTRATO)
                FROM
                    MOVIMIENTOS_AHORRO MA
                WHERE
                    MA.CDG_TIPO_PAGO = 2
                    AND MA.CDG_CONTRATO = APA.CONTRATO
            ) > 0
        sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryOne($queryValidacion, ['cliente' => $datos['credito']]);
            if ($res) return self::Responde(false, "El cliente ya cuenta con un contrato de ahorro");

            $noContrato = $datos['credito'] . date('Ymd');

            $query = <<<sql
            INSERT INTO ASIGNA_PROD_AHORRO
                (CONTRATO, CDGCL, FECHA_APERTURA, CDGPR_PRIORITARIO, ESTATUS, SALDO, TASA, CDGCO, CDGPE_COMISIONA, CDGPE_REGISTRO)
            VALUES
                (:contrato, :cliente, TO_TIMESTAMP(:fecha_apertura, 'DD/MM/YYYY HH24:MI:SS'), :producto, 'A', 0, :tasa, :sucursal, :ejecutivo_comisiona, :ejecutivo_registro)
            sql;

            $queryBen = <<<sql
            INSERT INTO BENEFICIARIOS_AHORRO
                (CDG_CONTRATO, NOMBRE, CDGCT_PARENTESCO, ESTATUS, FECHA_MODIFICACION, PORCENTAJE)
            VALUES
                (:contrato, :nombre, :parentesco, 'A', SYSDATE, :porcentaje)
            sql;

            $fecha = DateTime::createFromFormat('Y-m-d', $datos['fecha']);
            $fecha = $fecha !== false && $fecha->format('Y-m-d') === $datos['fecha'] ? $fecha->format('d-m-Y') : $datos['fecha'];

            $datosInsert = [
                [
                    'contrato' => $noContrato,
                    'cliente' => $datos['credito'],
                    'fecha_apertura' => $fecha,
                    'tasa' => $datos['tasa'],
                    'sucursal' => $datos['sucursal'],
                    'ejecutivo_comisiona' => $datos['ejecutivo_comision'],
                    'ejecutivo_registro' => $datos['ejecutivo'],
                    'producto' => $datos['tipo_ahorro']
                ]
            ];

            $inserts = [
                $query
            ];

            if ($datos['beneficiario_1']) {
                $datosInsert[] = [
                    'contrato' => $noContrato,
                    'nombre' => $datos['beneficiario_1'],
                    'parentesco' => $datos['parentesco_1'],
                    'porcentaje' => $datos['porcentaje_1']
                ];
                $inserts[] = $queryBen;
            }

            if ($datos['beneficiario_2']) {
                $datosInsert[] = [
                    'contrato' => $noContrato,
                    'nombre' => $datos['beneficiario_2'],
                    'parentesco' => $datos['parentesco_2'],
                    'porcentaje' => $datos['porcentaje_2']
                ];
                $inserts[] = $queryBen;
            }

            if ($datos['beneficiario_3']) {
                $datosInsert[] = [
                    'contrato' => $noContrato,
                    'nombre' => $datos['beneficiario_3'],
                    'parentesco' => $datos['parentesco_3'],
                    'porcentaje' => $datos['porcentaje_3']
                ];
                $inserts[] = $queryBen;
            }


            try {
                $mysqli = new Database();
                $res = $mysqli->insertaMultiple($inserts, $datosInsert);
                if ($res) {
                    LogTransaccionesAhorro::LogTransacciones($inserts, $datosInsert, $_SESSION['cdgco_ahorro'], $_SESSION['usuario'], $noContrato, "Registro de nuevo contrato ahorro corriente");
                    return self::Responde(true, "Contrato de ahorro registrado correctamente.", ['contrato' => $noContrato]);
                }
                return self::Responde(false, "Ocurrió un error al registrar el contrato de ahorro.");
            } catch (Exception $e) {
                return self::Responde(false, "Ocurrió un error al registrar el contrato de ahorro.", null, $e->getMessage());
            }
        } catch (Exception $e) {
            return self::Responde(false, "Ocurrió un error al validar si el cliente ya cuenta con un contrato de ahorro.", null, $e->getMessage());
        }
    }

    public static function AddPagoApertura($datos)
    {
        if ($datos['monto'] == 0) return self::Responde(false, "El monto de apertura no puede ser de 0.");
        if ($datos['monto'] < $datos['sma']) return self::Responde(false, "El monto mínimo de apertura no puede ser menor a " . $datos['sma'] . ".");

        $query = [
            self::GetQueryTicket(),
            self::GetQueryMovimientoAhorro(),
            self::GetQueryMovimientoAhorro(),
            self::GetQueryActualizaSaldoSucursal()
        ];

        $validacion = [
            'query' => self::GetQueryValidaAhorro(),
            'datos' => ['contrato' => $datos['contrato']],
            'funcion' => [CajaAhorro::class, 'ValidaMovimientoAhorro']
        ];

        $datosInsert = [
            [
                'contrato' => $datos['contrato'],
                'monto' => $datos['monto'],
                'ejecutivo' => $datos['ejecutivo'],
                'sucursal' => $datos['sucursal']
            ],
            [
                'tipo_pago' => '1',
                'contrato' => $datos['contrato'],
                'monto' => $datos['monto'],
                'movimiento' => '1',
                'cliente' => $datos['codigo_cl'],
                'ejecutivo' => $datos['ejecutivo'],
                'sucursal' => $datos['sucursal'],
                'apoderado' => null
            ],
            [
                'tipo_pago' => '2',
                'contrato' => $datos['contrato'],
                'monto' => $datos['inscripcion'],
                'movimiento' => '0',
                'cliente' => $datos['codigo_cl'],
                'ejecutivo' => $datos['ejecutivo'],
                'sucursal' => $datos['sucursal'],
                'apoderado' => null
            ],
            [
                'monto' => $datos['monto'],
                'sucursal' => $datos['sucursal']
            ]
        ];

        try {
            $mysqli = new Database();
            $res = $mysqli->insertaMultiple($query, $datosInsert, $validacion);

            if ($res) {
                LogTransaccionesAhorro::LogTransacciones($query, $datosInsert, $_SESSION['cdgco_ahorro'], $_SESSION['usuario'], $datos['contrato'], "Depósito de apertura de cuenta de ahorro corriente");
                $ticket = self::RecuperaTicket($datos['contrato']);
                return self::Responde(true, "Pago de apertura registrado correctamente.", ['ticket' => $ticket['CODIGO']]);
            }
            return self::Responde(false, "Ocurrió un error al registrar el pago de apertura.");
        } catch (Exception $e) {
            return self::Responde(false, "Ocurrió un error al registrar el pago de apertura.", null, $e->getMessage());
        }
    }

    public static function GetLayoutPagosCredito($datos)
    {
        $query = <<<SQL
        	SELECT
                TO_CHAR(FECHA, 'DD/MM/YY') FECHA,
                CASE
                    WHEN (PGD.TIPO = 'P' OR PGD.TIPO = 'X') THEN 'P' || PRN.CDGNS || PRN.CDGTPC || FN_DV('P' || PRN.CDGNS || PRN.CDGTPC)
                    WHEN PGD.TIPO = 'G' THEN '0' || PRN.CDGNS || PRN.CDGTPC || FN_DV('0' || PRN.CDGNS || PRN.CDGTPC)
                    ELSE 'NO IDENTIFICADO'
                END REFERENCIA,
                PGD.MONTO,
                'MN' MONEDA
            FROM
                PAGOSDIA PGD, PRN
            WHERE
                PGD.CDGEM = PRN.CDGEM
                AND PGD.CDGNS = PRN.CDGNS
                AND PGD.CICLO = PRN.CICLO
                AND PGD.CDGEM = 'EMPFIN'
                AND PGD.ESTATUS = 'A'
                AND PGD.TIPO IN ('P','G', 'X')
                AND PGD.FECHA BETWEEN TO_DATE(:fechaI, 'YYYY-MM-DD') AND TO_DATE(:fechaF, 'YYYY-MM-DD') 
            ORDER BY
                PGD.FECHA
        SQL;

        $parametros = [
            'fechaI' => $datos['fechaI'],
            'fechaF' => $datos['fechaF']
        ];

        try {
            $db = new Database();
            $r = $db->queryAll($query, $parametros);
            if (count($r) > 0) return self::Responde(true, "Consulta realizada correctamente.", $r);
            return self::Responde(false, "No se encontraron datos para el rango de fechas seleccionado.", $r);
        } catch (\Exception $e) {
            return self::Responde(false, "Ocurrió un error al consultar los datos.", null, $e->getMessage());
        }
    }

    public static function RegistraOperacion($datos)
    {
        $esDeposito = $datos['esDeposito'] === true || $datos['esDeposito'] === 'true';

        $query = [
            self::GetQueryTicket(),
            self::GetQueryMovimientoAhorro(),
            self::GetQueryActualizaSaldoSucursal(!$esDeposito)
        ];

        $datosInsert = [
            [
                'contrato' => $datos['contrato'],
                'monto' => $datos['montoOperacion'],
                'ejecutivo' => $datos['ejecutivo'],
                'sucursal' => $datos['sucursal']
            ],
            [
                'tipo_pago' => $esDeposito ? '3' : '4',
                'contrato' => $datos['contrato'],
                'monto' => $datos['montoOperacion'],
                'movimiento' => $esDeposito ? '1' : '0',
                'cliente' => $datos['cliente'],
                'ejecutivo' => $datos['ejecutivo'],
                'sucursal' => $datos['sucursal'],
                'apoderado' => $datos['apoderado']
            ],
            [
                'monto' => $datos['montoOperacion'],
                'sucursal' => $datos['sucursal']
            ]
        ];

        $tipoMov = $esDeposito ? "depósito" : "retiro";

        $validacion = [
            'query' => "SELECT SALDO FROM SUC_ESTADO_AHORRO WHERE CDG_SUCURSAL = :sucursal",
            'datos' => ['sucursal' => $datos['sucursal']],
            'funcion' => [CajaAhorro::class, 'ValidarSaldoSucursal']
        ];

        try {
            $mysqli = new Database();
            $res = $mysqli->insertaMultiple($query, $datosInsert, $validacion);
            if ($res) {
                LogTransaccionesAhorro::LogTransacciones($query, $datosInsert, $_SESSION['cdgco_ahorro'], $_SESSION['usuario'], $datos['contrato'], "Registro de " . $tipoMov . " en " . $datos['producto']);
                $ticket = self::RecuperaTicket($datos['contrato']);
                return self::Responde(true, "El " . $tipoMov . " fue registrado correctamente.", ['ticket' => $ticket['CODIGO']]);
            }
            return self::Responde(false, "Ocurrió un error al registrar el " . $tipoMov . ".");
        } catch (Exception $e) {
            return self::Responde(false, "Ocurrió un error al registrar el " . $tipoMov  . ".", null, $e->getMessage());
        }
    }

    public static function ValidaMovimientoAhorro($validar)
    {
        $resultado = [
            'success' => true,
            'mensaje' => ""
        ];

        if (count($validar) > 0) return $resultado;

        $resultado['success'] = false;
        $resultado['mensaje'] = "Se detecto diferencia entre el registro del ticket y los movimiento de ahorro.";
        return $resultado;
    }

    public static function GetQueryTicket()
    {
        return <<<sql
        INSERT INTO TICKETS_AHORRO
            (CODIGO, FECHA, CDG_CONTRATO, MONTO, CDGPE, CDG_SUCURSAL)
        VALUES
            ((SELECT NVL(MAX(TO_NUMBER(CODIGO)),0) FROM TICKETS_AHORRO) + 1, SYSDATE, :contrato, :monto, :ejecutivo, :sucursal)
        sql;
    }

    public static function GetQueryMovimientoAhorro()
    {
        return <<<SQL
        INSERT INTO
            MOVIMIENTOS_AHORRO (
                CODIGO,
                FECHA_MOV,
                CDG_TIPO_PAGO,
                CDG_CONTRATO,
                MONTO,
                MOVIMIENTO,
                DESCRIPCION,
                CDG_TICKET,
                FECHA_VALOR,
                CDG_RETIRO,
                CDGCO,
                CDGCL,
                CDGPE,
                APODERADO
            )
        VALUES
            (
                (
                    SELECT
                        NVL(MAX(TO_NUMBER(CODIGO)), 0)
                    FROM
                        MOVIMIENTOS_AHORRO
                ) + 1,
                SYSDATE,
                :tipo_pago,
                :contrato,
                :monto,
                :movimiento,
                'ALGUNA_DESCRIPCION',
                (
                    SELECT
                        MAX(TO_NUMBER(CODIGO)) AS CODIGO
                    FROM
                        TICKETS_AHORRO
                    WHERE
                        CDG_CONTRATO = :contrato
                ),
                SYSDATE,
                (
                    SELECT
                        CASE
                            :tipo_pago
                            WHEN '6' THEN MAX(TO_NUMBER(ID_SOL_RETIRO_AHORRO))
                            WHEN '7' THEN MAX(TO_NUMBER(ID_SOL_RETIRO_AHORRO))
                            ELSE NULL
                        END
                    FROM
                        SOLICITUD_RETIRO_AHORRO
                    WHERE
                        CONTRATO = :contrato
                ),
                :sucursal,
                :cliente,
                :ejecutivo,
                :apoderado
            )
        SQL;
    }

    public static function GetQueryValidaAhorro()
    {
        return <<<sql
        SELECT
            *
        FROM
            (
            SELECT
                T.CODIGO AS TC,
                T.MONTO AS TM,
                MA.CODIGO AS MC,
                MA.MONTO AS MM,
                NVL(T.MONTO,0) - NVL(MA.MONTO,0) AS DIFERENCIA
            FROM
                TICKETS_AHORRO T
            FULL JOIN
                (
                SELECT
                    M.CDG_TICKET AS CODIGO,
                    SUM(CASE M.MOVIMIENTO
                        WHEN '1' THEN M.MONTO
                        ELSE -M.MONTO
                    END) AS MONTO
                FROM
                    MOVIMIENTOS_AHORRO M
                GROUP BY
                    M.CDG_TICKET
                ) MA
            ON T.CODIGO = MA.CODIGO
            WHERE
                T.CDG_CONTRATO = :contrato
            )
        WHERE
            DIFERENCIA != 0
        sql;
    }

    public static function GetQueryActualizaSaldoSucursal($cargo = false)
    {
        $tipo = $cargo ? '-' : '+';
        return <<<sql
        UPDATE
            SUC_ESTADO_AHORRO
        SET
            SALDO = SALDO $tipo :monto
        WHERE
            CDG_SUCURSAL = :sucursal
        sql;
    }

    public static function ValidarSaldoSucursal($datos)
    {
        $resultado = [
            'success' => true,
            'mensaje' => ""
        ];

        if ($datos['SALDO'] >= 0) return $resultado;

        $resultado['success'] = false;
        $resultado['mensaje'] = "La sucursal no cuenta con saldo suficiente para realizar la operación.";
        return $resultado;
    }

    public static function RecuperaTicket($contrato)
    {
        $queryTicket = <<<sql
        SELECT
            MAX(TO_NUMBER(CODIGO)) AS CODIGO
        FROM
            TICKETS_AHORRO
        WHERE
            CDG_CONTRATO = '$contrato'
        sql;

        try {
            $mysqli = new Database();
            return $mysqli->queryOne($queryTicket);
        } catch (Exception $e) {
            return 0;
        }
    }

    public static function DatosTicket($ticket)
    {
        $query = <<< sql
        SELECT
            TO_CHAR(T.FECHA, 'dd/mm/yyyy HH24:MI:SS') AS FECHA,
            CONCATENA_NOMBRE(CL.NOMBRE1, CL.NOMBRE2, CL.PRIMAPE, CL.SEGAPE) AS NOMBRE_CLIENTE,
            T.CDG_SUCURSAL,
            (
                SELECT
                    NOMBRE
                FROM
                    CO
                WHERE
                    CODIGO = T.CDG_SUCURSAL
            ) AS NOMBRE_SUCURSAL,
            CL.CODIGO,
            APA.CONTRATO,
            T.MONTO,
            (
                SELECT
                    SUM(
                        CASE MA.MOVIMIENTO
                            WHEN '0' THEN 
                                CASE MA.CDG_TIPO_PAGO
                                    WHEN '6' THEN 0
                                    WHEN '7' THEN 0
                                    ELSE -MA.MONTO
                                END
                            ELSE 
                                CASE MA.CDG_TIPO_PAGO
                                    WHEN '8' THEN 0
                                    WHEN '9' THEN 0
                                    ELSE MA.MONTO
                                END
                        END 
                    )
                FROM
                    MOVIMIENTOS_AHORRO MA
                WHERE
                    TO_NUMBER(MA.CDG_TICKET) < T.CODIGO
                    AND T.CDG_CONTRATO = MA.CDG_CONTRATO
            ) AS SALDO_ANTERIOR,
            (
                SELECT
                    SUM(MA.MONTO)
                FROM
                    MOVIMIENTOS_AHORRO MA
                WHERE
                    TO_NUMBER(MA.CDG_TICKET) = T.CODIGO
                    AND MA.CDG_TIPO_PAGO = 2
            ) AS COMISION,
            (
                NVL((SELECT
                    SUM(
                        CASE MA.MOVIMIENTO
                            WHEN '0' THEN 
                                CASE MA.CDG_TIPO_PAGO
                                    WHEN '6' THEN 0
                                    WHEN '7' THEN 0
                                    ELSE -MA.MONTO
                                END
                            ELSE 
                                CASE MA.CDG_TIPO_PAGO
                                    WHEN '8' THEN 0
                                    WHEN '9' THEN 0
                                    ELSE MA.MONTO
                                END
                        END 
                    )
                FROM
                    MOVIMIENTOS_AHORRO MA
                WHERE
                    TO_NUMBER(MA.CDG_TICKET) = T.CODIGO
                    AND T.CDG_CONTRATO = MA.CDG_CONTRATO), 0)
                +
                NVL((SELECT
                    SUM(
                        CASE MA.MOVIMIENTO
                            WHEN '0' THEN 
                                CASE MA.CDG_TIPO_PAGO
                                    WHEN '6' THEN 0
                                    WHEN '7' THEN 0
                                    ELSE -MA.MONTO
                                END
                            ELSE 
                            CASE MA.CDG_TIPO_PAGO
                                WHEN '8' THEN 0
                                WHEN '9' THEN 0
                                ELSE MA.MONTO
                            END
                        END 
                    )
                FROM
                    MOVIMIENTOS_AHORRO MA
                WHERE
                    TO_NUMBER(MA.CDG_TICKET) < T.CODIGO
                    AND T.CDG_CONTRATO = MA.CDG_CONTRATO), 0)
            ) AS SALDO_NUEVO,
            (
                SELECT
                    CASE MA.CDG_TIPO_PAGO
                        WHEN '5' THEN 'ENVIÓ A INVERSIÓN'
                        ELSE CASE MA.MOVIMIENTO
                            WHEN '0' THEN 'RET. DE CTA. AHORRO'
                            ELSE 'DEP. A CTA. AHORRO'
                        END
                    END
                FROM
                    MOVIMIENTOS_AHORRO MA
                WHERE
                    TO_NUMBER(MA.CDG_TICKET) = T.CODIGO
                    AND MA.CDG_TIPO_PAGO != 2
            ) AS ES_DEPOSITO,
            (
                SELECT
                    CASE MA.CDG_TIPO_PAGO
                        WHEN '5' THEN 'TRANSFERENCIA'
                        WHEN '6' THEN 'EN TRANSITO'
                        WHEN '7' THEN 'EN TRANSITO'
                        WHEN '8' THEN 'EN TRANSITO'
                        WHEN '9' THEN 'EN TRANSITO'
                        ELSE 'EFECTIVO'
                    END
                FROM
                    MOVIMIENTOS_AHORRO MA
                WHERE
                    TO_NUMBER(MA.CDG_TICKET) = T.CODIGO
                    AND MA.CDG_TIPO_PAGO != 2
            ) AS METODO,
            (
                SELECT
                    CASE MA.CDG_TIPO_PAGO
                        WHEN '5' THEN 'APERTURADO POR'
                        WHEN '6' THEN 'SOLICITUD RETIRÓ'
                        WHEN '7' THEN 'SOLICITUD RETIRÓ'
                        WHEN '8' THEN 'CANCELACIÓN RETIRÓ'
                        WHEN '9' THEN 'CANCELACIÓN RETIRÓ'
                        ELSE CASE MOVIMIENTO
                            WHEN '0' THEN 'ENTREGAMOS'
                            ELSE 'RECIBIMOS'
                        END
                    END
                FROM
                    MOVIMIENTOS_AHORRO MA
                WHERE
                    TO_NUMBER(MA.CDG_TICKET) = T.CODIGO
                    AND MA.CDG_TIPO_PAGO != 2
            ) AS ENTREGA,
            (
                SELECT
                    CASE MA.CDG_TIPO_PAGO
                        WHEN '5' THEN 'Atendió'
                        WHEN '6' THEN 'Atendió'
                        WHEN '7' THEN 'Atendió'
                        WHEN '8' THEN 'Atendió'
                        WHEN '9' THEN 'Atendió'
                        ELSE CASE MA.MOVIMIENTO
                            WHEN '0' THEN 'Entrego'
                            ELSE 'Recibió'
                        END
                    END
                FROM
                    MOVIMIENTOS_AHORRO MA
                WHERE
                    TO_NUMBER(MA.CDG_TICKET) = T.CODIGO
                    AND MA.CDG_TIPO_PAGO != 2
            ) AS RECIBIO,
            (
                SELECT
                    CASE MA.CDG_TIPO_PAGO
                        WHEN '5' THEN 'INVERSIÓN'
                        WHEN '6' THEN 'RETIRO EXPRESS'
                        WHEN '7' THEN 'RETIRO PROGRAMADO'
                        WHEN '8' THEN 'CANCELACIÓN'
                        WHEN '9' THEN 'CANCELACIÓN'
                        ELSE CASE MA.MOVIMIENTO
                            WHEN '0' THEN 'RETIRO'
                            ELSE 'DEPÓSITO'
                        END
                    END
                FROM
                    MOVIMIENTOS_AHORRO MA
                WHERE
                    TO_NUMBER(MA.CDG_TICKET) = T.CODIGO
                    AND MA.CDG_TIPO_PAGO != 2
            ) AS COMPROBANTE,
            (
                SELECT
                    DISTINCT CONCATENA_NOMBRE(PE.NOMBRE1, PE.NOMBRE2, PE.PRIMAPE, PE.SEGAPE) AS NOMBRE
                FROM
                    PE
                WHERE
                    PE.CODIGO = T.CDGPE
            ) AS NOM_EJECUTIVO,
            (
                SELECT
                    CASE MA.CDG_TIPO_PAGO
                        WHEN '5' THEN 'APERTURA CUENTA DE INVERSIÓN'
                        ELSE 
                            (
                                SELECT
                                    DESCRIPCION
                                FROM
                                    PR_PRIORITARIO
                                WHERE
                                    CODIGO = APA.CDGPR_PRIORITARIO
                            )
                        END
                    END
                FROM
                    MOVIMIENTOS_AHORRO MA
                WHERE
                    TO_NUMBER(MA.CDG_TICKET) = T.CODIGO
                    AND MA.CDG_TIPO_PAGO != 2
            ) AS PRODUCTO,
            T.CDGPE AS COD_EJECUTIVO,
            (
                SELECT
                    MA.CDG_TIPO_PAGO
                FROM
                    MOVIMIENTOS_AHORRO MA
                WHERE
                    TO_NUMBER(MA.CDG_TICKET) = T.CODIGO
                    AND MA.CDG_TIPO_PAGO != 2
            ) AS TIPO_PAGO
        FROM
            TICKETS_AHORRO T,
            ASIGNA_PROD_AHORRO APA,
            CL
        WHERE
            CL.CODIGO = APA.CDGCL
            AND T.CDG_CONTRATO = APA.CONTRATO
            AND T.CODIGO = '$ticket'
        sql;

        try {
            $mysqli = new Database();
            return $mysqli->queryOne($query);
        } catch (Exception $e) {
            return 0;
        }
    }

    public static function BuscaClienteNvoContratoPQ($datos)
    {
        $queryValidacion = <<<sql
        SELECT
            CONCATENA_NOMBRE(CL.NOMBRE1, CL.NOMBRE2, CL.PRIMAPE, CL.SEGAPE) AS NOMBRE,
            CL.CURP,
            TO_CHAR(CL.REGISTRO, 'DD/MM/YYYY') AS FECHA_REGISTRO,
            UPPER((CL.CALLE
            || ', '
            || COL.NOMBRE
            || ', '
            || LO.NOMBRE
            || ', '
            || MU.NOMBRE
            || ', '
                || EF.NOMBRE)) AS DIRECCION,
            (
                SELECT
                    COUNT(*)
                FROM
                    ASIGNA_PROD_AHORRO
                WHERE
                    CDGCL = CL.CODIGO
                    AND CDGPR_PRIORITARIO = 2
            ) AS HIJAS,
            (
                SELECT
                    CASE 
                        WHEN COUNT(MA.CDG_CONTRATO) > 0 THEN 1
                        ELSE 0
                    END 
                FROM
                    MOVIMIENTOS_AHORRO MA
                WHERE
                    MA.CDG_TIPO_PAGO = 2
                    AND MA.CDG_CONTRATO = (SELECT CONTRATO FROM ASIGNA_PROD_AHORRO WHERE CDGCL = CL.CODIGO AND CDGPR_PRIORITARIO != 2)
            ) AS CONTRATO_COMPLETO,
            (
                SELECT
                    COUNT(APA.CONTRATO)
                FROM
                    ASIGNA_PROD_AHORRO APA
                WHERE
                    APA.CDGCL = CL.CODIGO
                    AND CDGPR_PRIORITARIO != 2
            ) AS NO_CONTRATOS
        FROM
            CL,
            COL,
            LO,
            MU,
            EF
        WHERE
            EF.CODIGO = CL.CDGEF
            AND MU.CODIGO = CL.CDGMU
            AND LO.CODIGO = CL.CDGLO
            AND COL.CODIGO = CL.CDGCOL
            AND EF.CODIGO = MU.CDGEF
            AND EF.CODIGO = LO.CDGEF
            AND EF.CODIGO = COL.CDGEF
            AND MU.CODIGO = LO.CDGMU
            AND MU.CODIGO = COL.CDGMU
            AND LO.CODIGO = COL.CDGLO
            AND CL.CODIGO = '{$datos['cliente']}'
        sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryOne($queryValidacion);
            if (!$res) return self::Responde(false, "No se encontraron datos para el cliente {$datos['cliente']}.");
            if ($res['NO_CONTRATOS'] == 0) return self::Responde(false, "El cliente {$datos['cliente']} no cuenta con un contrato de ahorro.", $res);
            //if ($res['NO_CONTRATOS'] >= 1 && $res['CONTRATO_COMPLETO'] == 0) return self::Responde(false, "El cliente {$datos['cliente']} no ha concluido el proceso de apertura de su cuenta de ahorro.", $res);
            return self::Responde(true, "Consulta realizada correctamente.", $res);
        } catch (Exception $e) {
            return self::Responde(false, "Ocurrió un error al consultar los datos del cliente.", null, $e->getMessage());
        }
    }

    public static function AgregaContratoAhorroPQ($datos)
    {
        $queryValidacion = <<<sql
        SELECT
            *
        FROM
            CL_PQS
        WHERE
            CDGCL = :cliente
        sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryAll($queryValidacion, ['cliente' => $datos['credito']]);
            if ($res) {
                foreach ($res as $key => $value) {
                    if ($value['CURP'] == $datos['curp']) {
                        return self::Responde(false, "El cliente (Peque), ya tiene registrada una cuenta de ahorro con el contrato: " . $value['CDG_CONTRATO'] . ".");
                    }
                }
            }

            $noContrato = $datos['credito'] . date_format(date_create($datos['fecha']), 'Ymd') . str_pad((count($res) + 1), 2, '0', STR_PAD_LEFT);

            $queryAPA = <<<sql
            INSERT INTO ASIGNA_PROD_AHORRO
                (CONTRATO, CDGCL, FECHA_APERTURA, CDGPR_PRIORITARIO, ESTATUS, SALDO, TASA, CDGCO, CDGPE_REGISTRO)
            VALUES
                (:contrato, :cliente, SYSDATE, '2', 'A', 0, :tasa, :sucursal, :ejecutivo)
            sql;

            $queryCL_PQ = <<<sql
            INSERT INTO CL_PQS
                (CDGCL,CDG_CONTRATO,NOMBRE1,NOMBRE2,APELLIDO1,APELLIDO2,FECHA_NACIMIENTO,SEXO,CURP,PAIS,ENTIDAD,FECHA_REGISTRO,FECHA_MODIFICACION,ESTATUS,CDGCO,CDGPE_REGISTRO, tasa)
            VALUES
                (:cliente, :contrato, :nombre1, :nombre2, :apellido1, :apellido2, TO_DATE(:fecha_nacimiento, 'DD-MM-YYYY'), :sexo, :curp, :pais, :entidad, SYSDATE, SYSDATE, 'A', :sucursal, :ejecutivo, :tasa)
            sql;

            $fecha = DateTime::createFromFormat('Y-m-d', $datos['fecha_nac']);
            $fecha = $fecha !== false && $fecha->format('Y-m-d') === $datos['fecha_nac'] ? $fecha->format('d-m-Y') : $datos['fecha_nac'];
            $sexo = $datos['sexo'] === true || $datos['sexo'] === 'true';

            $parametros = [
                [
                    'contrato' => $noContrato,
                    'cliente' => $datos['credito'],
                    'tasa' => $datos['tasa'],
                    'sucursal' => $datos['sucursal'],
                    'ejecutivo' => $datos['ejecutivo'],
                ],
                [
                    'cliente' => $datos['credito'],
                    'contrato' => $noContrato,
                    'nombre1' => $datos['nombre1'],
                    'nombre2' => $datos['nombre2'],
                    'apellido1' => $datos['apellido1'],
                    'apellido2' => $datos['apellido2'],
                    'fecha_nacimiento' => $fecha,
                    'sexo' => $sexo ? 'H' : 'M',
                    'curp' => $datos['curp'],
                    'pais' => $datos['pais'],
                    'entidad' => $datos['ciudad'],
                    'sucursal' => $datos['sucursal'],
                    'ejecutivo' => $datos['ejecutivo'],
                    'tasa' => $datos['tasa']
                ]
            ];

            $inserts = [
                $queryAPA,
                $queryCL_PQ
            ];

            try {
                $mysqli = new Database();
                $res = $mysqli->insertaMultiple($inserts, $parametros);
                LogTransaccionesAhorro::LogTransacciones($inserts, $parametros, $_SESSION['cdgco_ahorro'], $_SESSION['usuario'], $noContrato, "Registro de nueva cuenta de ahorro Peque");
                if ($res) return self::Responde(true, "Contrato de ahorro registrado correctamente.", ['contrato' => $noContrato]);
                return self::Responde(false, "Ocurrió un error al registrar el contrato de ahorro.");
            } catch (Exception $e) {
                return self::Responde(false, "Ocurrió un error al registrar el contrato de ahorro.", null, $e->getMessage());
            }
        } catch (Exception $e) {
            return self::Responde(false, "Ocurrió un error al validar si el cliente ya cuenta con un contrato de ahorro.", null, $e->getMessage());
        }
    }

    public static function BuscaClienteContratoPQ($datos)
    {
        $query = <<<sql
        SELECT
            CONCATENA_NOMBRE(CL_PQS.NOMBRE1, CL_PQS.NOMBRE2, CL_PQS.APELLIDO1, CL_PQS.APELLIDO2) AS NOMBRE,
            CL_PQS.CURP,
            CL_PQS.CDG_CONTRATO,
            CL_PQS.CDGCL,
            CL_PQS.CDGCO AS SUCURSAL,
            (SELECT NOMBRE FROM CO WHERE CODIGO = CL_PQS.CDGCO) AS NOMBRE_SUCURSAL,
            (SELECT COUNT(*) FROM HUELLAS WHERE CLIENTE = CL_PQS.CDGCL) AS HUELLAS,
            NVL((
                SELECT
                    SALDO_REAL
                FROM
                    ASIGNA_PROD_AHORRO APA
                WHERE
                    APA.CONTRATO = CL_PQS.CDG_CONTRATO
            ),0) AS SALDO
        FROM
            CL_PQS
        WHERE
            CL_PQS.CDGCL = '{$datos['cliente']}'
        sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryAll($query);
            if (count($res) === 0) {
                $qryVal = <<<sql
                SELECT
                    CL.CODIGO,
                    (
                        SELECT
                            CASE 
                                WHEN COUNT(MA.CDG_CONTRATO) > 0 THEN 1
                                ELSE 0
                            END 
                        FROM
                            MOVIMIENTOS_AHORRO MA
                        WHERE
                            MA.CDG_TIPO_PAGO = 2
                            AND MA.CDG_CONTRATO = (SELECT CONTRATO FROM ASIGNA_PROD_AHORRO WHERE CDGCL = CL.CODIGO AND CDGPR_PRIORITARIO > 2)
                    ) AS CONTRATO_COMPLETO,
                    (
                        SELECT
                            COUNT(APA.CONTRATO)
                        FROM
                            ASIGNA_PROD_AHORRO APA
                        WHERE
                            APA.CDGCL = CL.CODIGO
                            AND CDGPR_PRIORITARIO > 2
                    ) AS NO_CONTRATOS
                FROM
                    CL
                WHERE
                    CL.CODIGO = '{$datos['cliente']}'
                sql;

                $res2 = $mysqli->queryOne($qryVal);
                if (!$res2) return self::Responde(false, "No se encontraron datos para el cliente {$datos['cliente']}.");
                if ($res2['NO_CONTRATOS'] == 0) return self::Responde(false, "El cliente {$datos['cliente']} no cuenta con un contrato de ahorro.", $res2);
                if ($res2['NO_CONTRATOS'] >= 1 && $res2['CONTRATO_COMPLETO'] == 0) return self::Responde(false, "El cliente {$datos['cliente']} no ha concluido el proceso de apertura de su cuenta de ahorro.", $res2);
                return self::Responde(false, "El cliente {$datos['cliente']} no cuenta con cuentas de ahorro Peques.", $res2);
            }
            return self::Responde(true, "Consulta realizada correctamente.", $res);
        } catch (Exception $e) {
            return self::Responde(false, "Ocurrió un error al consultar los datos del cliente.", null, $e->getMessage());
        }
    }

    public static function RegistraInversion($datos)
    {
        $qryInversion = <<<SQL
        INSERT INTO CUENTA_INVERSION
            (CDG_CONTRATO, CDG_TASA, MONTO_INVERSION, FECHA_APERTURA, ESTATUS, ACCION, CDG_USUARIO, CODIGO)
        VALUES
            (:contrato, :tasa, :monto, SYSDATE, 'A', :accion, :usuario, (SELECT NVL(MAX(TO_NUMBER(CODIGO)),0) FROM CUENTA_INVERSION) + 1)
        SQL;

        $query = [
            $qryInversion,
            self::GetQueryTicket(),
            self::GetQueryMovimientoAhorro()
        ];

        $datosInsert = [
            [
                'contrato' => $datos['contrato'],
                'monto' => $datos['monto'],
                'tasa' => $datos['tasa'],
                'accion' => $datos['renovacion'],
                'usuario' => $datos['ejecutivo']
            ],
            [
                'contrato' => $datos['contrato'],
                'monto' => $datos['montoOperacion'],
                'ejecutivo' => $datos['ejecutivo'],
                'sucursal' => $datos['sucursal']
            ],
            [
                'tipo_pago' => '5',
                'contrato' => $datos['contrato'],
                'monto' => $datos['montoOperacion'],
                'movimiento' => '0',
                'cliente' => $datos['cliente'],
                'ejecutivo' => $datos['ejecutivo'],
                'sucursal' => $datos['sucursal'],
                'apoderado' => null
            ]
        ];

        $qryCodInv = <<<SQL
            UPDATE MOVIMIENTOS_AHORRO
            SET CDG_INVERSION = :codigo
            WHERE CDG_TICKET = :ticket
        SQL;

        try {
            $mysqli = new Database();
            $res = $mysqli->insertaMultiple($query, $datosInsert);
            if ($res) {
                LogTransaccionesAhorro::LogTransacciones($query, $datosInsert, $_SESSION['cdgco_ahorro'], $_SESSION['usuario'], $datos['contrato'], "Registro de inversión de cuenta ahorro corriente");
                $ticket = self::RecuperaTicket($datos['contrato']);
                $codg = self::RecuperaCodigoInversion($datos['contrato']);
                $mysqli->insertar($qryCodInv, ['codigo' => $codg['CODIGO'], 'ticket' => $ticket['CODIGO']]);
                return self::Responde(true, "Inversión registrada correctamente.", ['ticket' => $ticket['CODIGO'], 'codigo' => $codg['CODIGO']]);
            }
            return self::Responde(false, "Ocurrió un error al registrar la inversión.");
        } catch (Exception $e) {
            return self::Responde(false, "Ocurrió un error al registrar la inversión.", null, $e->getMessage());
        }
    }

    public static function GetInversion($datos)
    {
        $qry = <<<SQL
            SELECT
                CI.CODIGO AS CODIGO,
                TI.CODIGO AS CODIGO_TASA,
                TO_CHAR(CI.FECHA_APERTURA, 'DD/MM/YYYY') AS F_APERTURA,
                TO_CHAR(CI.FECHA_VENCIMIENTO, 'DD/MM/YYYY') AS F_VENCIMIENTO,
                TO_CHAR(CI.MODIFICACION, 'DD/MM/YYYY') AS F_ACTUALIZACION,
                CI.MONTO_INVERSION AS MONTO,
                TI.TASA AS TASA,
                PI.PLAZO AS PLAZO,
                PI.PERIODICIDAD AS PERIODICIDAD,
                NVL(DDI.RENDIMIENTO, 0) AS RENDIMIENTO
            FROM    
                CUENTA_INVERSION CI
                JOIN TASA_INVERSION TI ON CI.CDG_TASA = TI.CODIGO
                JOIN PLAZO_INVERSION PI ON TI.CDG_PLAZO = PI.CODIGO
                LEFT JOIN (
                    SELECT
                        CONTRATO,
                        SUM(DEVENGO) AS RENDIMIENTO
                    FROM
                        DEVENGO_DIARIO_INVERSION DDI
                    WHERE
                        DDI.CONTRATO = '{$datos['contrato']}'
                        AND DDI.CONTABILIZADO IS NULL
                    GROUP BY 
                        CONTRATO
                ) DDI ON CI.CDG_CONTRATO = DDI.CONTRATO
            WHERE
                CI.CDG_CONTRATO = '{$datos['contrato']}'
                AND CI.ESTATUS = 'A'
        SQL;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryOne($qry);
            if (!$res) return self::Responde(false, "No se encontraron datos para la inversión.");
            return self::Responde(true, "Consulta realizada correctamente.", $res);
        } catch (Exception $e) {
            return self::Responde(false, "Ocurrió un error al consultar los datos de la inversión.", null, $e->getMessage());
        }
    }

    public static function ActualizaInversion($datos)
    {
        $qryInversion = <<<SQL
            UPDATE CUENTA_INVERSION
            SET
                MONTO_INVERSION = MONTO_INVERSION + :monto,
                CDG_TASA = :tasa,
                MODIFICACION = SYSDATE,
                FECHA_VENCIMIENTO = CALCULAR_FECHA_LIQUIDACION(:tasa, SYSDATE)
            WHERE
                CDG_CONTRATO = :contrato
                AND CODIGO = :codigo
                AND ESTATUS = 'A'
        SQL;

        $qryDevengo = <<<SQL
            UPDATE DEVENGO_DIARIO_INVERSION
            SET
                CONTABILIZADO = SYSDATE
            WHERE
                CONTRATO = :contrato
                AND ID_INVERSION = :codigo
                AND CONTABILIZADO IS NULL
        SQL;

        $qrys = [
            $qryDevengo,
            self::GetQueryTicket(),
            self::GetQueryMovimientoAhorro(),
            $qryInversion,
            self::GetQueryTicket(),
            self::GetQueryMovimientoAhorro()
        ];

        $datosInsert = [
            [
                'contrato' => $datos['contrato'],
                'codigo' => $datos['codigo']
            ],
            [
                'contrato' => $datos['contrato'],
                'monto' => $datos['rendimiento'],
                'ejecutivo' => $datos['ejecutivo'],
                'sucursal' => $datos['sucursal']
            ],
            [
                'tipo_pago' => '17',
                'contrato' => $datos['contrato'],
                'monto' => $datos['rendimiento'],
                'movimiento' => '1',
                'cliente' => $datos['cliente'],
                'ejecutivo' => $datos['ejecutivo'],
                'sucursal' => $datos['sucursal'],
                'apoderado' => null
            ],
            [
                'monto' => $datos['monto'],
                'tasa' => $datos['tasa'],
                'contrato' => $datos['contrato'],
                'codigo' => $datos['codigo']
            ],
            [
                'contrato' => $datos['contrato'],
                'monto' => $datos['monto'],
                'ejecutivo' => $datos['ejecutivo'],
                'sucursal' => $datos['sucursal']
            ],
            [
                'tipo_pago' => '5',
                'contrato' => $datos['contrato'],
                'monto' => $datos['monto'],
                'movimiento' => '0',
                'cliente' => $datos['cliente'],
                'ejecutivo' => $datos['ejecutivo'],
                'sucursal' => $datos['sucursal'],
                'apoderado' => null
            ]
        ];

        if ($datos['rendimiento'] == 0) {
            $qrys = array_splice($qrys, -3);
            $datosInsert = array_splice($datosInsert, -3);
        }

        $qryCodInv = <<<SQL
            UPDATE MOVIMIENTOS_AHORRO
            SET CDG_INVERSION = :codigo
            WHERE CDG_TICKET = :ticket
        SQL;

        try {
            $mysqli = new Database();
            $res = $mysqli->insertaMultiple($qrys, $datosInsert);
            if ($res) {
                LogTransaccionesAhorro::LogTransacciones($qrys, $datosInsert, $_SESSION['cdgco_ahorro'], $_SESSION['usuario'], $datos['contrato'], "Actualización de inversión de cuenta ahorro corriente");
                $ticket = self::RecuperaTicket($datos['contrato']);
                $mysqli->insertar($qryCodInv, ['codigo' => $datos['codigo'], 'ticket' => $ticket['CODIGO']]);
                return self::Responde(true, "Inversión actualizada correctamente.", ['ticket' => $ticket['CODIGO']]);
            }
            return self::Responde(false, "Ocurrió un error al actualizar la inversión.");
        } catch (Exception $e) {
            return self::Responde(false, "Ocurrió un error al actualizar la inversión.", null, $e->getMessage());
        }
    }

    public static function DatosCertificadoInversion($idInversion)
    {
        $qry = <<<SQL
            SELECT
                APA.CDGCL AS CLIENTE,
                CL.PRIMAPE AS APELLIDO1,
                CL.SEGAPE AS APELLIDO2,
                CL.NOMBRE1 || (
                CASE
                    WHEN CL.NOMBRE2 IS NOT NULL THEN ' ' || CL.NOMBRE2
                    ELSE ''
                END) AS NOMBRE,
                CO.CODIGO || '-' || CO.NOMBRE AS SUCURSAL,
                CONCATENA_NOMBRE(PE.NOMBRE1, PE.NOMBRE2, PE.PRIMAPE, PE.SEGAPE) AS EJECUTIVO,
                TO_CHAR(SYSDATE, 'DD/MM/YYYY') AS FECHA,
                TO_CHAR(CI.FECHA_APERTURA, 'DD/MM/YYYY') AS F_APERTURA,
                TO_CHAR(CI.FECHA_VENCIMIENTO, 'DD/MM/YYYY') AS F_VENCIMIENTO,
                'PLAZO FIJO' AS TIPO_NVVERSION,
                TO_CHAR(CI.MONTO_INVERSION, 'FM$999,999,999.00') AS MONTO,
                PI.PLAZO || ' ' || CASE
                    WHEN PI.PERIODICIDAD = 'D' THEN 'DÍAS'
                    WHEN PI.PERIODICIDAD = 'S' THEN 'SEMANAS'
                    WHEN PI.PERIODICIDAD = 'M' THEN 'MESES'
                    WHEN PI.PERIODICIDAD = 'A' THEN 'AÑOS'
                    ELSE ''
                END AS PLAZO,
                TO_CHAR(TI.TASA, 'FM99.00') || '%' AS TASA,
                'TRANSFERENCIA' AS FORMA_PAGO,
                TO_CHAR((TRUNC(CI.FECHA_VENCIMIENTO) - TRUNC(SYSDATE)) * TI.TASA * CI.MONTO_INVERSION / 36000, 'FM$999,999,999.00') AS RENDIMIENTO,
                TO_CHAR(((TRUNC(CI.FECHA_VENCIMIENTO) - TRUNC(SYSDATE)) * TI.TASA * CI.MONTO_INVERSION / 36000) + CI.MONTO_INVERSION, 'FM$999,999,999.00') AS MONTO_FINAL
            FROM    
                CUENTA_INVERSION CI
                JOIN TASA_INVERSION TI ON CI.CDG_TASA = TI.CODIGO
                JOIN PLAZO_INVERSION PI ON TI.CDG_PLAZO = PI.CODIGO
                LEFT JOIN ASIGNA_PROD_AHORRO APA ON APA.CONTRATO = CI.CDG_CONTRATO
                LEFT JOIN CL ON CL.CODIGO = APA.CDGCL
                LEFT JOIN PE ON PE.CODIGO = CI.CDG_USUARIO
                LEFT JOIN CO ON CO.CODIGO = APA.CDGCO
            WHERE
                CI.CODIGO = '$idInversion'
        SQL;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryOne($qry);
            if (!$res) [];
            return $res;
        } catch (Exception $e) {
            return [];
        }
    }

    public static function RecuperaCodigoInversion($contrato)
    {
        $query = <<<sql
        SELECT
            MAX(CODIGO) AS CODIGO
        FROM
            CUENTA_INVERSION
        WHERE
            CDG_CONTRATO = '$contrato'
        sql;

        try {
            $mysqli = new Database();
            return $mysqli->queryOne($query);
        } catch (Exception $e) {
            return 0;
        }
    }

    public static function GetInversiones($datos)
    {
        $query = <<<sql
        SELECT
            TO_CHAR(CI.FECHA_APERTURA, 'DD/MM/YYYY') AS APERTURA,
            CI.MONTO_INVERSION AS MONTO,
            (SELECT TASA FROM TASA_INVERSION WHERE CODIGO = CI.CDG_TASA) AS TASA,
            (SELECT PLAZO FROM PLAZO_INVERSION WHERE CODIGO = (SELECT CDG_PLAZO FROM TASA_INVERSION WHERE CODIGO =CI.CDG_TASA)) AS PLAZO,
            (SELECT CASE PERIODICIDAD WHEN 'D' THEN 'Días' WHEN 'S' THEN 'Semanas' WHEN 'M' THEN 'Meses' WHEN 'A' THEN 'Años' ELSE 'No definido' END FROM PLAZO_INVERSION WHERE CODIGO = (SELECT CDG_PLAZO FROM TASA_INVERSION WHERE CODIGO =CI.CDG_TASA)) AS PERIODICIDAD,
            TO_CHAR(CI.FECHA_VENCIMIENTO, 'DD/MM/YYYY') AS VENCIMIENTO,
            NVL(CI.RENDIMIENTO,0) AS RENDIMIENTO,
            TO_CHAR(CI.FECHA_LIQUIDACION, 'DD/MM/YYYY') AS LIQUIDACION,
            CASE CI.ACCION WHEN 'D' THEN 'Depósito' WHEN 'R' THEN 'Renovación' ELSE 'No aplica' END AS ACCION 
        FROM
            CUENTA_INVERSION CI
        WHERE
            CI.CDG_CONTRATO = '{$datos['contrato']}'
        ORDER BY
            CI.FECHA_VENCIMIENTO
        sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryAll($query);
            if (count($res) === 0) return self::Responde(false, "No se encontraron inversiones para el contrato {$datos['contrato']}.");
            return self::Responde(true, "Consulta realizada correctamente.", $res);
        } catch (Exception $e) {
            return self::Responde(false, "Ocurrió un error al consultar las inversiones.", null, $e->getMessage());
        }
    }

    public static function DatosContratoAhorro($contrato)
    {
        $query = <<<sql
        SELECT
            APA.CONTRATO,
            APA.CDGCL,
            LOWER(TO_CHAR(APA.FECHA_APERTURA, 'DD "de" MONTH "del" YYYY')) AS FECHA_F_LEGAL,
            TO_CHAR(APA.FECHA_APERTURA, 'DD/MM/YYYY') AS FECHA_APERTURA,
            CONCATENA_NOMBRE(CL.NOMBRE1, CL.NOMBRE2, CL.PRIMAPE, CL.SEGAPE) AS NOMBRE,
            UPPER(DOMICILIO_CLIENTE(CL.CODIGO)) AS DIRECCION,
            (
                SELECT
                    MONTO
                FROM
                    MOVIMIENTOS_AHORRO
                WHERE
                    CDG_CONTRATO = APA.CONTRATO
                    AND CDG_TIPO_PAGO = 1
                    AND FECHA_MOV = (
                        SELECT
                            MAX(MA.FECHA_MOV)
                        FROM
                            MOVIMIENTOS_AHORRO MA
                        WHERE
                            MA.CDG_CONTRATO = (SELECT CONTRATO FROM ASIGNA_PROD_AHORRO WHERE CDGCL = CL.CODIGO AND CDGPR_PRIORITARIO != 2)
                            AND CDG_TIPO_PAGO = 2
                    )
            ) AS MONTO_APERTURA
        FROM
            ASIGNA_PROD_AHORRO APA,
            CL
        WHERE
            APA.CDGCL = CL.CODIGO
            AND APA.CONTRATO = '$contrato'
        sql;

        try {
            $mysqli = new Database();
            return $mysqli->queryOne($query);
        } catch (Exception $e) {
            return 0;
        }
    }

    public static function DatosContratoInversion($codigoInversion)
    {
        $query = <<<sql
        SELECT
            CI.CDG_CONTRATO AS CONTRATO,
            CONCATENA_NOMBRE(CL.NOMBRE1, CL.NOMBRE2, CL.PRIMAPE, CL.SEGAPE) AS NOMBRE,
            CI.MONTO_INVERSION AS MONTO,
            LOWER(TO_CHAR(CI.FECHA_APERTURA, 'DD "de" MONTH "del" YYYY')) AS FECHA_F_LEGAL,
            UPPER(DOMICILIO_CLIENTE(CL.CODIGO)) AS DIRECCION,
            TRUNC(CI.FECHA_VENCIMIENTO) - TRUNC(CI.FECHA_APERTURA) AS DIAS,
            (SELECT TASA FROM TASA_INVERSION WHERE CODIGO = CI.CDG_TASA) AS TASA
        FROM
            CUENTA_INVERSION CI,
            ASIGNA_PROD_AHORRO APA,
            CL
        WHERE
            CI.CDG_CONTRATO = APA.CONTRATO
            AND APA.CDGCL = CL.CODIGO
            AND CI.CODIGO = '$codigoInversion'
        sql;

        try {
            $mysqli = new Database();
            return $mysqli->queryOne($query);
        } catch (Exception $e) {
            return 0;
        }
    }

    public static function DatosContratoPeque($contrato)
    {
        $query = <<<sql
        SELECT
            APA.CONTRATO,
            APA.CDGCL,
            LOWER(TO_CHAR(APA.FECHA_APERTURA, 'DD "de" MONTH "del" YYYY')) AS FECHA_F_LEGAL,
            TO_CHAR(APA.FECHA_APERTURA, 'DD/MM/YYYY') AS FECHA_APERTURA,
            CONCATENA_NOMBRE(CL.NOMBRE1, CL.NOMBRE2, CL.PRIMAPE, CL.SEGAPE) AS NOMBRE,
            UPPER(DOMICILIO_CLIENTE(CL.CODIGO)) AS DIRECCION,
            (
                SELECT
                    MONTO
                FROM
                    MOVIMIENTOS_AHORRO
                WHERE
                    CDG_CONTRATO = APA.CONTRATO
                    AND CDG_TIPO_PAGO = 1
            ) AS MONTO_APERTURA
        FROM
            ASIGNA_PROD_AHORRO APA,
            CL
        WHERE
            APA.CDGCL = CL.CODIGO
            AND APA.CONTRATO = '$contrato'
        sql;

        try {
            $mysqli = new Database();
            return $mysqli->queryOne($query);
        } catch (Exception $e) {
            return 0;
        }
    }

    public static function getSucursal($noSuc)
    {
        $query = <<<sql
        SELECT
            CODIGO, 
            NOMBRE
        FROM
            CO
        WHERE
            CODIGO = '$noSuc'
        sql;

        try {
            $mysqli = new Database();
            return $mysqli->queryOne($query);
        } catch (Exception $e) {
            return 0;
        }
    }

    public static function RegistraSolicitud($datos)
    {
        $qrySolicitud = <<<sql
        INSERT INTO SOLICITUD_RETIRO_AHORRO
            (ID_SOL_RETIRO_AHORRO, CONTRATO, FECHA_SOLICITUD, CANTIDAD_SOLICITADA, AUTORIZACION_CLIENTE, CDGPE, ESTATUS, FECHA_ESTATUS, PRORROGA, TIPO_RETIRO, FECHA_REGISTRO, CDG_SUCURSAL)
        VALUES
            ((SELECT NVL(MAX(TO_NUMBER(ID_SOL_RETIRO_AHORRO)),0) FROM SOLICITUD_RETIRO_AHORRO) + 1, :contrato, TO_TIMESTAMP(:fecha_solicitud, 'DD/MM/YYYY HH24:MI:SS'), :monto, NULL, :ejecutivo, 0, SYSDATE, 0, :tipo_retiro, SYSDATE, :sucursal)
        sql;
        $qryTicket = self::GetQueryTicket();
        $qryMovimiento = self::GetQueryMovimientoAhorro();

        $tipoRetiro = $datos['retiroExpress'] === true || $datos['retiroExpress'] === 'true' ? 1 : 2;

        $datosSolicitud = [
            'contrato' => $datos['contrato'],
            'fecha_solicitud' => $datos['fecha_retiro'],
            'monto' => $datos['monto'],
            'ejecutivo' => $datos['ejecutivo'],
            'tipo_retiro' => $tipoRetiro,
            'sucursal' => $datos['sucursal']
        ];

        $datosTicket = [
            'contrato' => $datos['contrato'],
            'monto' => $datos['monto'],
            'ejecutivo' => $datos['ejecutivo'],
            'sucursal' => $datos['sucursal']
        ];

        $datosMovimiento = [
            'tipo_pago' => $tipoRetiro === 1 ? '6' : '7',
            'contrato' => $datos['contrato'],
            'monto' => $datos['monto'],
            'movimiento' => '0',
            'cliente' => $datos['cliente'],
            'ejecutivo' => $datos['ejecutivo'],
            'sucursal' => $datos['sucursal'],
            'apoderado' => null
        ];

        $query = [
            $qrySolicitud,
            $qryTicket,
            $qryMovimiento
        ];

        $datosInsert = [
            $datosSolicitud,
            $datosTicket,
            $datosMovimiento
        ];

        $tipoMov = $tipoRetiro === 1 ? "express" : "programado";
        try {
            $mysqli = new Database();
            $res = $mysqli->insertaMultiple($query, $datosInsert);
            if ($res) {
                LogTransaccionesAhorro::LogTransacciones($query, $datosInsert, $_SESSION['cdgco_ahorro'], $_SESSION['usuario'], $datos['contrato'], "Registro de solicitud de retiro " . $tipoMov . " de cuenta de ahorro corriente");
                $ticket = self::RecuperaTicket($datos['contrato']);
                return self::Responde(true, "El retiro " . $tipoMov . " fue registrado correctamente.", ['ticket' => $ticket['CODIGO']]);
            }
            return self::Responde(false, "Ocurrió un error al registrar el retiro " . $tipoMov . ".");
        } catch (Exception $e) {
            return self::Responde(false, "Ocurrió un error al registrar el retiro " . $tipoMov  . ".", null, $e->getMessage());
        }
    }

    public static function DetalleMovimientosXdia()
    {
        $qry = <<<sql
        SELECT
            MA.MOVIMIENTO,
            TPA.CODIGO AS CODOP,
            TPA.DESCRIPCION AS OPERACION,
            CONCATENA_NOMBRE(CL.NOMBRE1, CL.NOMBRE2, CL.PRIMAPE, CL.SEGAPE) AS NOMBRE,
            CL.CODIGO AS CLIENTE,
            TO_CHAR(MA.FECHA_MOV,'DD/MM/YYYY HH24:MI:SS') AS FECHA,
            MA.MONTO,
            'AUT_CLIENTE' AS AUTORIZACION
        FROM
            MOVIMIENTOS_AHORRO MA
            INNER JOIN CL ON CL.CODIGO = (SELECT CDGCL FROM ASIGNA_PROD_AHORRO WHERE CONTRATO = MA.CDG_CONTRATO)
            INNER JOIN TIPO_PAGO_AHORRO TPA ON TPA.CODIGO = MA.CDG_TIPO_PAGO
        WHERE
            MA.FECHA_MOV >= TRUNC(SYSDATE) AND MA.FECHA_MOV < TRUNC(SYSDATE) + 1
        ORDER BY
            MA.CODIGO
        sql;

        try {
            $mysqli = new Database();
            return $mysqli->queryAll($qry);
        } catch (Exception $e) {
            return array();
        }
    }

    public static function HistoricoSolicitudRetiro($datos)
    {
        $qry = <<<sql
        SELECT
            SR.ID_SOL_RETIRO_AHORRO AS ID,
            CASE SR.TIPO_RETIRO
                WHEN 1 THEN 'EXPRESS'
                WHEN 2 THEN 'PROGRAMADO'
                ELSE 'NO DEFINIDO'
            END AS TIPO_RETIRO,
            CONCATENA_NOMBRE(CL.NOMBRE1, CL.NOMBRE2, CL.PRIMAPE, CL.SEGAPE) AS NOMBRE,
            CL.CODIGO AS CLIENTE,
            TO_CHAR(SR.FECHA_ESTATUS, 'DD/MM/YYYY HH24:MI:SS') AS FECHA_ESTATUS,
            SR.CANTIDAD_SOLICITADA AS MONTO,
            CASE SR.ESTATUS
                WHEN 0 THEN 'REGISTRADO'
                WHEN 1 THEN 'APROBADO'
                WHEN 2 THEN 'RECHAZADO'
                WHEN 3 THEN 'ENTREGADO'
                WHEN 4 THEN 'DEVUELTO'
                WHEN 5 THEN 'CANCELADO'
                ELSE 'NO DEFINIDO'
            END AS ESTATUS,
            TO_CHAR(SR.FECHA_SOLICITUD, 'DD/MM/YYYY') AS FECHA_SOLICITUD
        FROM
            SOLICITUD_RETIRO_AHORRO SR
            INNER JOIN CL ON CL.CODIGO = (SELECT CDGCL FROM ASIGNA_PROD_AHORRO WHERE CONTRATO = SR.CONTRATO)
        WHERE
            CONTRATO IN (SELECT CONTRATO FROM ASIGNA_PROD_AHORRO WHERE CDGPR_PRIORITARIO = '{$datos['producto']}' GROUP BY CONTRATO)
        sql;

        if ($datos['fechaI'] && $datos['fechaF']) $qry .= " AND TRUNC(SR.FECHA_REGISTRO) BETWEEN TO_DATE('{$datos['fechaI']}', 'YYYY-MM-DD') AND TO_DATE('{$datos['fechaF']}', 'YYYY-MM-DD')";
        if ($datos['estatus']) $qry .= " AND SR.ESTATUS = '{$datos['estatus']}'";
        if ($datos['tipo']) $qry .= " AND SR.TIPO_RETIRO = '{$datos['tipo']}'";

        $qry .= " ORDER BY TRUNC(SR.FECHA_SOLICITUD), SR.FECHA_ESTATUS DESC";
        try {
            $mysqli = new Database();
            $res = $mysqli->queryAll($qry);
            if (count($res) === 0) return self::Responde(false, "No se encontraron solicitudes de retiro para el producto {$datos['producto']}.", null);
            return self::Responde(true, "Consulta realizada correctamente.", $res);
        } catch (Exception $e) {
            return self::Responde(false, "Ocurrió un error al consultar las solicitudes de retiro.", null, $e->getMessage());
        }
    }

    public static function ResumenEntregaRetiro($datos)
    {
        $qry = <<<sql
        SELECT
            SRA.ID_SOL_RETIRO_AHORRO AS ID,
            CONCATENA_NOMBRE(CL.NOMBRE1, CL.NOMBRE2, CL.PRIMAPE, CL.SEGAPE) AS NOMBRE,
            CL.CODIGO AS CLIENTE,
            SRA.CANTIDAD_SOLICITADA AS MONTO,
            (
                SELECT
                    CONCATENA_NOMBRE(PE.NOMBRE1, PE.NOMBRE2, PE.PRIMAPE, PE.SEGAPE)
                FROM
                    PE
                WHERE
                    PE.CODIGO = SRA.CDGPE_ASIGNA_ESTATUS
                    AND CDGEM = 'EMPFIN'
            ) AS APROBADO_POR,
            TO_CHAR(SRA.FECHA_SOLICITUD, 'DD/MM/YYYY') AS FECHA_ESPERADA,
            SRA.CONTRATO,
            SRA.TIPO_RETIRO
        FROM
            SOLICITUD_RETIRO_AHORRO SRA
            INNER JOIN CL ON CL.CODIGO = (SELECT CDGCL FROM ASIGNA_PROD_AHORRO WHERE CONTRATO = SRA.CONTRATO)
        WHERE
            SRA.ID_SOL_RETIRO_AHORRO = '{$datos['id']}'
        sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryOne($qry);
            if (!$res) return self::Responde(false, "No se encontraron datos para el retiro solicitado.");
            return self::Responde(true, "Consulta realizada correctamente.", $res);
        } catch (Exception $e) {
            return self::Responde(false, "Ocurrió un error al consultar los datos de la solicitud.", null, $e->getMessage());
        }
    }

    public static function EntregaRetiro($datos)
    {
        $tipoRetiro = $datos['tipo'] == 1 ? "Express" : "Programado";

        $datosSolicitud = [
            'estatus' => 3,
            'ejecutivo' => $datos['ejecutivo'],
            'id' => $datos['id']
        ];

        $datosTicket = [
            'contrato' => $datos['contrato'],
            'monto' => $datos['monto'],
            'ejecutivo' => $datos['ejecutivo'],
            'sucursal' => $datos['sucursal']
        ];

        $datosMovimiento = [
            'tipo_pago' => $tipoRetiro === "Express" ? '13' : '14',
            'contrato' => $datos['contrato'],
            'monto' => $datos['monto'],
            'movimiento' => '0',
            'cliente' => $datos['cliente'],
            'ejecutivo' => $datos['ejecutivo'],
            'sucursal' => $datos['sucursal'],
            'apoderado' => null
        ];

        $datosInsert = [
            $datosSolicitud,
            $datosTicket,
            $datosMovimiento,
            ['sucursal' => $datos['sucursal'], 'monto' => $datos['monto']]
        ];

        $qrySolicitud = <<<sql
        UPDATE
            SOLICITUD_RETIRO_AHORRO
        SET
            ESTATUS = :estatus,
            CDG_CIERRE_SOLICITUD = :ejecutivo,
            FECHA_ESTATUS = SYSDATE,
            FECHA_ENTREGA = SYSDATE
        WHERE
            ID_SOL_RETIRO_AHORRO = :id
        sql;

        $query = [
            $qrySolicitud,
            self::GetQueryTicket(),
            self::GetQueryMovimientoAhorro(),
            self::GetQueryActualizaSaldoSucursal(true)
        ];

        $validacion = [
            'query' => "SELECT SALDO FROM SUC_ESTADO_AHORRO WHERE CDG_SUCURSAL = :sucursal",
            'datos' => ['sucursal' => $datos['sucursal']],
            'funcion' => [CajaAhorro::class, 'ValidarSaldoSucursal']
        ];

        try {
            $mysqli = new Database();
            $res = $mysqli->insertaMultiple($query, $datosInsert, $validacion);

            LogTransaccionesAhorro::LogTransacciones($query[0], $datosInsert[0], $_SESSION['cdgco_ahorro'], $_SESSION['usuario'], $datos['contrato'], "Actualización de estatus por entrega de retiro " . $tipoRetiro);
            LogTransaccionesAhorro::LogTransacciones($query[1], $datosInsert[1], $_SESSION['cdgco_ahorro'], $_SESSION['usuario'], $datos['contrato'], "Creación de ticket por entrega de retiro " . $tipoRetiro);
            LogTransaccionesAhorro::LogTransacciones($query[2], $datosInsert[2], $_SESSION['cdgco_ahorro'], $_SESSION['usuario'], $datos['contrato'], "Registro de movimiento por entrega de retiro " . $tipoRetiro);
            LogTransaccionesAhorro::LogTransacciones($query[3], $datosInsert[4], $_SESSION['cdgco_ahorro'], $_SESSION['usuario'], $datos['contrato'], "Actualización de saldo de sucursal por entrega de retiro " . $tipoRetiro);

            if (!$res) return self::Responde(false, "Ocurrió un error al registrar la entrega del retiro " . $tipoRetiro . ".");

            $ticket = self::RecuperaTicket($datos['contrato']);
            return self::Responde(true, "Entrega de retiro " . $tipoRetiro . " registrada correctamente.", $ticket);
        } catch (Exception $e) {
            return self::Responde(false, "Ocurrió un error al registrar la entrega del retiro " . $tipoRetiro . ".", null, $e->getMessage());
        }
    }

    public static function DevolucionRetiro($datos)
    {
        $query = [
            self::GetQueryTicket(),
            self::GetQueryMovimientoAhorro()
        ];

        $datosInsert = [
            [
                'contrato' => $datos['contrato'],
                'monto' => $datos['monto'],
                'ejecutivo' => $datos['ejecutivo'],
                'sucursal' => $datos['sucursal']
            ],
            [
                'tipo_pago' => $datos['tipo'] == 1 ? '8' : '9',
                'contrato' => $datos['contrato'],
                'monto' => $datos['monto'],
                'movimiento' => '1',
                'cliente' => $datos['cliente'],
                'ejecutivo' => $datos['ejecutivo'],
                'sucursal' => $datos['sucursal'],
                'apoderado' => null
            ]
        ];

        try {
            $mysqli = new Database();
            $res = $mysqli->insertaMultiple($query, $datosInsert);
            if ($res) {
                LogTransaccionesAhorro::LogTransacciones($query, $datosInsert, $_SESSION['cdgco_ahorro'], $_SESSION['usuario'], $datos['contrato'], "Registro de devolución de retiro " . ($datos['tipo'] == 1 ? "express" : "programado") . " de cuenta de ahorro corriente");
                $ticket = self::RecuperaTicket($datos['contrato']);
                return self::Responde(true, "Se han liberado $ " . number_format($datos['monto'], 2) . " a la cuenta del cliente por el apartado para el retiro " . ($datos['tipo'] == 1 ? "express" : "programado") . ".", ['ticket' => $ticket['CODIGO']]);
            }
            return self::Responde(false, "Ocurrió un error al registrar la devolución.");
        } catch (Exception $e) {
            return self::Responde(false, "Ocurrió un error al registrar la devolución.", null, $e->getMessage());
        }
    }

    public static function GetLogTransacciones($datos)
    {
        $qry = <<<sql
        SELECT
            TO_CHAR(LTA.FECHA_TRANSACCION, 'DD/MM/YYYY HH24:MI:SS') AS FECHA,
            LTA.SUCURSAL,
            LTA.USUARIO,
            (SELECT CDGCL FROM ASIGNA_PROD_AHORRO WHERE CONTRATO = LTA.CONTRATO) AS CLIENTE,
            LTA.CONTRATO,
            LTA.TIPO
        FROM
            LOG_TRANSACCIONES_AHORRO LTA
        WHERE
            TRUNC(LTA.FECHA_TRANSACCION) BETWEEN TO_DATE(:fecha_inicio, 'YYYY-MM-DD') AND TO_DATE(:fecha_fin, 'YYYY-MM-DD')
        sql;

        $qry .= $datos["operacion"] ? " AND LTA.TIPO = :operacion" : "";
        $qry .= $datos["usuario"] ? " AND LTA.USUARIO = :usuario" : "";
        $qry .= $datos["sucursal"] ? " AND LTA.SUCURSAL = :sucursal" : "";

        try {
            $mysqli = new Database();
            $resultado = $mysqli->queryAll($qry, $datos);
            if (count($resultado) === 0) return self::Responde(false, "No se encontraron registros para la consulta.", $qry);
            return self::Responde(true, "Consulta realizada correctamente.", $resultado);
        } catch (Exception $e) {
            return self::Responde(false, "Ocurrió un error al consultar los registros.", null, $e->getMessage());
        }
    }

    public static function GetMovimientosSucursal($datos)
    {
        $qry = <<<sql
        SELECT
            TO_CHAR(MA.FECHA_MOV, 'DD/MM/YYYY HH24:MI:SS') AS FECHA,
            MA.CDG_CONTRATO AS CONTRATO,
            (
                SELECT
                    DESCRIPCION
                FROM
                    TIPO_PAGO_AHORRO
                WHERE
                    CODIGO = MA.CDG_TIPO_PAGO
            ) CONCEPTO,
            MA.MONTO,
            CASE MA.MOVIMIENTO
                WHEN '0' THEN 'CARGO'
                WHEN '1' THEN 'ABONO'
                ELSE 'NO DEFINIDO'
            END AS MOVIMIENTO,
            (
                SELECT
                    CONCATENA_NOMBRE(CL.NOMBRE1, CL.NOMBRE2, CL.PRIMAPE, CL.SEGAPE)
                FROM
                    CL
                WHERE
                    CL.CODIGO = (SELECT CDGCL FROM ASIGNA_PROD_AHORRO WHERE CONTRATO = MA.CDG_CONTRATO)
            ) AS CLIENTE
        FROM
            MOVIMIENTOS_AHORRO MA
            INNER JOIN TICKETS_AHORRO TA ON TA.CODIGO = MA.CDG_TICKET
        WHERE
            TA.CDG_SUCURSAL = '{$datos['sucursal']}'
        ORDER BY
            MA.FECHA_MOV DESC
        sql;

        try {
            $mysqli = new Database();
            return $mysqli->queryAll($qry);
        } catch (Exception $e) {
            return array();
        }
    }

    public static function GetDatosEdoCta($cliente)
    {
        $qryDatosGenerale = <<<sql
            SELECT
                CONCATENA_NOMBRE(CL.NOMBRE1, CL.NOMBRE2, CL.PRIMAPE, CL.SEGAPE) AS NOMBRE,
                CL.CODIGO AS CLIENTE,
                TO_CHAR(APA.FECHA_APERTURA, 'DD/MM/YYYY') AS FECHA_APERTURA,
                APA.CONTRATO,
                APA.SALDO
            FROM
                ASIGNA_PROD_AHORRO APA
                INNER JOIN CL ON CL.CODIGO = APA.CDGCL
            WHERE
                APA.CDGCL = '$cliente'
                AND APA.CDGPR_PRIORITARIO > 2
                AND APA.ESTATUS = 'A'
        sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryOne($qryDatosGenerale);
            if (!$res) return array();
            return $res;
        } catch (Exception $e) {
            return array();
        }
    }

    public static function GetMovimientosAhorro($contrato, $fI, $fF)
    {
        $qryMovimientos = <<<sql
        SELECT
            *
        FROM (
            SELECT
                TO_CHAR(MA.FECHA_MOV, 'DD/MM/YYYY') AS FECHA,
                MA.CDG_TIPO_PAGO AS TIPO,
                CONCAT(
                        (SELECT DESCRIPCION
                        FROM TIPO_PAGO_AHORRO
                        WHERE CODIGO = MA.CDG_TIPO_PAGO),  CASE 
                    WHEN SRA.FECHA_SOLICITUD IS NULL THEN ''
                    ELSE TO_CHAR(SRA.FECHA_SOLICITUD, ' - DD/MM/YYYY')
                    END 
                    )
                AS DESCRIPCION,
                CASE MA.MOVIMIENTO
                    WHEN '0' THEN
                        CASE MA.CDG_TIPO_PAGO
                            WHEN '6' THEN MA.MONTO
                            WHEN '7' THEN MA.MONTO
                            ELSE 0
                        END
                    ELSE 
                        CASE MA.CDG_TIPO_PAGO
                            WHEN '8' THEN MA.MONTO
                            WHEN '9' THEN MA.MONTO
                            ELSE 0
                        END
                END AS TRANSITO,
                CASE MA.MOVIMIENTO
                    WHEN '1' THEN 
                        CASE MA.CDG_TIPO_PAGO
                            WHEN '8' THEN 0
                            WHEN '9' THEN 0
                            ELSE MA.MONTO
                        END
                    ELSE 0
                END AS ABONO,
                CASE MA.MOVIMIENTO
                    WHEN '0' THEN
                        CASE MA.CDG_TIPO_PAGO
                            WHEN '6' THEN 0
                            WHEN '7' THEN 0
                            ELSE MA.MONTO
                        END
                    ELSE 0
                END AS CARGO,
                SUM(
                    CASE MA.MOVIMIENTO
                        WHEN '0' THEN 
                            CASE MA.CDG_TIPO_PAGO
                                WHEN '6' THEN 0
                                WHEN '7' THEN 0
                                ELSE -MA.MONTO
                            END
                        WHEN '1' THEN
                            CASE MA.CDG_TIPO_PAGO
                                WHEN '8' THEN 0
                                WHEN '9' THEN 0
                                ELSE MA.MONTO
                            END
                    END
                ) OVER (ORDER BY MA.FECHA_MOV, MA.MOVIMIENTO DESC) AS SALDO
            FROM
                MOVIMIENTOS_AHORRO MA
                INNER JOIN TIPO_PAGO_AHORRO TPA ON TPA.CODIGO = MA.CDG_TIPO_PAGO
                LEFT JOIN SOLICITUD_RETIRO_AHORRO SRA ON SRA.ID_SOL_RETIRO_AHORRO = MA.CDG_RETIRO 
            WHERE
                MA.CDG_CONTRATO = '$contrato'
            ORDER BY
                MA.FECHA_MOV, MA.MOVIMIENTO DESC
        ) WHERE TO_DATE(FECHA, 'DD/MM/YYYY') BETWEEN TO_DATE('$fI', 'DD/MM/YYYY') AND TO_DATE('$fF', 'DD/MM/YYYY')
        sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryAll($qryMovimientos);
            if (count($res) === 0) return array();
            return $res;
        } catch (Exception $e) {
            return array();
        }
    }

    public static function GetMovimientosInversion($contrato)
    {
        $qryMovimientos = <<<sql
        SELECT
            TO_CHAR(FECHA_APERTURA, 'DD/MM/YYYY') AS FECHA_APERTURA,
            TO_CHAR(FECHA_VENCIMIENTO, 'DD/MM/YYYY') AS FECHA_VENCIMIENTO,
            NVL(MONTO_INVERSION, 0) AS MONTO,
            (
                SELECT
                    CONCAT(
                        CONCAT(PI.PLAZO, ' '),
                        CASE PI.PERIODICIDAD
                            WHEN 'D' THEN 'Días'
                            WHEN 'S' THEN 'Semanas'
                            WHEN 'M' THEN 'Meses'
                            WHEN 'A' THEN 'Años'
                        END
                    )
                FROM
                    PLAZO_INVERSION PI
                WHERE
                    CODIGO = (
                        SELECT
                            TI.CDG_PLAZO
                        FROM
                            TASA_INVERSION TI
                        WHERE
                            CODIGO = CI.CDG_TASA
                    )
            ) AS PLAZO,
            (
                SELECT
                    TASA
                FROM
                    TASA_INVERSION
                WHERE
                    CODIGO = CI.CDG_TASA
            ) AS TASA,
            CASE CI.ESTATUS
                WHEN 'A' THEN 'Activa'
                WHEN 'C' THEN 'Cancelada'
                WHEN 'L' THEN 'Liquidada'
                ELSE 'No definido'
            END AS ESTATUS,
            TO_CHAR(FECHA_LIQUIDACION, 'DD/MM/YYYY') AS FECHA_LIQUIDACION,
            NVL(RENDIMIENTO,0) AS RENDIMIENTO,
            CASE ACCION
                WHEN 'D' THEN 'Cuenta ahorro'
                WHEN 'R' THEN 'Renovación'
                ELSE 'No definido'
            END AS ACCION
        FROM
            CUENTA_INVERSION CI
        WHERE
            CDG_CONTRATO = '$contrato'
        sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryAll($qryMovimientos);
            if (count($res) === 0) return array();
            return $res;
        } catch (Exception $e) {
            return array();
        }
    }

    public static function GetCuentasPeque($contrato)
    {
        $qryCuentas = <<<sql
        SELECT
            APA.CONTRATO,
            CONCATENA_NOMBRE(CL.NOMBRE1, CL.NOMBRE2, CL.APELLIDO1, CL.APELLIDO2) AS NOMBRE,
            TO_CHAR(APA.FECHA_APERTURA, 'DD/MM/YYYY') AS FECHA_APERTURA,
            APA.SALDO
        FROM
            ASIGNA_PROD_AHORRO APA
            RIGHT JOIN CL_PQS CL ON CL.CDG_CONTRATO = APA.CONTRATO
        WHERE
            APA.CDGCL = '$contrato'
            AND APA.CDGPR_PRIORITARIO = 2
            AND APA.ESTATUS = 'A'
        ORDER BY
            APA.CONTRATO
        sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryAll($qryCuentas);
            if (count($res) === 0) return array();
            return $res;
        } catch (Exception $e) {
            return array();
        }
    }

    public static function GetMovimientosPeque($contrato, $fI, $fF)
    {
        $qryMovimientos = <<<sql
        SELECT * FROM (
            SELECT
                TO_CHAR(MA.FECHA_MOV, 'DD/MM/YYYY') AS FECHA,
                (
                    SELECT
                        DESCRIPCION
                    FROM
                        TIPO_PAGO_AHORRO
                    WHERE
                        CODIGO = MA.CDG_TIPO_PAGO
                ) AS DESCRIPCION,
                CASE MA.MOVIMIENTO
                    WHEN '0' THEN
                        CASE MA.CDG_TIPO_PAGO
                            WHEN '6' THEN MA.MONTO
                            WHEN '7' THEN MA.MONTO
                            ELSE 0
                        END
                    ELSE 
                        CASE MA.CDG_TIPO_PAGO
                            WHEN '8' THEN MA.MONTO
                            WHEN '9' THEN MA.MONTO
                            ELSE 0
                        END
                END AS TRANSITO,
                CASE MA.MOVIMIENTO
                    WHEN '0' THEN
                        CASE MA.CDG_TIPO_PAGO
                            WHEN '6' THEN 0
                            WHEN '7' THEN 0
                            ELSE MA.MONTO
                        END
                    ELSE 0
                END AS CARGO,
                CASE MA.MOVIMIENTO
                    WHEN '1' THEN 
                    CASE MA.CDG_TIPO_PAGO
                        WHEN '8' THEN 0
                        WHEN '9' THEN 0
                        ELSE MA.MONTO
                    END
                    ELSE 0
                END AS ABONO,
                SUM(
                    CASE MA.MOVIMIENTO
                        WHEN '0' THEN -MA.MONTO
                        WHEN '1' THEN MA.MONTO
                    END
                ) OVER (ORDER BY MA.FECHA_MOV, MA.MOVIMIENTO DESC) AS SALDO
            FROM
                MOVIMIENTOS_AHORRO MA
                INNER JOIN TIPO_PAGO_AHORRO TPA ON TPA.CODIGO = MA.CDG_TIPO_PAGO
            WHERE
                MA.CDG_CONTRATO = '$contrato'
            ORDER BY
                MA.FECHA_MOV, MA.MOVIMIENTO DESC
        ) WHERE TO_DATE(FECHA, 'DD/MM/YYYY') BETWEEN TO_DATE('$fI', 'DD/MM/YYYY') AND TO_DATE('$fF', 'DD/MM/YYYY')
        sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryAll($qryMovimientos);
            if (count($res) === 0) return array();
            return $res;
        } catch (Exception $e) {
            return array();
        }
    }

    public static function ValidaRetirosDia($datos)
    {
        $qry = <<<sql
        SELECT
            SUM(MONTO) AS RETIROS
        FROM
            MOVIMIENTOS_AHORRO
        WHERE
            CDG_TIPO_PAGO = 4
            AND TRUNC(FECHA_MOV) = TRUNC(SYSDATE)
            AND CDG_CONTRATO = '{$datos['contrato']}'
        GROUP BY
            TRUNC(FECHA_MOV)
        sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryOne($qry);
            if (!$res) return self::Responde(true, "No se encontraron retiros para el día.");
            return self::Responde(false, "Retiros del día.", $res);
        } catch (Exception $e) {
            return self::Responde(false, "Ocurrió un error al validar los retiros del día.");
        }
    }

    public static function GetSaldoMinimoApertura($sucursal)
    {
        $qry = <<<sql
        SELECT
            NVL(MONTO_MINIMO, 100),
            NVL(MONTO_MAXIMO, 1000)
        FROM
            PARAMETROS_AHORRO
        WHERE
            CDG_SUCURSAL = '$sucursal'
sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryOne($qry);
            if (!$res) return ['MONTO_MINIMO' => 300, 'MONTO_MAXIMO' => 10000];
            return $res;
        } catch (Exception $e) {
            return ['MONTO_MINIMO' => 300, 'MONTO_MAXIMO' => 10000];
        }
    }

    public static function GetAllTransacciones($Inicial, $Final, $Operacion, $Producto, $Sucursal)
    {
        if ($Inicial == '' || $Final == '') {
            $fechaActual = date('Y-m-d');
            $Inicial = $fechaActual;
            $Final = $fechaActual;
        }

        if ($Operacion == '' || $Operacion == '0') {
            $ope = "";
        } else {
            if ($Operacion == 1) {
                $operac = 'APERTURA DE CUENTA - INSCRIPCIÓN';
            } else if ($Operacion == 2) {
                $operac = 'CAPITAL INICIAL - CUENTA CORRIENTE';
            } else if ($Operacion == 3) {
                $operac = 'DEPOSITO';
            } else if ($Operacion == 4) {
                $operac = 'RETIRO';
            } else if ($Operacion == 5) {
                $operac = 'DEVOLUCIÓN RETIRO EXPRESS';
            } else if ($Operacion == 6) {
                $operac = 'DEVOLUCIÓN RETIRO PROGRAMADO';
            } else if ($Operacion == 7) {
                $operac = 'RETIRO EXPRESS';
            } else if ($Operacion == 8) {
                $operac = 'RETIRO PROGRAMADO';
            } else if ($Operacion == 9) {
                $operac = 'TRANSFERENCIA INVERSION';
            } else if ($Operacion == 10) {
                $operac = 'TRANSFERENCIA INVERSION A AHORRO';
            }
            $ope = " AND CONCEPTO = '" . $operac . "'";
        }

        if ($Producto == '' || $Producto == 0) {
            $pro = '';
        } else {
            if ($Producto == '3') {
                $pro = "AND PRODUCTO = 'TRANSFERENCIA INVERSION'";
            } else if ($Producto == '1') {
                $pro = "AND PRODUCTO = 'AHORRO CUENTA CORRIENTE'";
            } else if ($Producto == '2') {
                $pro = "AND PRODUCTO = 'AHORRO CUENTA PEQUE'";
            }
        }


        if ($Sucursal == '' || $Sucursal == 0) {
            $suc = "";
        } else {
            $suc = " AND CDGCO = '" . $Sucursal . "'";
        }




        $query = <<<sql
        SELECT 
            CONSECUTIVO,
            MOVIMIENTO,
            CDGCO,
            SUCURSAL,
            USUARIO_CAJA,
            NOMBRE_CAJERA,
            CLIENTE,
            TITULAR_CUENTA_EJE,
            FECHA_MOV,
            FECHA_MOV_FILTRO,
            CDG_TICKET,
            MONTO,
            CONCEPTO,
            TIPO_MOVIMIENTO,
            PRODUCTO,
            CASE WHEN TIPO_MOVIMIENTO = 'INGRESO' THEN MONTO ELSE 0 END AS INGRESO,
            CASE WHEN TIPO_MOVIMIENTO = 'EGRESO' THEN MONTO ELSE 0 END AS EGRESO,
            CASE WHEN TIPO_MOVIMIENTO = 'REPORTE' THEN MONTO ELSE 0  END AS REPORTE,
    
            CASE 
		        WHEN TIPO_MOVIMIENTO = 'REPORTE' THEN MONTO
		        ELSE SUM(CASE 
		                    WHEN TIPO_MOVIMIENTO = 'INGRESO' THEN MONTO 
		                    WHEN CONCEPTO = 'SALDO INICIAL DEL DIA (DIARIO)' THEN MONTO 
		                    WHEN TIPO_MOVIMIENTO = 'EGRESO' THEN -MONTO 
		                    ELSE 0 
		                 END) OVER (ORDER BY CONSECUTIVO ASC)
		    END AS SALDO
        FROM (
            SELECT 
                ROW_NUMBER() OVER (ORDER BY FECHA_MOV_FILTRO ASC) AS CONSECUTIVO,
                MOVIMIENTO,
                CDGCO,
                USUARIO_CAJA,
                NOMBRE_CAJERA,
                SUCURSAL,
                CLIENTE,
                TITULAR_CUENTA_EJE,
                FECHA_MOV,
                FECHA_MOV_FILTRO,
                CDG_TICKET,
                MONTO,
                CONCEPTO,
                TIPO_MOVIMIENTO,
                PRODUCTO
            FROM (
             
                (
                   SELECT 
                      MOVIMIENTO,
                      CDG_SUCURSAL AS CDGCO,
                      c.NOMBRE AS SUCURSAL,
                      p.CODIGO AS USUARIO_CAJA,
                      p.NOMBRE1 || ' '|| p.NOMBRE2 || ' ' || p.PRIMAPE || ' '|| p.SEGAPE AS NOMBRE_CAJERA,
                      'NO APLICA' AS CLIENTE, 
                      'NO APLICA' AS TITULAR_CUENTA_EJE, 
                      TO_CHAR(FECHA, 'DD/MM/YYYY HH24:MI:SS') AS FECHA_MOV,
                      FECHA AS FECHA_MOV_FILTRO,
                      'NO APLICA' AS CDG_TICKET, 
                      MONTO, 
                      CASE 
                        WHEN MOVIMIENTO = 0 THEN 'RETIRO DE EFECTIVO'
                        WHEN MOVIMIENTO = 2 THEN 'SALDO INICIAL DEL DIA (DIARIO)'
                        WHEN MOVIMIENTO = 3 THEN 'SALDO FINAL AL CIERRE DE LA SUCURSAL (DIARIO)'
                        ELSE 'FONDEO SUCURSAL'
                    END AS CONCEPTO, 
                    CASE 
                        WHEN MOVIMIENTO = 0 THEN 'EGRESO'
                        WHEN MOVIMIENTO = 2 THEN 'REPORTE'
                        WHEN MOVIMIENTO = 3 THEN 'REPORTE'
                        ELSE 'INGRESO'
                    END AS TIPO_MOVIMIENTO,
                      'AHORRO CUENTA CORRIENTE' AS PRODUCTO 
                      FROM SUC_MOVIMIENTOS_AHORRO sma 
                    INNER JOIN SUC_ESTADO_AHORRO sea ON sea.CODIGO = sma.CDG_ESTADO_AHORRO 
                    INNER JOIN CO c ON c.CODIGO = sea.CDG_SUCURSAL
                    INNER JOIN PE p ON p.CODIGO = sma.CDG_USUARIO
                    WHERE p.CDGEM = 'EMPFIN' 
                    )	
                UNION 
                (
                    SELECT 
                    ma.MOVIMIENTO,
                    c2.CODIGO AS CDGCO,
                    c2.NOMBRE AS SUCURSAL,
                    p.CODIGO AS USUARIO_CAJA,
                    p.NOMBRE1 || ' '|| p.NOMBRE2 || ' ' || p.PRIMAPE || ' '|| p.SEGAPE AS NOMBRE_CAJERA,
                    c.CODIGO AS CLIENTE, 
                    (c.NOMBRE1 || ' ' || c.NOMBRE2 || ' ' || c.PRIMAPE || ' ' || c.SEGAPE) AS TITULAR_CUENTA_EJE, 
                    TO_CHAR(ma.FECHA_MOV, 'DD/MM/YYYY HH24:MI:SS') AS FECHA_MOV,
                    ma.FECHA_MOV AS FECHA_MOV_FILTRO,
                    ma.CDG_TICKET, 
                    CASE 
                        WHEN tpa.DESCRIPCION = 'CAPITAL INICIAL - CUENTA CORRIENTE' THEN ma.MONTO - (
                            SELECT ma2.MONTO 
                            FROM MOVIMIENTOS_AHORRO ma2 
                            INNER JOIN TIPO_PAGO_AHORRO tpa2 ON tpa2.CODIGO = ma2.CDG_TIPO_PAGO 
                            WHERE tpa2.DESCRIPCION = 'APERTURA DE CUENTA - INSCRIPCIÓN' 
                            AND ma2.CDG_TICKET = ma.CDG_TICKET
                        )
                        ELSE ma.MONTO
                    END AS MONTO,
                    tpa.DESCRIPCION AS CONCEPTO, 
                    CASE 
                        WHEN tpa.DESCRIPCION IN ('APERTURA DE CUENTA - INSCRIPCIÓN', 'CAPITAL INICIAL - CUENTA CORRIENTE', 'DEPOSITO') THEN 'INGRESO'
                        WHEN tpa.DESCRIPCION IN ('RETIRO', 'ENTREGA RETIRO PROGRAMADO', 'ENTREGA RETIRO EXPRESS') THEN 'EGRESO'
                        WHEN tpa.DESCRIPCION IN ('TRANSFERENCIA INVERSIÓN (ENVIO)') THEN 'EGRESO SISTEMA'
                        ELSE 'MOVIMIENTO VIRTUAL'
                    END AS TIPO_MOVIMIENTO,
                    CASE 
                        WHEN tpa.DESCRIPCION = 'TRANSFERENCIA INVERSIÓN (ENVIO)' AND pp.DESCRIPCION = 'Ahorro Corriente' THEN 'INVERSION'
                        ELSE pp.DESCRIPCION 
                    END AS PRODUCTO
                FROM MOVIMIENTOS_AHORRO ma
                INNER JOIN TIPO_PAGO_AHORRO tpa ON tpa.CODIGO = ma.CDG_TIPO_PAGO 
                INNER JOIN ASIGNA_PROD_AHORRO apa ON apa.CONTRATO = ma.CDG_CONTRATO 
                INNER JOIN PR_PRIORITARIO pp ON pp.CODIGO = apa.CDGPR_PRIORITARIO 
                INNER JOIN CL c ON c.CODIGO = apa.CDGCL 
                INNER JOIN CO c2 ON c2.CODIGO = apa.CDGCO 
                INNER JOIN TICKETS_AHORRO ta ON ta.CODIGO = ma.CDG_TICKET 
                INNER JOIN PE p ON p.CODIGO = ta.CDGPE 
                WHERE p.CDGEM = 'EMPFIN'
                )
            )
        ) 
        WHERE TIPO_MOVIMIENTO != 'MOVIMIENTO VIRTUAL' AND TIPO_MOVIMIENTO != 'EGRESO SISTEMA'
        AND FECHA_MOV_FILTRO BETWEEN TO_TIMESTAMP('$Inicial 00:00:00', 'YYYY-MM-DD HH24:MI:SS') AND TO_TIMESTAMP('$Final 23:59:59', 'YYYY-MM-DD HH24:MI:SS')
        $suc
        $pro
        $ope
        ORDER BY CONSECUTIVO ASC
        
        
        
sql;


        try {
            $mysqli = new Database();
            $res = $mysqli->queryAll($query);
            if ($res) return $res;
            return array();
        } catch (Exception $e) {
            return array();
        }
    }

    public static function GetAllTransaccionesDetalle($Inicial, $Final, $Operacion, $Producto, $Sucursal)
    {
        if ($Inicial == '' || $Final == '') {
            $fechaActual = date('Y-m-d');
            $Inicial = $fechaActual;
            $Final = $fechaActual;
        }

        if ($Operacion == '' || $Operacion == '0') {
            $ope = "";
        } else {
            if ($Operacion == 1) {
                $operac = 'APERTURA DE CUENTA - INSCRIPCIÓN';
            } else if ($Operacion == 2) {
                $operac = 'CAPITAL INICIAL - CUENTA CORRIENTE';
            } else if ($Operacion == 3) {
                $operac = 'DEPOSITO';
            } else if ($Operacion == 4) {
                $operac = 'RETIRO';
            } else if ($Operacion == 5) {
                $operac = 'DEVOLUCIÓN RETIRO EXPRESS';
            } else if ($Operacion == 6) {
                $operac = 'DEVOLUCIÓN RETIRO PROGRAMADO';
            } else if ($Operacion == 7) {
                $operac = 'RETIRO EXPRESS';
            } else if ($Operacion == 8) {
                $operac = 'RETIRO PROGRAMADO';
            } else if ($Operacion == 9) {
                $operac = 'TRANSFERENCIA INVERSION';
            } else if ($Operacion == 10) {
                $operac = 'TRANSFERENCIA INVERSION A AHORRO';
            }
            $ope = " AND CONCEPTO = '" . $operac . "'";
        }

        if ($Producto == '' || $Producto == 0) {
            $pro = '';
        } else {
            if ($Producto == '3') {
                $pro = "AND PRODUCTO = 'TRANSFERENCIA INVERSION'";
            } else if ($Producto == '1') {
                $pro = "AND PRODUCTO = 'AHORRO CUENTA CORRIENTE'";
            } else if ($Producto == '2') {
                $pro = "AND PRODUCTO = 'AHORRO CUENTA PEQUE'";
            }
        }


        if ($Sucursal == '' || $Sucursal == 0) {
            $suc = "";
        } else {
            $suc = " AND CDGCO = '" . $Sucursal . "'";
        }




        $query = <<<SQL
        SELECT
            CONSECUTIVO,
            MOVIMIENTO,
            CDGCO,
            SUCURSAL,
            USUARIO_CAJA,
            NOMBRE_CAJERA,
            NOMBRE_PROMOTOR,
            CLIENTE,
            TITULAR_CUENTA_EJE,
            FECHA_MOV,
            FECHA_MOV_FILTRO,
            FECHA_MOV_APLICA,
            CDG_TICKET,
            MONTO,
            CONCEPTO,
            PLAZO_INVERSION,
            FECHA_FIN_INVERSION,
            TIPO_MOVIMIENTO,
            PRODUCTO,
            CASE
                WHEN TIPO_MOVIMIENTO = 'INGRESO' THEN MONTO
                ELSE 0
            END AS INGRESO,
            CASE
                WHEN TIPO_MOVIMIENTO = 'EGRESO' THEN MONTO
                ELSE 0
            END AS EGRESO,
            CASE
                WHEN TIPO_MOVIMIENTO = 'REPORTE' THEN MONTO
                ELSE 0
            END AS REPORTE,
            CASE
                WHEN TIPO_MOVIMIENTO = 'REPORTE' THEN MONTO
                ELSE SUM(
                    CASE
                        WHEN TIPO_MOVIMIENTO = 'INGRESO' THEN MONTO
                        WHEN TIPO_MOVIMIENTO = 'EGRESO' THEN - MONTO
                        ELSE 0
                    END
                ) OVER (
                    ORDER BY
                        CONSECUTIVO ASC
                )
            END AS SALDO,
            ID_MENOR,
            NOMBRE_MENOR
        FROM
            (
                SELECT
                    ROW_NUMBER() OVER (
                        ORDER BY
                            FECHA_MOV_FILTRO ASC
                    ) AS CONSECUTIVO,
                    MOVIMIENTO,
                    CDGCO,
                    USUARIO_CAJA,
                    NOMBRE_CAJERA,
                    NOMBRE_PROMOTOR,
                    SUCURSAL,
                    CLIENTE,
                    TITULAR_CUENTA_EJE,
                    FECHA_MOV,
                    FECHA_MOV_APLICA,
                    FECHA_MOV_FILTRO,
                    CDG_TICKET,
                    MONTO,
                    CONCEPTO,
                    PLAZO_INVERSION,
                    FECHA_FIN_INVERSION,
                    TIPO_MOVIMIENTO,
                    PRODUCTO,
                    ID_MENOR,
                    NOMBRE_MENOR
                FROM
                    (
                        (
                            SELECT
                                MOVIMIENTO,
                                CDG_SUCURSAL AS CDGCO,
                                c.NOMBRE AS SUCURSAL,
                                p.CODIGO AS USUARIO_CAJA,
                                p.NOMBRE1 || ' ' || p.NOMBRE2 || ' ' || p.PRIMAPE || ' ' || p.SEGAPE AS NOMBRE_CAJERA,
                                NULL AS NOMBRE_PROMOTOR,
                                'NO APLICA' AS CLIENTE,
                                'NO APLICA' AS TITULAR_CUENTA_EJE,
                                TO_CHAR(FECHA, 'DD/MM/YYYY HH24:MI:SS') AS FECHA_MOV,
                                TO_CHAR(FECHA, 'DD/MM/YYYY') AS FECHA_MOV_APLICA,
                                FECHA AS FECHA_MOV_FILTRO,
                                'NO APLICA' AS CDG_TICKET,
                                MONTO,
                                CASE
                                    WHEN MOVIMIENTO = 0 THEN 'RETIRO DE EFECTIVO'
                                    WHEN MOVIMIENTO = 2 THEN 'SALDO INICIAL DEL DIA (DIARIO)'
                                    WHEN MOVIMIENTO = 3 THEN 'SALDO FINAL AL CIERRE DE LA SUCURSAL (DIARIO)'
                                    ELSE 'FONDEO SUCURSAL'
                                END AS CONCEPTO,
                                NULL AS PLAZO_INVERSION,
                                NULL AS FECHA_FIN_INVERSION,
                                CASE
                                    WHEN MOVIMIENTO = 0 THEN 'EGRESO'
                                    WHEN MOVIMIENTO = 2 THEN 'REPORTE'
                                    WHEN MOVIMIENTO = 3 THEN 'REPORTE'
                                    ELSE 'INGRESO'
                                END AS TIPO_MOVIMIENTO,
                                'AHORRO CUENTA CORRIENTE' AS PRODUCTO,
                                NULL AS ID_MENOR,
                                NULL AS NOMBRE_MENOR
                            FROM
                                SUC_MOVIMIENTOS_AHORRO sma
                                INNER JOIN SUC_ESTADO_AHORRO sea ON sea.CODIGO = sma.CDG_ESTADO_AHORRO
                                INNER JOIN CO c ON c.CODIGO = sea.CDG_SUCURSAL
                                INNER JOIN PE p ON p.CODIGO = sma.CDG_USUARIO
                            WHERE
                                p.CDGEM = 'EMPFIN'
                        )
                        UNION
                        (
                            SELECT
                                ma.MOVIMIENTO,
                                c2.CODIGO AS CDGCO,
                                c2.NOMBRE AS SUCURSAL,
                                p.CODIGO AS USUARIO_CAJA,
                                p.NOMBRE1 || ' ' || p.NOMBRE2 || ' ' || p.PRIMAPE || ' ' || p.SEGAPE AS NOMBRE_CAJERA,
                                CASE
                                    WHEN LENGTH(ma.CDG_CONTRATO) > 14 THEN (
                                        SELECT
                                            CONCATENA_NOMBRE(PE.NOMBRE1, PE.NOMBRE2, PE.PRIMAPE, PE.SEGAPE)
                                        FROM
                                            PE
                                        WHERE
                                            PE.CODIGO = (
                                                SELECT
                                                    CLP.CDGPE_REGISTRO
                                                FROM
                                                    CL_PQS CLP
                                                WHERE
                                                    CLP.CDG_CONTRATO = ma.CDG_CONTRATO
                                            )
                                    )
                                    ELSE NULL
                                END AS NOMBRE_PROMOTOR,
                                c.CODIGO AS CLIENTE,
                                (
                                    c.NOMBRE1 || ' ' || c.NOMBRE2 || ' ' || c.PRIMAPE || ' ' || c.SEGAPE
                                ) AS TITULAR_CUENTA_EJE,
                                TO_CHAR(ma.FECHA_MOV, 'DD/MM/YYYY HH24:MI:SS') AS FECHA_MOV,
                                TO_CHAR(ma.FECHA_MOV, 'DD/MM/YYYY') AS FECHA_MOV_APLICA,
                                ma.FECHA_MOV AS FECHA_MOV_FILTRO,
                                ma.CDG_TICKET,
                                CASE
                                    WHEN tpa.DESCRIPCION = 'CAPITAL INICIAL - CUENTA CORRIENTE' THEN ma.MONTO - (
                                        SELECT
                                            ma2.MONTO
                                        FROM
                                            MOVIMIENTOS_AHORRO ma2
                                            INNER JOIN TIPO_PAGO_AHORRO tpa2 ON tpa2.CODIGO = ma2.CDG_TIPO_PAGO
                                        WHERE
                                            tpa2.DESCRIPCION = 'APERTURA DE CUENTA - INSCRIPCIÓN'
                                            AND ma2.CDG_TICKET = ma.CDG_TICKET
                                    )
                                    ELSE ma.MONTO
                                END AS MONTO,
                                tpa.DESCRIPCION AS CONCEPTO,
                                CASE
                                    WHEN tpa.DESCRIPCION = 'TRANSFERENCIA INVERSIÓN (ENVIO)' THEN (
                                        SELECT
                                            MONTHS_BETWEEN(ca.FECHA_VENCIMIENTO, ca.FECHA_APERTURA) || ' MESES'
                                        FROM
                                            CUENTA_INVERSION ca
                                        WHERE
                                            ca.CODIGO = ma.CDG_INVERSION
                                    )
                                    ELSE NULL
                                END AS PLAZO_INVERSION,
                                CASE
                                    WHEN tpa.DESCRIPCION = 'TRANSFERENCIA INVERSIÓN (ENVIO)' THEN (
                                        SELECT
                                            TO_CHAR(ca.FECHA_VENCIMIENTO, 'DD/MM/YYYY')
                                        FROM
                                            CUENTA_INVERSION ca
                                        WHERE
                                            ca.CODIGO = ma.CDG_INVERSION
                                    )
                                    ELSE NULL
                                END AS FECHA_FIN_INVERSION,
                                CASE
                                    WHEN tpa.DESCRIPCION IN (
                                        'APERTURA DE CUENTA - INSCRIPCIÓN',
                                        'CAPITAL INICIAL - CUENTA CORRIENTE',
                                        'DEPOSITO'
                                    ) THEN 'INGRESO'
                                    WHEN tpa.DESCRIPCION IN (
                                        'RETIRO',
                                        'TRANSFERENCIA INVERSIÓN (ENVIO)',
                                        'ENTREGA RETIRO EXPRESS',
                                        'ENTREGA RETIRO PROGRAMADO'
                                    ) THEN 'EGRESO'
                                    ELSE 'MOVIMIENTO VIRTUAL'
                                END AS TIPO_MOVIMIENTO,
                                CASE
                                    WHEN tpa.DESCRIPCION = 'TRANSFERENCIA INVERSIÓN (ENVIO)'
                                    AND pp.DESCRIPCION = 'Ahorro Corriente' THEN 'INVERSION'
                                    ELSE pp.DESCRIPCION
                                END AS PRODUCTO,
                                CASE
                                    WHEN LENGTH(ma.CDG_CONTRATO) > 14 THEN SUBSTR(ma.CDG_CONTRATO, -2)
                                    ELSE NULL
                                END AS ID_MENOR,
                                CASE
                                    WHEN LENGTH(ma.CDG_CONTRATO) > 14 THEN (
                                        SELECT
                                            CONCATENA_NOMBRE(
                                                CLP.NOMBRE1,
                                                CLP.NOMBRE2,
                                                CLP.APELLIDO1,
                                                CLP.APELLIDO2
                                            )
                                        FROM
                                            CL_PQS CLP
                                        WHERE
                                            CLP.CDG_CONTRATO = ma.CDG_CONTRATO
                                    )
                                    ELSE NULL
                                END AS NOMBRE__MENOR
                            FROM
                                MOVIMIENTOS_AHORRO ma
                                INNER JOIN TIPO_PAGO_AHORRO tpa ON tpa.CODIGO = ma.CDG_TIPO_PAGO
                                INNER JOIN ASIGNA_PROD_AHORRO apa ON apa.CONTRATO = ma.CDG_CONTRATO
                                INNER JOIN PR_PRIORITARIO pp ON pp.CODIGO = apa.CDGPR_PRIORITARIO
                                INNER JOIN CL c ON c.CODIGO = apa.CDGCL
                                INNER JOIN CO c2 ON c2.CODIGO = apa.CDGCO
                                INNER JOIN TICKETS_AHORRO ta ON ta.CODIGO = ma.CDG_TICKET
                                INNER JOIN PE p ON p.CODIGO = ta.CDGPE
                            WHERE
                                p.CDGEM = 'EMPFIN'
                        )
                        UNION
                        (
                            SELECT
                                ma.MOVIMIENTO,
                                c2.CODIGO AS CDGCO,
                                c2.NOMBRE AS SUCURSAL,
                                p.CODIGO AS USUARIO_CAJA,
                                p.NOMBRE1 || ' ' || p.NOMBRE2 || ' ' || p.PRIMAPE || ' ' || p.SEGAPE AS NOMBRE_CAJERA,
                                NULL AS NOMBRE_PROMOTOR,
                                c.CODIGO AS CLIENTE,
                                (
                                    c.NOMBRE1 || ' ' || c.NOMBRE2 || ' ' || c.PRIMAPE || ' ' || c.SEGAPE
                                ) AS TITULAR_CUENTA_EJE,
                                TO_CHAR(ma.FECHA_MOV, 'DD/MM/YYYY HH24:MI:SS') AS FECHA_MOV,
                                TO_CHAR(ma.FECHA_MOV, 'DD/MM/YYYY') AS FECHA_MOV_APLICA,
                                ma.FECHA_MOV AS FECHA_MOV_FILTRO,
                                ma.CDG_TICKET,
                                tpa.MONTO_INVERSION AS MONTO,
                                'TRANSFERENCIA INVERSIÓN (RECEPCIÓN)' AS CONCEPTO,
                                NULL AS PLAZO_INVERSION,
                                NULL AS FECHA_FIN_INVERSION,
                                'INGRESO' TIPO_MOVIMIENTO,
                                'INVERSION' AS PRODUCTO,
                                NULL AS ID_MENOR,
                                NULL AS NOMBRE_MENOR
                            FROM
                                MOVIMIENTOS_AHORRO ma
                                INNER JOIN CUENTA_INVERSION tpa ON tpa.FECHA_APERTURA = ma.FECHA_MOV
                                INNER JOIN ASIGNA_PROD_AHORRO apa ON apa.CONTRATO = ma.CDG_CONTRATO
                                INNER JOIN PR_PRIORITARIO pp ON pp.CODIGO = apa.CDGPR_PRIORITARIO
                                INNER JOIN CL c ON c.CODIGO = apa.CDGCL
                                INNER JOIN CO c2 ON c2.CODIGO = apa.CDGCO
                                INNER JOIN TICKETS_AHORRO ta ON ta.CODIGO = ma.CDG_TICKET
                                INNER JOIN PE p ON p.CODIGO = ta.CDGPE
                            WHERE
                                p.CDGEM = 'EMPFIN'
                        )
                    )
            )
        WHERE
        FECHA_MOV_FILTRO BETWEEN TO_TIMESTAMP('$Inicial 00:00:00', 'YYYY-MM-DD HH24:MI:SS') AND TO_TIMESTAMP('$Final 23:59:59', 'YYYY-MM-DD HH24:MI:SS')
        $suc
        $pro
        $ope
        ORDER BY CONSECUTIVO ASC
        SQL;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryAll($query);
            if ($res) return $res;
            return array();
        } catch (Exception $e) {
            return array();
        }
    }

    public static function GetSolicitudesPendientesAdminAll()
    {
        $query = <<<sql
        SELECT tar.CODIGO AS CODIGO_REIMPRIME, tar.CDGTICKET_AHORRO, apa.CONTRATO, (c.NOMBRE1 || ' ' || c.NOMBRE2 || ' ' || c.PRIMAPE || ' ' || c.SEGAPE) AS NOMBRE_CLIENTE,
        tar.MOTIVO, ta.MONTO, tar.DESCRIPCION_MOTIVO, tar.FREGISTRO, (p.NOMBRE1 || ' ' || p.NOMBRE2 || ' ' || p.PRIMAPE || ' ' || p.SEGAPE) AS NOMBRE_CAJERA  FROM TICKETS_AHORRO_REIMPRIME tar 
        INNER JOIN TICKETS_AHORRO ta ON ta.CODIGO = tar.CDGTICKET_AHORRO 
        INNER JOIN ASIGNA_PROD_AHORRO apa ON apa.CONTRATO = ta.CDG_CONTRATO 
        INNER JOIN CL c ON c.CODIGO = apa.CDGCL 
        INNER JOIN PE p ON p.CODIGO = tar.CDGPE_SOLICITA 
        WHERE p.CDGEM = 'EMPFIN'
        AND tar.AUTORIZA = '0'
        sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryAll($query);
            if ($res) return $res;
            return array();
        } catch (Exception $e) {
            return array();
        }
    }

    public static function GetSolicitudesHistorialAdminAll()
    {
        $query = <<<sql
        SELECT tar.CDGTICKET_AHORRO, apa.CONTRATO, (c.NOMBRE1 || ' ' || c.NOMBRE2 || ' ' || c.PRIMAPE || ' ' || c.SEGAPE) AS NOMBRE_CLIENTE,
        tar.MOTIVO, ta.MONTO, tar.DESCRIPCION_MOTIVO, tar.FREGISTRO, tar.AUTORIZA, tar.FAUTORIZA, tar.CDGPE_AUTORIZA, (pp.NOMBRE1 || ' ' || pp.NOMBRE2 || ' ' || pp.PRIMAPE || ' ' || pp.SEGAPE) AS TESORERIA  FROM TICKETS_AHORRO_REIMPRIME tar 
        INNER JOIN TICKETS_AHORRO ta ON ta.CODIGO = tar.CDGTICKET_AHORRO 
        INNER JOIN ASIGNA_PROD_AHORRO apa ON apa.CONTRATO = ta.CDG_CONTRATO 
        INNER JOIN CL c ON c.CODIGO = apa.CDGCL 
        INNER JOIN PE p ON p.CODIGO = tar.CDGPE_SOLICITA 
        INNER JOIN PE pp ON pp.CODIGO = tar.CDGPE_AUTORIZA 
        WHERE p.CDGEM = 'EMPFIN'
        AND  pp.CDGEM = 'EMPFIN'
        AND tar.AUTORIZA != '0'
        ORDER BY tar.FREGISTRO

sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryAll($query);
            if ($res) return $res;
            return array();
        } catch (Exception $e) {
            return array();
        }
    }


    public static function AutorizaSolicitudtICKET($update, $user)
    {
        $query = <<<sql
        UPDATE ESIACOM.TICKETS_AHORRO_REIMPRIME
        SET CDGPE_AUTORIZA='$user', AUTORIZA= '$update->_valor', FAUTORIZA=CURRENT_TIMESTAMP
        WHERE CODIGO = $update->_ticket
sql;

        $mysqli = new Database();
        return $mysqli->insert($query);
    }


    public static function HistoricoArqueo($datos)
    {
        $qry = <<<sql
        SELECT
            TO_CHAR(AR.FECHA, 'DD/MM/YYYY HH24:MI:SS') AS FECHA,
            AR.CDG_USUARIO AS EJECUTIVO,
            (
                SELECT
                    CONCATENA_NOMBRE(PE.NOMBRE1, PE.NOMBRE2, PE.PRIMAPE, PE.SEGAPE)
                FROM
                    PE
                WHERE
                    PE.CODIGO = AR.CDG_USUARIO
                    AND PE.CDGEM = 'EMPFIN'
            ) AS NOMBRE_EJECUTIVO,
            AR.CDG_SUCURSAL AS CDG_SUCURSAL,
            (
                SELECT
                    CO.NOMBRE
                FROM
                    CO
                WHERE
                    CO.CODIGO = AR.CDG_SUCURSAL
            ) AS SUCURSAL,
            AR.MONTO
        FROM
            ARQUEO AR
        sql;

        if ($datos) {
            $parametros = [];
            if ($datos['fecha_inicio'] && $datos['fecha_fin']) array_push($parametros, "TRUNC(AR.FECHA) BETWEEN TO_DATE('" . $datos['fecha_inicio'] . "', 'YYYY-MM-DD') AND TO_DATE('" . $datos['fecha_fin'] . "', 'YYYY-MM-DD')");
            if ($datos['sucursal']) array_push($parametros, "AR.CDG_SUCURSAL = '" . $datos['sucursal'] . "'");
            if ($datos['ejecutivo']) array_push($parametros, "AR.CDG_USUARIO = '" . $datos['ejecutivo'] . "'");
            if (count($parametros) > 0) $qry .= " WHERE " . implode(" AND ", $parametros);
        }

        $qry .= " ORDER BY AR.FECHA DESC";

        try {
            $mysqli = new Database();
            $res = $mysqli->queryAll($qry);
            if ($res) return self::Responde(true, "Consulta realizada correctamente.", $res);
            return self::Responde(false, "No se encontraron registros para la consulta.", $qry);
        } catch (Exception $e) {
            return self::Responde(false, "Ocurrió un error al consultar los registros.", null, $e->getMessage());
        }
    }

    public static function RegistraArqueo($datos)
    {
        $qryValidacion = <<<sql
        SELECT
            SALDO
        FROM
            SUC_ESTADO_AHORRO
        WHERE
            CDG_SUCURSAL = '{$datos['sucursal']}'
        sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryOne($qryValidacion);
            if (!$res) return self::Responde(false, "No se encontró el saldo de la sucursal {$datos['sucursal']}.");
            if ($res['SALDO'] > $datos['monto']) return self::Responde(false, "No es posible realizar el arqueo ya que hay un saldo negativo.");

            $qry = <<<sql
            INSERT INTO ARQUEO
                (CDG_ARQUEO, CDG_USUARIO, CDG_SUCURSAL, FECHA, MONTO, B_1000, B_500, B_200, B_100, B_50, B_20, M_10, M_5, M_2, M_1, M_050, M_020, M_010, SALDO_SUCURSAL)
            VALUES
                ((SELECT NVL(MAX(CDG_ARQUEO),0) FROM ARQUEO) + 1, :ejecutivo, :sucursal, SYSDATE, :monto, :b_1000, :b_500, :b_200, :b_100, :b_50, :b_20, :m_10, :m_5, :m_2, :m_1, :m_050, :m_020, :m_010, :saldo)
            sql;

            $parametros = [
                'ejecutivo' => $datos['ejecutivo'],
                'sucursal' => $datos['sucursal'],
                'monto' => $datos['monto'],
                'b_1000' => $datos['b_1000'],
                'b_500' => $datos['b_500'],
                'b_200' => $datos['b_200'],
                'b_100' => $datos['b_100'],
                'b_50' => $datos['b_50'],
                'b_20' => $datos['b_20'],
                'm_10' => $datos['m_10'],
                'm_5' => $datos['m_5'],
                'm_2' => $datos['m_2'],
                'm_1' => $datos['m_1'],
                'm_050' => $datos['m_050'],
                'm_020' => $datos['m_020'],
                'm_010' => $datos['m_010'],
                'saldo' => $res['SALDO']
            ];

            $res = $mysqli->insertar($qry, $parametros);
            return self::Responde(true, "Arqueo registrado correctamente.");
        } catch (Exception $e) {
            return self::Responde(false, "Ocurrió un error al registrar el arqueo.", null, $e->getMessage());
        }
    }

    public static function DatosTicketArqueo($datos)
    {
        $qry = <<<sql
        SELECT
            *
        FROM
            (
                SELECT
                AR.CDG_ARQUEO,
                AR.CDG_USUARIO,
                (SELECT CONCATENA_NOMBRE(PE.NOMBRE1, PE.NOMBRE2, PE.PRIMAPE, PE.SEGAPE) FROM PE WHERE PE.CODIGO = AR.CDG_USUARIO AND PE.CDGEM = 'EMPFIN') AS USUARIO,
                AR.CDG_SUCURSAL,
                (SELECT NOMBRE FROM CO WHERE CODIGO = AR.CDG_SUCURSAL) AS SUCURSAL,
                TO_CHAR(AR.FECHA, 'DD/MM/YYYY HH24:MI:SS') AS FECHA,
                AR.MONTO,
                AR.B_1000,
                AR.B_500,
                AR.B_200,
                AR.B_100,
                AR.B_50,
                AR.B_20,
                AR.M_10,
                AR.M_5,
                AR.M_2,
                AR.M_1,
                AR.M_050,
                AR.M_020,
                AR.M_010
                FROM
                    ARQUEO AR
                WHERE
                    AR.CDG_SUCURSAL = '{$datos['sucursal']}'
                ORDER BY
                    AR.FECHA DESC
            )
        sql;

        if (isset($datos['arqueo'])) {
            $qry .= " WHERE CDG_ARQUEO = '{$datos['arqueo']}'";
        } else {
            $qry .= " WHERE ROWNUM <= 1";
        }

        try {
            $mysqli = new Database();
            return $mysqli->queryOne($qry);
        } catch (Exception $e) {
            return [];
        }
    }

    public static function GetModuloAhorroPermisos($update, $user)
    {
        $query = <<<sql
        UPDATE ESIACOM.TICKETS_AHORRO_REIMPRIME
        SET CDGPE_AUTORIZA='$user', AUTORIZA= '$update->_valor', FAUTORIZA=CURRENT_TIMESTAMP
        WHERE CODIGO = $update->_ticket
sql;

        $mysqli = new Database();
        return $mysqli->insert($query);
    }

    public static function GetSolicitudesRetiroAhorroOrdinario()
    {
        $query = <<<sql
        SELECT 
        sra.ID_SOL_RETIRO_AHORRO, 
        sra.CONTRATO, 
        CONCATENA_NOMBRE(c.NOMBRE1, c.NOMBRE2, c.PRIMAPE, c.SEGAPE) AS CLIENTE, 
        TO_CHAR(sra.FECHA_SOLICITUD, 'Day DD Month YYYY (DD/MM/YYYY)') AS FECHA_SOLICITUD,
        TO_CHAR(sra.FECHA_SOLICITUD, 'DD/MM/YYYY') AS FECHA_SOLICITUD_EXCEL,
        TO_CHAR(sra.FECHA_REGISTRO, 'Day DD Month YYYY (DD/MM/YYYY)') AS FECHA_REGISTRO,
        CASE
            WHEN TRUNC(SYSDATE) = TRUNC(sra.FECHA_REGISTRO) THEN 'Hoy'
            ELSE TO_CHAR(TRUNC(SYSDATE) - TRUNC(sra.FECHA_REGISTRO))
        END AS days_since_order,
        CASE
            WHEN (TRUNC(SYSDATE) - TRUNC(sra.FECHA_REGISTRO)) > 7 THEN 
                'VENCIDA (' || TO_CHAR((TRUNC(SYSDATE) - TRUNC(sra.FECHA_REGISTRO)) - 7) || ' días vencida)'
            ELSE 'EN TIEMPO (' || TO_CHAR(7 - (TRUNC(SYSDATE) - TRUNC(sra.FECHA_REGISTRO))) || ' días restantes)'
        END AS solicitud_vencida,
        sra.CANTIDAD_SOLICITADA, 
        sra.CDGPE,
        CONCATENA_NOMBRE(p.NOMBRE1, p.NOMBRE2, p.PRIMAPE, p.SEGAPE) AS CDGPE_NOMBRE, 
        sra.TIPO_RETIRO, 
        sra.FECHA_ENTREGA,
        UPPER(pp.DESCRIPCION) AS TIPO_PRODUCTO,
        (SELECT NOMBRE FROM CO WHERE CODIGO = sra.CDG_SUCURSAL AND CDGEM = 'EMPFIN') AS SUCURSAL
    FROM 
        SOLICITUD_RETIRO_AHORRO sra 
    INNER JOIN 
        ASIGNA_PROD_AHORRO apa ON apa.CONTRATO = sra.CONTRATO 
    INNER JOIN 
        PR_PRIORITARIO pp ON pp.CODIGO = apa.CDGPR_PRIORITARIO 
    INNER JOIN 
        CL c ON c.CODIGO = apa.CDGCL 
    INNER JOIN 
        PE p ON p.CODIGO = sra.CDGPE 
    WHERE 
        sra.ESTATUS = 0 
        AND sra.CDGPE_ASIGNA_ESTATUS IS NULL
        AND sra.TIPO_RETIRO = 2
        AND p.CDGEM = 'EMPFIN'
    ORDER BY 
        sra.FECHA_ESTATUS
            
sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryAll($query);
            if ($res) return $res;
            return array();
        } catch (Exception $e) {
            return array();
        }
    }

    public static function GetSolicitudesRetiroAhorroOrdinariaHistorial()
    {
        $query = <<<sql
       
        SELECT 
            sra.ID_SOL_RETIRO_AHORRO, 
            sra.CONTRATO, 
            c.NOMBRE1 || ' ' || c.NOMBRE2 || ' ' || c.PRIMAPE || ' ' || c.SEGAPE AS CLIENTE, 
            TO_CHAR(sra.FECHA_SOLICITUD, 'Day DD Month YYYY (DD/MM/YYYY)') AS FECHA_SOLICITUD,
            TO_CHAR(sra.FECHA_SOLICITUD, 'DD/MM/YYYY') AS FECHA_SOLICITUD_EXCEL,
            TO_CHAR(sra.FECHA_ENTREGA, 'DD/MM/YYYY') AS FECHA_SOLICITUD_EXCEL_ENTREGA,
            sra.CANTIDAD_SOLICITADA, 
            sra.CDGPE,
            p.NOMBRE1 || ' ' || p.NOMBRE2 || ' ' || p.PRIMAPE || ' ' || p.SEGAPE AS CDGPE_NOMBRE, 
            sra.CDGPE_ASIGNA_ESTATUS,
            p2.NOMBRE1 || ' ' || p2.NOMBRE2 || ' ' || p2.PRIMAPE || ' ' || p2.SEGAPE AS CDGPE_NOMBRE_AUTORIZA, 
            sra.TIPO_RETIRO, 
            sra.FECHA_ENTREGA,
            UPPER(pp.DESCRIPCION) AS TIPO_PRODUCTO
        FROM 
            SOLICITUD_RETIRO_AHORRO sra 
        INNER JOIN 
            ASIGNA_PROD_AHORRO apa ON apa.CONTRATO = sra.CONTRATO 
        INNER JOIN 
            PR_PRIORITARIO pp ON pp.CODIGO = apa.CDGPR_PRIORITARIO 
        INNER JOIN 
            CL c ON c.CODIGO = apa.CDGCL 
        INNER JOIN 
            PE p ON p.CODIGO = sra.CDGPE 
        INNER JOIN 
            PE p2 ON p2.CODIGO = sra.CDGPE_ASIGNA_ESTATUS 
        WHERE 
            sra.ESTATUS != 0 
            AND sra.CDGPE_ASIGNA_ESTATUS IS NOT NULL
            AND sra.TIPO_RETIRO = 2
                
sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryAll($query);
            if ($res) return $res;
            return array();
        } catch (Exception $e) {
            return array();
        }
    }

    public static function GetSolicitudesRetiroAhorroExpress()
    {
        $query = <<<sql
       
         SELECT 
        sra.ID_SOL_RETIRO_AHORRO, 
        sra.CONTRATO, 
        c.NOMBRE1 || ' ' || c.NOMBRE2 || ' ' || c.PRIMAPE || ' ' || c.SEGAPE AS CLIENTE, 
        TO_CHAR(sra.FECHA_SOLICITUD, 'Day DD Month YYYY (DD/MM/YYYY)') AS FECHA_SOLICITUD,
        TO_CHAR(sra.FECHA_SOLICITUD, 'DD/MM/YYYY') AS FECHA_SOLICITUD_EXCEL,
        TO_CHAR(sra.FECHA_REGISTRO, 'Day DD Month YYYY (DD/MM/YYYY)') AS FECHA_REGISTRO,
        CASE
            WHEN TRUNC(SYSDATE) = TRUNC(sra.FECHA_REGISTRO) THEN 'Hoy'
            ELSE TO_CHAR(TRUNC(SYSDATE) - TRUNC(sra.FECHA_REGISTRO))
        END AS days_since_order,
        CASE
            WHEN (TRUNC(SYSDATE) - TRUNC(sra.FECHA_REGISTRO)) > 1 THEN 
                'VENCIDA (' || TO_CHAR((TRUNC(SYSDATE) - TRUNC(sra.FECHA_REGISTRO)) - 0) || ' días vencida)'
            ELSE 'EN TIEMPO (' || TO_CHAR(0 - (TRUNC(SYSDATE) - TRUNC(sra.FECHA_REGISTRO))) || ' días restantes)'
        END AS solicitud_vencida,
        sra.CANTIDAD_SOLICITADA, 
        sra.CDGPE,
        p.NOMBRE1 || ' ' || p.NOMBRE2 || ' ' || p.PRIMAPE || ' ' || p.SEGAPE AS CDGPE_NOMBRE, 
        sra.TIPO_RETIRO, 
        sra.FECHA_ENTREGA,
        UPPER(pp.DESCRIPCION) AS TIPO_PRODUCTO,
        (SELECT NOMBRE FROM CO WHERE CODIGO = sra.CDG_SUCURSAL AND CDGEM = 'EMPFIN') AS SUCURSAL
    FROM 
        SOLICITUD_RETIRO_AHORRO sra 
    INNER JOIN 
        ASIGNA_PROD_AHORRO apa ON apa.CONTRATO = sra.CONTRATO 
    INNER JOIN 
        PR_PRIORITARIO pp ON pp.CODIGO = apa.CDGPR_PRIORITARIO 
    INNER JOIN 
        CL c ON c.CODIGO = apa.CDGCL 
    INNER JOIN 
        PE p ON p.CODIGO = sra.CDGPE 
    WHERE 
        sra.ESTATUS = 0 
        AND sra.CDGPE_ASIGNA_ESTATUS IS NULL
        AND sra.TIPO_RETIRO = 1
        AND p.CDGEM = 'EMPFIN'
    ORDER BY 
        sra.FECHA_ESTATUS
            
sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryAll($query);
            if ($res) return $res;
            return array();
        } catch (Exception $e) {
            return array();
        }
    }

    public static function GetSolicitudesRetiroAhorroExpressHistorial()
    {
        $query = <<<sql
       
        SELECT 
        sra.ID_SOL_RETIRO_AHORRO, 
        sra.CONTRATO, 
        c.NOMBRE1 || ' ' || c.NOMBRE2 || ' ' || c.PRIMAPE || ' ' || c.SEGAPE AS CLIENTE, 
        TO_CHAR(sra.FECHA_SOLICITUD, 'Day DD Month YYYY (DD/MM/YYYY)') AS FECHA_SOLICITUD,
        TO_CHAR(sra.FECHA_SOLICITUD, 'DD/MM/YYYY') AS FECHA_SOLICITUD_EXCEL,
        TO_CHAR(sra.FECHA_REGISTRO, 'Day DD Month YYYY (DD/MM/YYYY)') AS FECHA_REGISTRO,
        CASE
            WHEN TRUNC(SYSDATE) = TRUNC(sra.FECHA_REGISTRO) THEN 'Hoy'
            ELSE TO_CHAR(TRUNC(SYSDATE) - TRUNC(sra.FECHA_REGISTRO))
        END AS days_since_order,
        CASE
            WHEN (TRUNC(SYSDATE) - TRUNC(sra.FECHA_REGISTRO)) > 7 THEN 
                'VENCIDA (' || TO_CHAR((TRUNC(SYSDATE) - TRUNC(sra.FECHA_REGISTRO)) - 7) || ' días vencida)'
            ELSE 'EN TIEMPO (' || TO_CHAR(7 - (TRUNC(SYSDATE) - TRUNC(sra.FECHA_REGISTRO))) || ' días restantes)'
        END AS solicitud_vencida,
        sra.CANTIDAD_SOLICITADA, 
        sra.CDGPE,
        p.NOMBRE1 || ' ' || p.NOMBRE2 || ' ' || p.PRIMAPE || ' ' || p.SEGAPE AS CDGPE_NOMBRE, 
         p2.NOMBRE1 || ' ' || p2.NOMBRE2 || ' ' || p2.PRIMAPE || ' ' || p2.SEGAPE AS CDGPE_NOMBRE_AUTORIZA, 
        sra.TIPO_RETIRO, 
        sra.FECHA_ENTREGA,
        UPPER(pp.DESCRIPCION) AS TIPO_PRODUCTO,
        (SELECT NOMBRE FROM CO WHERE CODIGO = sra.CDG_SUCURSAL AND CDGEM = 'EMPFIN') AS SUCURSAL, 
        CASE sra.ESTATUS
        WHEN 0 THEN 'REGISTRADO'
        WHEN 1 THEN 'APROBADO'
        WHEN 2 THEN 'RECHAZADO'
        WHEN 3 THEN 'ENTREGADO'
        WHEN 4 THEN 'DEVUELTO'
        ELSE 'No definido'
    END AS ESTATUS_ASIGNA_ACEPTA
    FROM 
        SOLICITUD_RETIRO_AHORRO sra 
    INNER JOIN 
        ASIGNA_PROD_AHORRO apa ON apa.CONTRATO = sra.CONTRATO 
    INNER JOIN 
        PR_PRIORITARIO pp ON pp.CODIGO = apa.CDGPR_PRIORITARIO 
    INNER JOIN 
        CL c ON c.CODIGO = apa.CDGCL 
    INNER JOIN 
        PE p ON p.CODIGO = sra.CDGPE 
    INNER JOIN 
        PE p2 ON p2.CODIGO = sra.CDGPE_ASIGNA_ESTATUS 
    WHERE 
        sra.ESTATUS != 0 
        AND sra.CDGPE_ASIGNA_ESTATUS IS NOT NULL
        AND sra.TIPO_RETIRO = 1
        AND p.CDGEM = 'EMPFIN'
    ORDER BY 
        sra.FECHA_ESTATUS
            
sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryAll($query);
            if ($res) return $res;
            return array();
        } catch (Exception $e) {
            return array();
        }
    }

    public static function ActualizaSolicitudRetiro($datos)
    {
        $qry = <<<sql
        UPDATE
            SOLICITUD_RETIRO_AHORRO
        SET
            FECHA_ESTATUS = SYSDATE,
            ESTATUS = '{$datos['estatus']}',
            CDGPE_ASIGNA_ESTATUS = '{$datos['ejecutivo']}'
        WHERE
            ID_SOL_RETIRO_AHORRO = '{$datos['idSolicitud']}'
        sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->actualizar($qry);
            if ($res === true) {
                $accion = $datos['estatus'] === '1' ? 'aprobada' : 'rechazada';
                return self::Responde(true, "Solicitud " . $accion . " correctamente.");
            }
            return self::Responde(false, "Ocurrió un error al actualizar la solicitud.");
        } catch (Exception $e) {
            return self::Responde(false, "Ocurrió un error al actualizar la solicitud.", null, $e->getMessage());
        }
    }

    public static function ModificaSolicitudRetiro($datos)
    {
        $qry = <<<sql
        UPDATE SOLICITUD_RETIRO_AHORRO
        SET FECHA_SOLICITUD = TO_DATE(:fechaNueva, 'YYYY-MM-DD')
        WHERE ID_SOL_RETIRO_AHORRO = :idSolicitud
        sql;

        $params = [
            "fechaNueva" => $datos["fechaNueva"],
            "idSolicitud" => $datos["idSolicitud"]
        ];

        try {
            $mysqli = new Database();
            $res = $mysqli->insertar($qry, $params);
            return self::Responde(true, "Solicitud actualizada correctamente.");
        } catch (Exception $e) {
            return self::Responde(false, "Error al actualizar solicitud.", null, $e->getMessage());
        }
    }

    public static function RegistraHuellas($datos)
    {
        $params = [
            "cliente" => $datos["cliente"],
            "ejecutivo" => $datos["ejecutivo"],
            "pulgarI" => is_null($datos["izquierda"]["pulgar"]) ? "" : $datos["izquierda"]["pulgar"],
            "indiceI" => is_null($datos["izquierda"]["indice"]) ? "" : $datos["izquierda"]["indice"],
            "medioI" => is_null($datos["izquierda"]["medio"]) ? "" : $datos["izquierda"]["medio"],
            "anularI" => is_null($datos["izquierda"]["anular"]) ? "" : $datos["izquierda"]["anular"],
            "meniqueI" => is_null($datos["izquierda"]["menique"]) ? "" : $datos["izquierda"]["menique"],
            "pulgarD" => is_null($datos["derecha"]["pulgar"]) ? "" : $datos["derecha"]["pulgar"],
            "indiceD" => is_null($datos["derecha"]["indice"]) ? "" : $datos["derecha"]["indice"],
            "medioD" => is_null($datos["derecha"]["medio"]) ? "" : $datos["derecha"]["medio"],
            "anularD" => is_null($datos["derecha"]["anular"]) ? "" : $datos["derecha"]["anular"],
            "meniqueD" => is_null($datos["derecha"]["menique"]) ? "" : $datos["derecha"]["menique"]
        ];

        $qry = <<<sql
        INSERT INTO HUELLAS
            (CLIENTE, FECHA_REGISTRO, EJECUTIVO, PULGAR_I, INDICE_I, MEDIO_I, ANULAR_I, MENIQUE_I, PULGAR_D, INDICE_D, MEDIO_D, ANULAR_D, MENIQUE_D)
        VALUES
            ('{$params["cliente"]}', SYSDATE, '{$params["ejecutivo"]}', '{$params["pulgarI"]}', '{$params["indiceI"]}', '{$params["medioI"]}', '{$params["anularI"]}', '{$params["meniqueI"]}', '{$params["pulgarD"]}', '{$params["indiceD"]}', '{$params["medioD"]}', '{$params["anularD"]}', '{$params["meniqueD"]}')
        sql;

        try {
            $mysqli = new Database();
            $mysqli->insertar($qry, []);
            return self::Responde(true, "Huellas registradas correctamente.");
        } catch (Exception $e) {
            return self::Responde(false, "Error al registrar huella.", null, $e->getMessage());
        }
    }

    public static function GetHuellas($datos)
    {
        $d = $datos["dedo"] ? $datos["dedo"] : "PULGAR_I, INDICE_I, MEDIO_I, ANULAR_I, MENIQUE_I, PULGAR_D, INDICE_D, MEDIO_D, ANULAR_D, MENIQUE_D";

        $qry = <<<sql
        SELECT
            CLIENTE,
            HUELLA
        FROM
            HUELLAS
        UNPIVOT (
            HUELLA FOR columna IN (
                $d
            )
        )
        WHERE HUELLA IS NOT NULL
        sql;

        $qry .= $datos["cliente"] ? " AND CLIENTE = '{$datos["cliente"]}'" : "";

        try {
            $mysqli = new Database();
            return $mysqli->queryAll($qry);
        } catch (Exception $e) {
            return [];
        }
    }

    public static function ValidaRegistroHuellas($datos)
    {
        $qry = <<<sql
        SELECT
            COUNT(*) AS HUELLAS
        FROM
            HUELLAS
        WHERE
            CLIENTE = '{$datos["cliente"]}'
        sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryOne($qry);
            return self::Responde(true, "Consulta realizada correctamente.", $res);
        } catch (Exception $e) {
            return self::Responde(false, "Error al consultar huellas.", null, $e->getMessage());
        }
    }

    public static function ActualizaHuella($datos)
    {
        $dedos = [];
        foreach ($datos["dedos"] as $dedo => $huella) {
            array_push($dedos, "$dedo = '$huella'");
        }
        $dedos = implode(", ", $dedos);

        $qry = <<<sql
        UPDATE
            HUELLAS
        SET
            $dedos
        WHERE
            CLIENTE = '{$datos["cliente"]}'
        sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->insertar($qry, []);
            return self::Responde(true, "Huellas actualizadas correctamente.", $res);
        } catch (Exception $e) {
            return self::Responde(false, "Error al actualizar huellas.", null, $e->getMessage());
        }
    }

    public static function EliminaHuellas($datos)
    {
        $qry = <<<sql
        DELETE FROM
            HUELLAS
        WHERE
            CLIENTE = '{$datos["cliente"]}'
        sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->eliminar($qry);
            return self::Responde(true, "Huellas eliminadas correctamente.", $res);
        } catch (Exception $e) {
            return self::Responde(false, "Error al eliminar huellas.", null, $e->getMessage());
        }
    }

    public static function GetTipoAhorro()
    {
        $qry = <<<SQL
        SELECT
            CODIGO,
            DESCRIPCION,
            COSTO_INSCRIPCION,
            SALDO_APERTURA,
            TASA
        FROM
            PR_PRIORITARIO
        WHERE
            ESTATUS = 'A'
            AND CODIGO NOT IN (2, 5)
        SQL;

        try {
            $mysqli = new Database();
            return $mysqli->queryAll($qry);
        } catch (Exception $e) {
            return [];
        }
    }

    public static function GetInfoCuentaPeque()
    {
        $qry = <<<SQL
        SELECT
            CODIGO,
            DESCRIPCION,
            COSTO_INSCRIPCION,
            SALDO_APERTURA,
            TASA
        FROM
            PR_PRIORITARIO
        WHERE
            ESTATUS = 'A'
            AND CODIGO = 2
        SQL;

        try {
            $mysqli = new Database();
            return $mysqli->queryOne($qry);
        } catch (Exception $e) {
            return [];
        }
    }

    public static function GetInfoCuentaEstudiantil()
    {
        $qry = <<<SQL
            SELECT
                CODIGO,
                DESCRIPCION,
                COSTO_INSCRIPCION,
                SALDO_APERTURA,
                TASA
            FROM
                PR_PRIORITARIO
            WHERE
                ESTATUS = 'A'
                AND CODIGO = 5
        SQL;

        try {
            $mysqli = new Database();
            return $mysqli->queryOne($qry);
        } catch (Exception $e) {
            return [];
        }
    }

    public static function RegistraApoderado($datos)
    {
        $qry = <<<SQL
        INSERT INTO APODERADO_AHORRO
            (CONTRATO, NOMBRE_1, NOMBRE_2, APELLIDO_1, APELLIDO_2, CURP, PARENTESCO, TIPO_ACCESO, MONTO, FECHA_REGISTRO, FECHA_ACTUALIZACION, ESTATUS)
        VALUES
            (:contrato, :nombre1, :nombre2, :apellido1, :apellido2, :curp, :parentesco, :tipoAcceso, :monto, SYSDATE, SYSDATE, 'A')
        SQL;

        $parametros = [
            "contrato" => $datos["contrato"],
            "nombre1" => $datos["nombre1"],
            "nombre2" => $datos["nombre2"],
            "apellido1" => $datos["apellido1"],
            "apellido2" => $datos["apellido2"],
            "curp" => $datos["curp"],
            "parentesco" => $datos["parentesco"],
            "tipoAcceso" => $datos["tipoAcceso"],
            "monto" => $datos["monto"]
        ];

        try {
            $mysqli = new Database();
            $res = $mysqli->insertar($qry, $parametros);
            return self::Responde(true, "Apoderado registrado correctamente.", $res);
        } catch (Exception $e) {
            return self::Responde(false, "Error al registrar apoderado.", null, $e->getMessage());
        }
    }

    public static function GetApoderados($datos)
    {
        $qry = <<<SQL
        SELECT
            CONCATENA_NOMBRE(AA.NOMBRE_1, AA.NOMBRE_2, AA.APELLIDO_1, AA.APELLIDO_2) AS NOMBRE,
            AA.TIPO_ACCESO,
            CASE
                WHEN AA.TIPO_ACCESO = '1' THEN (
                    SELECT
                        ROUND(APA.SALDO_REAL * (AA.MONTO / 100))
                    FROM
                        ASIGNA_PROD_AHORRO APA
                    WHERE
                        APA.CONTRATO = AA.CONTRATO
                )
                WHEN AA.TIPO_ACCESO = '2' THEN AA.MONTO
            END AS MONTO
        FROM
            APODERADO_AHORRO AA
        WHERE
            AA.ESTATUS = 'A'
            AND AA.CONTRATO = '{$datos["contrato"]}'
        SQL;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryAll($qry);
            return self::Responde(true, "Consulta realizada correctamente.", $res);
        } catch (Exception $e) {
            return self::Responde(false, "Error al consultar apoderados.", null, $e->getMessage());
        }
    }

    public static function DatosResponsivaApoderado($contrato, $curp)
    {
        $qry = <<<SQL
        SELECT
            CURP,
            UPPER(CONCATENA_NOMBRE(NOMBRE_1, NOMBRE_2, APELLIDO_1, APELLIDO_2)) AS NOMBRE_APODERADO,
            UPPER(PARENTESCO) AS PARENTESCO,
            CONTRATO,
            (
                SELECT 
                    UPPER(CONCATENA_NOMBRE(NOMBRE1, NOMBRE2, PRIMAPE, SEGAPE))
                FROM
                    CL
                WHERE
                    CODIGO = (
                        SELECT
                            CDGCL
                        FROM
                            ASIGNA_PROD_AHORRO
                        WHERE
                            CONTRATO = '{$contrato}'
                    )
            ) AS NOMBRE_CLIENTE
        FROM
            APODERADO_AHORRO
        WHERE
            CONTRATO = '{$contrato}'
            AND CURP = '{$curp}'
            AND ESTATUS = 'A'
        SQL;

        try {
            $mysqli = new Database();
            return $mysqli->queryOne($qry);
        } catch (Exception $e) {
            return [];
        }
    }

    public static function BuscaCredito($datos)
    {
        $qry = <<<SQL
            SELECT 
                SC.CDGNS NO_CREDITO,
                SC.CDGCL ID_CLIENTE,
                GET_NOMBRE_CLIENTE(SC.CDGCL) CLIENTE,
                SC.CICLO,
                NVL(SC.CANTAUTOR,SC.CANTSOLIC) MONTO,
                PRN.SITUACION,
                CASE PRN.SITUACION
                    WHEN 'S'THEN 'SOLICITADO' 
                    WHEN 'E'THEN 'ENTREGADO' 
                    WHEN 'A'THEN 'AUTORIZADO' 
                    WHEN 'L'THEN 'LIQUIDADO' 
                    ELSE 'DESCONOCIDO'
                END SITUACION_NOMBRE,
                CASE PRN.SITUACION
                    WHEN 'S'THEN '#1F6CC1FF'
                    WHEN 'E'THEN '#298732FF' 
                    WHEN 'A'THEN '#A31FC1FF' 
                    WHEN 'L'THEN '#000000FF' 
                    ELSE '#FF0000FF'
                END COLOR,
                CASE PRN.SITUACION
                    WHEN 'E'THEN ''
                    ELSE 'none'
                END ACTIVO,
                SN.PLAZOSOL PLAZO,
                SN.PERIODICIDAD,
                SN.TASA,
                DIA_PAGO(SN.NOACUERDO) DIA_PAGO,
                CALCULA_PARCIALIDAD(SN.PERIODICIDAD, SN.TASA, NVL(SC.CANTAUTOR,SC.CANTSOLIC), SN.PLAZOSOL) PARCIALIDAD,
                SN.CDGCO ID_SUCURSAL,
                GET_NOMBRE_SUCURSAL(SN.CDGCO) SUCURSAL,
                SN.CDGOCPE ID_EJECUTIVO,
                GET_NOMBRE_EMPLEADO(SN.CDGOCPE) EJECUTIVO,
                SC.CDGPI ID_PROYECTO,
                (
                    SELECT HORA_CIERRE FROM CIERRE_HORARIO WHERE CDGCO = SN.CDGCO
                ) AS HORA_CIERRE
            FROM 
                SN, SC, PRN
            WHERE
                SC.CDGNS = :cliente
                AND SC.CDGNS = SN.CDGNS
                AND SC.CICLO = SN.CICLO
                AND PRN.CICLO = SC.CICLO 
                AND PRN.CDGNS = SC.CDGNS 
                AND PRN.SITUACION IN ('E', 'L')
                AND SC.CANTSOLIC <> '9999'
        SQL;

        $parametros = [
            'cliente' => $datos['cliente']
        ];

        if ($datos['perfil'] && $datos['perfil'] != 'ADMIN') {
            $qry .= <<<SQL
            AND PRN.CDGCO = ANY(
                SELECT
                    CO.CODIGO ID_SUCURSAL
                FROM
                    PCO, CO, RG
                WHERE
                    PCO.CDGCO = CO.CODIGO
                    AND CO.CDGRG = RG.CODIGO
                    AND PCO.CDGEM = 'EMPFIN'
                    AND PCO.CDGPE = :usuario
            )
            SQL;

            $parametros['usuario'] = $datos['usuario'];
        }

        $qry .= 'ORDER BY SC.SOLICITUD DESC';

        try {
            $db = new Database();
            $res = $db->queryOne($qry, $parametros);
            if ($res) return self::Responde(true, "Datos del crédito obtenidos correctamente.", $res);
            return self::Responde(false, "No se encontraron datos de crédito para el cliente solicitado.");
        } catch (Exception $e) {
            return self::Responde(false, "Ocurrió un error al consultar los datos de crédito del cliente.", null, $e->getMessage());
        }
    }

    public static function GetDiaFestivo($datos)
    {
        $qry = <<<SQL
            SELECT COUNT(*) AS TOT, TO_CHAR(FECHA_CAPTURA, 'YYYY-mm-dd') as FECHA_CAPTURA FROM DIAS_FESTIVOS WHERE FECHA_CAPTURA =  TO_DATE(:fecha, 'YYYY-MM-DD')
            GROUP BY FECHA_CAPTURA 
        SQL;

        $parametros = [
            "fecha" => $datos["fecha"]
        ];

        try {
            $mysqli = new Database();
            $res = $mysqli->queryOne($qry, $parametros);
            if ($res) return self::Responde(true, "Consulta realizada correctamente.", $res);
            return self::Responde(false, "No se encontraron registros para la consulta.");
        } catch (Exception $e) {
            return self::Responde(false, "Error al consultar día festivo.", null, $e->getMessage());
        }
    }

    public static function ListaEjecutivosCredito($datos)
    {
        $qry = <<<SQL
            SELECT 
                PRN.CDGCO,
                PE.CODIGO AS EJECUTIVO,
                CONCATENA_NOMBRE(PE.NOMBRE1, PE.NOMBRE2, PE.PRIMAPE, PE.SEGAPE) AS EJECUTIVO_NOMBRE
            FROM 
                PRN
            JOIN 
                PE ON PRN.CDGCO = PE.CDGCO
            WHERE 
                PRN.CDGNS = :credito
                AND PRN.CICLO = :ciclo
                AND PE.CDGEM = 'EMPFIN'
                AND PE.ACTIVO = 'S'
                AND PE.BLOQUEO = 'N'
            ORDER BY 
                PE.CODIGO
        SQL;

        $parametros = [
            "credito" => $datos["credito"],
            "ciclo" => $datos["ciclo"]
        ];

        try {
            $db = new Database();
            $res = $db->queryAll($qry, $parametros);
            if ($res) return self::Responde(true, "Ejecutivos obtenidos correctamente.", $res);
            return self::Responde(false, "No se encontraron ejecutivos para el crédito.");
        } catch (Exception $e) {
            return self::Responde(false, "Ocurrió un error al consultar los ejecutivos del crédito.", null, $e->getMessage());
        }
    }

    public static function ConsultarPagosCredito($datos)
    {
        $qry = <<<SQL
            SELECT
                RG.CODIGO ID_REGION,
                RG.NOMBRE REGION,
                NS.CDGCO ID_SUCURSAL,
                GET_NOMBRE_SUCURSAL(NS.CDGCO) AS NOMBRE_SUCURSAL,
                PAGOSDIA.SECUENCIA,
                TO_CHAR(PAGOSDIA.FECHA, 'YYYY-MM-DD' ) AS FECHA,
                PAGOSDIA.CDGNS,
                PAGOSDIA.NOMBRE,
                PAGOSDIA.CICLO,
                PAGOSDIA.MONTO,
                TIPO_OPERACION(PAGOSDIA.TIPO) as TIPO_OP,
                PAGOSDIA.TIPO AS TIPO,
                PAGOSDIA.EJECUTIVO,
                PAGOSDIA.CDGOCPE,
                (PE.NOMBRE1 || ' ' || PE.NOMBRE2 || ' ' ||PE.PRIMAPE || ' ' ||PE.SEGAPE) AS NOMBRE_CDGPE,
                PAGOSDIA.FREGISTRO,
                ------PAGOSDIA.FIDENTIFICAPP,
                TRUNC(FECHA) AS DE,
                TRUNC(FECHA) + 1 + 10/24 +  10/1440 AS HASTA,
                CASE
                    WHEN SYSDATE 
                    BETWEEN (FECHA) 
                    AND TO_DATE((TO_CHAR((TRUNC(FECHA) + 1),  'YYYY-MM-DD') || ' ' || :hora), 'YYYY-MM-DD HH24:MI:SS')
                    THEN 'SI'
                    ELSE 'NO'
                END AS DESIGNACION,
                CASE
                    WHEN SYSDATE BETWEEN (FECHA) AND (TRUNC(FECHA) + 2 + 11/24 + 0/1440) THEN 'SI'
                    ELSE 'NO'
                END AS DESIGNACION_ADMIN
            FROM
                PAGOSDIA, NS, CO, RG, PE    
            WHERE
                PAGOSDIA.CDGEM = 'EMPFIN'
                AND PAGOSDIA.ESTATUS = 'A'
                AND PAGOSDIA.CDGNS = :noCredito
                AND NS.CODIGO = PAGOSDIA.CDGNS
                AND NS.CDGCO = CO.CODIGO 
                AND CO.CDGRG = RG.CODIGO
                AND PE.CODIGO = PAGOSDIA.CDGPE
                AND PE.CDGEM = 'EMPFIN'
            ORDER BY
                FREGISTRO DESC, SECUENCIA
        SQL;

        $parametros = [
            "noCredito" => $datos["NO_CREDITO"],
            "hora" => $datos["HORA_CIERRE"]
        ];

        try {
            $mysqli = new Database();
            $res = $mysqli->queryAll($qry, $parametros);
            if ($res) return self::Responde(true, "Pagos obtenidos correctamente.", $res);
            return self::Responde(false, "No se encontraron pagos para el crédito.");
        } catch (Exception $e) {
            return self::Responde(false, "Ocurrió un error al consultar los pagos del crédito.", null, $e->getMessage());
        }
    }

    public static function RegistrarPagoCredito($datos)
    {
        $credito = $datos['credito'];
        $fecha = $datos['fecha'];
        $ciclo = $datos['ciclo'];
        $monto = $datos['monto'];
        $tipo = $datos['tipo'];
        $nombre = $datos['nombre'];
        $usuario = $datos['usuario'];
        $ejecutivo = $datos['ejecutivo'];
        $ejecutivo_nombre = $datos['ejecutivo_nombre'];
        $tipo_procedure_ = 1;
        $fecha_aux = "";

        $qry = <<<SQL
            INSERT INTO TICKETS_CREDITO
                (FECHA, CLIENTE, CREDITO, CICLO, MONTO, CDGPE, CDG_SUCURSAL)
            VALUES
                (SYSDATE, :cliente, :credito, :ciclo, :monto, :usuario, :sucursal)
        SQL;

        $parametros = [
            "cliente" => $datos["cliente"],
            "credito" => $credito,
            "ciclo" => $ciclo,
            "monto" => $monto,
            "usuario" => $usuario,
            "sucursal" => $datos["sucursal"]
        ];

        $qryRecuperaTkt = <<<SQL
            SELECT 
                CODIGO
            FROM 
                TICKETS_CREDITO
            WHERE 
                CLIENTE = :cliente
                AND CREDITO = :credito
                AND CICLO = :ciclo
                AND MONTO = :monto
                AND CDGPE = :usuario
                AND CDG_SUCURSAL = :sucursal
            ORDER BY 
                FECHA DESC
            FETCH FIRST 1 ROWS ONLY
        SQL;

        try {
            $db = new Database();
            $r = $db->queryProcedurePago($credito, $ciclo, $monto, $tipo, $nombre, $usuario,  $ejecutivo, $ejecutivo_nombre,  $tipo_procedure_, $fecha_aux, "", $fecha);
            if (str_starts_with($r, "1")) {
                $db->insertar($qry, $parametros);
                $tkt = $db->queryOne($qryRecuperaTkt, $parametros);
                return self::Responde(true, "Pago registrado correctamente.", $tkt);
            }
            return self::Responde(false, "El pago no se registro.");
        } catch (\Error $e) {
            return self::Responde(false, "Error al registrar pago.", null, $e->getMessage());
        }
    }

    public static function EditarPagoCredito($datos)
    {
        $credito = $datos['credito'];
        $fecha = $datos['fecha'];
        $secuencia = $datos['secuencia'];
        $ciclo = $datos['ciclo'];
        $monto = $datos['monto'];
        $tipo = $datos['tipo'];
        $nombre = $datos['nombre'];
        $usuario = $datos['usuario'];
        $ejecutivo = $datos['ejecutivo'];
        $ejecutivo_nombre = $datos['ejecutivo_nombre'];
        $fecha_aux = $datos['fecha_aux'];
        $tipo_procedure = 2;

        try {
            $db = new Database();
            $r = $db->queryProcedurePago($credito, $ciclo, $monto, $tipo, $nombre, $usuario,  $ejecutivo, $ejecutivo_nombre, $tipo_procedure, $fecha_aux, $secuencia, $fecha);
            if (str_starts_with($r, "1")) return self::Responde(true, "Pago actualizado correctamente.");
            return self::Responde(false, "El pago no se actualizó.");
        } catch (\Error $e) {
            return self::Responde(false, "Error al actualizar pago.", null, $e->getMessage());
        }
    }

    public static function EliminaPagoCredito($datos)
    {
        $cdgns = $datos['cdgns'];
        $fecha = $datos['fecha'];
        $usuario = $datos['usuario'];
        $secuencia = $datos['secuencia'];

        try {
            $db = new Database();
            $r = $db->queryProcedureDeletePago($cdgns, $fecha, $usuario, $secuencia);
            if (str_starts_with($r, "1")) return self::Responde(true, "Pago eliminado correctamente.");
            return self::Responde(false, "El pago no se eliminó.");
        } catch (Exception $e) {
            return self::Responde(false, "Error al eliminar pago.", null, $e->getMessage());
        }
    }

    public static function DatosTicket_Credito($datos)
    {
        $qry = <<<SQL
            SELECT
                TO_CHAR(TC.FECHA, 'DD/MM/YYYY HH24:MI:SS') AS FECHA,
                TC.CDGPE EJECUTIVO,
                CONCATENA_NOMBRE(PE.NOMBRE1, PE.NOMBRE2, PE.PRIMAPE, PE.SEGAPE) AS NOMBRE_EJECUTIVO,
                TC.CDG_SUCURSAL SUCURSAL,
                CO.NOMBRE AS NOMBRE_SUCURSAL,
                TC.CLIENTE,
                CONCATENA_NOMBRE(CL.NOMBRE1, CL.NOMBRE2, CL.PRIMAPE, CL.SEGAPE) AS NOMBRE_CLIENTE,
                TC.MONTO,
                TC.CREDITO,
                TC.CICLO
            FROM 
                TICKETS_CREDITO TC
                JOIN PE ON TC.CDGPE = PE.CODIGO AND PE.CDGEM = 'EMPFIN'
                JOIN CO ON TC.CDG_SUCURSAL = CO.CODIGO
                JOIN CL ON TC.CLIENTE = CL.CODIGO
            WHERE 
                TC.CODIGO = :codigo
        SQL;

        $parametros = [
            "codigo" => $datos["ticket"]
        ];

        try {
            $mysqli = new Database();
            $res = $mysqli->queryOne($qry, $parametros);
            if ($res) return self::Responde(true, "Datos del ticket obtenidos correctamente.", $res);
            return self::Responde(false, "No se encontraron datos del ticket.");
        } catch (Exception $e) {
            return self::Responde(false, "Ocurrió un error al consultar los datos del ticket.", null, $e->getMessage());
        }
    }
}
