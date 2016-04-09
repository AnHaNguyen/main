<?php

//require "update_IS_PR_type.php";

/*
 * Given a student's major, admission year, primary focus area, and an array of module codes,
 * returns a json associative array containing module code keys and type values.
 * ("type" here refers to the type of graduation requirement that the respective key's module fulfils)
 *
 * This program is tightly coupled with the formatting defined in following json files:
 * - ../data/simplified.json (For module's module credit value)
 * - ../req/CS/<adm_year>.json (For graduation requirements based on year of matriculation)
 * - ../req/CS/science.json (For the list of modules that can be used to fulfil the science requirement in CS)
 * - ../req/CS/fa.json (For the list of modules that can be used to fulfil a given focus area requirement in CS)
 *
 * Parameters:
 * - major (e.g. CS, IS)
 * - adm_year (e.g. 1314, 1516)
 * - focus_area (e.g. IR, DB, AI)
 * - mods (e.g. ["CS1101S","CS2020"])
 *
 * Returns:
 * - JSON associative array, with key = module code, value = [main type, sub-type]
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
 * - This program only works with the CS major for now.
 * - Module sub-types still have to be tweaked as required by front-end.
 *
 * @author Pierce Anderson Fu
 */

// @TODO: Refactor
// @TODO: Include iLead + NOC modules in CS internship reqs
// @TODO: Deal with IEM1201x and IEM2201x modules in CS PR (in place of CS2101)
// @TODO: Deal with specializations in IS?

// Module types used in both precise and non-precise mode
define("ULR_TYPE", "ULR");
define("PR_TYPE", "PR");
define("UE_TYPE", "UE");

// Module types used exclusively in precise mode
// ULR (AY < 15-16)
define("GEM_A_TYPE", "GEM_A");
define("GEM_B_TYPE", "GEM_B");
define("SS_TYPE", "SS");
define("BREADTH_TYPE", "BREADTH");

// ULR (AY >= 15-16)
define("GEH_TYPE", "GEH");
define("GEQ_TYPE", "GEQ");
define("GER_TYPE", "GER");
define("GES_TYPE", "GES");
define("GET_TYPE", "GET");

// PR
define("CORE_TYPE", "CORE");
define("ADV_SE_TYPE", "ADV_SE");
define("FOCUS_AREA_TYPE", "FA");
define("CS_4K_TYPE", "CS_4K");
define("CS_BREADTH_TYPE", "CS_BREADTH");
define("FYP_TYPE", "FYP");
define("INTERN_TYPE", "INTERN");
define("SCIENCE_TYPE", "SCI");
define("STATS_TYPE", "STATS");

if (!isset($_GET["major"])) {
    echo ("major is not set, exiting");
} else if (!isset($_GET["adm_year"])) {
    echo ("adm_year is not set, exiting");
} else if (!isset($_GET["focus_area"])) {
    echo ("focus_area is not set, exiting");
} else if (!isset($_GET["mods"])) {
    echo ("mods is not set, exiting");
} else {
    echo json_encode(check_module_type($_GET["major"], $_GET["adm_year"], $_GET["focus_area"], json_decode($_GET["mods"])));
}


function check_module_type($major, $adm_year, $focus_area, $mods) {
    $mods_with_types = array_fill_keys($mods, "");

    switch($major) {
        case 'CS':
            return get_type_CS($adm_year, $focus_area, $mods_with_types);
            break;
        case 'IS':
            return get_type_IS($adm_year, $mods_with_types);
            break;
        /*case 'CEG':
            return get_type_CEG($adm_year, $focus_area, $mods_with_types);
            break;
        case 'BZA':
            return get_type_BZA($adm_year, $focus_area, $mods_with_types);
            break;*/
        default:
            // Error
            echo "Invalid major entered, exiting";
            return null;
            break;
    }
}


function get_type_CS($adm_year, $focus_area, $mods) {

    $mods = update_ULR_type($adm_year, $mods);
    $mods = update_CS_PR_type($adm_year, $focus_area, $mods);

    return $mods;
}


function get_type_IS($adm_year, $mods) {

    $mods = update_ULR_type($adm_year, $mods);
    //$mods = update_IS_PR_type($adm_year, $mods);

    return $mods;
}


