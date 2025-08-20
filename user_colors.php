<?php
require 'connection.php';

$connection = new Connection();
$db = $connection->getConnection();

$user_id = $_GET['id'] ?? null;

if (!$user_id) {
    header("Location: index.php");
    exit;
}

// Buscar usuário
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Usuário não encontrado.");
}

// Buscar todas as cores
$colors = $db->query("SELECT * FROM colors")->fetchAll(PDO::FETCH_ASSOC);

// Buscar cores já vinculadas ao usuário
$stmt = $db->prepare("SELECT color_id FROM user_colors WHERE user_id = ?");
$stmt->execute([$user_id]);
$user_colors = $stmt->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Receber cores selecionadas no formulário
    $selected_colors = $_POST['colors'] ?? [];

    // Remover vínculos antigos
    $stmt = $db->prepare("DELETE FROM user_colors WHERE user_id = ?");
    $stmt->execute([$user_id]);

    // Inserir novos vínculos
    foreach ($selected_colors as $color_id) {
        $stmt = $db->prepare("INSERT INTO user_colors (user_id, color_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $color_id]);
    }

    // Volta para a lista
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Vincular Cores</title>
</head>
<body>
    <h1>Vincular cores ao usuário</h1>
    <p><strong>Usuário:</strong> <?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['email']) ?>)</p>

    <form method="post">
        <?php foreach ($colors as $color): ?>
            <div>
                <label>
                    <input type="checkbox" name="colors[]" value="<?= $color['id'] ?>"
                        <?= in_array($color['id'], $user_colors) ? 'checked' : '' ?>>
                    <?= htmlspecialchars($color['name']) ?>
                </label>
            </div>
        <?php endforeach; ?>

        <br>
        <button type="submit">Salvar</button>
        <a href="index.php">Voltar</a>
    </form>
</body>
</html>
