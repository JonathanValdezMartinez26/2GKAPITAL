<?php

namespace App\models;

defined("APPPATH") or die("Access denied");

use Core\Database;
use Core\Model;
use Exception;

class AdminSucursales extends Model
{
    public static function GetSucursalesActivas()
    {
        $query = <<<sql
            SELECT
                TO_CHAR(FECHA_REGISTRO, 'DD/MM/YYYY') FECHA_REGISTRO,
                SEA.CDG_SUCURSAL,
                CO.NOMBRE,
                SCA.CDG_USUARIO,
                (
                    SELECT
                        CONCATENA_NOMBRE(PE.NOMBRE1, PE.NOMBRE2, PE.PRIMAPE, PE.SEGAPE)
                    FROM 
                        PE
                    WHERE
                        PE.CODIGO = SCA.CDG_USUARIO
                        AND PE.CDGEM = 'EMPFIN'
                ) NOMBRE_CAJERA,
                TO_CHAR(TO_DATE(SCA.HORA_APERTURA, 'HH24:MI:SS'), 'HH:MI AM') HORA_APERTURA,
                TO_CHAR(TO_DATE(SCA.HORA_CIERRE, 'HH24:MI:SS'), 'HH:MI AM') HORA_CIERRE,
                TO_CHAR(TO_NUMBER(SEA.SALDO_MINIMO), 'FM$999,999,999.00') SALDO_MINIMO,
                TO_CHAR(TO_NUMBER(SEA.SALDO_MAXIMO), 'FM$999,999,999.00') SALDO_MAXIMO,
                NULL ACCIONES
            FROM
                SUC_ESTADO_AHORRO SEA
            JOIN
                CO ON CO.CODIGO = SEA.CDG_SUCURSAL
            RIGHT JOIN
                SUC_CAJERA_AHORRO SCA ON SCA.CDG_ESTADO_AHORRO = SEA.CODIGO
            WHERE
                SEA.ESTATUS = 'A'
            ORDER BY
               SEA.CDG_SUCURSAL
        sql;

        try {
            $mysqli = new Database();
            return $mysqli->queryAll($query);
        } catch (Exception $e) {
            return [];
        }
    }

    public static function GetUserAdmin()
    {
        $query = <<<sql
            SELECT
                * FROM PE
sql;

        try {
            $mysqli = new Database();
            return $mysqli->queryAll($query);
        } catch (Exception $e) {
            return [];
        }
    }

    public static function GetSucursales()
    {
        $query = <<<sql
            SELECT
                CO.CODIGO,
                CO.NOMBRE
            FROM
                CO
            WHERE
                CO.CODIGO NOT IN (
                    SELECT
                        CDG_SUCURSAL
                    FROM
                        SUC_ESTADO_AHORRO
                    WHERE
                        ESTATUS = 'A'
                    )
            ORDER BY
                CO.NOMBRE
        sql;

        try {
            $mysqli = new Database();
            return $mysqli->queryAll($query);
        } catch (Exception $e) {
            return [];
        }
    }

