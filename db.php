<?php

return new PDO("mysql:host=localhost;dbname=mp.phphlx.com",
    'mp.phphlx.com',
    'duck520zhou',
    [
        PDO::ERRMODE_EXCEPTION,
        PDO::FETCH_ASSOC
    ]
);
