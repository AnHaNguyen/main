<?php

/*
 * See get_type.php for documentation.
 */

// PR subtypes
define("IS_PE_TYPE", "IS_PE"); // Programme Elective
define("IS_PE_4K_TYPE", "IS_PE_4K");
define("IS_4K_TYPE", "IS_4K");


function update_IS_PR_type($adm_year, $mods) {
    $all_mod_info_string = file_get_contents('../data/modules_min.json');
    $grad_reqs_string = file_get_contents('../req/IS/' . $adm_year . '.json');
    $elective_reqs_string = file_get_contents('../req/IS/elective.json');

    $all_mod_info = json_decode($all_mod_info_string, true);
    $grad_reqs = json_decode($grad_reqs_string, true);
    $core_reqs = $grad_reqs["and"]["PR"]["mod"];
    $calculus_reqs_2d_array = $grad_reqs["or"][0];
    $calculus_reqs = [];

    $elective_reqs = json_decode($elective_reqs_string, true);
    $electives_mc_req = ($adm_year <= "1314") ? $core_reqs["Elective"] : $grad_reqs["or"][1][0][1];
    $electives_mc_taken = 0;

    $electives_4k_mc_req = ($adm_year <= "1314") ? $core_reqs["Elective4"] : explode(";", $grad_reqs["or"][1][1][1])[1];
    $electives_4k_mc_taken = 0;


    // Flatten 2d array containing module code and MC into 1d array containing only module codes
    foreach ($calculus_reqs_2d_array as $calculus_mod_code_and_mc) {
        $calculus_reqs[] = $calculus_mod_code_and_mc[0]; // Just extract the module codes
    }

    // Set type for FYP or internship modules
    $fyp_mod_code = "CP4101";
    $intern_branches = [ // Hard coded because data is not in json
        "CP3880", // ATAP
        "IS4010" // IIP
    ];
    $has_taken_intern = false;
    $fyp_replacement_IS_4k_mc_req = 0;
    $fyp_replacement_IS_4k_mc_taken = 0;

    // Check if an internship branch was taken
    foreach ($intern_branches as $intern_mod_code) {
        if (array_key_exists($intern_mod_code, $mods)) {
            $mods[$intern_mod_code] = [PR_TYPE,INTERN_TYPE];
            $has_taken_intern = true;
            break;
        }
    }

    // Check if FYP was taken
    if (array_key_exists($fyp_mod_code, $mods)) {

        $mods[$fyp_mod_code] = [PR_TYPE,FYP_TYPE];

        // AY14-15 and onwards, if FYP is taken, number of elective MCs and level 4k elective MCs required are reduced
        if ($adm_year >= "1415") {
            $fyp_mod_credit = $all_mod_info[$fyp_mod_code]["MC"];
            $electives_mc_req -= $fyp_mod_credit;
            $electives_4k_mc_req -= $fyp_mod_credit;
        }
    }

    // (AY10-11 or AY11-12) FYP not taken
    // (AY12-13 or AY13-14) FYP and intern not taken
    else if (!$has_taken_intern) {
        // Student has to take level 4k IS modules instead
        $fyp_replacement_IS_4k_mc_req = (int)$grad_reqs["or"][1][1][1];
    }

    // Set type for all other modules
    foreach ($mods as $mod_code => $mod_type) {

        if (!$mod_type) { // Skip if module type is already set

            $mod_credit = $all_mod_info[$mod_code]["MC"];
            $is_IS_mod = strpos($mod_code, "IS") === 0;
            $is_lvl_4k = $mod_code[2] === "4";

            // Set type for core IS modules
            if (array_key_exists($mod_code, $core_reqs)) {
                $mods[$mod_code] = [PR_TYPE,CORE_TYPE];
            }

            // Set type for calculus modules
            else if (array_key_exists($mod_code, $calculus_reqs)) {
                $mods[$mod_code] = [PR_TYPE,CORE_TYPE];
            }

            // Set type for level 4k programme elective modules
            else if ($is_lvl_4k && array_key_exists($mod_code, $elective_reqs)) {

                // (AY10-11 or AY11-12) Fulfil FYP replacement requirement
                // (AY12-13 or AY13-14) Fulfil FYP and intern replacement requirement
                if ($is_IS_mod && $fyp_replacement_IS_4k_mc_taken < $fyp_replacement_IS_4k_mc_req) {
                    $mods[$mod_code] = [PR_TYPE,IS_4K_TYPE];
                    $fyp_replacement_IS_4k_mc_taken += $mod_credit;
                }

                // Fulfil programme elective's level 4k requirement
                else if ($electives_4k_mc_taken < $electives_4k_mc_req) {
                    $mods[$mod_code] = [PR_TYPE,IS_PE_4K_TYPE];
                    $electives_4k_mc_taken += $mod_credit;
                }

                // Fulfil programme elective's MC requirement
                else if ($electives_mc_taken < $electives_mc_req) {
                    $mods[$mod_code] = [PR_TYPE,IS_PE_TYPE];
                    $electives_mc_taken += $mod_credit;
                }

                // Dump into UE
                else {
                    $mods[$mod_code] = [UE_TYPE,UE_TYPE];
                }
            }

            // Set type for level 3k and below programme elective modules
            // Avoid blocking off level 4k modules that have yet to be taken
            else if (array_key_exists($mod_code, $elective_reqs)
                && $electives_mc_taken + ($electives_4k_mc_req - $electives_4k_mc_taken) < $electives_mc_req) {

                $mods[$mod_code] = [PR_TYPE,IS_PE_TYPE];
                $electives_mc_taken += $mod_credit;
            }

            // Dump into UE
            else {
                $mods[$mod_code] = [UE_TYPE,UE_TYPE];
            }
        }
    }
    
    return $mods;
}