    public static function GetMontoSucursal($sucursal)
    {
        $query = <<<sql
            SELECT
                SALDO_MINIMO,
                SALDO_MAXIMO
            FROM
                PARAMETROS_AHORRO
            WHERE
                CODIGO = '$sucursal'
        sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryOne($query);
            if ($res) return self::Responde(true, "Monto de sucursal encontrado", $res);
            return self::Responde(false, "No se encontró monto de sucursal");
        } catch (Exception $e) {
            return self::Responde(false, "Error al buscar monto de sucursal", null, $e->getMessage());
        }
    }

    public static function GetCajeras($sucursal)
    {
        $qry = <<<sql
        SELECT * FROM (
            SELECT DISTINCT 
            CONCATENA_NOMBRE(PE.NOMBRE1, PE.NOMBRE2, PE.PRIMAPE, PE.SEGAPE) NOMBRE, PE.CODIGO
            FROM
                PE,
                UT
            WHERE
            PE.CODIGO = UT.CDGPE
            AND PE.ACTIVO = 'S'
            AND (PE.BLOQUEO = 'N' OR PE.BLOQUEO IS NULL) 
            AND (PE.CDGCO = '$sucursal' OR (PE.CODIGO = 'LGFR' AND UT.CDGTUS = 'CAJA' ))
            ) 
        sql;


        try {
            $mysqli = new Database();
            $res = $mysqli->queryAll($qry);
            return self::Responde(true, "Cajeras encontradas", $res);
        } catch (Exception $e) {
            return self::Responde(false, "Error al buscar cajeras", null, $e->getMessage());
        }
    }

    public static function GetHorarioCajera($cajera)
    {
        $qry = <<<sql
        SELECT
            HORA_APERTURA,
            HORA_CIERRE
        FROM
            SUC_CAJERA_AHORRO
        WHERE
            CDG_USUARIO = '$cajera'
        sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryOne($qry);
            if ($res) return self::Responde(true, "Horario de cajera encontrado", $res);
            else return self::Responde(false, "No se encontró horario de cajera");
        } catch (Exception $e) {
            return self::Responde(false, "Error al buscar horario de cajera", null, $e->getMessage());
        }
    }

    public static function ActivarSucursal($datos)
    {
        $qrySuc = <<<sql
        INSERT INTO SUC_ESTADO_AHORRO
            (CODIGO, CDG_SUCURSAL, FECHA_REGISTRO, MODIFICACION, ESTATUS, SALDO, SALDO_MINIMO, SALDO_MAXIMO, SALDO_INICIAL, CDGPE_REGISTRO)
        VALUES
            (
                (SELECT NVL(MAX(TO_NUMBER(CODIGO)), 0) FROM SUC_ESTADO_AHORRO) + 1,
                :sucursal,
                SYSDATE,
                SYSDATE,
                'A',
                0,
                :minimo,
                :maximo,
                :saldo,
                :usuario
            )
        sql;

        $qryCaj = <<<sql
        INSERT INTO SUC_CAJERA_AHORRO
            (CDG_ESTADO_AHORRO, CDG_USUARIO, HORA_APERTURA, HORA_CIERRE, CDGPE_REGISTRO)
        VALUES
            (
                (SELECT MAX(TO_NUMBER(CODIGO)) FROM SUC_ESTADO_AHORRO),
                :cajera,
                :apertura,
                :cierre,
                :usuario
            )
        sql;

        $qrys = [
            $qrySuc,
            $qryCaj
        ];

        if (!$datos['saldo']) $datos['saldo'] = 0;

        $params = [
            [
                "sucursal" => $datos['sucursal'],
                "minimo" => $datos['montoMin'],
                "maximo" => $datos['montoMax'],
                "saldo" => $datos['saldo'],
                "usuario" => $datos['usuario']
            ],
            [
                "cajera" => $datos['cajera'],
                "apertura" => $datos['horaA'],
                "cierre" => $datos['horaC'],
                "usuario" => $datos['usuario']
            ]
        ];

        try {
            $ora = new Database();
            $res = $ora->insertaMultiple($qrys, $params);
            if (!$res) return self::Responde(false, "Error al activar sucursal");

            if ($datos['saldo'] > 0) {
                $res = $ora->queryOne("SELECT MAX(CODIGO) AS ID FROM SUC_ESTADO_AHORRO WHERE CDG_SUCURSAL = '{$datos['sucursal']}' AND ESTATUS = 'A'");
                $fondeo = self::AplicarFondeo([
                    "codigoSEA" => $res["ID"],
                    "montoOperacion" => $datos['saldo'],
                    "usuario" => $datos["usuario"]
                ]);

                if (!$fondeo['success']) return self::Responde(false, "Error al activar sucursal", null, $fondeo['error']);
            }

            return self::Responde(true, "Sucursal activada correctamente");
        } catch (Exception $e) {
            return self::Responde(false, "Error al activar sucursal", null, $e->getMessage());
        }
    }

    public static function GetDatosFondeoRetiro($datos)
    {
        $qry = <<<sql
        SELECT
            SEA.CODIGO,
            SEA.CDG_SUCURSAL AS CODIGO_SUCURSAL,
            (
                SELECT
                    NOMBRE
                FROM
                    CO
                WHERE
                    CODIGO = SEA.CDG_SUCURSAL
            ) AS NOMBRE_SUCURSAL,
            SCA.CDG_USUARIO AS CODIGO_CAJERA,
            (
                SELECT
                    CONCATENA_NOMBRE(NOMBRE1, NOMBRE2, PRIMAPE, SEGAPE)
                FROM
                    PE
                WHERE
                    CODIGO = SCA.CDG_USUARIO
                    AND PE.CDGEM = 'EMPFIN'
            ) AS NOMBRE_CAJERA,
            NULL AS FECHA_CIERRE,
            SEA.SALDO_MINIMO AS MONTO_MIN,
            SEA.SALDO_MAXIMO AS MONTO_MAX,
            NVL(SEA.SALDO, 0) AS SALDO
        FROM
            SUC_ESTADO_AHORRO SEA
        RIGHT JOIN
            SUC_CAJERA_AHORRO SCA ON SCA.CDG_ESTADO_AHORRO = SEA.CODIGO
        WHERE
            SEA.CDG_SUCURSAL = '{$datos["sucursal"]}'
            AND SEA.ESTATUS = 'A'
        sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryOne($qry);
            if ($res) return self::Responde(true, "Datos encontrados.", $res);
            else return self::Responde(false, "La sucursal " . $datos["sucursal"] . " no se encuentra habilitada para operar cuentas de ahorro.");
        } catch (Exception $e) {
            return self::Responde(false, "Error al buscar información de la sucursal.", null, $e->getMessage());
        }
    }

    public static function AplicarFondeo($datos)
    {
        $qry = <<<sql
        INSERT INTO SUC_MOVIMIENTOS_AHORRO
            (CODIGO, CDG_ESTADO_AHORRO, FECHA, MONTO, MOVIMIENTO, CDG_USUARIO)
        VALUES
            (
                (SELECT NVL(MAX(TO_NUMBER(CODIGO)), 0) FROM SUC_MOVIMIENTOS_AHORRO) + 1,
                :codigo,
                SYSDATE,
                :monto,
                '1',
                :usuario
            )
        sql;

        $params = [
            "codigo" => $datos["codigoSEA"],
            "monto" => $datos["montoOperacion"],
            "usuario" => $datos["usuario"]
        ];

        try {
            $mysqli = new Database();
            $mysqli->insertar($qry, $params);
            return self::Responde(true, "Fondeo realizado correctamente.");
        } catch (Exception $e) {
            return self::Responde(false, "Error al realizar fondeo.", null, $e->getMessage());
        }
    }

    public static function AplicarRetiro($datos)
    {
        $qry = <<<sql
        INSERT INTO SUC_MOVIMIENTOS_AHORRO
            (CODIGO, CDG_ESTADO_AHORRO, FECHA, MONTO, MOVIMIENTO, CDG_USUARIO)
        VALUES
            (
                (SELECT NVL(MAX(TO_NUMBER(CODIGO)), 0) FROM SUC_MOVIMIENTOS_AHORRO) + 1,
                :codigo,
                SYSDATE,
                :monto,
                '0',
                :usuario
            )
        sql;

        $params = [
            "codigo" => $datos["codigoSEA"],
            "monto" => $datos["montoOperacion"],
            "usuario" => $datos["usuario"]
        ];

        try {
            $mysqli = new Database();
            $mysqli->insertar($qry, $params);
            return self::Responde(true, "Retiro realizado correctamente.");
        } catch (Exception $e) {
            return self::Responde(false, "Error al realizar retiro.", null, $e->getMessage());
        }
    }

    public static function GetMovimientos($datos)
    {
        $qry = <<<sql
        SELECT
            TO_CHAR(FECHA, 'DD/MM/YYYY HH24:MI:SS') FECHA,
            MONTO,
            CASE
                WHEN MOVIMIENTO = '1' THEN 'FONDEO'
                WHEN MOVIMIENTO = '2' THEN 'RETIRO'
                ELSE 'DESCONOCIDO'
            END MOVIMIENTO,
            (
                SELECT
                    CONCATENA_NOMBRE(NOMBRE1, NOMBRE2, PRIMAPE, SEGAPE)
                FROM
                    PE
                WHERE
                    CODIGO = CDG_USUARIO
            ) USUARIO
        FROM
            SUC_MOVIMIENTOS_AHORRO
        WHERE
            CDG_ESTADO_AHORRO = '{$datos["codigo"]}'
        ORDER BY
            FECHA DESC
        sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryAll($qry);
            return self::Responde(true, "Movimientos encontrados.", $res);
        } catch (Exception $e) {
            return self::Responde(false, "Error al buscar movimientos.", null, $e->getMessage());
        }
    }

    public static function GetMontosApertura($sucursal)
    {
        $qry = <<<sql
        SELECT
            CODIGO,
            MONTO_MINIMO,
            MONTO_MAXIMO
        FROM
            PARAMETROS_AHORRO
        WHERE
            CDG_SUCURSAL = '$sucursal'
        sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryOne($qry);
            if ($res) return self::Responde(true, "Montos de apertura encontrados.", $res);
            return self::Responde(false, "No se encontraron montos de apertura.");
        } catch (Exception $e) {
            return self::Responde(false, "Error al buscar montos de apertura.", null, $e->getMessage());
        }
    }

    public static function GuardarParametrosSucursal($datos)
    {
        $qryInsert = <<<sql
        INSERT INTO PARAMETROS_AHORRO
            (CODIGO, CDG_SUCURSAL, MONTO_MINIMO, MONTO_MAXIMO)
        VALUES
            (
                (SELECT NVL(MAX(TO_NUMBER(CODIGO)), 0) FROM PARAMETROS_AHORRO) + 1,
                :sucursal,
                :minimo,
                :maximo
            )
        sql;

        $qryUpdate = <<<sql
        UPDATE
            PARAMETROS_AHORRO
        SET
            MONTO_MINIMO = :minimo,
            MONTO_MAXIMO = :maximo
        WHERE
            CODIGO = :codigo
        sql;

        $params = [
            "minimo" => $datos["minimoApertura"],
            "maximo" => $datos["maximoApertura"]
        ];

        if ($datos["codigo"] === "") {
            $qry = $qryInsert;
            $params["sucursal"] = $datos["codSucMontos"];
        } else {
            $qry = $qryUpdate;
            $params["codigo"] = $datos["codigo"];
        }

        try {
            $mysqli = new Database();
            $mysqli->insertar($qry, $params);
            return self::Responde(true, "Montos de apertura guardados correctamente.");
        } catch (Exception $e) {
            return self::Responde(false, "Error al guardar montos de apertura.", null, $e->getMessage());
        }
    }

    public static function ResumenCuenta($datos)
    {
        $contrato = $datos['CONTRATO'];

        $qry = <<<SQL
            SELECT * FROM (
                SELECT
                    MA.CODIGO,
                    TO_CHAR(MA.FECHA_MOV, 'DD/MM/YYYY HH24:MI:SS') AS FECHA,
                    MA.CDG_TIPO_PAGO AS TIPO,
                    'AHORRO' AS CUENTA,
                    CONCAT(
                        (SELECT DESCRIPCION
                        FROM TIPO_PAGO_AHORRO
                        WHERE CODIGO = MA.CDG_TIPO_PAGO),
                        CASE 
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
                    ) OVER (ORDER BY MA.FECHA_MOV, MA.MOVIMIENTO DESC, MA.CODIGO) AS SALDO,
                    (
                    SELECT
                        T.CDGPE
                    FROM
                        TICKETS_AHORRO T
                    WHERE
                        T.CODIGO = MA.CDG_TICKET
                    ) AS USUARIO
                FROM
                    MOVIMIENTOS_AHORRO MA
                    INNER JOIN TIPO_PAGO_AHORRO TPA ON TPA.CODIGO = MA.CDG_TIPO_PAGO
                    LEFT JOIN SOLICITUD_RETIRO_AHORRO SRA ON SRA.ID_SOL_RETIRO_AHORRO = MA.CDG_RETIRO 
                WHERE
                    MA.CDG_CONTRATO = '$contrato'
                UNION ALL
                SELECT
                    NULL,
                    TO_CHAR((
                        CASE MA.CDG_TIPO_PAGO
                            WHEN '5' THEN MA.FECHA_MOV + INTERVAL '1' SECOND
                            WHEN '10' THEN MA.FECHA_MOV - INTERVAL '1' SECOND
                        END
                    ), 'DD/MM/YYYY HH24:MI:SS') AS FECHA,
                    MA.CDG_TIPO_PAGO AS TIPO,
                    'INVERSIÓN' AS CUENTA,
                    (
                        SELECT
                            TPA.DESCRIPCION
                        FROM
                            TIPO_PAGO_AHORRO TPA
                        WHERE
                            TPA.CODIGO = (CASE MA.CDG_TIPO_PAGO
                                    WHEN '5' THEN '10'
                                    WHEN '10' THEN '5'
                                END)
                    ) AS DESCRIPCION,
                    0 AS TRANSITO,
                    CASE MA.CDG_TIPO_PAGO
                        WHEN '5' THEN MA.MONTO 
                        ELSE 0
                    END AS ABONO,
                    CASE MA.CDG_TIPO_PAGO
                        WHEN '10' THEN MA.MONTO 
                        ELSE 0
                    END AS CARGO,
                    SUM(CASE MA.CDG_TIPO_PAGO
                        WHEN '5' THEN MA.MONTO
                        WHEN '10' THEN -MA.MONTO 
                        ELSE 0
                    END) OVER (ORDER BY MA.FECHA_MOV) AS SALDO,
                    NVL(MA.CDGPE, 'SISTEMA') AS USUARIO
                FROM
                    MOVIMIENTOS_AHORRO MA
                WHERE
                    MA.CDG_CONTRATO = '$contrato'
                    AND MA.CDG_TIPO_PAGO IN (5,10)
            ) ORDER BY TO_DATE(FECHA, 'DD/MM/YYYY HH24:MI:SS') DESC, CODIGO DESC, CUENTA DESC
        SQL;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryAll($qry);
            if (count($res) === 0) return [];
            return $res;
        } catch (Exception $e) {
            return [];
        }
    }

    public static function GetRendimientos($datos)
    {
        $qry = <<<sql
        SELECT
            TO_CHAR(FECHA, 'DD/MM/YYYY') AS FECHA,
            CONTRATO,
            SALDO_CIERRE AS SALDO,
            TASA,
            DEVENGO
        FROM
            DEVENGO_AHORRO DA
        WHERE
            DA.CONTRATO IN (
                SELECT
                    APA.CONTRATO
                FROM
                    ASIGNA_PROD_AHORRO APA
                WHERE
                    APA.CDGCL = '{$datos['CDGCL']}'
                    _filtro_producto_
                    _filtro_fecha_
                )
        ORDER BY
            DA.FECHA DESC
        sql;

        $filtroProducto = $datos['producto'] ? "AND APA.CDGPR_PRIORITARIO = '{$datos['producto']}'" : "";
        $filtroFecha = ($datos['fechaI'] && $datos['fechaF']) ? "AND TRUNC(DA.FECHA) BETWEEN TO_DATE('{$datos['fechaI']}', 'DD/MM/YYYY') AND TO_DATE('{$datos['fechaF']}', 'DD/MM/YYYY')" : "";
        $qry = str_ireplace("_filtro_producto_", $filtroProducto, $qry);
        $qry = str_ireplace("_filtro_fecha_", $filtroFecha, $qry);

        try {
            $mysqli = new Database();
            $res = $mysqli->queryAll($qry);
            if (count($res) === 0) return [];
            return $res;
        } catch (Exception $e) {
            return [];
        }
    }

    public static function GetUsuariosActivos()
    {
        $query = <<<sql
            SELECT
                CODIGO, (NOMBRE1 || ' ' || NOMBRE2 || ' ' || PRIMAPE || ' ' || SEGAPE) AS EMPLEADO
            FROM
                PE WHERE 
                CDGEM = 'EMPFIN'
            ORDER BY NOMBRE1 ASC
sql;

        try {
            $mysqli = new Database();
            return $mysqli->queryAll($query);
        } catch (Exception $e) {
            return [];
        }
    }

    public static function GetUsuariosAdminAhorro()
    {
        $query = <<<sql
        SELECT * FROM (SELECT ppa.ID_PERMISO_PERFIL_AHORRO, p.CODIGO, (p.NOMBRE1 || ' ' || p.NOMBRE2 || ' ' || p.PRIMAPE || ' ' || p.SEGAPE) AS EMPLEADO,
        ppa.NOMBRE_PUESTO, ppa.CDGCO AS SUCURSAL, 'TODAS LAS SUCURSALES' AS NOMBRE_SUCURSAL, ppa.ESTATUS AS ESTADO  
        FROM PE p 
        INNER JOIN PERMISOS_PERFIL_AHORRO ppa ON ppa.CDGPE = P.CODIGO 
        WHERE p.CDGEM = 'EMPFIN'
        AND ppa.CDGCO = '000'
        
        UNION 
        
        SELECT ppa.ID_PERMISO_PERFIL_AHORRO, p.CODIGO, (p.NOMBRE1 || ' ' || p.NOMBRE2 || ' ' || p.PRIMAPE || ' ' || p.SEGAPE) AS EMPLEADO,
        ppa.NOMBRE_PUESTO, ppa.CDGCO AS SUCURSAL, c.NOMBRE AS NOMBRE_SUCURSAL , ppa.ESTATUS AS ESTADO  
        FROM PE p 
        INNER JOIN PERMISOS_PERFIL_AHORRO ppa ON ppa.CDGPE = P.CODIGO 
        INNER JOIN CO c ON c.CODIGO = ppa.CDGCO
        WHERE p.CDGEM = 'EMPFIN')
        ORDER BY ID_PERMISO_PERFIL_AHORRO 
sql;

        try {
            $mysqli = new Database();
            return $mysqli->queryAll($query);
        } catch (Exception $e) {
            return [];
        }
    }

    public static function GetSaldosSucursales($datos)
    {
        $qrySaldos = <<<sql
        SELECT
            *
        FROM (
            SELECT
                TO_CHAR(SYSDATE, 'DD/MM/YYYY') FECHA,
                SEA.CDG_SUCURSAL SUCURSAL,
                CO.NOMBRE,
                SEA.SALDO,
                'En operación' DIFERENCIA,
                CASE
                    WHEN saldo_maximo = saldo_minimo THEN 0
                    ELSE ((SEA.SALDO - SEA.SALDO_MINIMO) / (SEA.SALDO_MAXIMO - SEA.SALDO_MINIMO)) * 100
                END PORCENTAJE
            FROM
                SUC_ESTADO_AHORRO SEA
            JOIN
                CO ON CO.CODIGO = SEA.CDG_SUCURSAL
            WHERE
                SEA.ESTATUS = 'A'
            UNION ALL
            SELECT
                TO_CHAR(A.FECHA, 'DD/MM/YYYY') FECHA,
                SEA.CDG_SUCURSAL SUCURSAL,
                CO.NOMBRE,
                A.SALDO_SUCURSAL AS SALDO,
                TO_CHAR(A.MONTO - A.SALDO_SUCURSAL, 'FM$999,999,999.00') DIFERENCIA,
                CASE
                    WHEN saldo_maximo = saldo_minimo THEN 0
                    ELSE ((A.SALDO_SUCURSAL - SEA.SALDO_MINIMO) / (SEA.SALDO_MAXIMO - SEA.SALDO_MINIMO)) * 100
                END PORCENTAJE
            FROM
                (
                SELECT
                    MAX(FECHA) AS MAX_FECHA,
                    CDG_SUCURSAL
                FROM
                    ARQUEO
                WHERE
                    TRUNC(FECHA) < TRUNC(SYSDATE)
                GROUP BY
                    TRUNC(FECHA),
                    CDG_SUCURSAL
                ) MAX_ARQUEO
            JOIN
                ARQUEO A ON A.FECHA = MAX_ARQUEO.MAX_FECHA AND A.CDG_SUCURSAL = MAX_ARQUEO.CDG_SUCURSAL
            JOIN
                SUC_ESTADO_AHORRO SEA ON SEA.CDG_SUCURSAL = A.CDG_SUCURSAL
            JOIN
                CO ON CO.CODIGO = SEA.CDG_SUCURSAL
        )
        sql;

        if ($datos['fechaI'] && $datos['fechaF']) $qrySaldos .= "WHERE TO_DATE(FECHA, 'DD/MM/YYYY') BETWEEN TO_DATE('{$datos['fechaI']}', 'YYYY-MM-DD') AND TO_DATE('{$datos['fechaF']}', 'YYYY-MM-DD')";

        $qrySaldos .= "ORDER BY FECHA DESC, NOMBRE";
        try {
            $mysqli = new Database();
            return $mysqli->queryAll($qrySaldos);
        } catch (Exception $e) {
            return [];
        }
    }

    public static function GetHistorialFondeosSucursal($datos)
    {
        $qry = <<<SQL
            SELECT
                TO_CHAR(SMA.FECHA, 'DD/MM/YYYY HH24:MI:SS') AS FECHA,
                SEA.CDG_SUCURSAL AS SUCURSAL,
                CO.NOMBRE AS NOMBRE_SUCURSAL,
                SMA.CDG_USUARIO AS USUARIO,
                (
                    SELECT
                        CONCATENA_NOMBRE(NOMBRE1, NOMBRE2, PRIMAPE, SEGAPE)
                    FROM
                        PE
                    WHERE
                        CODIGO = SMA.CDG_USUARIO
                        AND CDGEM = 'EMPFIN'
                ) AS NOMBRE_USUARIO,
                CASE
                    WHEN SMA.CODIGO = (SELECT MIN(CODIGO) FROM SUC_MOVIMIENTOS_AHORRO WHERE CDG_ESTADO_AHORRO = SMA.CDG_ESTADO_AHORRO) THEN 'FONDEO INICIAL (APERTURA)'
                    ELSE 'FONDEO'
                END AS MOVIMIENTO,
                SMA.MONTO
            FROM
                SUC_MOVIMIENTOS_AHORRO SMA
            JOIN
                SUC_ESTADO_AHORRO SEA ON SEA.CODIGO = SMA.CDG_ESTADO_AHORRO
            JOIN
                CO ON CO.CODIGO = SEA.CDG_SUCURSAL
            WHERE
                SMA.MOVIMIENTO = '1'
        SQL;


        if ($datos['sucursal']) $qry .= " AND SEA.CDG_SUCURSAL = '{$datos['sucursal']}'";
        if ($datos['fechaI'] && $datos['fechaF']) $qry .= " AND TRUNC(SMA.FECHA) BETWEEN TO_DATE('{$datos['fechaI']}', 'YYYY-MM-DD') AND TO_DATE('{$datos['fechaF']}', 'YYYY-MM-DD')";

        try {
            $mysqli = new Database();
            $res = $mysqli->queryAll($qry);
            if (count($res) === 0) return self::Responde(false, "No se encontraron registros de fondeos para los parámetros proporcionados.", null, $qry);
            return self::Responde(true, "Fondeo encontrados.", $res);
        } catch (Exception $e) {
            return self::Responde(false, "Error al buscar registros de fondeos.", null, $e->getMessage());
        }
    }

    public static function GetHistorialRetirosSucursal($datos)
    {
        $qry = <<<SQL
            SELECT
                TO_CHAR(SMA.FECHA, 'DD/MM/YYYY HH24:MI:SS') AS FECHA,
                SEA.CDG_SUCURSAL AS SUCURSAL,
                CO.NOMBRE AS NOMBRE_SUCURSAL,
                SMA.CDG_USUARIO AS USUARIO,
                (
                    SELECT
                        CONCATENA_NOMBRE(NOMBRE1, NOMBRE2, PRIMAPE, SEGAPE)
                    FROM
                        PE
                    WHERE
                        CODIGO = SMA.CDG_USUARIO
                        AND CDGEM = 'EMPFIN'
                ) AS NOMBRE_USUARIO,
                'RETIRO' AS MOVIMIENTO,
                SMA.MONTO
            FROM
                SUC_MOVIMIENTOS_AHORRO SMA
            JOIN
                SUC_ESTADO_AHORRO SEA ON SEA.CODIGO = SMA.CDG_ESTADO_AHORRO
            JOIN
                CO ON CO.CODIGO = SEA.CDG_SUCURSAL
            WHERE
                SMA.MOVIMIENTO = '0'
        SQL;

        if (isset($datos['sucursal'])) $qry .= " AND SEA.CDG_SUCURSAL = '{$datos['sucursal']}'";
        if (isset($datos['fechaI']) && isset($datos['fechaF'])) $qry .= " AND TRUNC(SMA.FECHA) BETWEEN TO_DATE('{$datos['fechaI']}', 'YYYY-MM-DD') AND TO_DATE('{$datos['fechaF']}', 'YYYY-MM-DD')";

        try {
            $mysqli = new Database();
            $res = $mysqli->queryAll($qry);
            if (count($res) === 0) return self::Responde(false, "No se encontraron registros de retiros para los parámetros proporcionados.", []);
            return self::Responde(true, "Retiros encontrados.", $res);
        } catch (Exception $e) {
            return self::Responde(false, "Error al buscar registros de retiros.", null, $e->getMessage());
        }
    }

    public static function GetSegmentos($datos)
    {
        $qry = <<<sql
        SELECT
            CDGCL,
            APA.CONTRATO AS AHORRO,
            APA.CDGPR_PRIORITARIO AS SEGMENTO,
            (
                SELECT
                    COUNT(CONTRATO)
                FROM
                    ASIGNA_PROD_AHORRO
                WHERE
                    CDGCL = APA.CDGCL
                    AND CDGPR_PRIORITARIO = 2
                GROUP BY
                    CDGCL
            ) PEQUES,
            (
                SELECT
                    COUNT(CDG_CONTRATO)
                FROM
                    CUENTA_INVERSION
                WHERE
                    CDG_CONTRATO = APA.CONTRATO
            ) AS INVERSIÓN
        FROM
            ASIGNA_PROD_AHORRO APA
        WHERE
            CONTRATO = '{$datos['CONTRATO']}'
        sql;

        try {
            $mysqli = new Database();
            $res = $mysqli->queryOne($qry);
            if ($res) return $res;
            return [];
        } catch (Exception $e) {
            return [];
        }
    }

    public static function GetSituacionAhorro($datos)
    {
        $fechaF = $datos['fechaF'];

        $qry = <<<SQL
            SELECT
                APA.CDGCL AS ID_SOCIO,
                CONCATENA_NOMBRE(CL.NOMBRE1, CL.NOMBRE2, CL.PRIMAPE, CL.SEGAPE) AS NOMBRE_SOCIO,
                CASE
                    WHEN LENGTH(APA.CONTRATO) > 14 THEN SUBSTR(APA.CONTRATO, 15, 2)
                    ELSE NULL
                END AS ID_PQ,
                CASE
                    WHEN LENGTH(APA.CONTRATO) > 14 THEN CONCATENA_NOMBRE(CLP.NOMBRE1, CLP.NOMBRE2, CLP.APELLIDO1, CLP.APELLIDO2)
                    ELSE NULL
                END AS NOMBRE_PQ,
                CO.NOMBRE AS SUCURSAL,
                PRP.DESCRIPCION AS TIPO_CUENTA,
                TO_CHAR(APA.FECHA_APERTURA, 'DD/MM/YYYY') AS FECHA_INICIO,
                NULL AS FECHA_VENCIMIENTO,
                NULL AS PLAZO,
                PRP.COSTO_INSCRIPCION AS COMISION_APERTURA,
                NULL AS BONIFICACION,
                CASE 
                    WHEN SC.SALDO_CORTE < 0 THEN 0
                    ELSE SC.SALDO_CORTE
                END AS SALDO_AHORRO,
                NULL AS FECHA_INVERSION,
                0 AS SALDO_INVERSION,
                CASE 
                    WHEN SC.SALDO_CORTE < 0 THEN 0
                    ELSE SC.SALDO_CORTE
                END AS SALDO_SOCIO,
                CONCATENA_NOMBRE(PE.NOMBRE1, PE.NOMBRE2, PE.PRIMAPE, PE.SEGAPE) AS EJECUTIVO,
                APA.TASA / 100 AS TASA,
                SC.RENDIMIENTO
            FROM
                ASIGNA_PROD_AHORRO APA
                LEFT JOIN CL ON CL.CODIGO = APA.CDGCL
                LEFT JOIN CL_PQS CLP ON CLP.CDG_CONTRATO = APA.CONTRATO
                LEFT JOIN PE ON PE.CODIGO = APA.CDGPE_REGISTRO
                LEFT JOIN CO ON CO.CODIGO = APA.CDGCO
                LEFT JOIN PR_PRIORITARIO PRP ON PRP.CODIGO = APA.CDGPR_PRIORITARIO
                LEFT JOIN (
                    SELECT
                        MA.CDG_CONTRATO AS CONTRATO,
                        SUM(
                            CASE 
                                WHEN MA.CDG_TIPO_PAGO IN (15) THEN 0
                                WHEN MA.MOVIMIENTO = 0 THEN -MA.MONTO
                                WHEN MA.MOVIMIENTO = 1 THEN MA.MONTO
                            END
                        ) AS SALDO_CORTE,
                        SUM(
                            CASE MA.CDG_TIPO_PAGO
                                WHEN '15' THEN MONTO
                                ELSE 0
                            END
                        ) AS RENDIMIENTO,
                        SUM(
                            CASE MA.CDG_TIPO_PAGO
                                WHEN '5' THEN MA.MONTO
                                ELSE 0
                            END
                        ) AS INVERSION
                    FROM
                        MOVIMIENTOS_AHORRO MA
                    WHERE
                        TRUNC(MA.FECHA_MOV) BETWEEN FECHA_INICIO_REPORTE AND TO_DATE('$fechaF', 'YYYY-MM-DD')
                    GROUP BY MA.CDG_CONTRATO
                ) SC ON SC.CONTRATO = APA.CONTRATO
            WHERE
                TRUNC(APA.FECHA_APERTURA) BETWEEN FECHA_INICIO_REPORTE AND TO_DATE('$fechaF', 'YYYY-MM-DD')
                filtroExtra
            UNION
            SELECT
                APA.CDGCL AS ID_SOCIO,
                CONCATENA_NOMBRE(CL.NOMBRE1, CL.NOMBRE2, CL.PRIMAPE, CL.SEGAPE) AS NOMBRE_SOCIO,
                NULL AS ID_PQ,
                NULL AS NOMBRE_PQ,
                CO.NOMBRE AS SUCURSAL,
                'INVERSIÓN' AS TIPO_CUENTA,
                TO_CHAR(APA.FECHA_APERTURA, 'DD/MM/YYYY') AS FECHA_INICIO,
                TO_CHAR(CI.FECHA_VENCIMIENTO, 'DD/MM/YYYY') AS FECHA_VENCIMIENTO,
                PI.PLAZO || ' ' || (
                    CASE
                        WHEN PI.PERIODICIDAD = 'D' THEN 'DÍAS'
                        WHEN PI.PERIODICIDAD = 'M' THEN 'MESES'
                        ELSE 'AÑOS'
                    END
                ) AS PLAZO,
                '0' AS COMISION_APERTURA,
                NULL AS BONIFICACION,
                0 AS SALDO_AHORRO,
                TO_CHAR(CI.FECHA_APERTURA, 'DD/MM/YYYY') AS FECHA_INVERSION,
                SC.INVERSION AS SALDO_INVERSION,
                SC.INVERSION AS SALDO_SOCIO,
                CONCATENA_NOMBRE(PE.NOMBRE1, PE.NOMBRE2, PE.PRIMAPE, PE.SEGAPE) AS EJECUTIVO,
                TI.TASA / 100 AS TASA,
                TO_NUMBER(
                    TO_CHAR(
                        (TRUNC(TO_DATE('2024-10-25', 'YYYY-MM-DD')) - TRUNC(CI.FECHA_APERTURA)) * ((CI.MONTO_INVERSION * (TI.TASA / 100)) / 365)
                    , '999999999.00')
                ) AS RENDIMIENTO
            FROM
                CUENTA_INVERSION CI 
                LEFT JOIN ASIGNA_PROD_AHORRO APA ON APA.CONTRATO =  CI.CDG_CONTRATO
                LEFT JOIN CL ON CL.CODIGO = APA.CDGCL
                LEFT JOIN PE ON PE.CODIGO = APA.CDGPE_REGISTRO
                LEFT JOIN CO ON CO.CODIGO = APA.CDGCO
                LEFT JOIN TASA_INVERSION TI ON TI.CODIGO = CI.CDG_TASA
                LEFT JOIN PLAZO_INVERSION PI ON PI.CODIGO = TI.CDG_PLAZO
                LEFT JOIN (
                    SELECT
                        MA.CDG_CONTRATO AS CONTRATO,
                        SUM(
                            CASE 
                                WHEN MA.CDG_TIPO_PAGO IN (15) THEN 0
                                WHEN MA.MOVIMIENTO = 0 THEN -MA.MONTO
                                WHEN MA.MOVIMIENTO = 1 THEN MA.MONTO
                            END
                        ) AS SALDO_CORTE,
                        SUM(
                            CASE MA.CDG_TIPO_PAGO
                                WHEN '15' THEN MONTO
                                ELSE 0
                            END
                        ) AS RENDIMIENTO,
                        SUM(
                            CASE MA.CDG_TIPO_PAGO
                                WHEN '5' THEN MA.MONTO
                                ELSE 0
                            END
                        ) AS INVERSION
                    FROM
                        MOVIMIENTOS_AHORRO MA
                    WHERE
                        TRUNC(MA.FECHA_MOV) BETWEEN FECHA_INICIO_REPORTE AND TO_DATE('$fechaF', 'YYYY-MM-DD')
                    GROUP BY MA.CDG_CONTRATO
                ) SC ON SC.CONTRATO = APA.CONTRATO
            WHERE
                TRUNC(APA.FECHA_APERTURA) BETWEEN FECHA_INICIO_REPORTE AND TO_DATE('$fechaF', 'YYYY-MM-DD')
                AND CI.ESTATUS = 'A'
                filtroExtra
        SQL;

        $fechaInicio = "TO_DATE('2024-01-01', 'YYYY-MM-DD')";
        if ($datos['sucursal']) $fechaInicio = <<<SQL
        (
            SELECT
                TRUNC(MIN(FECHA_APERTURA))
            FROM
                ASIGNA_PROD_AHORRO
            WHERE
                CDGCO = '{$datos['sucursal']}'
        )
        SQL;
        $qry = str_ireplace("FECHA_INICIO_REPORTE", $fechaInicio, $qry);

        $filtroExtra = "";
        if ($datos['sucursal']) $filtroExtra = "AND APA.CDGCO = '{$datos['sucursal']}'";
        $qry = str_ireplace("filtroExtra", $filtroExtra, $qry);

        try {
            $mysqli = new Database();
            $res = $mysqli->queryAll($qry);
            if (count($res) === 0) return self::Responde(false, "No se encontraron registros de ahorro para los parámetros proporcionados.");
            return self::Responde(true, "Ahorros encontrados.", $res);
        } catch (Exception $e) {
            return self::Responde(false, "Error al buscar registros de ahorro.", null, $e->getMessage());
        }
    }

    public static function GetDevengoAhorro($datos)
    {
        $fechaI = $datos['fechaI'];
        $fechaF = $datos['fechaF'];

        $qry = <<<SQL
        SELECT
            APA.CDGCL AS ID_SOCIO,
            CONCATENA_NOMBRE(CL.NOMBRE1, CL.NOMBRE2, CL.PRIMAPE, CL.SEGAPE) AS NOMBRE_SOCIO,
            CASE
                WHEN LENGTH(APA.CONTRATO) > 14 THEN SUBSTR(APA.CONTRATO, 15, 2)
                ELSE NULL
            END AS ID_PQ,
            CASE
                WHEN LENGTH(APA.CONTRATO) > 14 THEN CONCATENA_NOMBRE(CLP.NOMBRE1, CLP.NOMBRE2, CLP.APELLIDO1, CLP.APELLIDO2)
                ELSE NULL
            END AS NOMBRE_PQ,
            PRP.DESCRIPCION AS PRODUCTO,
            APA.TASA / 100 AS TASA,
            APA.CDGPE_REGISTRO AS ID_PROMOTOR,
            CONCATENA_NOMBRE(PE.NOMBRE1, PE.NOMBRE2, PE.PRIMAPE, PE.SEGAPE) AS NOMBRE_PROMOTOR,
            TO_CHAR(APA.FECHA_APERTURA, 'DD/MM/YYYY') AS FECHA_APERTURA,
            TO_CHAR(SYSDATE, 'DD/MM/YYYY') AS FECHA_CORTE,
            APA.SALDO_REAL AS SALDO,
            (
                SELECT
                    SUM(DA.DEVENGO)
                FROM
                    DEVENGO_AHORRO DA
                WHERE
                    DA.CONTRATO = APA.CONTRATO
                    AND TRUNC(DA.FECHA) BETWEEN TO_DATE('$fechaI', 'YYYY-MM-DD') AND TO_DATE('$fechaF', 'YYYY-MM-DD')
                GROUP BY
                    DA.CONTRATO
            ) AS INTERES_DEVENGADO,
            0 AS INTERES_DEV_SIN_IVA,
            0 AS IVA,
            .16 AS TASA_IVA,
            TRUNC(SYSDATE) - TRUNC(APA.FECHA_APERTURA) AS DIAS_DEVENGADOS
        FROM
            ASIGNA_PROD_AHORRO APA
            LEFT JOIN CL ON CL.CODIGO = APA.CDGCL
            LEFT JOIN CL_PQS CLP ON CLP.CDG_CONTRATO = APA.CONTRATO
            LEFT JOIN PE ON PE.CODIGO = APA.CDGPE_REGISTRO
            LEFT JOIN CO ON CO.CODIGO = APA.CDGCO
            LEFT JOIN PR_PRIORITARIO PRP ON PRP.CODIGO = APA.CDGPR_PRIORITARIO
        WHERE
            TRUNC(APA.FECHA_APERTURA) BETWEEN TO_DATE('$fechaI', 'YYYY-MM-DD') AND TO_DATE('$fechaF', 'YYYY-MM-DD')
            filtroExtra
        ORDER BY
            APA.CONTRATO
        SQL;

        $filtroExtra = "";
        if ($datos['sucursal']) $filtroExtra = "AND APA.CDGCO = '{$datos['sucursal']}'";

        $qry = str_ireplace("filtroExtra", $filtroExtra, $qry);

        try {
            $mysqli = new Database();
            $res = $mysqli->queryAll($qry);
            if (count($res) === 0) return self::Responde(false, "No se encontraron registros de ahorro para los parámetros proporcionados.");
            return self::Responde(true, "Ahorros encontrados.", $res);
        } catch (Exception $e) {
            return self::Responde(false, "Error al buscar registros de ahorro.", null, $e->getMessage());
        }
    }

    public static function GetLayoutPagosCredito($datos)
    {
        $query = <<<SQL
        	SELECT
                TO_CHAR(FECHA, 'DD/MM/YYYY') FECHA,
                TIPO_OPERACION(PGD.TIPO) TIPO,
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
                --AND PGD.TIPO IN ('P','G', 'X')
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
}
