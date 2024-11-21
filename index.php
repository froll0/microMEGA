<?php
/**
 * microMEGA - Un micro-framework PHP in un singolo file
 */

// --------------------
// Configurazione
// --------------------
$config = [
    'db_host' => 'localhost',
    'db_name' => 'example',
    'db_user' => 'root',
    'db_password' => '',
];

// --------------------
// Funzioni di Aiuto
// --------------------

// Autoloader semplice
spl_autoload_register(function ($class) {
    if (file_exists("$class.php")) {
        require "$class.php";
    }
});

// Funzione per la vista con supporto per template engine semplice
function view($template, $data = [])
{
    extract($data);
    $templateFile = __DIR__ . "/views/{$template}.php";
    if (file_exists($templateFile)) {
        ob_start();
        include $templateFile;
        $content = ob_get_clean();
        include __DIR__ . '/views/layout.php';
    } else {
        throw new Exception("Vista {$template} non trovata");
    }
}

// Funzione per la validazione dei dati
function validate($data, $rules)
{
    $errors = [];
    foreach ($rules as $field => $rule) {
        $value = trim($data[$field] ?? '');
        if ($rule === 'required' && empty($value)) {
            $errors[$field] = "Il campo {$field} è obbligatorio.";
        } elseif ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $errors[$field] = "Il campo {$field} deve essere un'email valida.";
        }
        // Aggiungi altre regole di validazione se necessario
    }
    return $errors;
}

// Funzione per il redirect
function redirect($url)
{
    header("Location: {$url}");
    exit;
}

// --------------------
// Gestione delle Sessioni
// --------------------
session_start();

// --------------------
// Classe per la connessione al database con cache semplice
// --------------------
class DB
{
    protected static $instance = null;

    public static function getInstance($config)
    {
        if (self::$instance === null) {
            $dsn = 'mysql:host=' . $config['db_host'] . ';dbname=' . $config['db_name'] . ';charset=utf8';
            try {
                self::$instance = new PDO($dsn, $config['db_user'], $config['db_password']);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die('Errore di connessione al database: ' . $e->getMessage());
            }
        }
        return self::$instance;
    }

    // Funzione per ottenere i risultati con cache
    public static function cachedQuery($sql, $params = [], $cacheTime = 60)
    {
        $cacheKey = md5($sql . serialize($params));
        $cacheFile = __DIR__ . "/cache/{$cacheKey}.cache";
        if (file_exists($cacheFile) && (filemtime($cacheFile) + $cacheTime) > time()) {
            $result = unserialize(file_get_contents($cacheFile));
        } else {
            $stmt = self::getInstance($GLOBALS['config'])->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!is_dir(__DIR__ . '/cache')) {
                mkdir(__DIR__ . '/cache');
            }
            file_put_contents($cacheFile, serialize($result));
        }
        return $result;
    }
}

// --------------------
// Classe Router con Middleware Semplice
// --------------------
class Router
{
    private $routes = [];
    private $middleware = [];

    public function add($method, $route, $callback, $middleware = [])
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'route' => $route,
            'callback' => $callback,
            'middleware' => $middleware
        ];
    }

    public function middleware($middleware)
    {
        $this->middleware = $middleware;
    }

    public function dispatch($requestUri, $requestMethod)
    {
        $url = parse_url($requestUri, PHP_URL_PATH);
        foreach ($this->routes as $route) {
            $pattern = preg_replace('/\//', '\\/', $route['route']);
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[a-zA-Z0-9_-]+)', $pattern);
            $pattern = '/^' . $pattern . '$/';
            if (preg_match($pattern, $url, $matches) && $route['method'] == $requestMethod) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                // Esegui middleware globali
                foreach ($this->middleware as $middleware) {
                    if (!$middleware()) {
                        return;
                    }
                }
                // Esegui middleware della rotta
                foreach ($route['middleware'] as $middleware) {
                    if (!$middleware()) {
                        return;
                    }
                }
                try {
                    return call_user_func_array($route['callback'], $params);
                } catch (Exception $e) {
                    $this->handleError($e);
                }
            }
        }
        http_response_code(404);
        echo '404 Not Found';
    }

    private function handleError($e)
    {
        http_response_code(500);
        echo 'Errore: ' . $e->getMessage();
    }
}

// --------------------
// Middleware Esempio
// --------------------
function authMiddleware()
{
    if (!isset($_SESSION['user'])) {
        redirect('/login');
        return false;
    }
    return true;
}

// --------------------
// Inizializzazione del Router
// --------------------
$router = new Router();

// --------------------
// Definizione delle Rotte con Supporto per Altri Metodi HTTP
// --------------------

// Rotta Home (GET)
$router->add('GET', '/', function () {
    view('home', ['message' => 'Benvenuto in microMEGA!']);
});

// Rotta per visualizzare gli utenti (GET)
$router->add('GET', '/users', function () use ($config) {
    $users = DB::cachedQuery('SELECT * FROM users');
    view('users', ['users' => $users]);
});

// Rotta per visualizzare un utente specifico (GET)
$router->add('GET', '/users/{id}', function ($id) use ($config) {
    $user = DB::cachedQuery('SELECT * FROM users WHERE id = :id', ['id' => $id]);
    if ($user) {
        view('user', ['user' => $user[0]]);
    } else {
        http_response_code(404);
        echo 'Utente non trovato';
    }
});

// Rotta per creare un nuovo utente (GET e POST)
$router->add('GET', '/users/create', function () {
    view('create_user');
}, ['authMiddleware']);

$router->add('POST', '/users/create', function () use ($config) {
    $errors = validate($_POST, [
        'name' => 'required',
        'email' => 'email'
    ]);

    if (empty($errors)) {
        $db = DB::getInstance($config);
        $stmt = $db->prepare('INSERT INTO users (name, email) VALUES (:name, :email)');
        $stmt->execute([
            'name' => $_POST['name'],
            'email' => $_POST['email']
        ]);
        // Invalida la cache degli utenti
        @unlink(__DIR__ . '/cache/' . md5('SELECT * FROM users') . '.cache');
        redirect('/users');
    } else {
        view('create_user', ['errors' => $errors]);
    }
}, ['authMiddleware']);

// Rotta per il login (GET e POST)
$router->add('GET', '/login', function () {
    view('login');
});

$router->add('POST', '/login', function () use ($config) {
    $errors = validate($_POST, [
        'username' => 'required',
        'password' => 'required'
    ]);

    if (empty($errors)) {
        // Autenticazione semplice (da migliorare in produzione)
        if ($_POST['username'] === 'admin' && $_POST['password'] === 'password') {
            $_SESSION['user'] = 'admin';
            redirect('/');
        } else {
            $errors['login'] = 'Credenziali non valide.';
            view('login', ['errors' => $errors]);
        }
    } else {
        view('login', ['errors' => $errors]);
    }
});

// Rotta per il logout (GET)
$router->add('GET', '/logout', function () {
    session_destroy();
    redirect('/');
});

// --------------------
// Esecuzione del Router
// --------------------
$router->middleware([]); // Aggiungi middleware globali qui
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
