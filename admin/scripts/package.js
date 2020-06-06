(function() {
var scriptSrc = document.currentScript.src;
var packagePath = scriptSrc.replace('/scripts/package.js', '').trim();
var re = /([a-f0-9]{8}(?:-[a-f0-9]{4}){3}-[a-f0-9]{12})/i;
var packageId = re.exec(scriptSrc.toLowerCase())[1];
document.addEventListener('DOMContentLoaded', function () {
const HOST = window.location.host;
var customFieldPrefix = packageId.replace(/-/g, "");
var userId = $('#userGuid').val();
var accessToken = 'Bearer ' + getCookie('webapitoken');
var rrpStatusExist = false;
var rrpStatusFieldId = 0;
var code = "";
var rrpStatusFieldCode = "";
var rrpPackageCheckBox = document.getElementById('myonoffswitch');
var itemerror;

var errors;
//switch
rrpPackageCheckBox.addEventListener('change', () => {
  saveStatus(rrpPackageCheckBox.checked);
});
  

function saveCSV(data)
 {
  var data = { 'data': data};
  var apiUrl = packagePath + '/upload.php';
  console.log('data ' + JSON.stringify(data))
 
 $.ajax({
     url: apiUrl,          
     method: 'POST',
     contentType: 'application/json',
     data: JSON.stringify(data),
     success: function(response) {

      errors = response;
      console.log(JSON.stringify(response));
      console.log(errors);
      // var error = errors.result[0].Name;
  
       console.log('success');
       $('#totalupload').text(JSON.parse(response).result[0].Total);

       $.each(JSON.parse(response).result, function(index, item) {
        var itemerror = [];
        $.each(item.Error, function(index,err){
          itemerror.push(err);
        });
        var tr_str;
         tr_str = "<tr>" +
         "<td align='center'>" + item.Name + "</td>" +
         "<td align='center'>" + itemerror.join(", ") + "</td>" +
         "</tr>";
         $("#userTable tbody").append(tr_str);
        
     });
    
        // $.each(itemdetails, function (i, item) {
        //     trHTML += '<tr><td>' + item.rank + '</td><td>' + item.content + '</td><td>' + item.UID + '</td></tr>';
        // });
        // $('#results').append(trHTML);
     
     },
     error: function (jqXHR, status, err) {
           toastr.error('---');
     },
    //  complete: function(response) {
  
    //  }

 });
 }








  function getMarketplaceCustomFields(callback){
    var apiUrl = '/api/v2/marketplaces'
    $.ajax({
        url: apiUrl,
        method: 'GET',
        contentType: 'application/json',
        success: function(result) {
            if (result) {
                callback(result.CustomFields);
            }
        }
    });
  }

function saveStatus(rrpStatus) {
  var data = { 'userId': userId, 'status': rrpStatus };
   var apiUrl = packagePath + '/package_switch.php';
  $.ajax({
      url: apiUrl,          
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify(data),
      success: function(response) {
         if(rrpStatus == 1){
         toastr.success('Plugin is enabled.');
         }else { 
           toastr.success('Plugin is disabled.');
        }
        
      },
      error: function (jqXHR, status, err) {
            toastr.error('---');
      }
  });
}


function deleteitems(){

  var apiUrl = packagePath + '/delete_items.php';
  $.ajax({
    url: apiUrl,          
    method: 'POST',
    contentType: 'application/json',
  //  data: JSON.stringify(data),
    success: function(response) {
     console.log('success delete');
      
    },
    error: function (jqXHR, status, err) {
          toastr.error('---');
    }
});
}

$(document).ready(function() {
  getMarketplaceCustomFields(function(result) {
      $.each(result, function(index, cf) {
          if (cf.Name == 'Delete Cart' && cf.Code.startsWith(customFieldPrefix)) {
               code = cf.Code;
              var rrp_status = cf.Values[0];
              if (rrp_status == 'true') {
                rrpPackageCheckBox.checked = true;
              } else {
                rrpPackageCheckBox.checked = false;
              }    
          }
          
      })
  });


  var fileInput = document.getElementById("csv"),
  readFile = function () {
      var reader = new FileReader();
      reader.onload = function () {
          // document.getElementById('out').innerHTML = reader.result;
          // var lines = $('#out').text().split('\n');
          var lines = reader.result.split('\n');

          function getCsvValuesFromLine(line) {
          var values = lines[0].split(',');
          value = values.map(function(value){
              return value.replace(/\"/g, '');
          });
          return values;
      }
      
    var headers = getCsvValuesFromLine(lines);
    // lines.shift(); // remove header line from array
    saveCSV(lines);
    // console.table(lines);

    lines.forEach(function (item, index) {
      JSON.stringify(item[0]);
      // console.log(item);
     
    });

      };
      // start reading the file. When it is done, calls the onload event defined above.
      reader.readAsBinaryString(fileInput.files[0]);
  };

fileInput.addEventListener('change', readFile);

$('#kill').click(function() {
  deleteitems();

});
















});

function getCookie (name) {
    var value = '; ' + document.cookie;
    var parts = value.split('; ' + name + '=');
    if (parts.length === 2) return parts.pop().split(';').shift();
  }
 


  
});
})();
