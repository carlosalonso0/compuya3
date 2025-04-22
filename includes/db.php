<?php
/**
 * Conexión a la base de datos y funciones relacionadas
 */

// Intentar establecer conexión con la base de datos
function conectar_db() {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Verificar conexión
    if ($mysqli->connect_error) {
        die('Error de conexión a la base de datos: ' . $mysqli->connect_error);
    }
    
    // Establecer charset
    $mysqli->set_charset(DB_CHARSET);
    
    return $mysqli;
}

// Obtener una instancia de la conexión
function db() {
    static $db = null;
    
    if ($db === null) {
        $db = conectar_db();
    }
    
    return $db;
}

// Función para ejecutar consultas
function query($sql, $params = []) {
    $db = db();
    $stmt = $db->prepare($sql);
    
    if (!$stmt) {
        die('Error en la preparación de la consulta: ' . $db->error);
    }
    
    // Si hay parámetros, vincularlos
    if (!empty($params)) {
        $types = '';
        $bindParams = [];
        
        // Construir la cadena de tipos
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } elseif (is_string($param)) {
                $types .= 's';
            } else {
                $types .= 'b';
            }
            
            $bindParams[] = $param;
        }
        
        // Referenciar los valores para bind_param
        $bindValues = array_merge([$types], $bindParams);
        $bindReferences = [];
        
        foreach ($bindValues as $key => $value) {
            $bindReferences[$key] = &$bindValues[$key];
        }
        
        call_user_func_array([$stmt, 'bind_param'], $bindReferences);
    }
    
    // Ejecutar la consulta
    if (!$stmt->execute()) {
        die('Error al ejecutar la consulta: ' . $stmt->error);
    }
    
    return $stmt;
}

// Obtener un solo registro
function get_row($sql, $params = []) {
    $stmt = query($sql, $params);
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row;
}

// Obtener múltiples registros
function get_rows($sql, $params = []) {
    $stmt = query($sql, $params);
    $result = $stmt->get_result();
    $rows = [];
    
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    
    $stmt->close();
    
    return $rows;
}

// Insertar un registro y devolver el ID
function insert($sql, $params = []) {
    $stmt = query($sql, $params);
    $insertId = db()->insert_id;
    $stmt->close();
    
    return $insertId;
}

// Actualizar o eliminar registros y devolver el número de filas afectadas
function update($sql, $params = []) {
    $stmt = query($sql, $params);
    $affectedRows = $stmt->affected_rows;
    $stmt->close();
    
    return $affectedRows;
}

// Escapar un valor para uso seguro en consultas
function escape($value) {
    return db()->real_escape_string($value);
}

// Iniciar una transacción
function begin_transaction() {
    db()->begin_transaction();
}

// Confirmar una transacción
function commit() {
    db()->commit();
}

// Revertir una transacción
function rollback() {
    db()->rollback();
}