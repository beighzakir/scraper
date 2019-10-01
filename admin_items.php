<?php
include_once __DIR__. '/includes/init.php';




function items(){
	$qry = "Select `asin`,`sku`,`updatedPrice`,`suggestedPrice`,`Quantity`,`fulfillment`,`brand`,`currency`,`updatedAt` from productDescription";
	$queryResponse = query_db($qry);
	$dataArr = [];
	while($row=mysqli_fetch_assoc($queryResponse['returned'])){
 
	 // print_r($row);       	
		$dataArr[] = $row;
	}
	if(count($dataArr) > 0)
		{
			return $dataArr;
		}
}



?>

<!doctype html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>

	<title>sellsmartforcash</title>

	<meta charset="utf-8" />
	<meta name="description" content="" />
	<meta name="author" content="" />		
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	
		
	<link rel="stylesheet" href="stylesheets/all.css" type="text/css" />
	
	<!--[if gte IE 9]>
	<link rel="stylesheet" href="stylesheets/ie9.css" type="text/css" />
	<![endif]-->
	
	<!--[if gte IE 8]>
	<link rel="stylesheet" href="stylesheets/ie8.css" type="text/css" />
	<![endif]-->
	
</head>

<body>

<div id="wrapper">
	
	<div id="header">
		<h1><a href="#">sellsmartforcash</a></h1>		
		<a href="javascript:;" id="reveal-nav">
			<span class="reveal-bar"></span>
			<span class="reveal-bar"></span>
			<span class="reveal-bar"></span>
		</a>
	</div> <!-- #header -->
	
	<div id="search">

	</div> <!-- #search -->
	
	<div id="sidebar">		
		
		<ul id="mainNav">	
			<li id="navTables" class="nav active">
				<span class="icon-list"></span>
				<a href="admin_items.php">Amazon Product</a>	
			</li>
			
		</ul>
				
	</div> <!-- #sidebar -->
	
	<div id="content">		
		
		<div id="contentHeader">
			<h1>Amazon Product</h1>
		</div> <!-- #contentHeader -->	
		

		
		<div class="container">
				
				<div class="grid-24">	
					
				
					
					
					<div class="widget widget-table">
					
						<div class="widget-header">
							<span class="icon-list"></span>
							<h3 class="icon chart">Items</h3>		
						</div>
					
						<div class="widget-content">
							
							<table class="table table-bordered table-striped data-table">
						<thead>
							<tr>
								
								<th>ASIN</th>
								<th>Sku</th>
								<th>Updated Price</th>
								<th>Suggested Price</th>
								<th>Quantity</th>
								<th>Fulfillment</th>
								<th>Brand</th>
								<th>Currency</th>
								<th>UpdatedAt</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$tabledata = items();
							foreach ($tabledata as $key => $value) {
								?>
							
							<tr>
								<td><?= $value['asin']; ?></td>
								<td><?= $value['sku']; ?></td>
								<td><?= $value['updatedPrice']; ?></td>
								<td><?= $value['suggestedPrice']; ?></td>
								<td><?= $value['Quantity']; ?></td>
								<td><?= $value['fulfillment']; ?></td>
								<td><?= $value['brand']; ?></td>
								<td><?= $value['currency']; ?></td>
								<td><?= $value['updatedAt']; ?></td>
								<td> 
									<div class="field-group">		
									<div class="field">
										<select name="cardtype" id="cardtype">
											<option>Activate</option>
											<option>Deactivate</option>
										</select>
									</div>		
								</div> <!-- .field-group -->
								</td>
							</tr>
								<?php  } ?>							
						</tbody>
					</table>
						</div> <!-- .widget-content -->
					
				</div> <!-- .widget -->
				
			</div> <!-- .grid -->
			
		</div> <!-- .container -->
		
	</div> <!-- #content -->
	
	<div id="topNav">
		 <ul>
		 	<li>
		 		<a href="#menuProfile" class="menu">John Doe</a>
	 		</li>
		 	<li><a href="index-2.html">Logout</a></li>
		 </ul>
	</div> <!-- #topNav -->
	
	
	
	
</div> <!-- #wrapper -->

<div id="footer">
	Copyright &copy; 2012, MadeByAmp Themes.
</div>


<script src="javascripts/all.js"></script>

</body>
</html>