
/**
 * renders timestamp
 */
function renderDate(timestamp) {
	var now = Math.floor(Date.now() / 1000);
	var difference;
	// how many seconds difference?
	difference = (now - timestamp);
	if (difference < 60) {
		difference = Math.round(difference);
		if (difference == 1) {
			return difference + ' second ago';
		} else {
			return difference + ' seconds ago';
		}
	}
	// how many minutes difference?
	difference = (now - timestamp) / 60;
	if (difference < 60) {
		difference = Math.round(difference);
		if (difference == 1) {
			return difference + ' minute ago';
		} else {
			return difference + ' minutes ago';
		}
	}
	// how many hours difference?
	difference = (now - timestamp) / 3600;
	if (difference < 24) {
		difference = Math.round(difference);
		if (difference == 1) {
			return difference + ' hour ago';
		} else {
			return difference + ' hours ago';
		}
	}
	// how many days difference?
	difference = (now - timestamp) / (3600*24);
	if (difference < 30) {
		difference = Math.round(difference);
		if (difference == 1) {
			return difference + ' day ago';
		} else {
			return difference + ' days ago';
		}
	}
	// how many months difference?
	difference = (now - timestamp) / (3600*24*30);
	if (difference < 12) {
		difference = Math.round(difference);
		if (difference == 1) {
			return difference + ' month ago';
		} else {
			return difference + ' months ago';
		}
	}
	// how many years difference?
	difference = (now - timestamp) / (3600*24*30*12);
	if (difference >= 1) {
		difference = Math.round(difference);
		if (difference == 1) {
			return difference + ' year ago';
		} else {
			return difference + ' years ago';
		}
	}
}



/**
 * displays apiresult as toast using toastr
 */
function toastApiResult(apiResult) {
	if (apiResult.message.type == 1) { // success
		toastr.success(apiResult.message.text)
	}
	if (apiResult.message.type == 2) { // warning
		toastr.warning(apiResult.message.text)
	}
	if (apiResult.message.type == 3) { // error
		toastr.error(apiResult.message.text)
	}
}
function toastAjaxResult(result) {
	var apiResult = JSON.parse(result.responseText);
	toastApiResult(apiResult);
}
