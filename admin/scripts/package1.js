const scriptSrc = document.currentScript.src;
const packagePath = scriptSrc.replace('/scripts/package1.js', '').trim();
const apiUrl = packagePath + '/upload.php';

new Vue({
    el: "#app",
    data() {
      return {
        channel_name: '',
        channel_fields: [],
        channel_entries: [],
        parse_header: [],
        parse_csv: [],
        sortOrders:{},
        sortKey: '',
        count: '',
        csvcontent:'',
        result:[]
      };
    },
    filters: {
      capitalize: function (str) {
        return str.charAt(0).toUpperCase() + str.slice(1)
      }
    },
    methods: {
      sortBy: function (key) {
        var vm = this
        vm.sortKey = key
        vm.sortOrders[key] = vm.sortOrders[key] * -1
      },
      csvJSON(csv){
        var vm = this
        var lines = csv.split("\n")
        // csvcontent =  lines.shift();
        vm.count = lines.length - 1
        var result = []
        var headers = lines[0].split(",")
        vm.parse_header = lines[0].split(",") 
        vm.csvcontent = lines
        lines[0].split(",").forEach(function (key) {
          vm.sortOrders[key] = 1
        })
        
        lines.map(function(line, indexLine){
          if (indexLine < 1) return // Jump header line
          
          var obj = {}
          var currentline = line.split(",")
          
          headers.map(function(header, indexHeader){
            obj[header] = currentline[indexHeader]
          })
          
          result.push(obj)
        })
        
        result.pop() // remove the last item because undefined values
        
        return result // JavaScript object
      },
      loadCSV(e) {
        var vm = this
        if (window.FileReader) {
          var reader = new FileReader();
          reader.readAsText(e.target.files[0]);
          // Handle errors load
          reader.onload = function(event) {
            
            var csv = event.target.result;
            vm.parse_csv = vm.csvJSON(csv)
           
          };
          reader.onerror = function(evt) {
            if(evt.target.error.name == "NotReadableError") {
              alert("Canno't read file !");
            }
          };
        } else {
          alert('FileReader are not supported in this browser.');
        }
      },
      onUpload: function() {
        var vm = this
        var data = { 'data': vm.csvcontent}
      
        axios({
          method: 'post',
          url: apiUrl,
          data: JSON.stringify(data)

         // config: { headers: {'Content-Type': 'multipart/form-data' }}
      })
      .then(function (response) {
          //handle success
          this.result =  response.data
          
          console.log(response)
          $('.data-loader').removeClass('active');
          
      })
      .catch(function (response) {
          //handle error
          console.log(response)
      });
      }
    }
  });  