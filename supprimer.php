<?php
require_once __DIR__ . '/functions.php';
exiger_connexion();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$liste = lire_etudiants();
if ($id > 0 && isset($liste[$id])) {
    $photo = (string) ($liste[$id]['photo'] ?? '');
    unset($liste[$id]);
    sauver_etudiants($liste);
    if ($photo !== '') {
        supprimer_photo($photo);
    }
    $_SESSION['flash'] = 'L’étudiant a été supprimé.';
} else {
    $_SESSION['flash'] = 'Étudiant introuvable.';
}

header('Location: liste.php');
exit;