function update_ULR_type($adm_year, $mods) {

    // Old ULR system, prior to 15-16 batch
    if ($adm_year < "1516") {
    
        $has_taken_GEM_A = false;
        $has_taken_GEM_B = false;
        $has_taken_SS = false;
        $num_breadth_taken = 0;
        define("NUM_BREADTH_REQ", 2);
        define("SOC_MODS_REGEX", "CS|CP|CG|IT|BT|XFC|FMC");
        
        foreach ($mods as $mod_code => $mod_type) {
            
            $is_soc_mod = preg_match(SOC_MODS_REGEX, $mod_code);
            
            // Set type for GEM
            if (strpos($mod_code, "GEM") === 0
                || strpos($mod_code, "GEK") === 0) {
                
                // If GEM B
                // Q7: http://www.nus.edu.sg/registrar/gem/pre2015/frequently-asked-questions
                if ($mod_code[4] === "0"
                    || $mod_code[4] === "9") {
                    
                    // Try to fill in GEM B requirement first
                    // CS students have to fulfill at least one GEM B
                    if (!$has_taken_GEM_B) {
                        $mods[$mod_code] = [ULR_TYPE,GEM_B_TYPE];
                        $has_taken_GEM_B = true;
                    }

                    else if ($has_taken_GEM_B && !$has_taken_GEM_A) {
                        $mods[$mod_code] = [ULR_TYPE,GEM_A_TYPE];
                        $has_taken_GEM_A = true;
                    }

                    else if ($num_breadth_taken < NUM_BREADTH_REQ
                              && $mod_code !== "GEK1901") { // Exception: "GEK1901" is offered by SoC

                        $mods[$mod_code] = [ULR_TYPE,BREADTH_TYPE ];
                        $num_breadth_taken++;

                    }

                    else {
                        $mods[$mod_code] = [UE_TYPE,UE_TYPE];
                    }
                    
                }
                
                // If GEM A
                // Q7: http://www.nus.edu.sg/registrar/gem/pre2015/frequently-asked-questions
                else if ($mod_code[4] === "5") {
                    if (!$has_taken_GEM_A) {
                        $mods[$mod_code] = [ULR_TYPE,GEM_A_TYPE];
                        $has_taken_GEM_A = true;
                    }

                    else if ($num_breadth_taken < NUM_BREADTH_REQ) {
                        $mods[$mod_code] = [ULR_TYPE,BREADTH_TYPE];
                        $num_breadth_taken++;
                    }

                    else {
                        $mods[$mod_code] = [UE_TYPE,UE_TYPE];
                    }
                }
            }
            
            // Set type for SS
            else if (strpos($mod_code, "SSS") === 0) {
                if (!$has_taken_SS) {
                    $mods[$mod_code] = [ULR_TYPE,SS_TYPE];
                }

                else if ($num_breadth_taken < NUM_BREADTH_REQ) {
                    $mods[$mod_code] = [ULR_TYPE,BREADTH_TYPE];
                    $num_breadth_taken++;
                }

                else {
                    $mods[$mod_code] = [UE_TYPE,UE_TYPE];
                }
            }
            
            // Set type for breadth
            else if (!$is_soc_mod
                && $num_breadth_taken < NUM_BREADTH_REQ) {

                $mods[$mod_code] = [ULR_TYPE,BREADTH_TYPE];
                $num_breadth_taken++;
            }
        }

    return $mods;

    }
    
    // New ULR system, 15-16 batch onwards
    else {
        $has_taken_GEH = false;
        $has_taken_GEQ = false;
        $has_taken_GER = false;
        $has_taken_GES = false;
        $has_taken_GET = false;
        
        foreach ($mods as $mod_code => $mod_type) {

            if (!$has_taken_GEH
                && strpos($mod_code, "GEH") === 0) {

                $mods[$mod_code] = [ULR_TYPE,GEH_TYPE];
                $has_taken_GEH = true;

            } else if (!$has_taken_GEQ
                && strpos($mod_code, "GEQ") === 0) {

                $mods[$mod_code] = [ULR_TYPE,GEQ_TYPE];
                $has_taken_GEQ = true;

            } else if (!$has_taken_GER
                && strpos($mod_code, "GER") === 0) {

                $mods[$mod_code] = [ULR_TYPE,GER_TYPE];
                $has_taken_GER = true;

            } else if (!$has_taken_GES
                && strpos($mod_code, "GES") === 0) {

                $mods[$mod_code] = [ULR_TYPE,GES_TYPE];
                $has_taken_GES = true;

            } else if (!$has_taken_GET
                && strpos($mod_code, "GET") === 0) {

                $mods[$mod_code] = [ULR_TYPE,GET_TYPE];
                $has_taken_GET = true;

            } else {
                $mods[$mod_code] = [UE_TYPE,UE_TYPE];
            }
            
        }

    return $mods;

    }
}


