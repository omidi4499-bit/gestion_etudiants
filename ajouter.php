<?php
require_once __DIR__ . '/functions.php';
exiger_connexion();

$liste = lire_etudiants();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$modification = $id > 0;
if ($modification && !isset($liste[$id])) {
    $_SESSION['flash'] = 'Étudiant introuvable.';
    header('Location: liste.php');
    exit;
}

$etudiant = $modification
    ? $liste[$id]
    : ['nom' => '', 'prenom' => '', 'filiere' => '', 'note' => '', 'photo' => ''];
$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $etudiant['nom'] = nettoyer((string) ($_POST['nom'] ?? ''));
    $etudiant['prenom'] = nettoyer((string) ($_POST['prenom'] ?? ''));
    $etudiant['filiere'] = (string) ($_POST['filiere'] ?? '');
    $noteSaisie = trim((string) ($_POST['note'] ?? ''));
    $etudiant['note'] = $noteSaisie;

    if ($etudiant['nom'] === '') {
        $erreurs[] = 'Le nom est obligatoire.';
    }
    if ($etudiant['prenom'] === '') {
        $erreurs[] = 'Le prénom est obligatoire.';
    }
    if (!in_array($etudiant['filiere'], FILIERES, true)) {
        $erreurs[] = 'Veuillez choisir une filière valide.';
    }
    if (!is_numeric($noteSaisie) || (float) $noteSaisie < 0 || (float) $noteSaisie > 20) {
        $erreurs[] = 'La note doit être un nombre compris entre 0 et 20.';
    }

    $photoNouvelle = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        try {
            $photoNouvelle = enregistrer_photo($_FILES['photo']);
        } catch (RuntimeException $exception) {
            $erreurs[] = $exception->getMessage();
        }
    }

    if (empty($erreurs)) {
        $anciennePhoto = (string) ($etudiant['photo'] ?? '');
        if ($modification) {
            $liste[$id] = [
                'id' => $id,
                'nom' => $etudiant['nom'],
                'prenom' => $etudiant['prenom'],
                'filiere' => $etudiant['filiere'],
                'note' => (float) $noteSaisie,
                'photo' => $photoNouvelle !== '' ? $photoNouvelle : $anciennePhoto,
            ];
        } else {
            $ids = array_keys($liste);
            $nouvelId = empty($ids) ? 1 : max($ids) + 1;
            $liste[$nouvelId] = [
                'id' => $nouvelId,
                'nom' => $etudiant['nom'],
                'prenom' => $etudiant['prenom'],
                'filiere' => $etudiant['filiere'],
                'note' => (float) $noteSaisie,
                'photo' => $photoNouvelle,
            ];
        }

        try {
            sauver_etudiants($liste);
            if ($modification && $photoNouvelle !== '' && $anciennePhoto !== '') {
                supprimer_photo($anciennePhoto);
            }
        } catch (RuntimeException $exception) {
            if ($photoNouvelle !== '') {
                supprimer_photo($photoNouvelle);
            }
            throw $exception;
        }

        $_SESSION['flash'] = $modification ? 'Les informations de l’étudiant ont été modifiées.' : 'L’étudiant a été ajouté.';
        header('Location: liste.php');
        exit;
    }

    if ($photoNouvelle !== '') {
        supprimer_photo($photoNouvelle);
    }
}

$titre = $modification ? 'Modifier un étudiant' : 'Ajouter un étudiant';
include __DIR__ . '/header.php';
?>
<section class="card form-card">
    <h2><?= h($titre) ?></h2>
    <?php if (!empty($erreurs)): ?>
        <div class="message error" role="alert">
            <p>Veuillez corriger les erreurs suivantes :</p>
            <ul>
                <?php foreach ($erreurs as $erreur): ?>
                    <li><?= h($erreur) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" action="ajouter.php<?= $modification ? '?id=' . $id : '' ?>">
        <label for="nom">Nom</label>
        <input type="text" id="nom" name="nom" maxlength="100" value="<?= h((string) $etudiant['nom']) ?>" required>

        <label for="prenom">Prénom</label>
        <input type="text" id="prenom" name="prenom" maxlength="100" value="<?= h((string) $etudiant['prenom']) ?>" required>

        <label for="filiere">Filière</label>
        <select id="filiere" name="filiere" required>
            <option value="">Choisir une filière</option>
            <?php foreach (FILIERES as $filiere): ?>
                <option value="<?= h($filiere) ?>" <?= $etudiant['filiere'] === $filiere ? 'selected' : '' ?>><?= h($filiere) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="note">Note (sur 20)</label>
        <input type="number" id="note" name="note" min="0" max="20" step="any" value="<?= h((string) $etudiant['note']) ?>" required>

        <label for="photo">Photo (JPG ou PNG, 1 Mo maximum)</label>
        <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
        <?php if ($modification && $etudiant['photo'] !== ''): ?>
            <p class="current-photo">Photo actuelle : <img class="student-photo" src="<?= h(chemin_photo_public((string) $etudiant['photo'])) ?>" alt="Photo actuelle"></p>
        <?php endif; ?>

        <div class="form-actions">
            <button type="submit"><?= $modification ? 'Enregistrer les modifications' : 'Ajouter l’étudiant' ?></button>
            <a class="secondary-link" href="liste.php">Annuler</a>
        </div>
    </form>
</section>
<?php include __DIR__ . '/footer.php'; ?>