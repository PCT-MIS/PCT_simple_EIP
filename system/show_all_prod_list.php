<?php

require_once('../functions/MyPDO.php');
require_once '../functions/MyFunctions.php';
require_once '../system/MyConfig.php';
header('Content-Type:text/html;charset=utf8');
	

	set_time_limit(30);
	// $Model = $_POST["Model"];
	// $data_id = '';
	$limit = 50;
	$limit = (int)strip_tags($_GET["limit"]);
	$page = (int)strip_tags($_GET["page"]);
	if(!(isset($limit) && $limit >= 1 && is_int($limit))){
		$limit = 10;
	}
	if(!(isset($page)&&$page>=1&&is_int($page))){
		$page = 1;
	}
	
	$OFFSET = ($page - 1) * $limit;
	$pdo = new MyPDO;
// ini_set( 'display_errors', 1 );

$sql_pct_count = "SELECT
				[Model]
				,SK_USE
				,SK_LOCATE
				,fd_name
				,SK_NAME
				,[SK_NO1]
				,[SK_NO2]
				,[SK_NO3]
				,[SK_NO4]
				,[Price]
				,[Suggested Price]
				,[Cost Price]
				,SPH_NowQtyByWare
				FROM (
					SELECT *
					FROM [PCT].[dbo].[Data_Prod_Reference]
					WHERE Model != ''
				) as PCT
				LEFT JOIN XMLY5000.dbo.SSTOCK on PCT.SK_NO1 = XMLY5000.dbo.SSTOCK.SK_NO collate chinese_taiwan_stroke_ci_as
				LEFT JOIN XMLY5000.dbo.SSTOCKFD on PCT.SK_NO1 = XMLY5000.dbo.SSTOCKFD.fd_skno collate chinese_taiwan_stroke_ci_as
				LEFT JOIN (
					SELECT
					*
					FROM XMLY5000.DBO.View_SPHNowQtyByWare
					WHERE WD_WARE = 'A'
				)QTY  on PCT.SK_NO1 = QTY.WD_SKNO collate chinese_taiwan_stroke_ci_as
				WHERE Model != :Model
				ORDER BY Model";
		$query_all = $pdo->bindQuery($sql_pct_count, [
		':Model' => ''
	]);
	$row_count_all = count($query_all);
	$per_page_count = $row_count_all/$limit+1;
	if($OFFSET>$row_count_all){
		$page = floor($per_page_count);
		$OFFSET = ($page - 1) * $limit;
	}
	
	//基本資料 - 在[PCT].[dbo].[Data_Prod_Reference]從型號找對應料號
	$sql_pct = "SELECT
				[Model]
				,SK_USE
				,SK_LOCATE
				,fd_name
				,SK_NAME
				,[SK_NO1]
				,[SK_NO2]
				,[SK_NO3]
				,[SK_NO4]
				,[Price]
				,[Suggested Price]
				,[Cost Price]
				,SPH_NowQtyByWare
				FROM (
					SELECT *
					FROM [PCT].[dbo].[Data_Prod_Reference]
					WHERE Model != ''
					ORDER BY Model
					OFFSET ".$OFFSET." ROWS
					FETCH NEXT ".$limit." ROWS ONLY
				) as PCT
				LEFT JOIN XMLY5000.dbo.SSTOCK on PCT.SK_NO1 = XMLY5000.dbo.SSTOCK.SK_NO collate chinese_taiwan_stroke_ci_as
				LEFT JOIN XMLY5000.dbo.SSTOCKFD on PCT.SK_NO1 = XMLY5000.dbo.SSTOCKFD.fd_skno collate chinese_taiwan_stroke_ci_as
				LEFT JOIN (
					SELECT
					*
					FROM XMLY5000.DBO.View_SPHNowQtyByWare
					WHERE WD_WARE = 'A'
				)QTY  on PCT.SK_NO1 = QTY.WD_SKNO collate chinese_taiwan_stroke_ci_as
				WHERE Model != :Model
				ORDER BY Model";
	$query = $pdo->bindQuery($sql_pct, [
		':Model' => ''
	]);
	$row_count = count($query);
?>
<!doctype html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;">
    <title><?=$Company_name."簡易EIP"?></title>
    <link rel="stylesheet" href="../CSS/prod_list.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <!-- <script src="https://releases.jquery.com/git/ui/jquery-ui-git.js"></script> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.10/clipboard.min.js"></script>
    <script src="../JS/index.js"></script>
    <script src="../JS/MyFunction.js"></script>
    <script src="../JS/prod_list_page.js"></script>
  </head>
  <body>	
	
