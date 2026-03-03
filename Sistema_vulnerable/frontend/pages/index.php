<?php
session_start();
if (!empty($_SESSION['logueado'])) {
    header('Location: frontend/pages/dashboard.php');
} else {
    header('Location: frontend/pages/login.php');
}
exit;
