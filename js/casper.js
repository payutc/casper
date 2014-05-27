var historique;
var rows;
var rowCount = 0;
var pageSize = 15;
var pageIndex = 0;
var pages = 0;

function init(){
  historique = document.getElementById("historique");
  rows = historique.getElementsByTagName("tr");
  rowCount = rows.length;
  pages = Math.ceil(rowCount / pageSize);
  
  if(pages<=10)
  {
    for ( var i=1; i <= pages; i++){
            var paging = document.getElementById("paging");
            paging.innerHTML += "<li><a onclick='selectPage(" + i + ");'>" + i + "</a></li>";
    }
  } /* else {
    // Il faut mettre des boutons suivants précédents...
    // La fonction est placé dans selectPage car on regénére le menu à chaque selection de pages...
  }*/
}

function selectPage(pageIndex){
  var current = (pageSize * (pageIndex - 1));
  var next = (current + pageSize < rowCount) ? current + pageSize : rowCount;
  var paging = document.getElementById("paging");

  if(pages<=10)
  {
    var button = paging.getElementsByTagName("li");
    for(var i=0; i<pages; i++)
      button[i].className="";
    button[pageIndex-1].className="active";
  } else {
    posCurrent = 3;
    if(pageIndex<3)
      posCurrent = pageIndex;
    start = (pageIndex - 3 < 0) ? 0 : pageIndex - 3;
    if(start + 5 < pages) {
      end = start + 6;
    } else {
      start = pages - 5;
      end = pages + 1;
      posCurrent = 5 - (pages - pageIndex); 
    }
    if(start == 0)
      paging.innerHTML = "<li><a>" + "<<" + "</a></li>";
    else
      paging.innerHTML = "<li><a onclick='selectPage(" + start + ");'>" + "<<" + "</a></li>";

    for ( var i=start+1; i < end; i++){
      var paging = document.getElementById("paging");
      paging.innerHTML += "<li><a onclick='selectPage(" + i + ");'>" + i + "</a></li>";
    }

    if(end>pages)
      paging.innerHTML += "<li><a>" + ">>" + "</a></li>";
    else
      paging.innerHTML += "<li><a onclick='selectPage(" + end + ");'>" + ">>" + "</a></li>";

    var button = paging.getElementsByTagName("li");
    for(var i=0; i<6; i++)
      button[i].className="";
    button[posCurrent].className="active";
    if(start==0)
      button[0].className="disabled";
    if(end>pages)
      button[6].className="disabled";
  }

  for (var idx =0; idx < current; idx++){
          rows[idx].style.display ='none';
  }
  
  for (var idx = current; idx < next; idx++){
          rows[idx].style.display = 'table-row';
  }
  
  
  for (var idx = next; idx < rowCount; idx++){
          rows[idx].style.display ='none';
  }
}

Number.prototype.formatMoney = function(c, d, t){
var n = this, 
    c = isNaN(c = Math.abs(c)) ? 2 : c, 
    d = d == undefined ? "," : d, 
    t = t == undefined ? "" : t, 
    s = n < 0 ? "-" : "+", 
    i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", 
    j = (j = i.length) > 3 ? j % 3 : 0;
   return s + " " + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "") + " €";
 };

function updateCalcul(){
  if($("#montant").val() > 0){
    $("#submitBut").val("Recharger et payer");
  }
  else {
    $("#submitBut").val("Payer");
  }
  var finalAmount = parseFloat($("#final").val()) + parseFloat($("#montant").val());

  $("#finalAmount").html(finalAmount.formatMoney());
}

$(document).ready(function(){
  // --- Validation
  $("#reloadLine").popover();

  $("#montant").change(updateCalcul);

  $("#boutons2").hide();
  
  $("#noaccount").click(function(e){
    e.preventDefault();
    $("#gopay").attr("disabled", "disabled");
    $("#boutons1").hide();
    $("#boutons2").show();
  });
  
  $("#cgu").change(function(){
    if($(this).is(":checked")){
      $("#gopay").removeAttr("disabled");
    }
    else {
      $("#gopay").attr("disabled", "disabled");
    }
  });
  
  $("#reload").change(function(){
    if($(this).is(":checked")){
      $("#montant").removeAttr("disabled").val(10.00);
    }
    else {
      $("#montant").attr("disabled", "disabled").val(0);
    }
    updateCalcul();
  });
  
  // --- Virement
  $('#userName').typeahead({
      hint: true,
      highlight: true,
      minLength: 1
    },
    {
      name: 'userName',
      displayKey: 'value',
      source: function (query, process) {
        return $.get('ajax', 'q='+input, function (data) {
            return process(data.options);
        });
      }
      source: function(input, process){
        $('#userId').val("");
        return $.get('ajax', 'q='+input, function(data) {
            map = {};
            usernames = [];
      
            $.each(JSON.parse(data), function (i, user) {
                map[user.name] = user;
                usernames.push(user.name);
            });
      
            return process(usernames);
        });
      }
  });


  // $('#userName').typeahead({
  //     source: function(input, process){
  //         $('#userId').val("");
  //         $.get('ajax', 'q='+input, function(data) {
  //             map = {};
  //             usernames = [];
        
  //             $.each(JSON.parse(data), function (i, user) {
  //                 map[user.name] = user;
  //                 usernames.push(user.name);
  //             });
        
  //             process(usernames);
  //         });
  //     },
  //     matcher: function(item){
  //         return true;
  //     },
  //     updater: function(item){
  //         $('#userId').val(map[item].id);
  //         $('#userName').blur();
  //         return item;
  //     }
  // });

  $('#userName').click(function(){
      $('#userName').val("");
      $('#userId').val("");
  })
});