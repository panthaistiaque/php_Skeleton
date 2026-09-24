<?php
$isEdit = true;
$minPasswordLength = (int)setting('security.min_password_length', 8);
require VIEW_PATH . '/users/_form.php';