/*jQuery time*/
$(function(){
	$("#accordian h3").click(function(){
		//slide up all the link lists
		$("#accordian ul ul").slideUp();
		//slide down the link list below the h3 clicked - only if its closed
		if(!$(this).next().is(":visible"))
		{
			$(this).next().slideDown();
		}
	});
});

function showVerticalHistory(id)
{
	$.ajax(
	{
		type	: "GET",
		url     : "getHistoryData.php",
		async	: false,
		data	: "id="+id,
		success: function(response){
			response = response.trim();
			if(response !='')
			{
				$('#changes-done-div').html(response);
				$('#changes-done-cover').show();
				$('#changes-done-div').show();
				$('.scroll_top_cls').animate({scrollTop:'0'},500);
			}
		}
	});
}
function close_changes_done_div()
{
	$('#changes-done-div').hide();
	$('#changes-done-cover').hide();
	
}
