<?php
/** Corrige municipalidades dañadas por una carga UTF-8 incorrecta. */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Este archivo solo puede ejecutarse desde la línea de comandos.');
}

require_once __DIR__ . '/../config/database.php';

$sqlBase = file_get_contents(__DIR__ . '/database.sql');
if ($sqlBase === false || !preg_match(
    "/INSERT IGNORE INTO municipalidades \\(nombre, region\\) VALUES(.*?);/s",
    $sqlBase,
    $bloque
)) {
    throw new RuntimeException('No fue posible leer la lista oficial de municipalidades.');
}

preg_match_all(
    "/\\('((?:[^']|'')*)',\\s*'((?:[^']|'')*)'\\)/u",
    $bloque[1],
    $filas,
    PREG_SET_ORDER
);

$municipalidades = [];
foreach ($filas as $fila) {
    $municipalidades[str_replace("''", "'", $fila[1])] = str_replace("''", "'", $fila[2]);
}
$municipalidades['Los Lagos'] = 'Los Ríos';

$conn->begin_transaction();
try {
    $buscar = $conn->prepare('SELECT id FROM municipalidades WHERE nombre = ? LIMIT 1');
    $reasignar = $conn->prepare('UPDATE solicitudes SET municipalidad_id = ? WHERE municipalidad_id = ?');
    $eliminar = $conn->prepare('DELETE FROM municipalidades WHERE id = ?');
    $corregir = $conn->prepare('UPDATE municipalidades SET nombre = ?, region = ? WHERE id = ?');
    $actualizarRegion = $conn->prepare('UPDATE municipalidades SET region = ? WHERE nombre = ?');
    $insertar = $conn->prepare('INSERT IGNORE INTO municipalidades (nombre, region) VALUES (?, ?)');

    foreach ($municipalidades as $nombreCorrecto => $regionCorrecta) {
        $nombreDanado = preg_replace('/[\\x80-\\xFF]/', '?', $nombreCorrecto);

        $buscar->bind_param('s', $nombreCorrecto);
        $buscar->execute();
        $correcto = $buscar->get_result()->fetch_assoc();

        if ($nombreDanado !== $nombreCorrecto) {
            $buscar->bind_param('s', $nombreDanado);
            $buscar->execute();
            $danado = $buscar->get_result()->fetch_assoc();

            if ($danado && $correcto && (int) $danado['id'] !== (int) $correcto['id']) {
                $idCorrecto = (int) $correcto['id'];
                $idDanado = (int) $danado['id'];
                $reasignar->bind_param('ii', $idCorrecto, $idDanado);
                $reasignar->execute();
                $eliminar->bind_param('i', $idDanado);
                $eliminar->execute();
            } elseif ($danado && !$correcto) {
                $idDanado = (int) $danado['id'];
                $corregir->bind_param('ssi', $nombreCorrecto, $regionCorrecta, $idDanado);
                $corregir->execute();
                $correcto = ['id' => $idDanado];
            }
        }

        if ($correcto) {
            $actualizarRegion->bind_param('ss', $regionCorrecta, $nombreCorrecto);
            $actualizarRegion->execute();
        } else {
            $insertar->bind_param('ss', $nombreCorrecto, $regionCorrecta);
            $insertar->execute();
        }
    }

    $conn->commit();
    echo "Municipalidades corregidas correctamente.\n";
} catch (Throwable $e) {
    $conn->rollback();
    fwrite(STDERR, 'Error: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

