$(document).ready(function(){
  var slider = new Slider('#opintopisteet', {
	formatter: function(value) {
		return 'Current value: ' + value;
	}
});
});
