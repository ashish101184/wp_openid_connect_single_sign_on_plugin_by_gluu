jQuery(window).load(function () {
	var elemDiv = document.createElement("div");
	elemDiv.id = "oxd-openid-content-id";
	elemDiv.style.display = "none";
	var message = getAutoRegisterDisabledMessage();
	elemDiv.innerHTML = "<p>" + message + "</p>";
	var linktag = document.createElement("a");
	linktag.id = "oxd_openid_popup_click";
	linktag.className = "thickbox";
	linktag.href = "#TB_inline?width=500&height=100&inlineId=oxd-openid-content-id";
	document.body.appendChild(elemDiv);
	document.body.appendChild(linktag);
	jQuery("#oxd_openid_popup_click").trigger('click');
});