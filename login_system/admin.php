
<?php include('views/header.php'); ?>
<body>

<div class="container">



<?php include ("views/nav.php"); ?>

	<div class="jumbotron">
		<h1 class="text-center"><?php 
		if(logged_in()){  

				echo "Welcome To FitnessFable";
				
		} else{

			redirect("index.php");
		}
		?></h1>
	</div>

<?php include ("views/footer.php"); ?>