<div id="search_bar_L">
	   <input type="text" placeholder="請輸入產品型號" name="model">
	   <button type="" name="" value="">搜尋</button>
		每頁顯示
		<select id="display_per_page" name="display_per_page">
			<option value="10">10</option>
			<option value="20">20</option>
			<option value="50">50</option>
			<option value="100">100</option>
		 </select>
		 <?php
			if($row_count){
		?>		
			<div id="pagejump">	
		<?php	
				
				$i = 1;
				for($i=1;$i<=$per_page_count;$i++){
		?>
				<a href="javascript:load_page(<?=$i?>)">[<?=$i?>]</a>
		<?php	
				}
		?>		
			</div>
		<?php		
			}
		 ?>
		
 </div>
 <div id="page_load_status"></div>
  <hr>
	<div id="main_content_L">
		<div class="data_room_L">

			<div id="copy_statu_L">複製</div>
			<div class="data_room_con0_L"><!-- pro_con_L: 欄，dr: 列 -->
				<button type="" name="" value="">新增一筆資料</button>
				<button type="" name="" value="">更新所選資料</button>
				<button type="" name="" value="">刪除所選資料</button>
			</div>
			<div class="data_room_con1_L"><!-- pro_con_L: 欄，dr: 列 -->
				<div class="pro_con_L0 dr0_L" >編號</div>
				<div class="pro_con_L1 dr0_L" ><input type="checkbox" name="select"></div>
				<div class="pro_con_L2 dr0_L" >圖片</div>
				<div class="pro_con_L3 dr0_L" >型號</div>
				<div class="pro_con_L4 dr0_L" >分類</div>
				<div class="pro_con_L5 dr0_L" >品名</div>
				<div class="pro_con_L7 dr0_L" >料號</div>
				<div class="pro_con_L8 dr0_L" >官網頁面</div>
				<div class="pro_con_L9 dr0_L" >售價與成本</div>
				<div class="pro_con_L10 dr0_L" >庫存</div>
				<div class="pro_con_L11 dr0_L" >銷售頁面範本</div>
			</div>
			

	
<?php	
	if($row_count){
		$i = 1;
		$i = ($page-1) * $limit + $i;
		foreach($query as $row){
			$Model = $row['Model'];
			$Category = $row['SK_USE']?$row['SK_USE'].">".$row['SK_LOCATE']:"";
			$prod_sales_name = $row['fd_name'];
			$SK_NAME = $row['SK_NAME'];			
			$SK_NO1 = $row['SK_NO1'];
			$SK_NO2 = $row['SK_NO2'];
			$SK_NO3 = $row['SK_NO3'];
			$SK_NO4 = $row['SK_NO4'];
			$Price = round($row['Price']);
			$Suggested_Price = round($row['Suggested Price']);
			$Cost_Price = round($row['Cost Price']);
			$QTY = round($row['SPH_NowQtyByWare']);
?>			
			<div class="data_room_con2_L">
				<div class="pro_con_L0 pn_L">
					 <div class="sk_data_L0 dr_L" >編號</div>
					 <div class="sk_data_L0 dr1_L" ><?=$i?></div>
				</div>
				<div class="pro_con_L1 pn_L">
					<div class="sk_data_L1 dr_L" >選擇</div>
					<div class="sk_data_L1 dr1_L" id="sk_no<?=$i?>" ><input type="checkbox" name="select"></div>
				</div>
				<div class="pro_con_L2 pn_L">
					<div class="sk_data_L2 dr_L" >圖片</div>
					<div class="sk_data_L2 dr1_L" ><?=$img_result?></div>
				</div>
				<div class="pro_con_L3 pn_L">
					<div class="sk_data_L3 dr_L" >型號</div>
					<div class="sk_data_L3 dr1_L" ><?=$Model?></div>
				</div>
				<div class="pro_con_L4 pn_L">
					<div class="sk_data_L4 dr_L" >分類</div>
					<div class="sk_data_L4 dr1_L" ><?=$Category?></div>
				</div>
				<div class="pro_con_L5 pn_L">
					<div class="sk_data_L5 dr_L" >品名</div>
					<div class="sk_data_L5 dr1_L" >
						<div><?="銷售: ".$prod_sales_name?></div><hr>
						<div><?="廠內: ".$SK_NAME?></div>
					</div>
				</div>
				<div class="pro_con_L7 pn_L">
					<div class="sk_data_L7 dr_L" >料號</div>
					<div class="sk_data_L7 dr1_L" ><?=$SK_NO1?></div>
				</div>
				<div class="pro_con_L8 pn_L">
					<div class="sk_data_L8 dr_L" >官網頁面</div>
					<div class="sk_data_L8 dr1_L" >
					
					</div>
				</div>
				<div class="pro_con_L9 pn_L">
					<div class="sk_data_L9 dr_L" >售價與成本</div>
					<div class="sk_data_L9 dr1_L" >
						<div>售價: <div class="price"><?=$Price?></div></div>
						<div>建議售價: <div class="price"><?=$Suggested_Price?></div></div>
						<div>成本: <div class="price"><?=$Cost_Price?></div></div>
					</div>
				</div>
				<div class="pro_con_L10 pn_L">
					<div class="sk_data_L10 dr_L" >庫存</div>
					<div class="sk_data_L10 dr1_L" ><?=$QTY?></div>
				</div>
				<div class="pro_con_L11 pn_L">
					<div class="sk_data_L11 dr_L" >銷售頁面範本</div>
					<div class="sk_data_L11 dr1_L" >
					
					</div>
				</div>
			</div>
			
<?php			
			$i++;
		}
		$i = 0;
		$query_all = null;
		$query = null;

	}
	
?>
			
			
				

	
		</div>
	</div>
	
	
	
  </body>
</html>