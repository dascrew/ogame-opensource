<?php

// Fleet and Defense Parameters.
$UnitParam = [        // structure, shield, attack, cargo capacity, speed, consumption
    GID_F_SC => [ 4000, 10, 5, 5000, 5000, 10 ],
    GID_F_LC => [ 12000, 25, 5, 25000, 7500, 50 ],
    GID_F_LF => [ 4000, 10, 50, 50, 12500, 20 ],
    GID_F_HF => [ 10000, 25, 150, 100, 10000, 75 ],
    GID_F_CRUISER => [ 27000, 50, 400, 800, 15000, 300 ],
    GID_F_BATTLESHIP => [ 60000, 200, 1000, 1500, 10000, 500 ],
    GID_F_COLON => [ 30000, 100, 50, 7500, 2500, 1000 ],
    GID_F_RECYCLER => [ 16000, 10, 1, 20000, 2000, 300 ],
    GID_F_PROBE => [ 1000, 0, 0, 5, 100000000, 1 ],
    GID_F_BOMBER => [ 75000, 500, 1000, 500, 4000, 1000 ],
    GID_F_SAT => [ 2000, 1, 1, 0, 0, 0 ],
    GID_F_DESTRO => [ 110000, 500, 2000, 2000, 5000, 1000 ],
    GID_F_DEATHSTAR => [ 9000000, 50000, 200000, 1000000, 100, 1 ],
    GID_F_BATTLECRUISER => [ 70000, 400, 700, 750, 10000, 250 ],

    GID_D_RL => [ 2000, 20, 80, 0, 0, 0 ],
    GID_D_LL => [ 2000, 25, 100, 0, 0, 0 ],
    GID_D_HL => [ 8000, 100, 250, 0, 0, 0 ],
    GID_D_GAUSS => [ 35000, 200, 1100, 0, 0, 0 ],
    GID_D_ION => [ 8000, 500, 150, 0, 0, 0 ],
    GID_D_PLASMA => [ 100000, 300, 3000, 0, 0, 0 ],
    GID_D_SDOME => [ 20000, 2000, 1, 0, 0, 0 ],
    GID_D_LDOME => [ 100000, 10000, 1, 0, 0, 0 ],

    GID_D_ABM => [ 8000, 1, 1, 0, 0, 0 ],
    GID_D_IPM => [ 15000, 1, 12000, 0, 0, 0 ],
];

// Rapid-fire settings.
$RapidFire = [
    GID_F_SC => [ GID_F_PROBE => 5, GID_F_SAT => 5 ],
    GID_F_LC => [ GID_F_PROBE => 5, GID_F_SAT => 5 ],
    GID_F_LF => [ GID_F_PROBE => 5, GID_F_SAT => 5 ],
    GID_F_HF => [ GID_F_SC => 3, GID_F_PROBE => 5, GID_F_SAT => 5 ],
    GID_F_CRUISER => [ GID_F_LF => 6, GID_F_PROBE => 5, GID_F_SAT => 5, GID_D_RL => 10 ],
    GID_F_BATTLESHIP => [ GID_F_PROBE => 5, GID_F_SAT => 5 ],
    GID_F_COLON => [ GID_F_PROBE => 5, GID_F_SAT => 5 ],
    GID_F_RECYCLER => [ GID_F_PROBE => 5, GID_F_SAT => 5 ],
    GID_F_PROBE => [ ],
    GID_F_BOMBER => [ GID_F_PROBE => 5, GID_F_SAT => 5, GID_D_RL => 20, GID_D_LL => 20, GID_D_HL => 10, GID_D_ION => 10 ],
    GID_F_SAT => [ ],
    GID_F_DESTRO => [ GID_F_PROBE => 5, GID_F_SAT => 5, GID_F_BATTLECRUISER => 2, GID_D_LL => 10 ],
    GID_F_DEATHSTAR => [ GID_F_SC => 250, GID_F_LC => 250, GID_F_LF => 200, GID_F_HF => 100, GID_F_CRUISER => 33, GID_F_BATTLESHIP => 30,
        GID_F_COLON => 250, GID_F_RECYCLER => 250, GID_F_PROBE => 1250, GID_F_BOMBER => 25, GID_F_SAT => 1250, GID_F_DESTRO => 5, GID_F_BATTLECRUISER => 15,
        GID_D_RL => 200, GID_D_LL => 200, GID_D_HL => 100, GID_D_GAUSS => 50, GID_D_ION => 100 ],
    GID_F_BATTLECRUISER => [ GID_F_SC => 3, GID_F_LC => 3, GID_F_HF => 4, GID_F_CRUISER => 4, GID_F_BATTLESHIP => 7, GID_F_PROBE => 5, GID_F_SAT => 5 ],
    // The defense doesn't feature rapid-fire
    GID_D_RL => [ ],
    GID_D_LL => [ ],
    GID_D_HL => [ ],
    GID_D_GAUSS => [ ],
    GID_D_ION => [ ],
    GID_D_PLASMA => [ ],
    GID_D_SDOME => [ ],
    GID_D_LDOME => [ ],
];