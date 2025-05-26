<?php
session_start();
require_once('inc/config.php'); // conexión a la BD
require_once('header.php');// tu cabecera del admin
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Reporte de Ventas</h1>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
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
                            $stmt = $pdo->prepare("SELECT * FROM tbl_payment ORDER BY payment_date DESC");
                            $stmt->execute();
                            $result = $stmt->fetchAll();
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
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once('footer.php'); ?>
