$(document).ready(function(){
	
	// 初始化控制項目
	init_ctl_ele();
	
	
	


	
	
	
	
	// 路徑檢查
	root_path = window.location.pathname;
	root_path = root_path.indexOf("php");
	if(root_path < 0){
		root_path = window.location.pathname + "/system/";
	}else{
		root_path = "";
	}

	// 變更每頁顯示數量
	$(document).on('change', '#display_per_page', function(event){
		if(event.isDefaultPrevented()) return; // 防止重複關聯事件
		event.preventDefault(); // 防止重複關聯事件
		setTimeout(function(){
				$('#pagejump').html("取得分頁中...");
		}, 20);
		let searchParams = new URLSearchParams(window.location.search);
		let page = searchParams.get('page');
		if(!page){
			page = 1;
		}
		if (typeof(search_text) === 'undefined') {
			search_text = "";
		}
		load_page(page, search_text);
	});
	
	
	// 防止重複關聯 $(window).on('popstate')
	if (typeof(url) === 'undefined') {
		url = "";
		// 瀏覽器按下上一頁/下一頁按鈕事件
		$(window).on('popstate', function(event) {
			url = event.originalEvent.state.url;
			if (typeof(url) === 'undefined') {
				url = "";
			}
			var searchParams = new URLSearchParams(window.location.search);
			var page = searchParams.get('page');
			
			if(url == "show_all_prod_list.php" && $('#search_bar_L').text()!="回上一頁"){
				
				if(!page){
					page = 1;
				}
				if (typeof(search_text) === 'undefined') {
					search_text = "";
				}
				load_page(page, search_text);
			}
			if(url == "show_all_prod_list.php" && $('#search_bar_L').text()=="回上一頁"){
				limit = searchParams.get('limit');
				return_previous_page(limit);
			}
			
		});
	}

});

// 分頁載入
function load_page(page, search_text, limit){
	
	if (typeof(limit) === 'undefined') {
		limit = $('#display_per_page').val();
	}
	
	$('#page_load_status').css("display","flex");
	
	let pagejump;
	$.get(root_path + 'show_all_prod_list.php?page=' + page + '&limit=' + limit + '&data=' + search_text, function (pagedata) {
		pagejump = $(pagedata).find('#pagejump');
	});
	
	$('#main_content_L').load(root_path + 'show_all_prod_list.php?page=' + page 
								+ '&limit=' + limit + '&data=' + search_text 
								+ ' .data_room_L', function(response, status, xhr) {
		if(status!="error"){
			$('#pagejump').html(pagejump);
			window.history.pushState({url: 'show_all_prod_list.php' }, "簡易EIP - 第" + page + "頁", "?page=" + page + '&limit=' + limit);
			setTimeout(function(){
				//$('#page_load_status').css("display","none");
				$("input[name='Data_Prod_Ref_ID[]']").change(function() {
					if($("input[name='Data_Prod_Ref_ID[]']").is(":checked")){
						$("#delete_btn").prop('disabled', false);
					}else{
						$("#delete_btn").prop('disabled', true);
					}     
				});
			}, 10);
		}else{
			$('#page_load_status').html("載入失敗!");
		}
		$("#search_btn").prop('disabled', false);
	});
	
	
}

// 載入產品規格編輯頁面與處理
function prod_data_edit(Model){
	
	// 沒登入的話要再做一次路徑檢查??
	root_path = window.location.pathname;
	root_path = root_path.indexOf("php");
	if(root_path < 0){
		root_path = window.location.pathname + "/system/";
	}else{
		root_path = "";
	}
	
	// 儲存先前頁面捲動位置
	last_scroll = $(window).scrollTop();
	
	// 儲存先前頁面到變數以便快速回復
	window.search_bar_L_Page = $('#search_bar_L').html();
	window.main_content_L_Page = $('#main_content_L').html();
	
	var searchParams = new URLSearchParams(window.location.search);
	var page = searchParams.get('page');
	
	limit = $('#display_per_page').val();
	window.history.pushState({url: 'input_update.php' }, "產品規格編輯", "?page=" + page + '&limit=' + limit + "&Model=" + Model);		
	
	$('#search_bar_L').html("<a href=\"javascript:return_previous_page("+ limit + "," + last_scroll +");\">回上一頁</a>");
	if(typeof(ajax_post) === "function"){
		// alert(root_path);
		ajax_post(root_path + 'input_update.php?Model=' + Model, Model, '#main_content_L');
	}
}

