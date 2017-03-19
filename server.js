var http = require('http');
var fs = require('fs');
var url = require('url');


// create server
http.createServer( function (request, response) {  
   // parse request include path name
   var pathname = url.parse(request.url).pathname;
   
   // output file name
   console.log("Request for " + pathname + " received.");
   
   // read file content from file system
   fs.readFile(pathname.substr(1), function (err, data) {
      if (err) {
         console.log(err);
         // HTTP status: 404 : NOT FOUND
         // Content Type: text/plain
         response.writeHead(404, {'Content-Type': 'text/html'});
      }else{	         
         // HTTP status: 200 : OK
         // Content Type: text/plain
         response.writeHead(200, {'Content-Type': 'text/html'});	
         
         // response file content
         response.write(data.toString());		
      }
      // send response file
      response.end();
   });   
}).listen(1442);

// console information
console.log('Server running at http://0.0.0.0:1442/');