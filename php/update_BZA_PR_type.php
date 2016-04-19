<?php

/*
 * See get_type.php for documentation.
 */

// PR subtypes
define("LIST_A_TYPE", "LIST_A");
define("LIST_B_TYPE", "LIST_B");


function update_BZA_PR_type($adm_year, $mods) {
    $all_mod_info_string = file_get_contents('../data/modules_min.json');
    $grad_reqs_string = file_get_contents('../req/BZA/' . $adm_year . '.json');
    $elective_reqs_string = file_get_contents('../req/BZA/elective.json');

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

    return $mods;
}