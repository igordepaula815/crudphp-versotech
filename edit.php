<?php
require_once 'connection.php';
$connection = new Connection();

$userId = $_GET['id'] ?? null;

// Se não houver ID, redireciona para o início
if (!$userId) {
    header('Location: index.php');
    exit;
}

// Lógica para processar o formulário de edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $colorIds = $_POST['colors'] ?? [];

    try {
        $connection->getPdo()->beginTransaction();

        // 1. Atualiza os dados do usuário na tabela 'users'
        $sqlUser = "UPDATE users SET name = ?, email = ? WHERE id = ?";
        $stmtUser = $connection->getPdo()->prepare($sqlUser);
        $stmtUser->execute([$name, $email, $userId]);

        // 2. Apaga todas as associações de cores antigas para este usuário
        $sqlDeleteColors = "DELETE FROM user_colors WHERE user_id = ?";
        $stmtDeleteColors = $connection->getPdo()->prepare($sqlDeleteColors);
        $stmtDeleteColors->execute([$userId]);

        // 3. Insere as novas associações de cores
        if (!empty($colorIds)) {
            $sqlInsertColors = "INSERT INTO user_colors (user_id, color_id) VALUES (?, ?)";
            $stmtInsertColors = $connection->getPdo()->prepare($sqlInsertColors);
            foreach ($colorIds as $colorId) {
                $stmtInsertColors->execute([$userId, $colorId]);
            }
        }

        $connection->getPdo()->commit();

        header("Location: index.php");
        exit;

    } catch (Exception $e) {
        $connection->getPdo()->rollBack();
        die("Erro ao editar usuário: " . $e->getMessage());
    }
}

// Busca os dados do usuário para preencher o formulário
$stmt = $connection->getPdo()->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_OBJ);

// Se o usuário não for encontrado, redireciona
if (!$user) {
    header('Location: index.php');
    exit;
}

// Busca todas as cores disponíveis
$allColors = $connection->query("SELECT * FROM colors");

// Busca os IDs das cores que o usuário já possui
$stmtUserColors = $connection->getPdo()->prepare("SELECT color_id FROM user_colors WHERE user_id = ?");
$stmtUserColors->execute([$userId]);
$userColorIds = $stmtUserColors->fetchAll(PDO::FETCH_COLUMN, 0);


require_once 'header.php';
?>

<h1>Editar Usuário</h1>

<form action="edit.php?id=<?php echo $user->id; ?>" method="POST" class="mt-4">
    <div class="mb-3">
        <label for="name" class="form-label">Nome</label>
        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user->name); ?>" required>
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user->email); ?>" required>
    </div>
    <div class="mb-3">
        <h5 class="mt-4">Cores</h5>
        <?php foreach ($allColors as $color): ?>
            <div class="form-check">
                <input 
                    class="form-check-input" 
                    type="checkbox" 
                    name="colors[]" 
                    value="<?php echo $color->id; ?>" 
                    id="color-<?php echo $color->id; ?>"
                    <?php if (in_array($color->id, $userColorIds)) echo 'checked'; ?>
                >
                <label class="form-check-label" for="color-<?php echo $color->id; ?>">
                    <?php echo htmlspecialchars($color->name); ?>
                </label>
            </div>
        <?php endforeach; ?>
    </div>
    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
    <a href="index.php" class="btn btn-secondary">Cancelar</a>
</form>

<?php
require_once 'footer.php';
?>