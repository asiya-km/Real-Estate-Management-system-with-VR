<?php
session_start();

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if user has admin role
function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

// Function to check if user has manager role
function isManager() {
    return isLoggedIn() && $_SESSION['role'] === 'manager';
}

// Function to check access permission
function checkPermission($requiredRole = 'admin') {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
    
    if ($requiredRole === 'admin' && !isAdmin()) {
        header("Location: access-denied.php");
        exit;
    }
    
    if ($requiredRole === 'manager' && !isAdmin() && !isManager()) {
        header("Location: access-denied.php");
        exit;
    }
}
?>