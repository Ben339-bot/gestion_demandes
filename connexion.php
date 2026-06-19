<?php
// ================================================
// Projet : Gestion des demandes étudiantes
// Fichier : connexion.php
// Auteur  : TRAORE Mohamed
// Partagé avec tout le groupe — NE PAS MODIFIER
// ================================================

$host     = "mysql-gestiondemandes.alwaysdata.net";
$dbname   = "gestiondemandes_db";
$user     = "gestiondemandes";
$password = "2HSwkWcY_uw";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
