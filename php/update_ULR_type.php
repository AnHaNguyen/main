<?php

/*
 * See get_type.php for documentation.
 */

// ULR (AY < 15-16) subtypes
define("GEM_A_TYPE", "GEM_A");
define("GEM_B_TYPE", "GEM_B");
define("SS_TYPE", "SS");
define("BREADTH_TYPE", "BREADTH");

// ULR (AY >= 15-16) subtypes
define("GEH_TYPE", "GEH");
define("GEQ_TYPE", "GEQ");
define("GER_TYPE", "GER");
define("GES_TYPE", "GES");
define("GET_TYPE", "GET");


function update_ULR_type($adm_year, $mods, $is_ceg_major) {

    // Old ULR system, prior to 15-16 batch
    if ($adm_year < "1516") {

        $has_taken_GEM_A = false;
        $has_taken_GEM_B = false;
        $has_taken_SS = false;
        $num_breadth_taken = 0;
        define("NUM_BREADTH_REQ", 2);
        define("SOC_MODS_REGEX", "/BT|CG|CP|CS|FMC|IS|IT|XFC/");
        define("FOE_MODS_REGEX", "/BN|CE|CN|EE|EG|ESE|ESP|FME|HR|IE|ME|MLE|MST|MT|OT|SDM|SE|SH|TC|TE|TG|TM|TP|UIS|XFE/");
        $ulr_foe_mods = [
            "GEK1523", // Bachelor Of Technology Programme
            "GEK2505", // Biomedical Engineering
            "GEM1915", // Chemical & Biomolecular Engineering
            "GET1011", // Chemical & Biomolecular Engineering
            "GEK1522", // Civil & Environmental Engineering
            "GES1017", // Division Of Engineering And Tech Mgt
            "SSE1201", // Division Of Engineering And Tech Mgt
            "GEK1501", // Electrical & Computer Engineering
            "GEK1513", // Electrical & Computer Engineering
            "GEH1057", // Materials Science And Engineering
        ];

        foreach ($mods as $mod_code => $mod_type) {

            $is_soc_mod = preg_match(SOC_MODS_REGEX, $mod_code);
            $is_foe_mod = preg_match(FOE_MODS_REGEX, $mod_code);

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

                    // Avoid setting FoE modules as breadth if student's major is CEG
                    else if ($num_breadth_taken < NUM_BREADTH_REQ
                        && !($is_ceg_major && array_key_exists($mod_code, $ulr_foe_mods))) {

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

                    // Avoid setting FoE modules as breadth if student's major is CEG
                    else if ($num_breadth_taken < NUM_BREADTH_REQ
                        && !($is_ceg_major && array_key_exists($mod_code, $ulr_foe_mods))) {

                        $mods[$mod_code] = [ULR_TYPE,BREADTH_TYPE];
                        $num_breadth_taken++;
                    }

                    else {
                        $mods[$mod_code] = [UE_TYPE,UE_TYPE];
                    }
                }
            }

            // Set type for SS
            else if (strpos($mod_code, "SS") === 0) {
                if (!$has_taken_SS) {
                    $mods[$mod_code] = [ULR_TYPE,SS_TYPE];
                }

                // Avoid setting FoE modules as breadth if student's major is CEG
                else if ($num_breadth_taken < NUM_BREADTH_REQ
                    && !($is_ceg_major && array_key_exists($mod_code, $ulr_foe_mods))) {

                    $mods[$mod_code] = [ULR_TYPE,BREADTH_TYPE];
                    $num_breadth_taken++;
                }

                else {
                    $mods[$mod_code] = [UE_TYPE,UE_TYPE];
                }
            }

            // Set type for breadth
            // Avoid setting FoE modules as breadth if student's major is CEG
            else if (!$is_soc_mod
                && $num_breadth_taken < NUM_BREADTH_REQ
                && !($is_ceg_major && $is_foe_mod)
                && !($is_ceg_major && array_key_exists($mod_code, $ulr_foe_mods))) {

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
