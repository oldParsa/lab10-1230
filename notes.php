<?php

// Create a new session for the user that has arrived

session_start();

// Error reporting in PHP

error_reporting(E_ALL);
ini_set('display_error', "1");

// Connect to the SQL database
$pdo = new PDO('mysql:host=f6561837.gblearn.com;dbname=f6561837_lab8;charset=utf8mb4',
        "f6561837_Parsa",
        "sqlpassword",
        [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
        ]
);

// Sign in and sign out logic
if(isset($_GET["logout"])) {      // Later we will need to redirect to .../notes?logout
    $_SESSION = [];
    session_destroy();
    header('Location: notes.php', true, 303);
    exit;
}

if(isset($_POST["login"])) {
    $stmt = $pdo->prepare("SELECT * FROM users where username = ?");
    $stmt->execute([$_POST["username"]] ?? "");
    $account = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if($account) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $account['id'];
        header('Location: notes.php');
        exit;
    }
    // The if statement was not entered
    $error = "Invalid username or password";
}

$user = null;

// This if statement will be entered if the user was validated
if(isset($_SESSION["user_id"])) {
    $stmt = $pdo->prepare("SELECT id, username, role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION["user_id"]]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC) ? : null;
}

const PERMISSIONS = [
        "employee" => ["create tasks"],
        "manager" => ["create tasks", "edit any task"]
];

function can_access(?object $user, string $action): bool {
    return $user !== null && in_array($action, PERMISSIONS[$user->role] ?? [], true);
}

// Authentication
function requireSignIn(?object $user): object {
    if($user === null) {
        http_response_code(403);
        exit("Please sign in first");
    }
    return $user;
}

// Authorize the user for this particular permission
function requirePermissions(?object $user, string $action): object {
    $user = requireSignIn($user);
    if(!can_access($user, $action)) {
        http_response_code(403);
        exit("You do not have permission for this action based on your user role");
    }
    return $user;
}

// Validate the CSRF token to ensure this request for the resources is nnot coming cross-site
// i.e is it not coming from another website

function csrfToken(): string {
    if(!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// In your form you need an input tag field with your hidden csrf token
function csrfField(): string {
    return "<input type='hidden' name='csrf' value='" . htmlspecialchars(csrfToken()) . "'/>";
}

function requireCsrf(): void {
    $sent = $_POST["csrf"];

    if(!is_string($sent) || !hash_equals(csrfToken(), $sent)) {
        http_response_code(403);
        exit("This form has expired or is originating from a cross-site/another site");
    }
}

// Check ownership of each task before editing it
$task = null;
if($user !== null && isset($_GET['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ?");
    $stmt->execute([$_GET['user_id']]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC) ? : null;

    if(!$task) {
        http_response_code(404);
        exit("This table for this user does not contain any tasks");
    }

    if( (int) $task["user_id"] !== $user["id"] && !can_access($user, "edit any task")) {
        http_response_code(403);
        exit("You do not have permission to edit this task");
    }
}

if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["save"])) {
    requireSignIn($user);
    requireCsrf();

    if($task === null) {
        http_response_code(404);
        exit("Task not found");
    }

    // Validate and verify inputs in a real application
    $stmt = $pdo->prepare("UPDATE tasks SET title = ? WHERE id = ?");
    $stmt->execute([$_POST["title"], $task["id"]]);

    exit;

}

$tasks = [];

if($user !== null) {
    $tasks = $pdo->prepare("SELECT tasks.*, users.username FROM tasks JOIN user ON tasks.");
    // ALERT
    // ALERT
    // ALERT
    // ALERT
    // ALERT
    // ALERT
    // ALERT
    // ALERT
    // ALERT
    // ALERT
    // ALERT
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Title of the document</title>
"</head>
"<body>
<h1>Notes application to manage Tasks</h1>
<?php if(isset($error)): ?>
    <p><?php echo htmlspecialchars($error) ?></p>
<?php endif; ?>
<?php if($user === null): ?>
    <h2>Sign in</h2>
    <form action="notes.php" method="POST">
        <input type="text" name="username" required>
        <input type="password" name="password">
        <button name="login" value="1">Sign in</button>
    </form>
<?php else: ?>
    <p>
        Signed in as:
        <?php echo htmlspecialchars($user->username); ?>
        <?php echo htmlspecialchars($user->role); ?>
        | <a href="notes.php?logout=1">Sign out</a>
    </p>
    <?php if($task === null): ?>
        <h2>Edit Task</h2>
        <form method="post">
            <?php echo csrfField(); ?>
            <input type="text" name="title" value="<?php echo $task["title"] ?? ""; ?>">
            <button name="save" value="1">Save changes</button>
        </form>
        <p><a href="notes.php">Back to tasks</a></p>
    <?php else: ?>
        <h2>Tasks</h2>
        <ul>
            <?php foreach($task as $item): ?>
                <li>
                    <?php htmlspecialchars($item["title"]); ?>
                    <?php if($item['user_id'] === $user["id"] || can_access($user, "edit any task")): ?>
                        <a href="notes.php?id= <?php echo (int) $item['id'] ?>">
                    <?php endif; ?>

                </li>
        </ul>
    <?php endif; ?>
<?php endif; ?>
</body>
</html>


