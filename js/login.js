var key = "8Qrg78UktVoi1XHeTYLk1";      //need hidden
var redirectUrl = "http://socplans.com/main/index.html"; //need hidden
var CS = "Computer Science";
var IS = "Information System";
var BZA = "Business Analytic";
var CEG = "Computer Engineering";

$("#login").on("click",function(){
    if (ivle.getToken(window.location.href) == null){
        var authUrl = ivle.login(key, redirectUrl);
        window.location.href = authUrl;
    }
            
});

$(function($){
    var token = ivle.getToken(window.location.href);
    if (token != null){
        initialUser(key,token);
    }
});



function initialUser(key, token){
    var user = ivle.User(key, token); // return a User instance
 
    // you must init user, it will validate the user and query his/her profile
    user.init().done(function() {
    // start doing things
    // e.g. get user's profile
    /*UserID    "a0113038"
Name    "NGUYEN AN HA"
Email   "a0113038@u.nus.edu"
Gender  "Male"
Faculty "School of Computing"
FirstMajor  "Computer Science (Hons)"
SecondMajor ""
MatriculationYear   "2013"
*/
    var modules = getModules(user);
    var admission_year = getAdmissionYear(user);

    var major = getMajor(user);
    alert(major + " " + admission_year);

    return {
        modules: modules,
        admission_year: admission_year,
        major: major
    }
});
}

function getAdmissionYear(user){
    var matricYear = user.profile('MatriculationYear').substring(2,4);
    var admission_year = matricYear + (parseInt(matricYear) + 1);
    return admission_year;
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

function getModules(user){
    /*ModuleCode    "CS3223"
ModuleTitle "Database Systems Implementation"
AcadYear    "2015/2016"
Semester    "2"
SemesterDisplay "Semester 2"*/
    
    var mods = new Array();
    var matric = user.profile('UserID');
    $.ajax({
        url: "php/authentication/connectdatabase.php?cmd=getModules&matric="+matric
    }).done(function(data){
        if (data == -1){
            alert("Error retrieving!");
            return;
        }
        if (data == ""){            //first time user, no record in DB
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
                    if (_data == -1){
                        alert("Error inserting!");
                        return;
                    }
                });   
                return mods;
            });
        } else{
            mods = JSON.parse(data);
            return mods;
        }      
    });
    return mods;
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
