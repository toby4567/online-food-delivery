<!-- Check for a valid transaction -->
<?php
	session_start();

	if(isset($_SESSION['account'])) {
		// Do something if anything special you need.
	}else{
		header("Location: index.php");
	}
?>

<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Credit Card Faurd Detecting System</title>

	<!-- Load all static files -->
	<link rel="stylesheet" type="text/css" href="assets/BS/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="assets/css/styles.css">

</head>
<body class="container">
	<!-- Navbar included -->
	<?php include 'helper/navbar.html' ?>
	<!-- Config included -->
	<?php include 'helper/config.php' ?>

	<div class="row m-r-0 m-l-0">
		<!-- After submitting the form -->
		<?php
			if($_SERVER['REQUEST_METHOD'] == 'POST') {
				// get data from form
				$amount = $_POST['amount'];
				$total_balance = $_POST['total_balance'];
				$trans_limit = $_POST['trans_limit'];
				$acc_table_id = $_POST['id'];

				// Withdrawal business logic
				if($amount <= $total_balance) {
					if($amount <= $trans_limit) {
						$branch_pk = $_SESSION['branch_pk'];
						// update will be rest of the amount
						$rest_amount = $total_balance - $amount;
						$update_query = "UPDATE account SET balance=".$rest_amount." WHERE id=".$acc_table_id;
						$conn->query($update_query);
						// Now it's time to add a row on transaction table
						$add_trans_sql = "INSERT INTO transaction (account_id, branch_id, amount) VALUES (".$acc_table_id.", ".$branch_pk.", ".$amount.")";
						$conn->query($add_trans_sql);
						unset($_SESSION["branch_pk"]); 

						// show success message
						echo '<p class="success-message">Successfully Withdrawn!!</p>';

					}else {
						// show error message (when maximum limit)
						echo '<p class="error-message">Sorry!! Maximum limit reached. Try Again!!</p>';
					}
				}else {
					// show error message (When insufficient funds)
					echo '<p class="error-message">Not Enough Fund!!</p>';
				}
				
			}
		?>
	</div>

	<!-- This part will show first -->
	<div class="row m-r-0 m-l-0">
		<?php
			if(isset($_SESSION['account'])) {
				$account_pk = $_SESSION['account_id'];
				// Warning OR Notification about last blocking message
				$blocked_sql = "SELECT block_history.account_id, block_history.branch_id, created_at, branch.id, branch.name as branch_name FROM block_history, branch WHERE block_history.account_id=".$account_pk." AND block_history.branch_id=branch.id ORDER BY created_at DESC";
				
				$blocked_last_row = $conn->query($blocked_sql);

				if($blocked_last_row->num_rows > 0) {
					$blocked_row = $blocked_last_row->fetch_row();
					$blocked_timestamp = $blocked_row[2];
					$blocked_branch_name = $blocked_row[4];

					// Show Warning
					echo '<p class="warning-message">You account was tryng to access from <strong>'.$blocked_branch_name.'</strong> at <strong>'.$blocked_timestamp.'</strong></p>';
				}


				$ac_number = $_SESSION['account'];
				$account_id = $_SESSION['account_id'];

				// Get data from account table
				$sql = "SELECT * FROM account WHERE id=".$account_id;
				$result = $conn->query($sql);

				if($result->num_rows == 1) {
					$row = $result->fetch_row();
					echo '
						<div class="col-sm-12 col-md-6">
							<div class="panel panel-primary">
								<div class="panel-heading">
									<h3>Transaction</h3>
								</div>
								<div class="panel-body">
									<form method="POST" action="" class="form-group">
										<p>Available Balance: '.$row[4].'</p>
										<label>Enter Amount</label>
										<input type="number" name="amount" class="form-control" required/>
										<input type="hidden" name="total_balance" value="'.$row[4].'"/>
										<input type="hidden" name="trans_limit" value="'.$row[5].'"/>
										<input type="hidden" name="id" value="'.$row[0].'"/>
										<br/>
										<input class="btn btn-primary btn-block" type="submit" name="submit" value="Withdraw"/>
									</form>
								</div>
							</div>
						</div>
					';
				}
			}
		?>
		<!-- The clock / time limit will be here -->
		<div class="col-sm-12 col-md-4 jumbotron pull-right m-r-15">
			<h2 class="alert-message-color">You are running out of time.</h2>
			<div id="s_timer"></div>
			<p class="p-t-sm f-16 alert-message-color">
				<strong>Note:</strong> You just have 60 seconds to finish this transaction. 
				If you missed to finish you have re-enter your AC number and PIN. 
			</p>
		</div>
	</div>
	
</body>
<footer>
	<!-- All the Javascript will be load here... -->
	<script type="text/javascript" src="assets/JS/jquery-3.1.1.min.js"></script>
	<script type="text/javascript" src="assets/JS/jquery.countdownTimer.min.js"></script>
	<script type="text/javascript" src="assets/JS/main.js"></script>
	<script type="text/javascript" src="assets/JS/timer.js"></script>
	<script type="text/javascript" src="assets/BS/js/bootstrap.min.js"></script>
</footer>
</html>
