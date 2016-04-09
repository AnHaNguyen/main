<?php

require_once "update_ULR_type.php";
require_once "update_CS_PR_type.php";
require_once "update_IS_PR_type.php";

/*
 * Given a student's major, admission year, primary focus area, and an array of module codes,
 * returns a json associative array containing module code keys and type values.
 * ("type" here refers to the type of graduation requirement that the respective key's module fulfils)
 *
 * This program is tightly coupled with the formatting defined in following json files:
 * - ../data/simplified.json (For module's module credit value)
 * - ../req/<major>/<adm_year>.json (For graduation requirements based on year of matriculation)
 * - ../req/CS/science.json (For the list of modules that can be used to fulfil the science requirement in CS)
 * - ../req/CS/fa.json (For the list of modules that can be used to fulfil a given focus area requirement in CS)
 * - ../req/IS/elective.json (For the list of modules that can be used to fulfil the programme elective requirement in IS)
 *
 * Parameters:
 * - major (e.g. CS, IS)
 * - adm_year (e.g. 1314, 1516)
 * - focus_area (e.g. IR, DB, AI)
 * - mods (e.g. ["CS1101S","CS2020"])
 *
 * Returns:
 * - JSON associative array, with key = module code, value = [main type, subtype]
 * e.g.
 * {
 *      "GEK1011": ["ULR","GEM_B"],
 *      "CS1101S": ["PR","CORE]
 * }
 *
 * Usage example: http://bit.ly/1MlMjXD
 *
 * Note:
 * - This program does not care for and will not deal with preclusion conflicts.
 * - This program only works with the CS an IS major for now.
 * - This program will not work for USP students nor students who went for NOC.
 * - Module subtypes still have to be tweaked as required by front-end.
 *
 * @author Pierce Anderson Fu
 */

// @TODO: Refactor
// @TODO: Deal with IEM1201x and IEM2201x modules in CS PR (in place of CS2101)
// @TODO: Deal with USP programmes in CS, IS
// @TODO: Deal with NOC programmes in CS, IS
// @TODO: Deal with specializations in IS??

// Module main types
define("ULR_TYPE", "ULR");
define("PR_TYPE", "PR");
define("UE_TYPE", "UE");

// PR subtypes
define("CORE_TYPE", "CORE");
define("FYP_TYPE", "FYP");
define("INTERN_TYPE", "INTERN");


if (!isset($_GET["major"])) {
    echo ("major is not set, exiting");
} else if (!isset($_GET["adm_year"])) {
    echo ("adm_year is not set, exiting");
} else if (!isset($_GET["mods"])) {
    echo ("mods is not set, exiting");
} else {
    echo json_encode(check_module_type($_GET["major"], $_GET["adm_year"], json_decode($_GET["mods"])));
}


function check_module_type($major, $adm_year, $mods) {
    $mods_with_types = array_fill_keys($mods, "");

    switch($major) {
        case 'CS':
            if (!isset($_GET["focus_area"])) {
                return "focus_area is not set, exiting";
            } else {
                return get_type_CS($adm_year, $_GET["focus_area"], $mods_with_types);
            }
        case 'IS':
            return get_type_IS($adm_year, $mods_with_types);
        /*case 'CEG':
            return get_type_CEG($adm_year, $focus_area, $mods_with_types);
        case 'BZA':
            return get_type_BZA($adm_year, $focus_area, $mods_with_types);*/
        default:
            // Error
            return "Invalid major entered, exiting";
    }
}


function get_type_CS($adm_year, $focus_area, $mods) {

    $mods = update_ULR_type($adm_year, $mods);
    $mods = update_CS_PR_type($adm_year, $focus_area, $mods);

    return $mods;
}


function get_type_IS($adm_year, $mods) {

    $mods = update_ULR_type($adm_year, $mods);
    $mods = update_IS_PR_type($adm_year, $mods);

    return $mods;
}
