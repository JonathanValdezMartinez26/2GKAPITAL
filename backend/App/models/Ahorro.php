<?php

namespace App\models;

defined("APPPATH") or die("Access denied");

use \Core\Database;
use Core\Model;

class Ahorro extends Model
{
    public static function ConsultaTickets($datos)
    {
        $query = <<<SQL
            SELECT
                TA.CODIGO,
                TA.CDG_CONTRATO,
                TO_CHAR(TA.FECHA, 'DD/MM/YYYY HH24:MI:SS') AS FECHA_ALTA,
                TA.MONTO,
                TA.CDGPE,
                (
                    CL.NOMBRE1 || ' ' || CL.NOMBRE2 || ' ' || CL.PRIMAPE || ' ' || CL.SEGAPE
                ) AS NOMBRE_CLIENTE,
                'CUENTA AHORRO' AS TIPO_AHORRO
            FROM
                TICKETS_AHORRO TA
                INNER JOIN ASIGNA_PROD_AHORRO ON ASIGNA_PROD_AHORRO.CONTRATO = TA.CDG_CONTRATO
                INNER JOIN CL ON CL.CODIGO = ASIGNA_PROD_AHORRO.CDGCL
            WHERE
                TRUNC(TA.FECHA) BETWEEN TO_DATE(:fechaI, 'YYYY-MM-DD') AND TO_DATE(:fechaF, 'YYYY-MM-DD')
        SQL;

        $parametros = [
            'fechaI' => $datos['fechaI'],
            'fechaF' => $datos['fechaF']
        ];

        if ($datos['usuario'] != 'AMGM') {
            $query .= ' AND TA.CDGPE = :usuario';
            $parametros['usuario'] = $datos['usuario'];
        }
        $query .= ' ORDER BY TA.FECHA DESC';

        try {
            $db = new Database();
            $r = $db->queryAll($query, $parametros);
            return self::Responde(true, "Tickets obtenidos correctamente", $r);
        } catch (\Exception $e) {
            return self::Responde(false, "Error al consultar los tickets de ahorro.", null, $e->getMessage());
        }
    }

    public static function ConsultaSolicitudesTickets($datos)
    {
        $query = <<<sql
        SELECT TAR.CODIGO, TAR.CDGTICKET_AHORRO, TAR.FREGISTRO, TAR.FREIMPRESION, TAR.MOTIVO, TAR.ESTATUS, TAR.CDGPE_SOLICITA, TAR.CDGPE_AUTORIZA, TAR.AUTORIZA, TAR.DESCRIPCION_MOTIVO, 
        TAR.AUTORIZA_CLIENTE, TA.CDG_CONTRATO 
        FROM ESIACOM.TICKETS_AHORRO_REIMPRIME TAR
        INNER JOIN TICKETS_AHORRO TA ON TA.CODIGO = TAR.CDGTICKET_AHORRO 
        sql;

        $filtros = [];
        if ($datos['usuario'] != 'AMGM') $filtros[] = "TAR.CDGPE_SOLICITA = '{$datos['usuario']}'";
        if ($datos['estatus']) $filtros[] = "TAR.ESTATUS = '{$datos['estatus']}'";
        if ($datos['fechaI'] && $datos['fechaF']) $filtros[] = "TRUNC(TAR.FREGISTRO) BETWEEN TO_DATE('{$datos['fechaI']}', 'YYYY-MM-DD') AND TO_DATE('{$datos['fechaF']}', 'YYYY-MM-DD')";

        if (count($filtros) > 0) $query .= " WHERE " . implode(" AND ", $filtros);
        $query .= " ORDER BY TAR.FREGISTRO DESC";

        $mysqli = new Database();
        return $mysqli->queryAll($query);
    }

    public static function insertSolicitudAhorro($datos)
    {

        $qry1 = <<<SQL
            SELECT
                COUNT(*) AS EXISTE
            FROM
                TICKETS_AHORRO_REIMPRIME
            WHERE
                CDGPE_SOLICITA = :cdgpe
                AND CDGTICKET_AHORRO = :folio
                AND ESTATUS = '0'
                AND AUTORIZA = '0'
        SQL;

        $param = [
            'cdgpe' => $datos['cdgpe'],
            'folio' => $datos['folio']
        ];

        try {
            $db = new Database();
            $res = $db->queryOne($qry1, $param);

            if ($res['EXISTE'] != 0) return self::Responde(false, 'Ya solicito la reimpresión de este ticket, espere a su validación o contacte a tesorería.', $res);
            $param['motivo'] = $datos['motivo'];
            $param['descripcion'] = $datos['descripcion'];

            $qry2 = <<<SQL
                INSERT INTO TICKETS_AHORRO_REIMPRIME
                    (CODIGO, CDGTICKET_AHORRO, FREGISTRO, FREIMPRESION, MOTIVO, ESTATUS, CDGPE_SOLICITA, CDGPE_AUTORIZA, AUTORIZA, DESCRIPCION_MOTIVO, FAUTORIZA, AUTORIZA_CLIENTE)
                VALUES
                    (SEC_TICKET_REIMPRIME.NEXTVAL, :folio, CURRENT_TIMESTAMP, '', :motivo, '0', :cdgpe, '', '0', :descripcion, NULL , '0')
            SQL;

            $db->insertar($qry2, $param);
            return self::Responde(true, 'Solicitud de reimpresión de ticket registrada correctamente.');
        } catch (\Exception $e) {
            return self::Responde(false, 'Error al solicitar la reimpresión del ticket.', null, $e->getMessage());
        }
    }


    public static function ConsultaMovimientosDia($fecha)
    {
        if ($fecha == 'AMGM') {
            $query = <<<sql
            SELECT TAR.CODIGO, TAR.CDGTICKET_AHORRO, TAR.FREGISTRO, TAR.FREIMPRESION, TAR.MOTIVO, TAR.ESTATUS, TAR.CDGPE_SOLICITA, TAR.CDGPE_AUTORIZA, TAR.AUTORIZA, TAR.DESCRIPCION_MOTIVO, 
            TAR.AUTORIZA_CLIENTE, TA.CDG_CONTRATO 
            FROM ESIACOM.TICKETS_AHORRO_REIMPRIME TAR
            INNER JOIN TICKETS_AHORRO TA ON TA.CODIGO = TAR.CDGTICKET_AHORRO 
            
sql;
        } else {
            $query = <<<sql
            SELECT * FROM MOVIMIENTOS_AHORRO
sql;
        }


        $mysqli = new Database();
        return $mysqli->queryAll($query);
    }
}
