<?php

function calculate_progression_percentage($starting_weight, $current_weight, $goal_weight) {

    if ($starting_weight === null || $current_weight === null || $goal_weight === null ||
        !is_numeric($starting_weight) || !is_numeric($current_weight) || !is_numeric($goal_weight)) {
        return null;
    }


    $starting_weight = (float)$starting_weight;
    $current_weight = (float)$current_weight;
    $goal_weight = (float)$goal_weight;


    if ($starting_weight == $goal_weight) {
 
        return ($current_weight == $goal_weight) ? 100.0 : 0.0;
    }

 
    $total_change_needed = $goal_weight - $starting_weight;
    $change_achieved = $current_weight - $starting_weight;

    if ($total_change_needed == 0) { 
        return ($current_weight == $goal_weight) ? 100.0 : 0.0;
    }

    $raw_progression = ($change_achieved / $total_change_needed) * 100.0;


    $progression = max(0.0, min(100.0, $raw_progression));

    return round($progression, 2);
}
?>