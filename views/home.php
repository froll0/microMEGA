<h1><?php echo htmlspecialchars($message); ?></h1>
<?php if (isset($_SESSION['user'])): ?>
    <p>Benvenuto, <?php echo htmlspecialchars($_SESSION['user']); ?>! <a href="/logout">Logout</a></p>
<?php else: ?>
    <p><a href="/login">Login</a></p>
<?php endif; ?>
<p><a href="/users">Visualizza utenti</a></p>
