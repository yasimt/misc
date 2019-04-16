define(['./module'], function (tmeModuleApp) {
	'use strict';
	tmeModuleApp.filter('capitalize', function() {
		return function(input, scope) {
			if (input!=null) {
				input = input.toLowerCase();
			}
			return input.substring(0,1).toUpperCase()+input.substring(1);
		}
	});
	
	tmeModuleApp.filter('unique', function() {
		   return function(collection, keyname) {
			  var output = [], 
				  keys = [];

			  angular.forEach(collection, function(item) {
				  var key = item[keyname];
				  if(keys.indexOf(key) === -1) {
					  keys.push(key);
					  output.push(item);
				  }
			  });

			  return output;
		   };
	});
	
	tmeModuleApp.filter('Cntletter',function() {
		return function(input, scope) {
			if (input!=null) {
				input = input.length;
			}
			return input;
		};
	});
	
	
	tmeModuleApp.filter('urlEncode',function() {
		return function(input, scope) {
			return encodeURIComponent(input);
		}
	});

	tmeModuleApp.filter('setDate',function() {
		return function(input, scope) {
			var t = input.split(/[- :]/);
			var date = new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);
			return date.toDateString();
		}
	});
	tmeModuleApp.filter('setDateTime',function() {
		return function(input, scope) {
			var t = input.split(/[- :]/);
			var date = new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);
			var dStr =  date.toDateString();
			dStr = dStr+' '+ t[3]+':'+ t[4]+':'+ t[5]
			return dStr;
		}
	});
	tmeModuleApp.filter('roundVals',function() {
		return function(input, params) {
			var retVal = input.toString().split(".");
			if(retVal[1] !== undefined) {
				var subStr	=	retVal[1].slice(0,params);
				return retVal[0]+'.'+subStr;
			} else {
				return retVal[0];
			}
		}
	});
	
	tmeModuleApp.filter('stripNames',function() {
		return function(input) {
			var retVal = input.toString().split(" ");
			if(retVal[1] !== undefined) {
				var subStr	=	retVal[1].slice(0,1);
				var subStr2	=	retVal[0].slice(0,1);
				return subStr2+''+subStr;
			} else {
				return retVal[0].slice(0,1);
			}
		}
	});
	
	tmeModuleApp.filter('range', function() {
		return function(input, total) {
			total = parseInt(total);
			for (var i=0; i<total; i++)
			input.push(i);
			return input;
		};
	});
	
	tmeModuleApp.filter('split', function() {
			return function(input, splitChar, splitIndex) {
				if(input != null) {
					return input.split(splitChar)[splitIndex];
				}
			}
    });
    
	tmeModuleApp.filter('splitAll', function() {
		return function(input, splitChar) {
			if(input != null) {
				return input.split(splitChar);
			}
		}
    });
    
    tmeModuleApp.filter('replace', function() {
		return function(input, firstChar,sencondChar) {
			if(input != null) {
				return input.replace(/\|~\|/g, sencondChar);
			}
		}
    });
    
    tmeModuleApp.filter('indexReplace', function() {
		return function(input) {
			if(input != null) {
				var premium_identifier 	= 	input.substring(input.lastIndexOf("___"));
				var premium_flag 		= 	premium_identifier.replace('___', '');
				if(premium_flag	==	1) {
					var exact_catname 		= 	input.replace(premium_identifier, '')+' [Premium Category]';
				} else {
					var exact_catname 		= 	input.replace(premium_identifier, '');
				}
				
				return exact_catname;
			}
		}
    });
    
    tmeModuleApp.filter('firstLetterArea', function() {
		return function (input, letter) {
			input = input || [];
			var out = [];
			if(letter	==	'all') {
				input.forEach(function (item) {
					out.push(item);
				});
			} else {
				input.forEach(function (item) {
					if (item.area.charAt(0).toLowerCase() == letter) {
						out.push(item);
					}
				});
			}
			return out;
		}
    });
    
    tmeModuleApp.filter('setUniqueArray', function() {
		return function (input) {
			var sendArr	=	[];
			var strSendPincode	=	"";
			
			angular.forEach(input,function(value,key) {
				if(sendArr.indexOf(value) == -1) {
					sendArr.push(value);
				}
			});
			return sendArr.length;
		}
    });
    
    tmeModuleApp.filter('offset', function() {
        return function(input, start) {
            start = parseInt(start, 10);
            return input.slice(start);
        };
    });
    
    tmeModuleApp.filter('setDecimal', function ($filter) {
		return function (input, places) {
			if (isNaN(input)) return input;
			var factor = "1" + Array(+(places > 0 && places + 1)).join("0");
			return Math.round(input * factor) / factor;
		};
	});
	
	tmeModuleApp.filter('numToWord',function () {
		return function(input) {	
			input	=	input  || "";
			var r=0;
			var inp	=	input.toString();
			var txter=inp;
			var sizer=txter.length;
			var numStr="";
			var n=parseInt(inp);
			var places=0;
			var str="";
			var entry=0;
			while(n>=1) {
				r=parseInt(n%10);
				if(places<3 && entry==0) {
					numStr=txter.substring(txter.length-0,txter.length-3) // Checks for 1 to 999.
					str=onlyDigit(numStr); //Calls function for last 3 digits of the value.
					entry=1;
				}

				if(places==3) {
					numStr=txter.substring(txter.length-5,txter.length-3)
					if(numStr!="")
					{
						str=onlyDigit(numStr)+ " Thousand "+str;
					}
				}

				if(places==5) {
					numStr=txter.substring(txter.length-7,txter.length-5) //Substring for 5 place to 7 place of the string
					if(numStr!="")
					{
						str=onlyDigit(numStr)+ " Lakhs "+str; //Appends the word lakhs to it
					}
				}

				if(places==6) {
					numStr=txter.substring(txter.length-9,txter.length-7)  //Substring for 7 place to 8 place of the string
					if(numStr!="") {
						str=onlyDigit(numStr)+ " Crores "+str;        //Appends the word Crores
					}
				}
				n=parseInt(n/10);
				places++;
			}
			return str;
			
			function onlyDigit(n)
			{
				//Arrays to store the string equivalent of the number to convert in words
				var units=['','One','Two','Three','Four','Five','Six','Seven','Eight','Nine'];
				var randomer=['','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen','Seventeen','Eighteen','Nineteen'];
				var tens=['','Ten','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];
				var r=0;
				var num=parseInt(n);
				var str="";
				var pl="";
				var tenser="";
				while(num>=1)
				{
					r=parseInt(num%10);
					tenser=r+tenser;
					if(tenser<=19 && tenser>10) //Logic for 10 to 19 numbers
					{
					str=randomer[tenser-10];
					}
					else
					{
					if(pl==0)        //If units place then call units array.
					{
					str=units[r];
					}
					else if(pl==1)    //If tens place then call tens array.
					{
					str=tens[r]+" "+str;
					}
					}
					if(pl==2)        //If hundreds place then call units array.
					{
					str=units[r]+" Hundred "+str;
					}

					num=parseInt(num/10);
					pl++;
				}
				return str;
			}
		}
	});

		tmeModuleApp.filter('toArray', function () {
			'use strict';

			return function (obj) {
			
				if (!(obj instanceof Object)) {
					return obj;
				}
				var result = [];
				angular.forEach(obj, function(obj, key) {
					obj.$key = key;
					result.push(obj);
				});
				return result;
			}
		}); 
		
		tmeModuleApp.filter('secondsToDateTime', function() {
        
        return function(seconds) {
            if(isNaN(seconds) || (seconds <= 0))
            {
                seconds = 0;
            }
            var d = new Date(0,0,0,0,0,0,0);
            d.setSeconds(seconds);
            return d;
        };
    });
    
    tmeModuleApp.filter('ceil', function () {
        return function (input) {
            return Math.ceil(input);
        };
    });
    tmeModuleApp.filter('dateNew', function($filter)
	{
		 return function(input){ 
			  if(input == null){ 
				  return "";
			  }else{
				  var newdate	= input.split(" "); 
				  var date = $filter('date')(new Date(newdate[0]), 'MMM d,yy');//alert(date)
				  return date;
			  }
		 };
	});
	
	tmeModuleApp.filter('timeNew', function($filter)
	{
		 return function(input){
		  if(input == null){ return ""; }else{ //alert("----"+input);
			  var newdate	= input.split(" "); 			    
			  var date_format = '12';
			  var newtime = newdate[1].split(":")
			var hour    = newtime[0];  /* Returns the hour (from 0-23) */
			var minutes     = newtime[1];  /* Returns the minutes (from 0-59) */
			var result  = hour;
			var ext     = '';			
			  
			  if(date_format == '12'){
			    if(hour > 12){
			        ext = 'PM';
			        hour = (hour - 12);
			     
			        if(hour < 10){
			            result = "0" + hour;
			        }else if(hour == 12){
			            hour = "00";
			            ext = 'AM';
			        }
			    }
			    else if(hour < 12){
			        result = ((hour < 10) ? "0" + hour : hour);
			        ext = 'AM';
			    }else if(hour == 12){
			        ext = 'PM';
			    }
			}
			if(minutes < 10){
			    minutes = "" + minutes;
			}
			result = result + ":" + minutes + ' ' + ext; 
			return result;
		  }
		 };
	});
});
