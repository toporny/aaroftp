document.onkeydown = function(evt) {
	evt = evt || window.event;
	console.log(event.keyCode);
        
	if (event.keyCode == 69 && event.ctrlKey && event.shiftKey) {
	  console.log('!'+event.keyCode+'!');



		var selectedText = ""
		if (window.getSelection) {
			selectedText = window.getSelection().toString();
			if (selectedText.length == 0) {
				alert('SELECT TEXT TO PARSE FIRST!');
			} else {
				console.log('selectedText = ', selectedText)
				copyToClipboard(selectedText);
			}
		}
		else {
			//alert('select text to parse first');
		}

	}

};

function copyToClipboard(selectedText) {
    var textField = document.createElement('textarea');
    textField.innerText = selectedText;
    document.body.appendChild(textField);
    textField.select();
  document.execCommand('SelectAll');
  document.execCommand("Copy", false, null);

  //  var successful = document.execCommand('copy');  
    //var msg = successful ? 'successful' : 'unsuccessful';  
    //console.log('Copy email command was ' + msg);

	console.log('skopiowano = ', selectedText);
    //textField.remove();
}

