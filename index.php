<?php
require_once __DIR__ . '/functions.php';

if (connecte()) {
    header('Location: liste.php');
    exit;
}

$erreur = '';
$login = $_COOKIE['dernier_user'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim((string) ($_POST['login'] ?? ''));
    $mdp = (string) ($_POST['mdp'] ?? '');

    if (isset(UTILISATEURS[$login]) && hash_equals(UTILISATEURS[$login], $mdp)) {
        session_regenerate_id(true);
        $_SESSION['user'] = $login;
        setcookie('dernier_user', $login, time() + 7 * 24 * 60 * 60, '/');
        header('Location: liste.php');
        exit;
    }

    $erreur = 'Identifiant ou mot de passe incorrect.';
}

$titre = 'Connexion';
include __DIR__ . '/header.php';
?>
<section class="card auth-card">
    <h2>Connexion</h2>
    <?php if ($erreur !== ''): ?>
        <p class="message error"><?= h($erreur) ?></p>
    <?php endif; ?>
    <form method="post" action="index.php">
        <label for="login">Identifiant</label>
        <input type="text" id="login" name="login" value="<?= h($login) ?>" required autofocus>

        <label for="mdp">Mot de passe</label>
        <input type="password" id="mdp" name="mdp" required>

        <button type="submit">Se connecter</button>
    </form>
</section>
<?php include __DIR__ . '/footer.php'; ?>