function update_CS_PR_type($adm_year, $focus_area, $mods) {
    $all_mod_info_string = file_get_contents('../data/simplified.json');
    $grad_reqs_string = file_get_contents('../req/CS/' . $adm_year . '.json');
    $sci_reqs_string = file_get_contents('../req/CS/science.json');
    $fa_reqs_string = file_get_contents('../req/CS/fa.json');

    $all_mod_info = json_decode($all_mod_info_string, true);
    $grad_reqs = json_decode($grad_reqs_string, true);
    $core_reqs = $grad_reqs["and"]["PR"]["mod"];
    $fa_reqs = json_decode($fa_reqs_string, true)[$focus_area];
    $sci_reqs = json_decode($sci_reqs_string, true);
    $phy_reqs = [];

    // Requirement exceptions not covered in json
    // Preclusions are not dealt with in this program
    $core_reqs["CS1101S"] = "4"; // CS1010 can be replaced with CS1101S
    $core_reqs["CS2020"] = "4"; // CS1020 and CS2010 can be replaced with CS2020
    $core_reqs["CS2103T"] = "4"; // Effectively equivalent to CS2103
    $core_reqs["MA1102R"] = "4"; // MA1521 can be replaced with MA1102R

    $cs_breadth_depth_mc_req = 24; // Hardcoded because data is not in json
    $cs_breath_depth_mc_taken = 0;

    $cs_4k_mc_req = $core_reqs["Lev4"];
    $cs_4k_mc_taken = 0;

    $fa_mod_req = 3; // Hardcoded because data is not in json
    $fa_mods_taken = 0;
    $has_taken_fa_4k = false;

    $sci_mc_req = $core_reqs["Scie"];
    $sci_mc_taken = 0;

    // Setting up more variables
    // From AY12-13 onwards, can choose from either of two physics mods
    if ($adm_year >= "1213") {
        $phy_reqs_2d_array = $grad_reqs["or"][1];

        // Flatten 2d array containing module code and MC into 1d array containing only module codes
        foreach ($phy_reqs_2d_array as $phy_mod_code_and_mc) {
            $phy_reqs[] = $phy_mod_code_and_mc[0]; // Just extract the module codes
        }
    }

    // Set type for statistics modules
    /*
     * Module codes are hard coded because the json files fail to account for the fact that even prior to AY15-16,
     * students had the option of choosing ST2131 + ST2132 over ST2334.
     */
    if (array_key_exists("ST2131", $mods)) {

        $mods["ST2132"] = [PR_TYPE,STATS_TYPE];

        if ($adm_year < "1516") {
            // Going to have to take MCs required for ST2132 out from science's requirement
            $sci_mc_req -= $all_mod_info["ST2131"]["ModuleCredit"];
        }
        if (array_key_exists("ST2132", $mods)) {
            $mods["ST2132"] = [PR_TYPE,STATS_TYPE];
        }
    } else {
        // Take ST2334 to be the default choice, and ST2132 to fulfil the science requirement
        if ($adm_year >= "1516") {
            // Going to have to add MCs required for ST2132 to science's requirement
            $sci_mc_req += $all_mod_info["ST2132"]["ModuleCredit"];
        }
    }

    // Set type for FYP or internship modules
    $fyp = "CP4101"; // Hard coded because data is not in json
    $intern_branches = [ // Hard coded because data is not in json
        ["CP3880"],
        ["IS4010"],
        ["CP3200", "CP3202"],
        ["CP3200", "CP3101A"]
    ];
    $has_taken_fyp_or_intern = false;

    // Check if FYP was taken
    if (array_key_exists($fyp, $mods)) {
        $mods[$fyp] = [PR_TYPE,FYP_TYPE];
        $has_taken_fyp_or_intern = true;
    }

    // Check if an internship branch was taken
    else {
        // Go through all branches
        for ($i = 0; !$has_taken_fyp_or_intern && $i < count($intern_branches); $i++) {

            $current_intern_branch = $intern_branches[$i];

            // When branch only requires one module
            if (count($current_intern_branch) === 1) {

                $intern_mod_code = $current_intern_branch[0];

                if (array_key_exists($intern_mod_code, $mods)) {
                    $mods[$intern_mod_code] = [PR_TYPE,INTERN_TYPE];
                    $has_taken_fyp_or_intern = true;
                }
            }

            // When branch only requires two modules
            else {
                $first_intern_mod_code = $current_intern_branch[0];
                $second_intern_mod_code = $current_intern_branch[1];

                if (array_key_exists($first_intern_mod_code, $mods)
                    && array_key_exists($second_intern_mod_code, $mods)
                ) {

                    $mods[$first_intern_mod_code] = [PR_TYPE,INTERN_TYPE];
                    $mods[$second_intern_mod_code] = [PR_TYPE,INTERN_TYPE];
                    $has_taken_fyp_or_intern = true;

                }
            }
        }
    }

    // If neither FYP nor internship branches was taken, can choose to take 12 MCs of CS modules at level 4k or above (AY13-14 and prior)
    if (!$has_taken_fyp_or_intern && $adm_year <= "1314") {
        $cs_4k_mc_req += 12;
        $cs_breadth_depth_mc_req += 12;
    }

    // Set type for advanced software engineering modules
    // Modules have to be cleared in a pair
    $adv_se_reqs = $grad_reqs["or"][0];
    $has_fulfilled_adv_se_reqs = false;

    for ($i = 0; !$has_fulfilled_adv_se_reqs && $i < count($adv_se_reqs); $i++) {
        $se_pair = explode(",", $adv_se_reqs[$i][0]);
        $first_half_mod_code = $se_pair[0];
        $second_half_mod_code = $se_pair[1];

        // Check if both parts of the pair can be found
        if (array_key_exists($first_half_mod_code, $mods)
            && array_key_exists($second_half_mod_code, $mods)) {

            $mods[$first_half_mod_code] = [PR_TYPE,ADV_SE_TYPE];
            $mods[$second_half_mod_code] = [PR_TYPE,ADV_SE_TYPE];
            $has_fulfilled_adv_se_reqs = true;

        }
    }

    // Set type for all other modules
    foreach ($mods as $mod_code => $mod_type) {

        if (!$mod_type) { // Skip if module type is already set

            $mod_credit = $all_mod_info[$mod_code]["ModuleCredit"];
            $is_cs_mod = strpos($mod_code, "CS") === 0;
            $is_lvl_4k_or_above = $mod_code[2] >= "4";

            // Set type for core CS modules
            if (array_key_exists($mod_code, $core_reqs)) {
                $mods[$mod_code] = [PR_TYPE,CORE_TYPE];
            }

            // Set type for physics requirement modules (AY12-13 onwards)
            else if ($adm_year >= "1213"
                && in_array($mod_code, $phy_reqs)) {

                $mods[$mod_code] = [PR_TYPE,CORE_TYPE];

            }

            // Set type for science requirement modules
            else if (array_key_exists($mod_code, $sci_reqs)) {
                if ($sci_mc_taken < $sci_mc_req) {
                    $mods[$mod_code] = [PR_TYPE,SCIENCE_TYPE];
                    $sci_mc_taken += $sci_reqs[$mod_code];
                } else {
                    $mods[$mod_code] = [UE_TYPE,UE_TYPE];
                }
            }

            // Set type for focus area modules (level 4k or above)
            else if ($is_cs_mod && $is_lvl_4k_or_above
                && array_key_exists($mod_code, $fa_reqs)) {

                // Fulfil focus area lvl 4k mod requirement
                if (!$has_taken_fa_4k) {
                    $mods[$mod_code] = [PR_TYPE,FOCUS_AREA_TYPE];
                    $cs_breath_depth_mc_taken += $mod_credit;
                    $cs_4k_mc_taken += $mod_credit;
                    $fa_mods_taken += 1;
                    $has_taken_fa_4k = true;
                }

                // Fulfil focus area mod requirement
                else if ($fa_mods_taken < $fa_mod_req) {
                    $mods[$mod_code] = [PR_TYPE,FOCUS_AREA_TYPE];
                    $cs_breath_depth_mc_taken += $mod_credit;
                    $cs_4k_mc_taken += $mod_credit;
                    $fa_mods_taken += 1;
                }

                // Fulfil CS breadth and depth lvl 4k requirement
                else if ($cs_4k_mc_taken < $cs_4k_mc_req) {
                    $mods[$mod_code] = [PR_TYPE,CS_4K_TYPE];
                    $cs_breath_depth_mc_taken += $mod_credit;
                    $cs_4k_mc_taken += $mod_credit;
                }

                // Fulfil CS breadth and depth MC requirement
                else if ($cs_breath_depth_mc_taken < $cs_breadth_depth_mc_req) {
                    $mods[$mod_code] = [PR_TYPE,CS_BREADTH_TYPE];
                    $cs_breath_depth_mc_taken += $mod_credit;
                }

                // Dump into UE
                else {
                    $mods[$mod_code] = [UE_TYPE,UE_TYPE];
                }
            }

            // Set type for focus area modules (level 3k and below)
            else if (array_key_exists($mod_code, $fa_reqs)) {

                // Fulfil focus area mod requirement
                // Avoids blocking level 4k or above focus area mod, if not already taken
                if (($has_taken_fa_4k && $fa_mods_taken < $fa_mod_req)
                    || ($fa_mods_taken + 1 < $fa_mod_req)) {

                    $mods[$mod_code] = [PR_TYPE,FOCUS_AREA_TYPE];
                    $cs_breath_depth_mc_taken += $mod_credit;
                    $fa_mods_taken += 1;

                }

                // Fulfil CS breadth and depth MC requirement
                // Avoids blocking level 4k or above CS mods, if not already fulfilled
                else if (($cs_4k_mc_taken >= $cs_4k_mc_req
                        && $cs_breath_depth_mc_taken < $cs_breadth_depth_mc_req)
                    || ($cs_breath_depth_mc_taken + $mod_credit < $cs_breadth_depth_mc_req)) {

                    $mods[$mod_code] = [PR_TYPE,CS_BREADTH_TYPE];
                    $cs_breath_depth_mc_taken += $mod_credit;

                }

                // Dump into UE
                else {
                    $mods[$mod_code] = [UE_TYPE,UE_TYPE];
                }
            }

            // Set type for non-focus area CS modules (level 4k or above)
            else if ($is_cs_mod && $is_lvl_4k_or_above) {

                // Fulfil CS breadth and depth lvl 4k requirement
                // Avoid blocking off focus area level 4k requirement
                if (($has_taken_fa_4k && $cs_4k_mc_taken < $cs_4k_mc_req)
                    || ($cs_4k_mc_taken + $mod_credit < $cs_4k_mc_req)) {

                    $mods[$mod_code] = [PR_TYPE,CS_4K_TYPE];
                    $cs_breath_depth_mc_taken += $mod_credit;
                    $cs_4k_mc_taken += $mod_credit;

                }

                // Fulfil CS breadth and depth MC requirement
                /*
                 * if ($cs_breath_depth_mc_taken < $cs_breadth_depth_mc_req) might be problematic.
                 * If user repeatedly inserts CS 4k mods without FA mods, then adds FA mods,
                 * the MCs calculated under the CS breadth/depth section could overshoot the requirement by 12 MCs.
                 * Problem lies with the fact that it's hard to prevent CS mods from eating
                 * into FA mods' MC because FA mod requirements are not MC-based.
                 *
                 * Workaround: Set aside MCs for FA mods not yet taken. Assume that each FA mod will take up 4 MCs.
                 */
                else if ($cs_breath_depth_mc_taken + 4 * ($fa_mod_req - $fa_mods_taken)
                    < $cs_breadth_depth_mc_req) {

                    $mods[$mod_code] = [PR_TYPE,CS_BREADTH_TYPE];
                    $cs_breath_depth_mc_taken += $mod_credit;

                }

                // Dump into UE
                else {
                    $mods[$mod_code] = [UE_TYPE,UE_TYPE];
                }
            }

            // Set type for non-focus area CS modules (level 3k and below)
            else if ($is_cs_mod) {

                // Fulfil CS breadth and depth requirement
                // Avoid blocking off focus area or level 4k module requirements
                /*
                 * For reasoning behind the attributing of focus area modules with 4 MCs,
                 * See comment block in section where CS breadth and depth MC requirements are fulfilled using
                 * non-focus area CS modules (level 4k or above)
                 */
                if ($cs_breath_depth_mc_taken
                        + (4 * ($fa_mod_req - $fa_mods_taken))
                        + ($cs_4k_mc_req - $cs_4k_mc_taken)
                        - ((!$has_taken_fa_4k) ? 4 : 0)
                    < $cs_breadth_depth_mc_req) {

                    $mods[$mod_code] = [PR_TYPE,CS_BREADTH_TYPE];
                    $cs_breath_depth_mc_taken += $mod_credit;

                }

                // Dump into UE
                else {
                    $mods[$mod_code] = [UE_TYPE,UE_TYPE];
                }
            }

            // If absolutely none of the above, dump into UE
            else {
                $mods[$mod_code] = [UE_TYPE,UE_TYPE];
            }
        }
    }

    return $mods;
}
