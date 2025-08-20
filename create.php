<?php
require_once 'connection.php';
$connection = new Connection();

// Lógica para processar o formulário de criação
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $colorIds = $_POST['colors'] ?? [];

    try {
        // Inicia uma transação
        $connection->getPdo()->beginTransaction();

        // 1. Insere o novo usuário
        $sqlUser = "INSERT INTO users (name, email) VALUES (?, ?)";
        $stmtUser = $connection->getPdo()->prepare($sqlUser);
        $stmtUser->execute([$name, $email]);

        // 2. Pega o ID do usuário que acabou de ser criado
        $userId = $connection->getPdo()->lastInsertId();

        // 3. Insere as cores associadas na tabela user_colors
        if (!empty($colorIds)) {
            $sqlColors = "INSERT INTO user_colors (user_id, color_id) VALUES (?, ?)";
            $stmtColors = $connection->getPdo()->prepare($sqlColors);
            foreach ($colorIds as $colorId) {
                $stmtColors->execute([$userId, $colorId]);
            }
        }

        // Se tudo deu certo, confirma a transação
        $connection->getPdo()->commit();

        // Redireciona para a página inicial
        header("Location: index.php");
        exit;

    } catch (Exception $e) {
        // Se algo deu errado, desfaz a transação
        $connection->getPdo()->rollBack();
        die("Erro ao criar usuário: " . $e->getMessage());
    }
}

// Busca todas as cores disponíveis para exibir no formulário
$colors = $connection->query("SELECT * FROM colors");

// Inclui o cabeçalho da página
require_once 'header.php';
?>

<h1>Adicionar Novo Usuário</h1>

<form action="create.php" method="POST" class="mt-4">
    <div class="mb-3">
        <label for="name" class="form-label">Nome</label>
        <input type="text" class="form-control" id="name" name="name" required>
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" required>
    </div>
    <div class="mb-3">
        <h5 class="mt-4">Cores</h5>
        <?php foreach ($colors as $color): ?>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="colors[]" value="<?php echo $color->id; ?>" id="color-<?php echo $color->id; ?>">
                <label class="form-check-label" for="color-<?php echo $color->id; ?>">
                    <?php echo htmlspecialchars($color->name); ?>
                </label>
            </div>
        <?php endforeach; ?>
    </div>
    <button type="submit" class="btn btn-success">Salvar</button>
    <a href="index.php" class="btn btn-secondary">Cancelar</a>
</form>

<?php
// Inclui o rodapé da página
require_once 'footer.php';
?>