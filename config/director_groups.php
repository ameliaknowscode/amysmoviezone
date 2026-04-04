<?php

/*
|--------------------------------------------------------------------------
| Director Groups
|--------------------------------------------------------------------------
|
| Virtual director entries that combine multiple individuals under a single
| label in the Director Connections feature. Each group needs a unique
| slug-style key, a display name, and the list of individual director names
| as they appear in the database.
|
*/

return [
    'coen-brothers' => [
        'id'      => 'coen-brothers',
        'name'    => 'The Coen Brothers',
        'members' => ['Joel Coen', 'Ethan Coen'],
    ],
];
