<?php

/*
 * See get_type.php for documentation.
 */

// @TODO: Deal with footnote swappables

// PR subtypes
define("PE_TYPE", "CEG_PE");
define("PE_DEPTH_TYPE", "CEG_PE_DEPTH");


function update_CEG_PR_type($adm_year, $mods) {
    $all_mod_info_string = file_get_contents('../data/simplified.json');
    $grad_reqs_string = file_get_contents('../req/CEG/' . $adm_year . '.json');
    $elective_reqs_string = file_get_contents('../req/CEG/elective.json');

    $all_mod_info = json_decode($all_mod_info_string, true);
    $grad_reqs = json_decode($grad_reqs_string, true);
    $core_reqs = $grad_reqs["and"]["PR"]["mod"];

    $elective_reqs = json_decode($elective_reqs_string, true);
    $elective_breadth_reqs = $elective_reqs["breadth"];
    $elective_depth_reqs = $elective_reqs["depth"];

    $electives_mc_req = $core_reqs["Elective"];
    $electives_mc_taken = 0;

    $electives_depth_mc_req = $core_reqs["ElectiveDepth"];
    $electives_depth_mc_taken = 0;

    // Internships requirement only applies to AY1415 and onwards
    if ($adm_year >= "1415") {
        $intern_reqs_2d_array = $grad_reqs["or"][0];
        $intern_branches = [];

        // Set type for internship modules
        foreach ($intern_reqs_2d_array as $intern_mod_code_and_mc) {
            $intern_branches[] = $intern_mod_code_and_mc[0]; // Just extract the module codes
        }

        // Check if an internship branch was taken
        foreach ($intern_branches as $intern_mod_code) {
            if (array_key_exists($intern_mod_code, $mods)) {
                $mods[$intern_mod_code] = [PR_TYPE,INTERN_TYPE];
                break;
            }
        }
    }

    // Set type for all other modules
    foreach ($mods as $mod_code => $mod_type) {

        if (!$mod_type) { // Skip if module type is already set

            $mod_credit = $all_mod_info[$mod_code]["ModuleCredit"];

            // Set type for core CEG modules
            if (array_key_exists($mod_code, $core_reqs)) {
                $mods[$mod_code] = [PR_TYPE, CORE_TYPE];
            }

            // Set type for elective depth modules
            else if (array_key_exists($mod_code, $elective_depth_reqs)) {

                // Fulfil elective depth MC requirement
                if ($electives_depth_mc_taken < $electives_depth_mc_req) {
                    $mods[$mod_code] = [PR_TYPE, PE_DEPTH_TYPE];
                    $electives_depth_mc_taken += $mod_credit;
                }

                // Fulfil elective MC requirement
                else if ($electives_mc_taken < $electives_mc_req) {
                    $mods[$mod_code] = [PR_TYPE, PE_TYPE];
                    $electives_mc_taken += $mod_credit;
                }

                // Dump into UE
                else {
                    $mods[$mod_code] = [UE_TYPE,UE_TYPE];
                }

            }

            // Set type for elective breadth modules
            else if  (array_key_exists($mod_code, $elective_breadth_reqs)) {

                // Fulfil elective MC requirement
                // Avoid blocking elective depth requirement
                if ($electives_mc_taken + ($electives_depth_mc_req - $electives_depth_mc_taken) < $electives_mc_req) {
                    $mods[$mod_code] = [PR_TYPE, PE_TYPE];
                    $electives_mc_taken += $mod_credit;
                }

                // Dump into UE
                else {
                    $mods[$mod_code] = [UE_TYPE,UE_TYPE];
                }

            }

            // Dump into UE
            else {
                $mods[$mod_code] = [UE_TYPE,UE_TYPE];
            }
        }
    }

    return $mods;
}
