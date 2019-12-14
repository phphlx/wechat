<?php

return new PDO("mysql:host=localhost;dbname=mp.phphlx.com",
    'homestead',
    'secret',
    [
        PDO::ERRMODE_EXCEPTION,
        PDO::FETCH_ASSOC
    ]
);
