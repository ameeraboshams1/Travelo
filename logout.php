<?php
session_start();

// نمسح كل بيانات السيشن
session_unset();
session_destroy();

// نمنع أي كاش للصفحة بعد الخروج
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// نرجّعه للهوم أو صفحة اللوجين
header("Location: index.php");
exit;
