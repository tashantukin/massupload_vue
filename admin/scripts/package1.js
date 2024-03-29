const scriptSrc = document.currentScript.src;
const packagePath = scriptSrc.replace("/scripts/package1.js", "").trim();
const apiUrl = packagePath + "/upload.php";
const apiUrl_createrandom = packagePath + "/create_random.php";

$("body").on("click", "#createrandom", function () {
  createrandom();
});

function createCSV() {
  // var data = { startdate: start, enddate: end };
  var apiUrl = packagePath + "/download.php";
  $.ajax({
    url: apiUrl,
    method: "POST",
    contentType: "application/json",
    // data: JSON.stringify(data),
    success: function (result) {
      console.log(JSON.stringify(result));
      console.log("csv created");
    },
    error: function (jqXHR, status, err) {
      toastr.error("Error!");
    },
  });
}

$(document).ready(function () {
  createCSV();
  $("#formatlink").attr({
    href: packagePath + "/downloads?file=example.csv&contentType=text/csv",
    target: "_blank",
  });

  $("#formatlink").on("click", function () {
    $(this).attr("download", "_Items.csv");
  });
});

new Vue({
  el: "#app",
  data() {
    return {
      channel_name: "",
      channel_fields: [],
      channel_entries: [],
      parse_header: [],
      parse_csv: [],
      sortOrders: {},
      sortKey: "",
      count: "",
      csvcontent: "",
      results: "",
    };
  },
  filters: {
    capitalize: function (str) {
      return str.charAt(0).toUpperCase() + str.slice(1);
    },
  },
  methods: {
    sortBy: function (key) {
      var vm = this;
      vm.sortKey = key;
      vm.sortOrders[key] = vm.sortOrders[key] * -1;
    },
    csvJSON(csv) {
      var vm = this;

      var lines = csv.split("\n");
      // lines.unshift(counter);
      // csvcontent =  lines.shift();
      vm.count = lines.length - 1;
      vm.count = vm.count - 1;
      var result = [];
      var headers = lines[0].split(",");
      vm.parse_header = lines[0].split(",");

      vm.csvcontent = lines;
      lines[0].split(",").forEach(function (key) {
        // counter++;
        vm.sortOrders[key] = 1;
      });

      lines.map(function (line, indexLine) {
        if (indexLine < 1) return; // Jump header line

        var obj = {};
        var currentline = line.split(",");

        headers.map(function (header, indexHeader) {
          obj[header] = currentline[indexHeader];
        });
        result.push(obj);
      });

      // result.pop() // remove the last item because undefined values

      return result; // JavaScript object
    },
    loadCSV(e) {
      var vm = this;
      if (window.FileReader) {
        var reader = new FileReader();
        reader.readAsText(e.target.files[0]);
        // Handle errors load
        reader.onload = function (event) {
          var csv = event.target.result;
          vm.parse_csv = vm.csvJSON(csv);
        };
        reader.onerror = function (evt) {
          if (evt.target.error.name == "NotReadableError") {
            alert("Canno't read file !");
          }
        };
      } else {
        alert("FileReader are not supported in this browser.");
      }
    },
    onU: function () {
      var vm = this;
      var data = { data: vm.csvcontent };

      axios({
        method: "post",
        url: apiUrl,
        data: JSON.stringify(data),

        // config: { headers: {'Content-Type': 'multipart/form-data' }}
      })
        .then((response) => {
          vm.results = JSON.parse(response.data).result;
          // vm.results = JSON.stringify(response);
          console.log(vm.results);
          $(".data-loader").removeClass("active");

          this.$nextTick(() => {
            $(".table").find("tbody tr:last").hide();
            // Scroll Down
          });
        })
        .catch(function (response) {
          //handle error
          console.log(response);
        });
    },
  },
  watch: {
    messages: function (val, oldVal) {
      $(".table").find("tbody tr:last").hide();
      //Scroll to bottom
    },
  },
});
