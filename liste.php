<?php
require_once __DIR__ . '/functions.php';
exiger_connexion();

$etudiants = array_values(lire_etudiants());
$q = trim((string) ($_GET['q'] ?? ''));
$tri = (string) ($_GET['tri'] ?? 'ajout');

if ($q !== '') {
    $etudiants = array_values(array_filter($etudiants, function (array $etudiant) use ($q): bool {
        return stripos($etudiant['nom'], $q) !== false || stripos($etudiant['prenom'], $q) !== false;
    }));
}

if (in_array($tri, ['nom', 'filiere', 'note'], true)) {
    usort($etudiants, function (array $a, array $b) use ($tri): int {
        if ($tri === 'nom') {
            return strcasecmp($a['nom'], $b['nom']);
        }
        if ($tri === 'filiere') {
            return strcasecmp($a['filiere'], $b['filiere']);
        }
        return $b['note'] <=> $a['note'];
    });
} else {
    $tri = 'ajout';
}

$nombre = count($etudiants);
$moyenne = $nombre > 0
    ? number_format(array_sum(array_column($etudiants, 'note')) / $nombre, 2, ',', ' ')
    : '—';
$meilleureNote = $nombre > 0 ? number_format((float) max(array_column($etudiants, 'note')), 2, ',', ' ') : '—';
$message = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

$titre = 'Liste des étudiants';
include __DIR__ . '/header.php';
?>
<section class="page-heading">
    <div>
        <h2>Liste des étudiants</h2>
        <p>Consultez et gérez les étudiants enregistrés.</p>
    </div>
    <a class="button-link" href="ajouter.php">Ajouter un étudiant</a>
</section>

<?php if ($message !== ''): ?>
    <p class="message success"><?= h($message) ?></p>
<?php endif; ?>

<form class="search-form card" method="get" action="liste.php">
    <div class="field">
        <label for="q">Recherche (nom ou prénom)</label>
        <input type="search" id="q" name="q" value="<?= h($q) ?>" placeholder="Saisir un nom ou prénom">
    </div>
    <div class="field">
        <label for="tri">Trier par</label>
        <select id="tri" name="tri">
            <option value="ajout" <?= $tri === 'ajout' ? 'selected' : '' ?>>Ordre d’ajout</option>
            <option value="nom" <?= $tri === 'nom' ? 'selected' : '' ?>>Nom</option>
            <option value="filiere" <?= $tri === 'filiere' ? 'selected' : '' ?>>Filière</option>
            <option value="note" <?= $tri === 'note' ? 'selected' : '' ?>>Note décroissante</option>
        </select>
    </div>
    <button type="submit">Rechercher</button>
</form>

<section class="statistics" aria-label="Statistiques">
    <div class="stat"><strong><?= $nombre ?></strong><span>étudiant<?= $nombre > 1 ? 's' : '' ?> affiché<?= $nombre > 1 ? 's' : '' ?></span></div>
    <div class="stat"><strong><?= h($moyenne) ?></strong><span>Moyenne</span></div>
    <div class="stat"><strong><?= h($meilleureNote) ?></strong><span>Meilleure note</span></div>
</section>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Photo</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Filière</th>
                <th>Note</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($nombre === 0): ?>
            <tr><td class="empty-state" colspan="6">Aucun étudiant</td></tr>
        <?php else: ?>
            <?php foreach ($etudiants as $etudiant): ?>
                <?php $noteClasse = $etudiant['note'] >= 10 ? 'grade-pass' : 'grade-fail'; ?>
                <tr data-student-row data-search="<?= h($etudiant['nom'] . ' ' . $etudiant['prenom']) ?>">
                    <td>
                        <?php if ($etudiant['photo'] !== ''): ?>
                            <img class="student-photo" src="<?= h(chemin_photo_public((string) $etudiant['photo'])) ?>" alt="Photo de <?= h($etudiant['prenom']) ?>">
                        <?php else: ?>
                            <span class="no-photo" aria-label="Aucune photo">—</span>
                        <?php endif; ?>
                    </td>
                    <td><?= h(strtoupper($etudiant['nom'])) ?></td>
                    <td><?= h(ucfirst(strtolower($etudiant['prenom']))) ?></td>
                    <td><?= h($etudiant['filiere']) ?></td>
                    <td class="<?= $noteClasse ?>"><?= h(number_format((float) $etudiant['note'], 2, ',', ' ')) ?></td>
                    <td class="actions">
                        <a href="ajouter.php?id=<?= (int) $etudiant['id'] ?>">Modifier</a>
                        <a class="delete-link" href="supprimer.php?id=<?= (int) $etudiant['id'] ?>">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <tr class="no-instant-results" hidden><td class="empty-state" colspan="6">Aucun étudiant correspondant</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/footer.php'; ?>