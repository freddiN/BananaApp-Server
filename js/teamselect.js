
//selection = AllUsers, listOfAllSelects = select_AllUsers|select_Camino|select_Minion|select_Throne Together|select_YAGNI
function showSelect(selection,listOfAllSelects) {
  //console.log("showSelect =%s=%s=", selection, listOfAllSelects);

  var arrayOfSelects = listOfAllSelects.split("|");
  
  for (i = 0; i < arrayOfSelects.length; i++) {
	  var selectTmp = document.getElementById(arrayOfSelects[i]);
	 
	  if(selection == arrayOfSelects[i]) {
		selectTmp.style.display = "inline";
	  } else {
		selectTmp.style.display = "none"; 
	  }
  }
}
