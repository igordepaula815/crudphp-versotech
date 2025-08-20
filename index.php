<?php
// Inclui o cabeçalho da página
require_once 'header.php';
require_once 'connection.php'; // Sua conexão com o banco

// A lógica PHP para buscar os usuários continua a mesma
$connection = new Connection();
$sql = "
    SELECT
        u.id,
        u.name,
        u.email,
        GROUP_CONCAT(c.name, ', ') AS user_colors
    FROM
        users u
    LEFT JOIN
        user_colors uc ON u.id = uc.user_id
    LEFT JOIN
        colors c ON uc.color_id = c.id
    GROUP BY
        u.id
";
$users = $connection->query($sql);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Lista de Usuários</h1>
    <a href="create.php" class="btn btn-success">Adicionar Novo Usuário</a>
</div>

<table class="table table-striped table-hover table-bordered">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Email</th>
            <th>Cores</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo $user->id; ?></td>
                <td><?php echo htmlspecialchars($user->name); ?></td>
                <td><?php echo htmlspecialchars($user->email); ?></td>
                <td><?php echo htmlspecialchars($user->user_colors ?? 'Nenhuma cor'); ?></td>
                <td>
                    <a href="edit.php?id=<?php echo $user->id; ?>" class="btn btn-primary btn-sm">Editar</a>
                    <a href="delete.php?id=<?php echo $user->id; ?>" class="btn btn-danger btn-sm">Excluir</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php
// Inclui o rodapé da página
require_once 'footer.php';
?>