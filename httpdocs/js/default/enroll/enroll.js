//support for member enrollment page

/**
 * Enroll user into a service
 */
function enroll(op,srvc) {
	$.ajax({
		async:false,
		url:'/enroll/act',
		data:{format:'json',op:op,srvcId:srvc},
		dataType:'json',
		type:'POST',
		success:function(response) {
			if (response.success) {
				window.location.reload();
			} else {
				alert(response.msg);
			}
		}
	});
}