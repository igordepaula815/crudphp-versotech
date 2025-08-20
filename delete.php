<?php
// Inclui o arquivo de conexão
require_once 'connection.php';

// Pega o ID do usuário da URL. Se não existir, interrompe o script.
$userId = $_GET['id'] ?? null;
if (!$userId) {
    header('Location: index.php');
    exit;
}

try {
    // Cria uma nova conexão
    $connection = new Connection();
    $pdo = $connection->getPdo();

    // Inicia uma transação para garantir a consistência dos dados
    $pdo->beginTransaction();

    // Passo 1: Apagar as associações de cores na tabela 'user_colors'.
    // Isso deve ser feito PRIMEIRO para manter a integridade do banco.
    $sqlDeleteColors = "DELETE FROM user_colors WHERE user_id = ?";
    $stmtDeleteColors = $pdo->prepare($sqlDeleteColors);
    $stmtDeleteColors->execute([$userId]);

    // Passo 2: Apagar o usuário da tabela 'users'.
    $sqlDeleteUser = "DELETE FROM users WHERE id = ?";
    $stmtDeleteUser = $pdo->prepare($sqlDeleteUser);
    $stmtDeleteUser->execute([$userId]);

    // Se ambas as exclusões foram bem-sucedidas, confirma a transação
    $pdo->commit();

    // Redireciona de volta para a página inicial
    header("Location: index.php");
    exit;

} catch (Exception $e) {
    // Se ocorrer qualquer erro, desfaz a transação
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Exibe uma mensagem de erro genérica e encerra o script
    die("Erro ao excluir usuário: " . $e->getMessage());
}