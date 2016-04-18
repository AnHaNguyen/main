var key = "8Qrg78UktVoi1XHeTYLk1";      //need hidden
var redirectUrl = "http://" + window.location.host + "/main/index.html"; //need hidden
var CS = "Computer Science";
var IS = "Information System";
var BZA = "Business Analytic";
var CEG = "Computer Engineering";
var totalSem = 8;
var token;

$("#login").on("click",function(){
    if (ivle.getToken(window.location.href) == null){
        var authUrl = ivle.login(key, redirectUrl);
        token = ivle.getToken(authUrl);
        window.location.href = authUrl;
    }
            
});

function updateToken(){
    token = ivle.getToken(window.location.href);
    return token;
}


/*function initialUser(key, token){
    var user = ivle.User(key, token); // return a User instance
 
    // you must init user, it will validate the user and query his/her profile
    user.init().done(function() {
    // start doing things
    // e.g. get user's profile
UserID    "a0113038"
Name    "NGUYEN AN HA"
Email   "a0113038@u.nus.edu"
Gender  "Male"
Faculty "School of Computing"
FirstMajor  "Computer Science (Hons)"
SecondMajor ""
MatriculationYear   "2013"

});
}*/

function getAdmissionYear(user){
    var matricYear = user.profile('MatriculationYear').substring(2,4);
    var admission_year = matricYear + (parseInt(matricYear) + 1);
    return admission_year;
}

function getYear(year){
	var matricYear = year.substring(2,4) + year.substring(7,9);
    return matricYear;

}
function getMajor(user){
    var major = user.profile('FirstMajor');
    if (major.indexOf(CS) != -1){
        return "CS";
    } else if (major.indexOf(IS) != -1){
        return "IS";
    } else if (major.indexOf(BZA) != -1){
        return "BZA";
    } else if (major.indexOf(CEG) != -1){
        return "CEG";
    } else return "Not supported";
}

function getModules(user, callback){
    /*ModuleCode    "CS3223"
ModuleTitle "Database Systems Implementation"
AcadYear    "2015/2016"
Semester    "2"
SemesterDisplay "Semester 2"*/
    
    var mods = new Array();
	for (var i = 0; i < totalSem; i++){
		mods[i] = new Array();
	}
    var matric = user.profile('UserID');
    $.ajax({
        url: "php/authentication/connectdatabase.php?cmd=getModules&matric="+matric
    }).done(function(data){
		//console.log(data, matric);
        if (data == -1){
            alert("Error retrieving!");
            return;
        }
        if (JSON.parse(data) == ""){            //first time user, no record in DB
			//console.log('WARNING FIRST TIME LOGIN');
            var allMods = new Array();
            user.modulesTaken(function(allMods){
                for (var i = 0; i < allMods.length; i++){
                    var semester = getSemester(allMods[i], user.profile('MatriculationYear'));
                    mods[semester-1].push(allMods[i]['ModuleCode']);
                }
                var modsStr = JSON.stringify(mods);
                $.ajax({
                    url: "php/authentication/connectdatabase.php?cmd=storeModules&matric="+matric+"&modules="+modsStr
                }).done(function(_data){
					//console.log('warning',_data);
                    if (_data == -1){
                        alert("Error inserting!");
                        return;
                    }
                });   
                callback(mods);
            });
        } else{
            mods = JSON.parse(data);
            callback(mods);
        }      
    });
}

function getSemester(moduleInfo, startYear){
    var takenYear = moduleInfo['AcadYear'].split("/")[0];
    var curYear = parseInt(takenYear) - parseInt(startYear) + 1;
    var takenSem = parseInt(moduleInfo['Semester']);
    if (takenSem == 1){
        return curYear*2 - 1;
    } else {
        return curYear*2;
    }
}

function getCurrentSem(startYear){
    var year = parseInt(startYear);
    var currentSem = 1;
    var currentAcadYear = "2016/2017";
    var curYear = parseInt(currentAcadYear.split("/")[0]);
    if (currentSem == 1){
        return (curYear - year + 1)*2 - 1;
    } else{
        return (curYear - year + 1)*2;
    }   
}


function getIVLEToken(){
    if (token == null){
        return updateToken();
    } else{
        return token;
    }
}

function initializeUser(token, callback){
	var user = ivle.User(key, token);
	
	user.init().done(function(){
		var user_id = user.profile('UserID');
		$.ajax({
			type: "POST",
			url:"php/authentication/connectdatabase.php",
			data: {user_id:user_id}
		}).done(function(data){
			callback(user);
		});
	});	
}

function getModulesLogin(token, callback){
	var user = ivle.User(key, token);
	user.init().done(function(){
		getModules(user, function(modules){
            states = getStates(user);
			callback(modules, states);
		});
		
	});
}


function getStates(user){     //add state to modules
    var startYear = user.profile('MatriculationYear');
    var curSem = getCurrentSem(startYear);
    var states = {};
    for (var i = 1; i <= totalSem; i++){
        if (i >= curSem){
            states[i] = "planned";
        } else{
            states[i] = "taken";
        }
    }
}

$("#logout").on("click",function(){
	window.location.href = 'php/authentication/logout.php';
});
