/**
basiert auf https://www.w3schools.com/howto/tryit.asp?filename=tryhow_js_filter_table
*/

function filterListUsers() {
	  var input, filter, table, tr, td, i;
	  input = document.getElementById("search_input");
	  filter = input.value.toUpperCase();
	  table = document.getElementById("search_table");
	  tr = table.getElementsByTagName("tr");
	  for (i = 1; i < tr.length; i++) {
		td = tr[i].getElementsByTagName("td")[0];
		if (td) {
		  if (td.innerHTML.toUpperCase().indexOf(filter) > -1) {
			tr[i].style.display = "";
		  } else {
			tr[i].style.display = "none";
		  }
		}       
	  }
	}
	
function filterListTransactions() {
  var input, filter, table, tr, td, i;
  input = document.getElementById("search_input");
  filter = input.value.toUpperCase();
  table = document.getElementById("search_table");
  tr = table.getElementsByTagName("tr");
  for (i = 1; i < tr.length; i++) {
	td = tr[i].getElementsByTagName("td");

	if (td[2].innerHTML.toUpperCase().indexOf(filter) > -1 || td[3].innerHTML.toUpperCase().indexOf(filter) > -1) {
	  tr[i].style.display = "";
	} else {
	  tr[i].style.display = "none";
	}
  }
}

function filterListTeams() {
  var input, filter, table, tr, td, i;
  input = document.getElementById("search_input_teams");
  filter = input.value.toUpperCase();
  table = document.getElementById("search_table");
  tr = table.getElementsByTagName("tr");
  for (i = 1; i < tr.length; i++) {
	td = tr[i].getElementsByTagName("td");

	if (td[6].innerHTML.toUpperCase().indexOf(filter) > -1) {
	  tr[i].style.display = "";
	} else {
	  tr[i].style.display = "none";
	}
  }
}