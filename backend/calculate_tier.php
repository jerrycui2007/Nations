<?php

function calculateTier($population) {
    if ($population >= 75000000) {
        return 10;
    } elseif ($population >= 18000000) {
        return 9;
    } elseif ($population >= 10000000) {
        return 8;
    } elseif ($population >= 5000000) {
        return 7;
    } elseif ($population >= 2000000) {
        return 6;
    } elseif ($population >= 1000000) {
        return 5;
    } elseif ($population >= 500000) {
        return 4;
    } elseif ($population >= 250000) {
        return 3;
    } elseif ($population >= 75000) {
        return 2;
    } else {
        return 1;
    }
}
