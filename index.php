<?php
/**
 * BukoJuice - Main Entry Point
 * 
 * A modern money tracking application
 * Built with PHP, Tailwind CSS, JavaScript
 */

session_start();

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    // Redirect to dashboard
    header('Location: pages/dashboard/');
} else {
    // Redirect to login
    header('Location: pages/login/');
}
exit;
