<?php
date_default_timezone_set("Asia/Kolkata");
session_start();

$conn = new mysqli("localhost", "root", "", "study_analyzer1");

if ($conn->connect_error) {
    die("Database connection failed");
}
