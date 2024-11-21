<h1>Login</h1>
<?php if (!empty($errors['login'])): ?>
    <p><?php echo htmlspecialchars($errors['login']); ?></p>
<?php endif; ?>
<form method="POST" action="/login">
    <label for="username">Username:</label><br>
    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"><br><br>
    <label for="password">Password:</label><br>
    <input type="password" id="password" name="password"><br><br>
    <input type="submit" value="Accedi">
</form>
<p><a href="/">Torna alla home</a></p>
