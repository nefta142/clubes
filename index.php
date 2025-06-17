<?php
$archivoXML = 'clubes.xml';
$mensaje = '';
$modo_edicion = false;
$datos_editar = ['cif' => '', 'nombre' => '', 'pais' => '', 'color' => ''];

// Si se pide eliminar
if (isset($_GET['delete'])) {
    $cif_eliminar = $_GET['delete'];
    if (file_exists($archivoXML)) {
        $xml = new DOMDocument();
        $xml->load($archivoXML);
        $xpath = new DOMXPath($xml);
        $nodos = $xpath->query("//club[@CIF='$cif_eliminar']");
        foreach ($nodos as $nodo) {
            $nodo->parentNode->removeChild($nodo);
        }
        $xml->save($archivoXML);
        $mensaje = "🗑️ Club eliminado correctamente.";
    }
}

// Si se pide editar
if (isset($_GET['edit'])) {
    $modo_edicion = true;
    $cif_editar = $_GET['edit'];
    if (file_exists($archivoXML)) {
        $xml = simplexml_load_file($archivoXML);
        foreach ($xml->club as $club) {
            if ((string)$club['CIF'] === $cif_editar) {
                $datos_editar = [
                    'cif' => (string)$club['CIF'],
                    'nombre' => (string)$club->nombre,
                    'pais' => (string)$club->pais,
                    'color' => (string)$club->color
                ];
                break;
            }
        }
    }
}

// Guardar o actualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $pais = $_POST['pais'] ?? '';
    $color = $_POST['color'] ?? '';
    $cif = $_POST['cif'] ?? '';
    $es_edicion = isset($_POST['es_edicion']);

    if (!preg_match('/^\d{4}[A-Z]{2}$/', $cif)) {
        $mensaje = "❌ CIF inválido. Debe tener 4 números y 2 letras mayúsculas (ej: 1234MX).";
    } else {
        $xml = new DOMDocument();
        $xml->preserveWhiteSpace = false;
        $xml->formatOutput = true;

        if (file_exists($archivoXML)) {
            $xml->load($archivoXML);
            $clubes = $xml->getElementsByTagName('clubes')->item(0);
        } else {
            $clubes = $xml->createElement('clubes');
            $xml->appendChild($clubes);
        }

        if ($es_edicion) {
            $xpath = new DOMXPath($xml);
            $nodos = $xpath->query("//club[@CIF='$cif']");
            foreach ($nodos as $nodo) {
                $nodo->getElementsByTagName('nombre')->item(0)->nodeValue = $nombre;
                $nodo->getElementsByTagName('pais')->item(0)->nodeValue = $pais;
                $nodo->getElementsByTagName('color')->item(0)->nodeValue = $color;
            }
            $mensaje = "✅ Club actualizado.";
        } else {
            $club = $xml->createElement('club');
            $club->setAttribute('CIF', $cif);
            $club->appendChild($xml->createElement('nombre', $nombre));
            $club->appendChild($xml->createElement('pais', $pais));
            $club->appendChild($xml->createElement('color', $color));
            $clubes->appendChild($club);
            $mensaje = "✅ Club guardado.";
        }

        $xml->save($archivoXML);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Clubes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-4">

<div class="container">
  <h2 class="mb-4"><?= $modo_edicion ? 'Editar Club' : 'Agregar nuevo Club' ?></h2>

  <?php if ($mensaje): ?>
    <div class="alert alert-info"><?= htmlspecialchars($mensaje) ?></div>
  <?php endif; ?>

  <form method="post" class="border p-4 bg-white shadow-sm rounded">
    <input type="hidden" name="es_edicion" value="<?= $modo_edicion ? '1' : '' ?>">

    <div class="mb-3">
      <label class="form-label">Nombre:</label>
      <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($datos_editar['nombre']) ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">País:</label>
      <input type="text" name="pais" class="form-control" value="<?= htmlspecialchars($datos_editar['pais']) ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Color:</label>
      <select name="color" class="form-select" required>
        <?php foreach (['rojo', 'blanco', 'negro', 'azul'] as $col): ?>
          <option value="<?= $col ?>" <?= $datos_editar['color'] === $col ? 'selected' : '' ?>><?= ucfirst($col) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">CIF:</label>
      <input type="text" name="cif" class="form-control" value="<?= htmlspecialchars($datos_editar['cif']) ?>" pattern="\d{4}[A-Z]{2}" <?= $modo_edicion ? 'readonly' : 'required' ?>>
    </div>

    <button type="submit" class="btn btn-primary"><?= $modo_edicion ? 'Guardar Cambios' : 'Guardar Club' ?></button>
  </form>

  <h2 class="mt-5">Clubes Registrados</h2>

  <?php if (file_exists($archivoXML) && count($xml = simplexml_load_file($archivoXML)->club) > 0): ?>
    <table class="table table-bordered table-striped mt-3">
      <thead class="table-light">
        <tr>
          <th>Nombre</th>
          <th>País</th>
          <th>Color</th>
          <th>CIF</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($xml as $club): ?>
          <tr>
            <td><?= htmlspecialchars($club->nombre) ?></td>
            <td><?= htmlspecialchars($club->pais) ?></td>
            <td><?= htmlspecialchars($club->color) ?></td>
            <td><?= htmlspecialchars($club['CIF']) ?></td>
            <td>
              <a href="?edit=<?= urlencode($club['CIF']) ?>" class="btn btn-sm btn-warning"> Editar</a>
              <a href="?delete=<?= urlencode($club['CIF']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este club?')"> Eliminar</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p class="text-muted">No hay clubes registrados todavía.</p>
  <?php endif; ?>
</div>

</body>
</html>
