<?php
session_start();
session_unset();
session_destroy();

// Correct path from /library/ to /library/pages/index.php
header("Location: pages/index.php");
exit();
