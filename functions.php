<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('FICHIER', __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'etudiants.txt');
define('FILIERES', ['GI', 'IDIA', 'GE', 'GC']);
define('UTILISATEURS', [
    'admin' => '1234',
    'prof' => 'ensa2026',
]);

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function connecte(): bool {
    return isset($_SESSION['user']);
}

function exiger_connexion(): void {
    if (!connecte()) {
        header('Location: index.php');
        exit;
    }
}

function nettoyer(string $s): string {
    return trim(str_replace([';', "\r", "\n"], '', $s));
}

function lire_etudiants(): array {
    if (!file_exists(FICHIER)) {
        return [];
    }

    $lignes = file(FICHIER, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lignes === false) {
        throw new RuntimeException('Impossible de lire le fichier des étudiants.');
    }

    $etudiants = [];

    foreach ($lignes as $ligne) {
        $parts = explode(';', $ligne);
        if (count($parts) < 5 || !is_numeric(trim($parts[0]))) {
            continue;
        }

        $id = (int) trim($parts[0]);
        if ($id < 1) {
            continue;
        }

        $etudiants[$id] = [
            'id' => $id,
            'nom' => trim($parts[1]),
            'prenom' => trim($parts[2]),
            'filiere' => trim($parts[3]),
            'note' => (float) trim($parts[4]),
            'photo' => isset($parts[5]) ? trim($parts[5]) : '',
        ];
    }

    return $etudiants;
}

function sauver_etudiants(array $liste): void {
    $lignes = [];

    foreach ($liste as $etu) {
        $lignes[] = implode(';', [
            (int) $etu['id'],
            nettoyer((string) $etu['nom']),
            nettoyer((string) $etu['prenom']),
            nettoyer((string) $etu['filiere']),
            (string) $etu['note'],
            nettoyer((string) ($etu['photo'] ?? '')),
        ]);
    }

    $contenu = empty($lignes) ? '' : implode(PHP_EOL, $lignes) . PHP_EOL;
    if (file_put_contents(FICHIER, $contenu, LOCK_EX) === false) {
        throw new RuntimeException('Impossible d’enregistrer le fichier des étudiants.');
    }
}

function dossier_avatars(): string {
    $dossier = __DIR__ . DIRECTORY_SEPARATOR . 'avatars';
    if (!is_dir($dossier) && !mkdir($dossier, 0777, true) && !is_dir($dossier)) {
        throw new RuntimeException('Impossible de créer le dossier des avatars des étudiants.');
    }

    return $dossier;
}

function chemin_photo_public(string $photo): string {
    $nom = basename($photo);
    if ($nom === '') {
        return '';
    }

    $chemins = [
        __DIR__ . DIRECTORY_SEPARATOR . 'avatars' . DIRECTORY_SEPARATOR . $nom,
        __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $nom,
    ];

    foreach ($chemins as $chemin) {
        if (is_file($chemin)) {
            $prefixe = strpos($chemin, 'avatars' . DIRECTORY_SEPARATOR) !== false ? 'avatars' : 'uploads';
            return $prefixe . '/' . $nom;
        }
    }

    return 'avatars/' . $nom;
}

function enregistrer_photo(array $fichier): string {
    if (!isset($fichier['error']) || is_array($fichier['error'])) {
        throw new RuntimeException('Le fichier envoyé est invalide.');
    }
    if ($fichier['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Une erreur est survenue pendant l’envoi de la photo.');
    }
    if ($fichier['size'] > 1024 * 1024) {
        throw new RuntimeException('La photo ne doit pas dépasser 1 Mo.');
    }

    $extension = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
        throw new RuntimeException('La photo doit être au format JPG, JPEG ou PNG.');
    }

    $image = getimagesize($fichier['tmp_name']);
    $typesAutorises = [IMAGETYPE_JPEG, IMAGETYPE_PNG];
    if ($image === false || !in_array($image[2], $typesAutorises, true)) {
        throw new RuntimeException('Le fichier envoyé n’est pas une image JPG ou PNG valide.');
    }

    $nom = uniqid('etu_', true) . '.' . $extension;
    $destination = dossier_avatars() . DIRECTORY_SEPARATOR . $nom;
    if (!move_uploaded_file($fichier['tmp_name'], $destination)) {
        throw new RuntimeException('Impossible d’enregistrer la photo envoyée.');
    }

    return $nom;
}

function supprimer_photo(string $photo): void {
    $nom = basename($photo);
    if ($nom === '' || $nom !== $photo) {
        return;
    }

    $chemins = [
        __DIR__ . DIRECTORY_SEPARATOR . 'avatars' . DIRECTORY_SEPARATOR . $nom,
        __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $nom,
    ];

    foreach ($chemins as $chemin) {
        if (is_file($chemin) && !unlink($chemin)) {
            throw new RuntimeException('Impossible de supprimer la photo de l’étudiant.');
        }
    }
}