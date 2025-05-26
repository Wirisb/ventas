<?php require_once('header.php'); ?>

<?php
$error_message = '';
if(isset($_POST['form1'])) {
    $valid = 1;
    if(empty($_POST['subject_text'])) {
        $valid = 0;
        $error_message .= 'El asunto no puede estar vacío\n';
    }
    if(empty($_POST['message_text'])) {
        $valid = 0;
        $error_message .= 'El mensaje no puede estar vacío\n';
    }
    if($valid == 1) {

        $subject_text = strip_tags($_POST['subject_text']);
        $message_text = strip_tags($_POST['message_text']);

        // Obteniendo la Dirección de Email del Cliente
        $statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_id=?");
        $statement->execute(array($_POST['cust_id']));
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
        foreach ($result as $row) {
            $cust_email = $row['cust_email'];
        }

        // Obteniendo la Dirección de Email del Administrador
        $statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
        foreach ($result as $row) {
            $admin_email = $row['contact_email'];
        }

        $order_detail = '';
        $statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE payment_id=?");
        $statement->execute(array($_POST['payment_id']));
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
        foreach ($result as $row) {
        	
        	if($row['payment_method'] == 'PayPal'):
        		$payment_details = '
ID de Transacción: '.$row['txnid'].'<br>
        		';
        	elseif($row['payment_method'] == 'Stripe'):
				$payment_details = '
ID de Transacción: '.$row['txnid'].'<br>
Número de Tarjeta: '.$row['card_number'].'<br>
CVV de Tarjeta: '.$row['card_cvv'].'<br>
Mes de Tarjeta: '.$row['card_month'].'<br>
Año de Tarjeta: '.$row['card_year'].'<br>
        		';
        	elseif($row['payment_method'] == 'Bank Deposit'):
				$payment_details = '
Detalles de Transacción: <br>'.$row['bank_transaction_info'];
        	endif;

            $order_detail .= '
Nombre del Cliente: '.$row['customer_name'].'<br>
Email del Cliente: '.$row['customer_email'].'<br>
Método de Pago: '.$row['payment_method'].'<br>
Fecha de Pago: '.$row['payment_date'].'<br>
Detalles de Pago: <br>'.$payment_details.'<br>
Monto Pagado: '.$row['paid_amount'].'<br>
Estado de Pago: '.$row['payment_status'].'<br>
Estado de Envío: '.$row['shipping_status'].'<br>
ID de Pago: '.$row['payment_id'].'<br>
            ';
        }

        $i=0;
        $statement = $pdo->prepare("SELECT * FROM tbl_order WHERE payment_id=?");
        $statement->execute(array($_POST['payment_id']));
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
        foreach ($result as $row) {
            $i++;
            $order_detail .= '
<br><b><u>Producto '.$i.'</u></b><br>
Nombre del Producto: '.$row['product_name'].'<br>
Talla: '.$row['size'].'<br>
Color: '.$row['color'].'<br>
Cantidad: '.$row['quantity'].'<br>
Precio Unitario: '.$row['unit_price'].'<br>
            ';
        }

        $statement = $pdo->prepare("INSERT INTO tbl_customer_message (subject,message,order_detail,cust_id) VALUES (?,?,?,?)");
        $statement->execute(array($subject_text,$message_text,$order_detail,$_POST['cust_id']));

        // enviando email
        $to_customer = $cust_email;
        $message = '
<html><body>
<h3>Mensaje: </h3>
'.$message_text.'
<h3>Detalles de la Orden: </h3>
'.$order_detail.'
</body></html>
';
        $headers = 'From: ' . $admin_email . "\r\n" .
                   'Reply-To: ' . $admin_email . "\r\n" .
                   'X-Mailer: PHP/' . phpversion() . "\r\n" . 
                   "MIME-Version: 1.0\r\n" . 
                   "Content-Type: text/html; charset=ISO-8859-1\r\n";

        // Enviando email al administrador                  
        mail($to_customer, $subject_text, $message, $headers);
        
        $success_message = 'Su email al cliente ha sido enviado exitosamente.';

    }
}
?>
<?php
if($error_message != '') {
    echo "<script>alert('".$error_message."')</script>";
}
if($success_message != '') {
    echo "<script>alert('".$success_message."')</script>";
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Ver Órdenes</h1>
	</div>
</section>


<section class="content">

  <div class="row">
    <div class="col-md-12">


      <div class="box box-info">
        
        <div class="box-body table-responsive">
          <table id="example1" class="table table-bordered table-hover table-striped">
			<thead>
			    <tr>
			        <th>#</th>
                    <th>Cliente</th>
			        <th>Detalles del Producto</th>
                    <th>
                    	Información de Pago
                    </th>
                    <th>Monto Pagado</th>
                    <th>Estado de Pago</th>
                    <th>Estado de Envío</th>
			        <th>Acción</th>
			    </tr>
			</thead>
            <tbody>
            	<?php
            	$i=0;
            	$statement = $pdo->prepare("SELECT * FROM tbl_payment ORDER by id DESC");
            	$statement->execute();
            	$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
            	foreach ($result as $row) {
            		$i++;
            		?>
					<tr class="<?php if($row['payment_status']=='Pending'){echo 'bg-r';}else{echo 'bg-g';} ?>">
	                    <td><?php echo $i; ?></td>
	                    <td>
                            <b>ID:</b> <?php echo $row['customer_id']; ?><br>
                            <b>Nombre:</b><br> <?php echo $row['customer_name']; ?><br>
                            <b>Email:</b><br> <?php echo $row['customer_email']; ?><br><br>
                            <a href="#" data-toggle="modal" data-target="#model-<?php echo $i; ?>"class="btn btn-warning btn-xs" style="width:100%;margin-bottom:4px;">Enviar Mensaje</a>
                            <div id="model-<?php echo $i; ?>" class="modal fade" role="dialog">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal">&times;</button>
											<h4 class="modal-title" style="font-weight: bold;">Enviar Mensaje</h4>
										</div>
										<div class="modal-body" style="font-size: 14px">
											<form action="" method="post">
                                                <input type="hidden" name="cust_id" value="<?php echo $row['customer_id']; ?>">
                                                <input type="hidden" name="payment_id" value="<?php echo $row['payment_id']; ?>">
												<table class="table table-bordered">
													<tr>
														<td>Asunto</td>
														<td>
                                                            <input type="text" name="subject_text" class="form-control" style="width: 100%;">
														</td>
													</tr>
                                                    <tr>
                                                        <td>Mensaje</td>
                                                        <td>
                                                            <textarea name="message_text" class="form-control" cols="30" rows="10" style="width:100%;height: 200px;"></textarea>
                                                        </td>
                                                    </tr>
													<tr>
														<td></td>
														<td><input type="submit" value="Enviar Mensaje" name="form1"></td>
													</tr>
												</table>
											</form>
										</div>
										<div class="modal-footer">
											<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
										</div>
									</div>
								</div>
							</div>
                        </td>
                        <td>
                           <?php
                           $statement1 = $pdo->prepare("SELECT * FROM tbl_order WHERE payment_id=?");
                           $statement1->execute(array($row['payment_id']));
                           $result1 = $statement1->fetchAll(PDO::FETCH_ASSOC);
                           foreach ($result1 as $row1) {
                                echo '<b>Producto:</b> '.$row1['product_name'];
                                echo '<br>(<b>Talla:</b> '.$row1['size'];
                                echo ', <b>Color:</b> '.$row1['color'].')';
                                echo '<br>(<b>Cantidad:</b> '.$row1['quantity'];
                                echo ', <b>Precio Unitario:</b> '.$row1['unit_price'].')';
                                echo '<br><br>';
                           }
                           ?>
                        </td>
                        <td>
                        	<?php if($row['payment_method'] == 'PayPal'): ?>
                        		<b>Método de Pago:</b> <?php echo '<span style="color:red;"><b>'.$row['payment_method'].'</b></span>'; ?><br>
                        		<b>ID de Pago:</b> <?php echo $row['payment_id']; ?><br>
                        		<b>Fecha:</b> <?php echo $row['payment_date']; ?><br>
                        		<b>ID de Transacción:</b> <?php echo $row['txnid']; ?><br>
                        	<?php elseif($row['payment_method'] == 'Stripe'): ?>
                        		<b>Método de Pago:</b> <?php echo '<span style="color:red;"><b>'.$row['payment_method'].'</b></span>'; ?><br>
                        		<b>ID de Pago:</b> <?php echo $row['payment_id']; ?><br>
								<b>Fecha:</b> <?php echo $row['payment_date']; ?><br>
                        		<b>ID de Transacción:</b> <?php echo $row['txnid']; ?><br>
                        		<b>Número de Tarjeta:</b> <?php echo $row['card_number']; ?><br>
                        		<b>CVV de Tarjeta:</b> <?php echo $row['card_cvv']; ?><br>
                        		<b>Mes de Vencimiento:</b> <?php echo $row['card_month']; ?><br>
                        		<b>Año de Vencimiento:</b> <?php echo $row['card_year']; ?><br>
                        	<?php elseif($row['payment_method'] == 'Bank Deposit'): ?>
                        		<b>Método de Pago:</b> <?php echo '<span style="color:red;"><b>'.$row['payment_method'].'</b></span>'; ?><br>
                        		<b>ID de Pago:</b> <?php echo $row['payment_id']; ?><br>
								<b>Fecha:</b> <?php echo $row['payment_date']; ?><br>
                        		<b>Información de Transacción:</b> <br><?php echo $row['bank_transaction_info']; ?><br>
                        	<?php endif; ?>
                        </td>
                        <td>$<?php echo $row['paid_amount']; ?></td>
                        <td>
                            <?php echo $row['payment_status']; ?>
                            <br><br>
                            <?php
                                if($row['payment_status']=='Pending'){
                                    ?>
                                    <a href="order-change-status.php?id=<?php echo $row['id']; ?>&task=Completed" class="btn btn-success btn-xs" style="width:100%;margin-bottom:4px;">Marcar Completado</a>
                                    <?php
                                }
                            ?>
                        </td>
                        <td>
                            <?php echo $row['shipping_status']; ?>
                            <br><br>
                            <?php
                            if($row['payment_status']=='Completed') {
                                if($row['shipping_status']=='Pending'){
                                    ?>
                                    <a href="shipping-change-status.php?id=<?php echo $row['id']; ?>&task=Completed" class="btn btn-warning btn-xs" style="width:100%;margin-bottom:4px;">Marcar Completado</a>
                                    <?php
                                }
                            }
                            ?>
                        </td>
	                    <td>
                            <a href="#" class="btn btn-danger btn-xs" data-href="order-delete.php?id=<?php echo $row['id']; ?>" data-toggle="modal" data-target="#confirm-delete" style="width:100%;">Eliminar</a>
	                    </td>
	                </tr>
            		<?php
            	}
            	?>
            </tbody>
          </table>
        </div>
      </div>
  

</section>


<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Confirmación de Eliminación</h4>
            </div>
            <div class="modal-body">
                ¿Está seguro de que desea eliminar este elemento?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <a class="btn btn-danger btn-ok">Eliminar</a>
            </div>
        </div>
    </div>
</div>


<?php require_once('footer.php'); ?>