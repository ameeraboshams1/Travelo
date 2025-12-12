<?php
// auth.php

// نضمن إن السيشن شغالة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// نمنع الكاش على الصفحات المحمية
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// دالة تلزم اليوزر يكون مسجّل دخول
function require_login($role = null) {
    if (empty($_SESSION['user_id'])) {
        // مش مسجّل دخول → رجّعه على صفحة اللوجين/الهوم
        header("Location: login.html"); // عدّلي المسار إذا عندك صفحة لوجين ثانية
        exit;
    }

    // لو بدنا نتحقق من دور معيّن (user/admin)
    if ($role !== null) {
        $currentRole = $_SESSION['role'] ?? null;
        if ($currentRole !== $role) {
            // مش من هالنوع → رجّعيه للهوم مثلاً
            header("Location: index.php");
            exit;
        }
    }
}