// 返回產品清單
function return_previous_page(limit, last_scroll){
	var searchParams = new URLSearchParams(window.location.search);
	var page = searchParams.get('page');
	window.history.pushState({url: 'show_all_prod_list.php' }, "簡易EIP - 第" + page + "頁", "?page=" + page + '&limit=' + limit);
	
	// 重新載入先前頁面
	$('#search_bar_L').html(search_bar_L_Page);
	$('#main_content_L').html(main_content_L_Page);
	
	// 重新載入分頁顯示項目數量設定
	$('#display_per_page').val(limit).change();
	
	// 重回先前頁面捲動位置
	$(document).scrollTop(last_scroll);
	
	// 重新初始化控制項目
	init_ctl_ele();
}

function btn_create_prod(){
	var searchParams = new URLSearchParams(window.location.search);
	limit = searchParams.get('limit');
	
	// 儲存先前頁面捲動位置
	last_scroll = $(window).scrollTop();
	
	// 儲存先前頁面到變數以便快速回復
	window.search_bar_L_Page = $('#search_bar_L').html();
	window.main_content_L_Page = $('#main_content_L').html();
	
	$('#main_content_L').html("載入中...");
	$('#search_bar_L').html("<a href=\"javascript:return_previous_page("+ limit + "," + last_scroll +");\">回上一頁</a>");
	$('#main_content_L').load(root_path + 'input_update.php?action=create', function(response, status, xhr) {
		if(status!="error"){
			
			setTimeout(function(){
				
			}, 10);
		}else{
			$('#main_content_L').html("載入失敗!");
		}
	});
}

// 刪除選擇項目
function btn_delete_prod(){

	var Data_Prod_Ref_ID = new Array();
	var Model_array = new Array();
	$("input[name='Data_Prod_Ref_ID[]']:checked").each(function() {
		Data_Prod_Ref_ID.push($(this).val());
		Model_array.push($("#"+$(this).val()).text() + "\n");
	});
	if(confirm("確定要刪除以下項目?\n" + Model_array)){
		
		$.post(root_path + "../system/prod_delete.php", {
			ID: Data_Prod_Ref_ID
			, Model: Model_array
		}).done(function(result){
			alert(result);
		}).fail(function() {
			alert("後端連線失敗");
		});
		
		// 取消所有選取
		$("input[name='Data_Prod_Ref_ID[]']").prop('checked', false);
		
		// 停用刪除按鈕
		$("#delete_btn").prop('disabled', true);
		
		$("#search_btn").prop('disabled', true);
		search_text = $("input[name=model]").val();
		let searchParams = new URLSearchParams(window.location.search);
		let page = searchParams.get('page');
		if(!page){
			page = 1;
		}
		load_page(page, search_text);
    }
	
}

// 初始化控制項目
function init_ctl_ele(){
	var searchParams = new URLSearchParams(window.location.search);
	limit = searchParams.get('limit');
	if(limit){
		$('#display_per_page').val(limit).change();
	}

	// 鍵盤Enter鍵事件
	$(function() {
		$("div input[name=model]").keypress(function (e) {
			if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
				$("#search_btn").click();
				return false;
			} else {
				return true;
			}
		});
	});
	
	// 搜尋按鈕按下時的功能
	$("#search_btn").click(function(event){
		if(event.isDefaultPrevented()) return; // 防止重複關聯事件
		event.preventDefault(); // 防止重複關聯事件
		$("input[name=model]").blur(); 
		$("#search_btn").prop('disabled', true);
		
		setTimeout(function(){
				$('#pagejump').html("取得分頁中...");
		}, 20);
		search_text = $("input[name=model]").val();

		let searchParams = new URLSearchParams(window.location.search);
		let page = searchParams.get('page');
		if(!page || typeof(search_text) !== 'undefined'){
			page = 1;
		}
		load_page(page, search_text);
	});
	
	if($("input[name='Data_Prod_Ref_ID[]']").is(":checked")){
		$("#delete_btn").prop('disabled', false);
	}else{
		$("#delete_btn").prop('disabled', true);
	}
	
    $("input[name='Data_Prod_Ref_ID[]']").change(function() {
        if($("input[name='Data_Prod_Ref_ID[]']").is(":checked")){
			$("#delete_btn").prop('disabled', false);
        }else{
			$("#delete_btn").prop('disabled', true);
		}     
    });
}