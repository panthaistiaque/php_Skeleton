<?php
$minPasswordLength = (int)setting('security.min_password_length', 8);
$departments = $departments ?? [];
$designations = $designations ?? [];
$roles = $roles ?? [];
require VIEW_PATH . '/users/_form.php';