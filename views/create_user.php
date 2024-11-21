<h1>Crea un nuovo utente</h1>
<?php if (!empty($errors)): ?>
    <ul>
        <?php foreach ($errors as $error): ?>
            <li><?php echo htmlspecialchars($error); ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
<form method="POST" action="/users/create">
    <label for="name">Nome:</label><br>
    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"><br><br>
    <label for="email">Email:</label><br>
    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"><br><br>
    <input type="submit" value="Crea Utente">
</form>
<p><a href="/users">Torna alla lista utenti</a></p>
