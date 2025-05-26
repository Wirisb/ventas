<?php
session_start();
require_once('inc/config.php'); // conexión a la BD
require_once('header.php');// tu cabecera del admin

// Procesar filtro de fecha
$fecha_filtro = '';
if (isset($_GET['fecha']) && !empty($_GET['fecha'])) {
    $fecha_filtro = $_GET['fecha'];
}
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Reporte de Ventas</h1>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- Formulario de filtro por fecha -->
            <form method="get" class="form-inline" style="margin-bottom:15px;">
                <label for="fecha">Buscar por fecha:</label>
                <input type="date" id="fecha" name="fecha" value="<?php echo htmlspecialchars($fecha_filtro); ?>" class="form-control" required>
                <button type="submit" class="btn btn-primary">Buscar</button>
                <?php if($fecha_filtro): ?>
                    <a href="sales-report.php" class="btn btn-default">Limpiar</a>
                <?php endif; ?>
            </form>

            <div class="box box-info">
                <div class="box-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID de Cliente</th>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Monto Pagado</th>
                                <th>Método de Pago</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Factura</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($fecha_filtro) {
                                // Mostrar solo pagos de la fecha seleccionada
                                $stmt = $pdo->prepare("SELECT * FROM tbl_payment WHERE DATE(payment_date) = :fecha ORDER BY payment_date DESC");
                                $stmt->execute(['fecha' => $fecha_filtro]);
                            } else {
                                // Mostrar todos los pagos
                                $stmt = $pdo->prepare("SELECT * FROM tbl_payment ORDER BY payment_date DESC");
                                $stmt->execute();
                            }
                            $result = $stmt->fetchAll();
                            if (empty($result)):
                            ?>
                                <tr>
                                    <td colspan="8" style="text-align:center;">No hay resultados para la fecha seleccionada.</td>
                                </tr>
                            <?php
                            else:
                                foreach ($result as $row):
                            ?>
                            <tr>
                                <td><?php echo $row['customer_id']; ?></td>
                                <td><?php echo $row['customer_name']; ?></td>
                                <td><?php echo $row['customer_email']; ?></td>
                                <td>$<?php echo number_format($row['paid_amount'], 2); ?></td>
                                <td><?php echo $row['payment_method']; ?></td>
                                <td><?php echo $row['payment_status']; ?></td>
                                <td><?php echo $row['payment_date']; ?></td>
                                <td>
                                    <form action="../payment/efectivo/factura_individual.php" method="post" target="_blank">
                                        <input type="hidden" name="payment_id" value="<?php echo $row['payment_id']; ?>">
                                        <button class="btn btn-xs btn-primary">Ver Factura</button>
                                    </form>
                                </td>
                            </tr>
                            <?php
                                endforeach;
                            endif;
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once('footer.php'); ?>