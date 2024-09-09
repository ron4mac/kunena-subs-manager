(function(Kscribe) {

	Kscribe.action = (evt, whch) => {
		evt.preventDefault();
		evt.stopPropagation();
		let gsb = document.getElementById('filter_usergroup');
		if (gsb.value || confirm(Joomla.Text._('COM_KSCRIBE_NO_GRPSEL'))) {
			Joomla.submitbutton(whch, document.adminform);
		}
		return true;
	};

	Kscribe.setAuto = (catid, val, elm) => {
		let ugid = document.getElementById('filter_usergroup').value;
		if (ugid) {
			const formData = new FormData();
			let json = true;
			formData.set('task', 'Json.setAuto');
			formData.set('ugid', ugid);
			formData.set('catid', catid);
			formData.set('val', val);
			formData.set(Joomla.getOptions('csrf.token'), 1);
			fetch('index.php?option=com_kscribe&format=json', {method:'POST',body:formData})
			.then(resp => { //console.log(resp.headers.get('content-type')); if (!resp.ok) throw new Error(`HTTP ${resp.status}`); if (json) return resp.json(); else return resp.text() 
				if (resp.headers.get('content-type').indexOf('json') < 0) {
					throw new Error('Request rejected');
				} else {
					return resp.json();
				}
			})
			.then(data => {
				if (data.error) {
					alert(data.message);
				} else {
					elm.parentElement.innerHTML = data.html;
				}
			})
			.catch(err => alert('Failure: '+err));
		} else {
			alert(Joomla.Text._('COM_KSCRIBE_SELECT_GROUP'));
		}
	};

})(window.Kscribe = window.Kscribe || {});
