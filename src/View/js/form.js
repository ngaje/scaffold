/*
8 = backspace
9 = tab
46 = delete
35-40 = home/end/arrows
48-57 = numbers
65-90 = a-Z
96-105 = numberpad numbers
110 = numberpad decimal
190 = full stop
109, 173 or 189 = hyphen
222 = single quote
*/
function keyDownAlphaOnly(event) {
  var key = event.keyCode || event.which;
  return ((key >= 65 && key <= 90) || (key >=35 && key <=40) || key == 8  || key == 9 || key == 189 || key == 173 || key == 109 || key == 222 || key == 46);
};

function keyDownNumericOnly(event) {
  var key = event.keyCode || event.which;
  return ((key >= 48 && key <= 57) || (key >= 96 && key <= 105) || (key >=35 && key <=40) || key == 8  || key == 9 || key == 46);
};