try {
	window.$ = window.jQuery = require('jquery');
	window.Popper = require('popper.js/dist/umd/popper.js');
	require('bootstrap');
} catch (e) {}


require('./countdown_admin');

