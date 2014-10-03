// Helper. Sólo usarlo una vez. Esto resuelve varios problemas con objetos en js.
if (typeof Object.create !== 'function') {
	Object.create = function(obj) {
		function F() {
		}
		F.prototype = obj;
		return new F();
	};
}
// Prevent [ENTER] form submit.
$('input').not(':submit').on('keydown', function(e) {
	if (e.keyCode === 13) {
		e.preventDefault();
		return false;
	}
});