function check_boolean(param){
	if(param){
		return "success";
	}else{
		return "failed";
	}
}
$(function(){
	var url = window.location.protocol+window.location.host+":"+window.location.port+"/";
	$("#btnLogin").on("click",function(){
		var form = $("#formLogin").serialize();
		$.post(url+"login",form,function(data,success){
			alert(data);
		});
	});
	$("#btnSimpanSub").on("click",function(){
		var form = $("#formKategori").serialize();
		$.post("simpan_sub",form,function(data,success){
			alert(check_boolean(data));
		});
	});
});