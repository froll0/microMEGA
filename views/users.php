<h1>Lista degli utenti</h1>
<ul>
    <?php foreach ($users as $user): ?>
        <li>
            <a href="/users/<?php echo htmlspecialchars($user['id']); ?>">
                <?php echo htmlspecialchars($user['name']); ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>
<p><a href="/users/create">Crea nuovo utente</a></p>
<p><a href="/">Torna alla home</a></p>
