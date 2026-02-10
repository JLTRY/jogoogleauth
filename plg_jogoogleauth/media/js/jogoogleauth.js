(function($){
	$(document).ready(function() {
		var ret = $("input[name='return']").val();
		if (typeof redirecturi !== 'undefined' && redirecturi !== '') {
			ret = btoa(redirecturi);
		}
		
		// Use event delegation and prevent multiple bindings
		$(document).on('click', '.plg_google_login_button', function(e) {
			e.preventDefault();
			var loginUrl = (typeof base !== 'undefined' ? base : '') + 
				'index.php?option=com_jogoogleauth&task=user.login&return=' + ret;
			window.location = loginUrl;
			return false; // Extra safety
		});
	});
}(jQuery));