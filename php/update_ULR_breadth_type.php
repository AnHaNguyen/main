<?php

/*
 * See get_type.php for documentation.
 */

// ULR (AY < 15-16) subtypes
define("BREADTH_TYPE", "BREADTH");


function update_ULR_breadth_type($adm_year, $mods, $is_ceg_major) {

    // Old ULR system, prior to 15-16 batch
    if ($adm_year < "1516") {

        $all_mod_info_string = file_get_contents('../data/simplified.json');
        $all_mod_info = json_decode($all_mod_info_string, true);

        $breadth_mc__req = 8;
        $breadth_mc_taken = 0;

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
            $mod_credit = $all_mod_info[$mod_code]["ModuleCredit"];

            // Set type for breadth, using modules previously typed as UE
            // Avoid setting 0 MC modules as breadth
            // Avoid setting SoC modules as breadth for SoC students
            // Avoid setting FoE modules as breadth if student's major is CEG
            if ($breadth_mc_taken < $breadth_mc__req
                && $mod_type[0] === UE_TYPE
                && $mod_credit !== 0
                && !$is_soc_mod
                && !($is_ceg_major && $is_foe_mod)
                && !($is_ceg_major && array_key_exists($mod_code, $ulr_foe_mods))) {

                $mods[$mod_code] = [ULR_TYPE,BREADTH_TYPE];
                $breadth_mc_taken += $mod_credit;
            }
        }
    }

    return $mods;
}
