<?php

namespace App\Oracle;

use Exception;

class OracleDB
{
    static public function getConnection()
    {
        $conn = oci_connect("venta_consorcio", "venta", "23.254.165.3/XE", "AL32UTF8" );
        if (!$conn) {
            throw new Exception('No se conectó');
        } else {
            $abc = oci_parse($conn, "alter session set nls_numeric_characters='.,'");
            oci_execute($abc);
            return $conn;
        }
    }

    static public function executeProcedure($conn, $procedure, $bindings)
    {
        $params = '';
        foreach ($bindings as $key => $value) {
            $params .= ':' . $key . ',';
        }
        $sql = "BEGIN " . $procedure . "(" . substr($params, 0, -1) . ");" . " END;";
        $stid = oci_parse($conn, $sql);
        foreach ($bindings as $key => $value) {
            oci_bind_by_name($stid, ':' . $key, $value);
        }
        $ddd = oci_execute($stid);        
        return $ddd;
        // if (!$r) {
        //     $e = oci_error($stid);
        //     throw new Exception($e);
        // } else {
        //     $r = oci_commit($conn);
        //     if (!$r) {
        //         $e = oci_error($stid);
        //         throw new Exception($e);
        //     }
        // }
    }
    static public function executeFunctionCursor($conn, $function, $cursor, $bindings)
    {
        $params = '';
        foreach ($bindings as $key => $value) {
            $params .= ':' . $key . ',';
        }
        $sql = "BEGIN :result := " . $function . "(" . substr($params, 0, -1) . ");" . " END;";
        $stid = oci_parse($conn, $sql);
        oci_bind_by_name($stid, ':result', $cursor, -1, OCI_B_CURSOR);
        foreach ($bindings as $key => $value) {
            oci_bind_by_name($stid, ':' . $key, $value);
        }
        oci_execute($stid);
        oci_execute($cursor);
        $lista = [];
        if ($stid) {
            while (($row = oci_fetch_array($cursor, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
                array_push(
                    $lista,
                    $row
                );
            }
            oci_free_statement($stid);
            oci_free_statement($cursor);
        }
        return $lista;
    }
}
