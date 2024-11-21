# microMEGA

**microMEGA** è un micro-framework PHP leggero e semplice, progettato per sviluppare applicazioni web in modo rapido ed efficiente. Offre funzionalità essenziali senza la complessità dei framework più pesanti.

## Caratteristiche Principali

### Routing avanzato

- **Supporto per tutti i metodi HTTP:** `GET`, `POST`, `PUT`, `DELETE`, ecc.
- **Definizione di rotte con parametri dinamici:**
   ```php
  $router->add('GET', '/users/{id}', 'callbackFunction');
- **Middleware semplificato**
  - Aggiunta di middleware globali o specifi per route.
  - Esempio di utilizzo:
    ```php
    $router->add('GET', '/dashboard', 'dashboardFunction', ['authMiddleware']);

### Templating base

- **Separazione della logica dalla presentazione:**
  - Utilizzo di un layout principale (`layout.php`).
  - Viste che estendono il layout e inseriscono contenuto dinamico.
- **Funzione `view`**:
  - Renderizza una vista passando dati:
    ```php
    view('home', ['message' => 'Benvenuto in microMEGA!']);

### Gestione del database base

- **Connessione al database MySQL tramite PDO.**
- **Esecuzione di query SQL semplici:**
  ```php
  $db = DB::getInstance($config);
  $stmt = $db->query('SELECT * FROM users');
  $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
- **Cache semplice per le query:**
  ```php
  $users = DB::cachedQuery('SELECT * FROM users');

### Validazione dei dati

- **Funzione `validate`:**
  - Valida gli input degli utenti secondo regole specifiche.
  - Regole disponibili: `required`, `email`.
  - Esempio:
    ```php
    $errors = validate($_POST, [
        'name' => 'required',
        'email' => 'email'
    ]);

### Middleware semplice

- **Definizione di middlware personalizzati:**
  ```php
  function authMiddleware() {
      if (!isset($_SESSION['user'])) {
          redirect('/login');
          return false;
      }
      return true;
  }
- **Utilizzo nei percorsi:**
  ```php
  $router->add('GET', '/admin', 'adminFunction', ['authMiddleware']);

### Cache semplice

- **Memorizzazione dei risultati delle query per migliorare le prestazioni.**
- **Invalidazione manuale della cache quando necessario:**
  ```php
  @unlink(__DIR__ . '/cache/' . md5('SELECT * FROM users') . '.cache');

### Funzione helper

- `view($template, $data = [])`: Renderizza una vista con i dati forniti.
- `validate($data, $rules)`: Valida i dati in base alle regole specificate.
- `redirect($url)`: Reindirizza a un'altra URL.
- `DB::getInstance($config)`: Ottiene l'istanza singleton del database.
- `DB::cachedQuery($sql, $params = [], $cacheTime = 60)`: Esegue una query con cache opzionale.
